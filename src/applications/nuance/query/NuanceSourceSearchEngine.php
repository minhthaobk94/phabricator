<?php

final class NuanceSourceSearchEngine
  extends PhabricatorApplicationSearchEngine {

  public function getApplicationClassName() {
    return 'PhabricatorNuanceApplication';
  }

  public function getResultTypeDescription() {
    return pht('Mã nguồn');
  }

  public function newQuery() {
    return new NuanceSourceQuery();
  }

  protected function buildQueryFromParameters(array $map) {
    $query = $this->newQuery();

    if ($map['match'] !== null) {
      $query->withNameNgrams($map['match']);
    }

    return $query;
  }

  protected function buildCustomSearchFields() {
    return array(
      id(new PhabricatorSearchTextField())
        ->setLabel(pht('Tên'))
        ->setKey('match')
        ->setDescription(pht('Tìm mã nguồn theo tên.')),
    );
  }

  protected function getURI($path) {
    return '/nuance/source/'.$path;
  }

  protected function getBuiltinQueryNames() {
    $names = array(
      'all' => pht('Tất cả mã nguồn'),
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
    array $sources,
    PhabricatorSavedQuery $query,
    array $handles) {
    assert_instances_of($sources, 'NuanceSource');

    $viewer = $this->requireViewer();

    $list = new PHUIObjectItemListView();
    $list->setUser($viewer);
    foreach ($sources as $source) {
      $item = id(new PHUIObjectItemView())
        ->setObjectName(pht('Mã nguồn %d', $source->getID()))
        ->setHeader($source->getName())
        ->setHref($source->getURI());

      $item->addIcon('none', $source->getType());

      $list->addItem($item);
    }

    $result = new PhabricatorApplicationSearchResultView();
    $result->setObjectList($list);
    $result->setNoDataString(pht('Không có mã nguồn nào được tìm thấy.'));

    return $result;
  }

}
