<?php

final class AlmanacDeviceSearchEngine
  extends PhabricatorApplicationSearchEngine {

  public function getResultTypeDescription() {
    return pht('Thiết bị');
  }

  public function getApplicationClassName() {
    return 'PhabricatorAlmanacApplication';
  }

  public function newQuery() {
    return new AlmanacDeviceQuery();
  }

  protected function buildCustomSearchFields() {
    return array(
      id(new PhabricatorSearchTextField())
        ->setLabel(pht('Tên'))
        ->setKey('match')
        ->setDescription(pht('Tìm theo tên.')),
      id(new PhabricatorSearchStringListField())
        ->setLabel(pht('Tên chính xác'))
        ->setKey('names')
        ->setDescription(pht('Tìm theo kí tự đặc biệt.')),
    );
  }

  protected function buildQueryFromParameters(array $map) {
    $query = $this->newQuery();

    if ($map['match'] !== null) {
      $query->withNameNgrams($map['match']);
    }

    if ($map['names']) {
      $query->withNames($map['names']);
    }

    return $query;
  }

  protected function getURI($path) {
    return '/almanac/device/'.$path;
  }

  protected function getBuiltinQueryNames() {
    $names = array(
      'all' => pht('Tất cả thiết bị'),
    );

    return $names;
  }

  public function buildSavedQueryFromBuiltin($query_key) {

    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);

    switch ($query_key) {
      case 'all':
        return $query;
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  protected function renderResultList(
    array $devices,
    PhabricatorSavedQuery $query,
    array $handles) {
    assert_instances_of($devices, 'AlmanacDevice');

    $viewer = $this->requireViewer();

    $list = new PHUIObjectItemListView();
    $list->setUser($viewer);
    foreach ($devices as $device) {
      $item = id(new PHUIObjectItemView())
        ->setObjectName(pht('Thiết bị %d', $device->getID()))
        ->setHeader($device->getName())
        ->setHref($device->getURI())
        ->setObject($device);

      if ($device->isClusterDevice()) {
        $item->addIcon('fa-sitemap', pht('Cụm thiết bị'));
      }

      $list->addItem($item);
    }

    $result = new PhabricatorApplicationSearchResultView();
    $result->setObjectList($list);
    $result->setNoDataString(pht('Không tìm thấy thiết bị.'));

    return $result;
  }

}
