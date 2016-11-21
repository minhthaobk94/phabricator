<?php

final class PhabricatorProjectSearchEngine
  extends PhabricatorApplicationSearchEngine {

  public function getResultTypeDescription() {
    return pht('Dự án');
  }

  public function getApplicationClassName() {
    return 'PhabricatorProjectApplication';
  }

  public function newQuery() {
    return id(new PhabricatorProjectQuery())
      ->needImages(true)
      ->withIsMilestone(false);
  }

  protected function buildCustomSearchFields() {
    return array(
      id(new PhabricatorSearchTextField())
        ->setLabel(pht('Tên'))
        ->setKey('name'),
      id(new PhabricatorUsersSearchField())
        ->setLabel(pht('Thành viên'))
        ->setKey('memberPHIDs')
        ->setAliases(array('member', 'members')),
      id(new PhabricatorUsersSearchField())
        ->setLabel(pht('Người xem'))
        ->setKey('watcherPHIDs')
        ->setAliases(array('watcher', 'watchers')),
      id(new PhabricatorSearchSelectField())
        ->setLabel(pht('Trạng thái'))
        ->setKey('status')
        ->setOptions($this->getStatusOptions()),
      id(new PhabricatorSearchCheckboxesField())
        ->setLabel(pht('Biểu tượng'))
        ->setKey('icons')
        ->setOptions($this->getIconOptions()),
      id(new PhabricatorSearchCheckboxesField())
        ->setLabel(pht('Màu sắc'))
        ->setKey('colors')
        ->setOptions($this->getColorOptions()),
    );
  }


  protected function buildQueryFromParameters(array $map) {
    $query = $this->newQuery();

    if (strlen($map['name'])) {
      $tokens = PhabricatorTypeaheadDatasource::tokenizeString($map['name']);
      $query->withNameTokens($tokens);
    }

    if ($map['memberPHIDs']) {
      $query->withMemberPHIDs($map['memberPHIDs']);
    }

    if ($map['watcherPHIDs']) {
      $query->withWatcherPHIDs($map['watcherPHIDs']);
    }

    if ($map['status']) {
      $status = idx($this->getStatusValues(), $map['status']);
      if ($status) {
        $query->withStatus($status);
      }
    }

    if ($map['icons']) {
      $query->withIcons($map['icons']);
    }

    if ($map['colors']) {
      $query->withColors($map['colors']);
    }

    return $query;
  }

  protected function getURI($path) {
    return '/project/'.$path;
  }

  protected function getBuiltinQueryNames() {
    $names = array();

    if ($this->requireViewer()->isLoggedIn()) {
      $names['joined'] = pht('Đã tham gia');
    }

    $names['active'] = pht('Hoạt động');
    $names['all'] = pht('Tất cả');

    return $names;
  }

  public function buildSavedQueryFromBuiltin($query_key) {
    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);

    $viewer_phid = $this->requireViewer()->getPHID();

    switch ($query_key) {
      case 'all':
        return $query;
      case 'active':
        return $query
          ->setParameter('status', 'active');
      case 'joined':
        return $query
          ->setParameter('memberPHIDs', array($viewer_phid))
          ->setParameter('status', 'active');
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  private function getStatusOptions() {
    return array(
      'active'   => pht('Hiện dự án Chỉ kích hoạt'),
      'archived' => pht('Hiện dự án Chỉ lưu trữ'),
      'all'      => pht('Hiển thị tất cả các dự án'),
    );
  }

  private function getStatusValues() {
    return array(
      'active'   => PhabricatorProjectQuery::STATUS_ACTIVE,
      'archived' => PhabricatorProjectQuery::STATUS_ARCHIVED,
      'all'      => PhabricatorProjectQuery::STATUS_ANY,
    );
  }

  private function getIconOptions() {
    $options = array();

    $set = new PhabricatorProjectIconSet();
    foreach ($set->getIcons() as $icon) {
      if ($icon->getIsDisabled()) {
        continue;
      }

      $options[$icon->getKey()] = array(
        id(new PHUIIconView())
          ->setIcon($icon->getIcon()),
        ' ',
        $icon->getLabel(),
      );
    }

    return $options;
  }

  private function getColorOptions() {
    $options = array();

    foreach (PhabricatorProjectIconSet::getColorMap() as $color => $name) {
      $options[$color] = array(
        id(new PHUITagView())
          ->setType(PHUITagView::TYPE_SHADE)
          ->setShade($color)
          ->setName($name),
      );
    }

    return $options;
  }

  protected function renderResultList(
    array $projects,
    PhabricatorSavedQuery $query,
    array $handles) {
    assert_instances_of($projects, 'PhabricatorProject');
    $viewer = $this->requireViewer();

    $list = id(new PhabricatorProjectListView())
      ->setUser($viewer)
      ->setProjects($projects)
      ->renderList();

    return id(new PhabricatorApplicationSearchResultView())
      ->setObjectList($list)
      ->setNoDataString(pht('Không tìm thấy dự án.'));
  }

  protected function getNewUserBody() {
    $create_button = id(new PHUIButtonView())
      ->setTag('a')
      ->setText(pht('Tạo dự án'))
      ->setHref('/project/edit/')
      ->setColor(PHUIButtonView::GREEN);

    $icon = $this->getApplication()->getIcon();
    $app_name =  $this->getApplication()->getName();
    $view = id(new PHUIBigInfoView())
      ->setIcon($icon)
      ->setTitle(pht('Chào mừng đến với %s', $app_name))
      ->setDescription(
        pht('Projects are flexible storage containers used as '.
            'tags, teams, projects, or anything you need to group.'))
      ->addAction($create_button);

      return $view;
  }

}
