<?php

final class PhabricatorRepositorySearchEngine
  extends PhabricatorApplicationSearchEngine {

  public function getResultTypeDescription() {
    return pht('Repositories');
  }

  public function getApplicationClassName() {
    return 'PhabricatorDiffusionApplication';
  }

  public function newQuery() {
    return id(new PhabricatorRepositoryQuery())
      ->needProjectPHIDs(true)
      ->needCommitCounts(true)
      ->needMostRecentCommits(true);
  }

  protected function buildCustomSearchFields() {
    return array(
      id(new PhabricatorSearchStringListField())
        ->setLabel(pht('Callsigns'))
        ->setKey('callsigns'),
      id(new PhabricatorSearchTextField())
        ->setLabel(pht('Tên bao hàm'))
        ->setKey('name'),
      id(new PhabricatorSearchSelectField())
        ->setLabel(pht('Trạng thái'))
        ->setKey('status')
        ->setOptions($this->getStatusOptions()),
      id(new PhabricatorSearchSelectField())
        ->setLabel(pht('Hosted'))
        ->setKey('hosted')
        ->setOptions($this->getHostedOptions()),
      id(new PhabricatorSearchCheckboxesField())
        ->setLabel(pht('Loại'))
        ->setKey('types')
        ->setOptions(PhabricatorRepositoryType::getAllRepositoryTypes()),
      id(new PhabricatorSearchStringListField())
        ->setLabel(pht('URIs'))
        ->setKey('uris')
        ->setDescription(
          pht('Tìm kiếm cho các kho của clone / kiểm URI.')),
    );
  }

  protected function buildQueryFromParameters(array $map) {
    $query = $this->newQuery();

    if ($map['callsigns']) {
      $query->withCallsigns($map['callsigns']);
    }

    if ($map['status']) {
      $status = idx($this->getStatusValues(), $map['status']);
      if ($status) {
        $query->withStatus($status);
      }
    }

    if ($map['hosted']) {
      $hosted = idx($this->getHostedValues(), $map['hosted']);
      if ($hosted) {
        $query->withHosted($hosted);
      }
    }

    if ($map['types']) {
      $query->withTypes($map['types']);
    }

    if (strlen($map['name'])) {
      $query->withNameContains($map['name']);
    }

    if ($map['uris']) {
      $query->withURIs($map['uris']);
    }

    return $query;
  }

  protected function getURI($path) {
    return '/diffusion/'.$path;
  }

  protected function getBuiltinQueryNames() {
    $names = array(
      'active' => pht('Repositories hoạt động'),
      'all' => pht('Ttất cả các Repositories'),
    );

    return $names;
  }

  public function buildSavedQueryFromBuiltin($query_key) {

    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);

    switch ($query_key) {
      case 'active':
        return $query->setParameter('status', 'open');
      case 'all':
        return $query;
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  private function getStatusOptions() {
    return array(
      '' => pht('Active and Inactive Repositories'),
      'open' => pht('Active Repositories'),
      'closed' => pht('Inactive Repositories'),
    );
  }

  private function getStatusValues() {
    return array(
      '' => PhabricatorRepositoryQuery::STATUS_ALL,
      'open' => PhabricatorRepositoryQuery::STATUS_OPEN,
      'closed' => PhabricatorRepositoryQuery::STATUS_CLOSED,
    );
  }

  private function getHostedOptions() {
    return array(
      '' => pht('Hosted and Remote Repositories'),
      'phabricator' => pht('Hosted Repositories'),
      'remote' => pht('Remote Repositories'),
    );
  }

  private function getHostedValues() {
    return array(
      '' => PhabricatorRepositoryQuery::HOSTED_ALL,
      'phabricator' => PhabricatorRepositoryQuery::HOSTED_PHABRICATOR,
      'remote' => PhabricatorRepositoryQuery::HOSTED_REMOTE,
    );
  }

  protected function getRequiredHandlePHIDsForResultList(
    array $repositories,
    PhabricatorSavedQuery $query) {
    return array_mergev(mpull($repositories, 'getProjectPHIDs'));
  }

  protected function renderResultList(
    array $repositories,
    PhabricatorSavedQuery $query,
    array $handles) {
    assert_instances_of($repositories, 'PhabricatorRepository');

    $viewer = $this->requireViewer();

    $list = new PHUIObjectItemListView();
    foreach ($repositories as $repository) {
      $id = $repository->getID();

      $item = id(new PHUIObjectItemView())
        ->setUser($viewer)
        ->setObject($repository)
        ->setHeader($repository->getName())
        ->setObjectName($repository->getMonogram())
        ->setHref($repository->getURI());

      $commit = $repository->getMostRecentCommit();
      if ($commit) {
        $commit_link = phutil_tag(
          'a',
          array(
            'href' => $commit->getURI(),
          ),
          pht(
            '%s: %s',
            $commit->getLocalName(),
            $commit->getSummary()));

        $item->setSubhead($commit_link);
        $item->setEpoch($commit->getEpoch());
      }

      $item->addIcon(
        'none',
        PhabricatorRepositoryType::getNameForRepositoryType(
          $repository->getVersionControlSystem()));

      $size = $repository->getCommitCount();
      if ($size) {
        $history_uri = $repository->generateURI(
          array(
            'action' => 'history',
          ));

        $item->addAttribute(
          phutil_tag(
            'a',
            array(
              'href' => $history_uri,
            ),
            pht('%s Commit(s)', new PhutilNumber($size))));
      } else {
        $item->addAttribute(pht('No Commits'));
      }

      $project_handles = array_select_keys(
        $handles,
        $repository->getProjectPHIDs());
      if ($project_handles) {
        $item->addAttribute(
          id(new PHUIHandleTagListView())
            ->setSlim(true)
            ->setHandles($project_handles));
      }

      if (!$repository->isTracked()) {
        $item->setDisabled(true);
        $item->addIcon('disable-grey', pht('Inactive'));
      } else if ($repository->isImporting()) {
        $item->addIcon('fa-clock-o indigo', pht('Importing...'));
      }

      $list->addItem($item);
    }

    $result = new PhabricatorApplicationSearchResultView();
    $result->setObjectList($list);
    $result->setNoDataString(pht('No repositories found for this query.'));

    return $result;
  }

  protected function willUseSavedQuery(PhabricatorSavedQuery $saved) {
    $project_phids = $saved->getParameter('projectPHIDs', array());

    $old = $saved->getParameter('projects', array());
    foreach ($old as $phid) {
      $project_phids[] = $phid;
    }

    $any = $saved->getParameter('anyProjectPHIDs', array());
    foreach ($any as $project) {
      $project_phids[] = 'any('.$project.')';
    }

    $saved->setParameter('projectPHIDs', $project_phids);
  }

  protected function getNewUserBody() {

    $new_button = id(new PHUIButtonView())
      ->setTag('a')
      ->setText(pht('New Repository'))
      ->setHref('/diffusion/edit/')
      ->setColor(PHUIButtonView::GREEN);

    $icon = $this->getApplication()->getIcon();
    $app_name =  $this->getApplication()->getName();
    $view = id(new PHUIBigInfoView())
      ->setIcon($icon)
      ->setTitle(pht('Chào mừng đến với %s', $app_name))
      ->setDescription(
        pht('Nhập, tạo ra, hoặc chỉ cần trình duyệt kho trong Diffusion.'))
      ->addAction($new_button);

      return $view;
  }

}
