<?php

final class PhabricatorDashboardQueryPanelType
  extends PhabricatorDashboardPanelType {

  public function getPanelTypeKey() {
    return 'query';
  }

  public function getPanelTypeName() {
    return pht('Câu lệnh truy vấn');
  }

  public function getPanelTypeDescription() {
    return pht(
      'Hiển thị kết quả tìm kiếm, kết quả gần đây nhất '.
      'phiên bản cần phải xem xét.');
  }

  public function getFieldSpecifications() {
    return array(
      'class' => array(
        'name' => pht('Tìm kiếm'),
        'type' => 'search.application',
      ),
      'key' => array(
        'name' => pht('Câu lệnh truy vấn'),
        'type' => 'search.query',
        'control.application' => 'class',
      ),
      'limit' => array(
        'name' => pht('Giới hạn'),
        'caption' => pht('rời khỏi khoảng trống này đến sôs măc định của các mục'),
        'type' => 'text',
      ),
    );
  }

  public function initializeFieldsFromRequest(
    PhabricatorDashboardPanel $panel,
    PhabricatorCustomFieldList $field_list,
    AphrontRequest $request) {

    $map = array();
    if (strlen($request->getStr('engine'))) {
      $map['class'] = $request->getStr('engine');
    }

    if (strlen($request->getStr('query'))) {
      $map['key'] = $request->getStr('query');
    }

    $full_map = array();
    foreach ($map as $key => $value) {
      $full_map["std:dashboard:core:{$key}"] = $value;
    }

    foreach ($field_list->getFields() as $field) {
      $field_key = $field->getFieldKey();
      if (isset($full_map[$field_key])) {
        $field->setValueFromStorage($full_map[$field_key]);
      }
    }
  }

  public function renderPanelContent(
    PhabricatorUser $viewer,
    PhabricatorDashboardPanel $panel,
    PhabricatorDashboardPanelRenderingEngine $engine) {

    $engine = $this->getSearchEngine($panel);

    $engine->setViewer($viewer);
    $engine->setContext(PhabricatorApplicationSearchEngine::CONTEXT_PANEL);

    $key = $panel->getProperty('key');
    if ($engine->isBuiltinQuery($key)) {
      $saved = $engine->buildSavedQueryFromBuiltin($key);
    } else {
      $saved = id(new PhabricatorSavedQueryQuery())
        ->setViewer($viewer)
        ->withEngineClassNames(array(get_class($engine)))
        ->withQueryKeys(array($key))
        ->executeOne();
    }

    if (!$saved) {
      throw new Exception(
        pht(
          'Query "%s" không được tìm thấy "%s"!',
          $key,
          get_class($engine)));
    }

    $query = $engine->buildQueryFromSavedQuery($saved);
    $pager = $engine->newPagerForSavedQuery($saved);

    if ($panel->getProperty('limit')) {
      $limit = (int)$panel->getProperty('limit');
      if ($pager->getPageSize() !== 0xFFFF) {
        $pager->setPageSize($limit);
      }
    }

    $results = $engine->executeQuery($query, $pager);

    return $engine->renderResults($results, $saved);
  }

  public function adjustPanelHeader(
    PhabricatorUser $viewer,
    PhabricatorDashboardPanel $panel,
    PhabricatorDashboardPanelRenderingEngine $engine,
    PHUIHeaderView $header) {

    $search_engine = $this->getSearchEngine($panel);
    $key = $panel->getProperty('key');
    $href = $search_engine->getQueryResultsPageURI($key);
    $icon = id(new PHUIIconView())
        ->setIcon('fa-search')
        ->setHref($href);
    $header->addActionItem($icon);

    return $header;
  }

  private function getSearchEngine(PhabricatorDashboardPanel $panel) {
    $class = $panel->getProperty('class');
    $engine = PhabricatorApplicationSearchEngine::getEngineByClassName($class);
    if (!$engine) {
      throw new Exception(
        pht(
          'Ứng dụng "%s" không được timf thấy!',
          $class));
    }

    if (!$engine->canUseInPanelContext()) {
      throw new Exception(
        pht(
          'ứng dụng tìm kiếm  "%s" không thể sử dụng để xay dựng '.
          'dashboard panels.',
          $class));
    }

    return $engine;
  }

}
