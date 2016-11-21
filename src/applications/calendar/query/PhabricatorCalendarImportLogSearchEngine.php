<?php

final class PhabricatorCalendarImportLogSearchEngine
  extends PhabricatorApplicationSearchEngine {

  public function getResultTypeDescription() {
    return pht(' Lịch nhập Logs');
  }

  public function getApplicationClassName() {
    return 'PhabricatorCalendarApplication';
  }

  public function newQuery() {
    return new PhabricatorCalendarImportLogQuery();
  }

  protected function buildCustomSearchFields() {
    return array(
      id(new PhabricatorPHIDsSearchField())
        ->setLabel(pht('Nhập nguồn'))
        ->setKey('importSourcePHIDs')
        ->setAliases(array('importSourcePHID')),
    );
  }

  protected function buildQueryFromParameters(array $map) {
    $query = $this->newQuery();

    if ($map['importSourcePHIDs']) {
      $query->withImportPHIDs($map['importSourcePHIDs']);
    }

    return $query;
  }

  protected function getURI($path) {
    return '/calendar/import/log/'.$path;
  }

  protected function getBuiltinQueryNames() {
    $names = array(
      'all' => pht('Tất cả Logs'),
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
    array $logs,
    PhabricatorSavedQuery $query,
    array $handles) {

    assert_instances_of($logs, 'PhabricatorCalendarImportLog');
    $viewer = $this->requireViewer();

    $view = id(new PhabricatorCalendarImportLogView())
      ->setShowImportSources(true)
      ->setViewer($viewer)
      ->setLogs($logs);

    return id(new PhabricatorApplicationSearchResultView())
      ->setTable($view->newTable());
  }
}
