<?php

final class DrydockAuthorizationSearchEngine
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
    return pht('Ủy quyền');
  }

  public function getApplicationClassName() {
    return 'PhabricatorDrydockApplication';
  }

  public function canUseInPanelContext() {
    return false;
  }

  public function newQuery() {
    $query = new DrydockAuthorizationQuery();

    $blueprint = $this->getBlueprint();
    if ($blueprint) {
      $query->withBlueprintPHIDs(array($blueprint->getPHID()));
    }

    return $query;
  }

  protected function buildQueryFromParameters(array $map) {
    $query = $this->newQuery();

    if ($map['blueprintPHIDs']) {
      $query->withBlueprintPHIDs($map['blueprintPHIDs']);
    }

    if ($map['objectPHIDs']) {
      $query->withObjectPHIDs($map['objectPHIDs']);
    }

    return $query;
  }

  protected function buildCustomSearchFields() {
    return array(
      id(new PhabricatorSearchDatasourceField())
        ->setLabel(pht('Chứng từ'))
        ->setKey('blueprintPHIDs')
        ->setConduitParameterType(new ConduitPHIDListParameterType())
        ->setDescription(pht('Tìm chứng từ ủy quyền.'))
        ->setAliases(array('blueprint', 'blueprints'))
        ->setDatasource(new DrydockBlueprintDatasource()),
      id(new PhabricatorPHIDsSearchField())
        ->setLabel(pht('Đối tượng'))
        ->setKey('objectPHIDs')
        ->setDescription(pht('Tìm theo đối tượng ủy quyền.'))
        ->setAliases(array('object', 'objects')),
    );
  }

  protected function getHiddenFields() {
    return array(
      'blueprintPHIDs',
      'objectPHIDs',
    );
  }

  protected function getURI($path) {
    $blueprint = $this->getBlueprint();
    if (!$blueprint) {
      throw new PhutilInvalidStateException('setBlueprint');
    }
    $id = $blueprint->getID();
    return "/drydock/blueprint/{$id}/authorizations/".$path;
  }

  protected function getBuiltinQueryNames() {
    return array(
      'all' => pht('Mọi ủy quyền'),
    );
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
    array $authorizations,
    PhabricatorSavedQuery $query,
    array $handles) {

    $list = id(new DrydockAuthorizationListView())
      ->setUser($this->requireViewer())
      ->setAuthorizations($authorizations);

    $result = new PhabricatorApplicationSearchResultView();
    $result->setTable($list);

    return $result;
  }

}
