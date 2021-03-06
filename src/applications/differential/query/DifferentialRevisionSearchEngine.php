<?php

final class DifferentialRevisionSearchEngine
  extends PhabricatorApplicationSearchEngine {

  public function getResultTypeDescription() {
    return pht('Phiên bản khác nhau');
  }

  public function getApplicationClassName() {
    return 'PhabricatorDifferentialApplication';
  }

  protected function newResultBuckets() {
    return DifferentialRevisionResultBucket::getAllResultBuckets();
  }

  public function newQuery() {
    return id(new DifferentialRevisionQuery())
      ->needFlags(true)
      ->needDrafts(true)
      ->needRelationships(true)
      ->needReviewerStatus(true);
  }

  protected function buildQueryFromParameters(array $map) {
    $query = $this->newQuery();

    if ($map['responsiblePHIDs']) {
      $query->withResponsibleUsers($map['responsiblePHIDs']);
    }

    if ($map['authorPHIDs']) {
      $query->withAuthors($map['authorPHIDs']);
    }

    if ($map['reviewerPHIDs']) {
      $query->withReviewers($map['reviewerPHIDs']);
    }

    if ($map['repositoryPHIDs']) {
      $query->withRepositoryPHIDs($map['repositoryPHIDs']);
    }

    if ($map['status']) {
      $query->withStatus($map['status']);
    }

    return $query;
  }

  protected function buildCustomSearchFields() {
    return array(
      id(new PhabricatorSearchDatasourceField())
        ->setLabel(pht('Người chịu trách nhiệm'))
        ->setKey('responsiblePHIDs')
        ->setAliases(array('responsiblePHID', 'responsibles', 'responsible'))
        ->setDatasource(new DifferentialResponsibleDatasource())
        ->setDescription(
          pht('Tìm phiên bản mà người dùng chịu trách nhiệm.')),
      id(new PhabricatorUsersSearchField())
        ->setLabel(pht('Authors'))
        ->setKey('authorPHIDs')
        ->setAliases(array('author', 'authors', 'authorPHID'))
        ->setDescription(
          pht('Tìm phiên bản có tác giả kahsc.')),
      id(new PhabricatorSearchDatasourceField())
        ->setLabel(pht('Reviewers'))
        ->setKey('reviewerPHIDs')
        ->setAliases(array('reviewer', 'reviewers', 'reviewerPHID'))
        ->setDatasource(new DiffusionAuditorFunctionDatasource())
        ->setDescription(
          pht('Tìm phienen bản có người đánh khác .')),
      id(new PhabricatorSearchDatasourceField())
        ->setLabel(pht('Chịu trách nhiệm'))
        ->setKey('repositoryPHIDs')
        ->setAliases(array('repository', 'repositories', 'repositoryPHID'))
        ->setDatasource(new DifferentialRepositoryDatasource())
        ->setDescription(
          pht('Tìm phiên bản của người chịu trách nhiệm khác.')),
      id(new PhabricatorSearchSelectField())
        ->setLabel(pht('Trạng thái'))
        ->setKey('status')
        ->setOptions($this->getStatusOptions())
        ->setDescription(
          pht('Tìm bằng trạng thái.')),
    );
  }

  protected function getURI($path) {
    return '/differential/'.$path;
  }

  protected function getBuiltinQueryNames() {
    $names = array();

    if ($this->requireViewer()->isLoggedIn()) {
      $names['active'] = pht('Active Revisions');
      $names['authored'] = pht('Authored');
    }

    $names['all'] = pht('All Revisions');

    return $names;
  }

  public function buildSavedQueryFromBuiltin($query_key) {
    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);

    $viewer = $this->requireViewer();

    switch ($query_key) {
      case 'active':
        $bucket_key = DifferentialRevisionRequiredActionResultBucket::BUCKETKEY;

        return $query
          ->setParameter('responsiblePHIDs', array($viewer->getPHID()))
          ->setParameter('status', DifferentialRevisionQuery::STATUS_OPEN)
          ->setParameter('bucket', $bucket_key);
      case 'authored':
        return $query
          ->setParameter('authorPHIDs', array($viewer->getPHID()));
      case 'all':
        return $query;
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  private function getStatusOptions() {
    return array(
      DifferentialRevisionQuery::STATUS_ANY            => pht('Tất cả'),
      DifferentialRevisionQuery::STATUS_OPEN           => pht('Mở'),
      DifferentialRevisionQuery::STATUS_ACCEPTED       => pht('Cho phép'),
      DifferentialRevisionQuery::STATUS_NEEDS_REVIEW   => pht('Cần đánh giá'),
      DifferentialRevisionQuery::STATUS_NEEDS_REVISION => pht('Cần phiên bản'),
      DifferentialRevisionQuery::STATUS_CLOSED         => pht('Đóng'),
      DifferentialRevisionQuery::STATUS_ABANDONED      => pht('bị bỏ rơi'),
    );
  }

  protected function renderResultList(
    array $revisions,
    PhabricatorSavedQuery $query,
    array $handles) {
    assert_instances_of($revisions, 'DifferentialRevision');

    $viewer = $this->requireViewer();
    $template = id(new DifferentialRevisionListView())
      ->setUser($viewer)
      ->setNoBox($this->isPanelContext());

    $bucket = $this->getResultBucket($query);

    $unlanded = $this->loadUnlandedDependencies($revisions);

    $views = array();
    if ($bucket) {
      $bucket->setViewer($viewer);

      try {
        $groups = $bucket->newResultGroups($query, $revisions);

        foreach ($groups as $group) {
          $views[] = id(clone $template)
            ->setHeader($group->getName())
            ->setNoDataString($group->getNoDataString())
            ->setRevisions($group->getObjects());
        }
      } catch (Exception $ex) {
        $this->addError($ex->getMessage());
      }
    } else {
      $views[] = id(clone $template)
        ->setRevisions($revisions)
        ->setHandles(array());
    }

    $phids = array_mergev(mpull($views, 'getRequiredHandlePHIDs'));
    if ($phids) {
      $handles = id(new PhabricatorHandleQuery())
        ->setViewer($viewer)
        ->withPHIDs($phids)
        ->execute();
    } else {
      $handles = array();
    }

    foreach ($views as $view) {
      $view->setHandles($handles);
      $view->setUnlandedDependencies($unlanded);
    }

    if (count($views) == 1) {
      // Reduce this to a PHUIObjectItemListView so we can get the free
      // support from ApplicationSearch.
      $list = head($views)->render();
    } else {
      $list = $views;
    }

    $result = new PhabricatorApplicationSearchResultView();
    $result->setContent($list);

    return $result;
  }

  protected function getNewUserBody() {
    $create_button = id(new PHUIButtonView())
      ->setTag('a')
      ->setText(pht('Create a Diff'))
      ->setHref('/differential/diff/create/')
      ->setColor(PHUIButtonView::GREEN);

    $icon = $this->getApplication()->getIcon();
    $app_name =  $this->getApplication()->getName();
    $view = id(new PHUIBigInfoView())
      ->setIcon($icon)
      ->setTitle(pht('Welcome to %s', $app_name))
      ->setDescription(
        pht('Pre-commit code review. Revisions that are waiting on your input '.
            'will appear here.'))
      ->addAction($create_button);

      return $view;
  }

  private function loadUnlandedDependencies(array $revisions) {
    $status_accepted = ArcanistDifferentialRevisionStatus::ACCEPTED;

    $phids = array();
    foreach ($revisions as $revision) {
      if ($revision->getStatus() != $status_accepted) {
        continue;
      }

      $phids[] = $revision->getPHID();
    }

    if (!$phids) {
      return array();
    }

    $query = id(new PhabricatorEdgeQuery())
      ->withSourcePHIDs($phids)
      ->withEdgeTypes(
        array(
          DifferentialRevisionDependsOnRevisionEdgeType::EDGECONST,
        ));

    $query->execute();

    $revision_phids = $query->getDestinationPHIDs();
    if (!$revision_phids) {
      return array();
    }

    $viewer = $this->requireViewer();

    $blocking_revisions = id(new DifferentialRevisionQuery())
      ->setViewer($viewer)
      ->withPHIDs($revision_phids)
      ->withStatus(DifferentialRevisionQuery::STATUS_OPEN)
      ->execute();
    $blocking_revisions = mpull($blocking_revisions, null, 'getPHID');

    $result = array();
    foreach ($revisions as $revision) {
      $revision_phid = $revision->getPHID();
      $blocking_phids = $query->getDestinationPHIDs(array($revision_phid));
      $blocking = array_select_keys($blocking_revisions, $blocking_phids);
      if ($blocking) {
        $result[$revision_phid] = $blocking;
      }
    }

    return $result;
  }

}
