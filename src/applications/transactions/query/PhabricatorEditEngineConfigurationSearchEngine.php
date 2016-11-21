<?php

final class PhabricatorEditEngineConfigurationSearchEngine
  extends PhabricatorApplicationSearchEngine {

  private $engineKey;

  public function setEngineKey($engine_key) {
    $this->engineKey = $engine_key;
    return $this;
  }

  public function getEngineKey() {
    return $this->engineKey;
  }

  public function canUseInPanelContext() {
    return false;
  }

  public function getResultTypeDescription() {
    return pht('Mẫu');
  }

  public function getApplicationClassName() {
    return 'PhabricatorTransactionsApplication';
  }

  public function newQuery() {
    return id(new PhabricatorEditEngineConfigurationQuery())
      ->withEngineKeys(array($this->getEngineKey()));
  }

  protected function buildQueryFromParameters(array $map) {
    $query = $this->newQuery();

    $is_create = $map['isCreate'];
    if ($is_create !== null) {
      $query->withIsDefault($is_create);
    }

    $is_edit = $map['isEdit'];
    if ($is_edit !== null) {
      $query->withIsEdit($is_edit);
    }

    return $query;
  }

  protected function buildCustomSearchFields() {
    return array(
      id(new PhabricatorSearchThreeStateField())
        ->setLabel(pht('Tạo'))
        ->setKey('isCreate')
        ->setOptions(
          pht('Hiển thị tất cả'),
          pht('Ẩn các mẫu đã tạo'),
          pht('Chỉ hiển thị những mẫu đã tạo')),
      id(new PhabricatorSearchThreeStateField())
        ->setLabel(pht('Sửa'))
        ->setKey('isEdit')
        ->setOptions(
          pht('Hiển thị tất cả'),
          pht('Ẩn những mẫu đã sửa'),
          pht('Chỉ hiển thị những mẫu đã sửa')),
    );
  }

  protected function getDefaultFieldOrder() {
    return array();
  }

  protected function getURI($path) {
    return '/transactions/editengine/'.$this->getEngineKey().'/'.$path;
  }

  protected function getBuiltinQueryNames() {
    $names = array(
      'all' => pht('Tất cả các mẫu'),
      'create' => pht('Tạo mẫu'),
      'modify' => pht('Sửa mẫu'),
    );

    return $names;
  }

  public function buildSavedQueryFromBuiltin($query_key) {
    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);

    switch ($query_key) {
      case 'all':
        return $query;
      case 'create':
        return $query->setParameter('isCreate', true);
      case 'modify':
        return $query->setParameter('isEdit', true);
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  protected function renderResultList(
    array $configs,
    PhabricatorSavedQuery $query,
    array $handles) {
    assert_instances_of($configs, 'PhabricatorEditEngineConfiguration');
    $viewer = $this->requireViewer();
    $engine_key = $this->getEngineKey();

    $list = id(new PHUIObjectItemListView())
      ->setUser($viewer);
    foreach ($configs as $config) {
      $item = id(new PHUIObjectItemView())
        ->setHeader($config->getDisplayName());

      $id = $config->getID();
      if ($id) {
        $item->addIcon('fa-file-text-o bluegrey', pht('Form %d', $id));
        $key = $id;
      } else {
        $item->addIcon('fa-file-text bluegrey', pht('Builtin'));
        $key = $config->getBuiltinKey();
      }
      $item->setHref("/transactions/editengine/{$engine_key}/view/{$key}/");

      if ($config->getIsDefault()) {
        $item->addAttribute(pht('Default Create Form'));
      }

      if ($config->getIsEdit()) {
        $item->addAttribute(pht('Sửa mẫu'));
      }

      if ($config->getIsDisabled()) {
        $item->setDisabled(true);
        $item->setStatusIcon('fa-ban grey', pht('Vô hiệu hóa'));
      } else {
        $item->setStatusIcon('fa-file-text-o green', pht('Kích hoạt'));
      }

      $list->addItem($item);
    }

    return id(new PhabricatorApplicationSearchResultView())
      ->setObjectList($list);
  }
}
