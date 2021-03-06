<?php

final class DrydockResourceSearchEngine
  extends PhabricatorApplicationSearchEngine {

  private $blueprint;

  public function setBlueprint(DrydockBlueprint $blueprint) {
    $this->blueprint = $blueprint;
    return $this;
  }

  public function getBlueprint() {
    return $this->blueprint;
  }

  public function getResultTypeDescription() {
    return pht('Tài nguyên');
  }

  public function getApplicationClassName() {
    return 'PhabricatorDrydockApplication';
  }

  public function newQuery() {
    $query = new DrydockResourceQuery();

    $blueprint = $this->getBlueprint();
    if ($blueprint) {
      $query->withBlueprintPHIDs(array($blueprint->getPHID()));
    }

    return $query;
  }

  protected function buildQueryFromParameters(array $map) {
    $query = $this->newQuery();

    if ($map['statuses']) {
      $query->withStatuses($map['statuses']);
    }

    return $query;
  }

  protected function buildCustomSearchFields() {
    return array(
      id(new PhabricatorSearchCheckboxesField())
        ->setLabel(pht('Trạng thái'))
        ->setKey('statuses')
        ->setOptions(DrydockResourceStatus::getStatusMap()),
    );
  }

  protected function getURI($path) {
    $blueprint = $this->getBlueprint();
    if ($blueprint) {
      $id = $blueprint->getID();
      return "/drydock/blueprint/{$id}/resources/".$path;
    } else {
      return '/drydock/resource/'.$path;
    }
  }

  protected function getBuiltinQueryNames() {
    return array(
      'active' => pht('Tài nguyên được kích hoạt'),
      'all' => pht('Tất cả tài nguyên'),
    );
  }

  public function buildSavedQueryFromBuiltin($query_key) {
    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);

    switch ($query_key) {
      case 'active':
        return $query->setParameter(
          'statuses',
          array(
            DrydockResourceStatus::STATUS_PENDING,
            DrydockResourceStatus::STATUS_ACTIVE,
          ));
      case 'all':
        return $query;
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  protected function renderResultList(
    array $resources,
    PhabricatorSavedQuery $query,
    array $handles) {

    $list = id(new DrydockResourceListView())
      ->setUser($this->requireViewer())
      ->setResources($resources);

    $result = new PhabricatorApplicationSearchResultView();
    $result->setTable($list);

    return $result;
  }

}
