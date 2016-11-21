<?php

final class PhabricatorSpacesNamespaceSearchEngine
  extends PhabricatorApplicationSearchEngine {

  public function getApplicationClassName() {
    return 'PhabricatorSpacesApplication';
  }

  public function getResultTypeDescription() {
    return pht('Không gian');
  }

  public function newQuery() {
    return new PhabricatorSpacesNamespaceQuery();
  }

  protected function buildCustomSearchFields() {
    return array(
      id(new PhabricatorSearchThreeStateField())
        ->setLabel(pht('Kích hoạt'))
        ->setKey('active')
        ->setOptions(
          pht('(Hiển thị tất cả)'),
          pht('Chỉ hiển thị các không gian được kịch hoạt'),
          pht('Ẩn các không gian được kích hoạt')),
    );
  }

  protected function buildQueryFromParameters(array $map) {
    $query = $this->newQuery();

    if ($map['active']) {
      $query->withIsArchived(!$map['active']);
    }

    return $query;
  }

  protected function getURI($path) {
    return '/spaces/'.$path;
  }

  protected function getBuiltinQueryNames() {
    $names = array(
      'active' => pht('Các không gian được kích hoạt'),
      'all' => pht('Tất cả các không gian'),
    );

    return $names;
  }

  public function buildSavedQueryFromBuiltin($query_key) {
    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);

    switch ($query_key) {
      case 'active':
        return $query->setParameter('active', true);
      case 'all':
        return $query;
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  protected function renderResultList(
    array $spaces,
    PhabricatorSavedQuery $query,
    array $handles) {
    assert_instances_of($spaces, 'PhabricatorSpacesNamespace');

    $viewer = $this->requireViewer();

    $list = new PHUIObjectItemListView();
    $list->setUser($viewer);
    foreach ($spaces as $space) {
      $item = id(new PHUIObjectItemView())
        ->setObjectName($space->getMonogram())
        ->setHeader($space->getNamespaceName())
        ->setHref('/'.$space->getMonogram());

      if ($space->getIsDefaultNamespace()) {
        $item->addIcon('fa-certificate', pht('Default Space'));
      }

      if ($space->getIsArchived()) {
        $item->setDisabled(true);
      }

      $list->addItem($item);
    }

    $result = new PhabricatorApplicationSearchResultView();
    $result->setObjectList($list);
    $result->setNoDataString(pht('Không có không gian được tìm thấy.'));

    return $result;
  }

  protected function getNewUserBody() {
    $create_button = id(new PHUIButtonView())
      ->setTag('a')
      ->setText(pht('Tạo một không gian'))
      ->setHref('/spaces/create/')
      ->setColor(PHUIButtonView::GREEN);

    $icon = $this->getApplication()->getIcon();
    $app_name =  $this->getApplication()->getName();
    $view = id(new PHUIBigInfoView())
      ->setIcon($icon)
      ->setTitle(pht('Chào mừng đến với %s', $app_name))
      ->setDescription(
        pht('Policy namespaces to segment object visibility throughout your '.
        'instance.'))
      ->addAction($create_button);

      return $view;
  }

}
