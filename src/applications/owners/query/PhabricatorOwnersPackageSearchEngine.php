<?php

final class PhabricatorOwnersPackageSearchEngine
  extends PhabricatorApplicationSearchEngine {

  public function getResultTypeDescription() {
<<<<<<< HEAD
    return pht('Chủ tệp');
=======
    return pht('Gói');
>>>>>>> origin/master
  }

  public function getApplicationClassName() {
    return 'PhabricatorOwnersApplication';ủ
  }

  public function newQuery() {
    return new PhabricatorOwnersPackageQuery();
  }

  protected function buildCustomSearchFields() {
    return array(
      id(new PhabricatorSearchDatasourceField())
        ->setLabel(pht('Tác giả'))
        ->setKey('authorityPHIDs')
        ->setAliases(array('authority', 'authorities'))
        ->setConduitKey('owners')
        ->setDescription(
          pht('Tìm gói theo tác giả.'))
        ->setDatasource(new PhabricatorProjectOrUserDatasource()),
      id(new PhabricatorSearchTextField())
        ->setLabel(pht('Tên'))
        ->setKey('name')
        ->setDescription(pht('Tìm gói theo tên.')),
      id(new PhabricatorSearchDatasourceField())
        ->setLabel(pht('Repositories'))
        ->setKey('repositoryPHIDs')
        ->setConduitKey('repositories')
        ->setAliases(array('repository', 'repositories'))
        ->setDescription(
          pht('Tìm gói theo repositories.'))
        ->setDatasource(new DiffusionRepositoryDatasource()),
      id(new PhabricatorSearchStringListField())
        ->setLabel(pht('Đường dẫn'))
        ->setKey('paths')
        ->setAliases(array('path'))
        ->setDescription(
          pht('Tìm gói theo đường dẫn.')),
      id(new PhabricatorSearchCheckboxesField())
        ->setKey('statuses')
        ->setLabel(pht('Trạng thái'))
        ->setDescription(
          pht('Tìm theo gói nén.'))
        ->setOptions(
          id(new PhabricatorOwnersPackage())
            ->getStatusNameMap()),
    );
  }

  protected function buildQueryFromParameters(array $map) {
    $query = $this->newQuery();

    if ($map['authorityPHIDs']) {
      $query->withAuthorityPHIDs($map['authorityPHIDs']);
    }

    if ($map['repositoryPHIDs']) {
      $query->withRepositoryPHIDs($map['repositoryPHIDs']);
    }

    if ($map['paths']) {
      $query->withPaths($map['paths']);
    }

    if ($map['statuses']) {
      $query->withStatuses($map['statuses']);
    }

    if (strlen($map['name'])) {
      $query->withNameNgrams($map['name']);
    }

    return $query;
  }

  protected function getURI($path) {
    return '/owners/'.$path;
  }

  protected function getBuiltinQueryNames() {
    $names = array();

    if ($this->requireViewer()->isLoggedIn()) {
      $names['authority'] = pht('Chủ');
    }

    $names += array(
      'active' => pht('Gói được kích hoạt'),
      'all' => pht('Tất cả các gói'),
    );

    return $names;
  }

  public function buildSavedQueryFromBuiltin($query_key) {
    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);

    switch ($query_key) {
      case 'all':
        return $query;
      case 'active':
        return $query->setParameter(
          'statuses',
          array(
            PhabricatorOwnersPackage::STATUS_ACTIVE,
          ));
      case 'authority':
        return $query->setParameter(
          'authorityPHIDs',
          array($this->requireViewer()->getPHID()));
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  protected function renderResultList(
    array $packages,
    PhabricatorSavedQuery $query,
    array $handles) {
    assert_instances_of($packages, 'PhabricatorOwnersPackage');

    $viewer = $this->requireViewer();

    $list = id(new PHUIObjectItemListView())
      ->setUser($viewer);
    foreach ($packages as $package) {
      $id = $package->getID();

      $item = id(new PHUIObjectItemView())
        ->setObject($package)
        ->setObjectName($package->getMonogram())
        ->setHeader($package->getName())
        ->setHref($package->getURI());

      if ($package->isArchived()) {
        $item->setDisabled(true);
      }

      $list->addItem($item);
    }

    $result = new PhabricatorApplicationSearchResultView();
    $result->setObjectList($list);
    $result->setNoDataString(pht('Không có gói nào được tìm thấy.'));

    return $result;

  }

  protected function getNewUserBody() {
    $create_button = id(new PHUIButtonView())
      ->setTag('a')
      ->setText(pht('Tạo gói'))
      ->setHref('/owners/edit/')
      ->setColor(PHUIButtonView::GREEN);

    $icon = $this->getApplication()->getIcon();
    $app_name =  $this->getApplication()->getName();
    $view = id(new PHUIBigInfoView())
      ->setIcon($icon)
      ->setTitle(pht('Chào mừng đến với %s', $app_name))
      ->setDescription(
        pht('Một nhóm mã nguồn gồm các gói có thể sử dụng lại được.'))
      ->addAction($create_button);

      return $view;
  }

}
