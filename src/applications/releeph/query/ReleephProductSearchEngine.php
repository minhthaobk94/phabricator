<?php

final class ReleephProductSearchEngine
  extends PhabricatorApplicationSearchEngine {

  public function getResultTypeDescription() {
    return pht('Sản phẩm releeph ');
  }

  public function getApplicationClassName() {
    return 'PhabricatorReleephApplication';
  }

  public function buildSavedQueryFromRequest(AphrontRequest $request) {
    $saved = new PhabricatorSavedQuery();

    $saved->setParameter('active', $request->getStr('active'));

    return $saved;
  }

  public function buildQueryFromSavedQuery(PhabricatorSavedQuery $saved) {
    $query = id(new ReleephProductQuery())
      ->setOrder(ReleephProductQuery::ORDER_NAME);

    $active = $saved->getParameter('active');
    $value = idx($this->getActiveValues(), $active);
    if ($value !== null) {
      $query->withActive($value);
    }

    return $query;
  }

  public function buildSearchForm(
    AphrontFormView $form,
    PhabricatorSavedQuery $saved_query) {

    $form->appendChild(
      id(new AphrontFormSelectControl())
        ->setName('active')
        ->setLabel(pht('Hiển thị sản phẩm'))
        ->setValue($saved_query->getParameter('active'))
        ->setOptions($this->getActiveOptions()));
  }

  protected function getURI($path) {
    return '/releeph/project/'.$path;
  }

  protected function getBuiltinQueryNames() {
    return array(
      'active' => pht('Kích hoạt'),
      'all' => pht('Tất cả'),
    );
  }

  public function buildSavedQueryFromBuiltin($query_key) {
    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);

    switch ($query_key) {
      case 'active':
        return $query
          ->setParameter('active', 'active');
      case 'all':
        return $query;
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  private function getActiveOptions() {
    return array(
      'all'       => pht('Kích hoạt và không kích hoạt sản phẩm'),
      'active'    => pht('Kích hoạt sản phẩm'),
      'inactive'  => pht('Ngưng kích hoạt sản phẩm'),
    );
  }

  private function getActiveValues() {
    return array(
      'all' => null,
      'active' => 1,
      'inactive' => 0,
    );
  }

  protected function renderResultList(
    array $products,
    PhabricatorSavedQuery $query,
    array $handles) {

    assert_instances_of($products, 'ReleephProject');
    $viewer = $this->requireViewer();

    $list = id(new PHUIObjectItemListView())
      ->setUser($viewer);

    foreach ($products as $product) {
      $id = $product->getID();

      $item = id(new PHUIObjectItemView())
        ->setHeader($product->getName())
        ->setHref($this->getApplicationURI("product/{$id}/"));

      if (!$product->getIsActive()) {
        $item->setDisabled(true);
        $item->addIcon('none', pht('Không kích hoạt'));
      }

      $repo = $product->getRepository();
      $item->addAttribute(
        phutil_tag(
          'a',
          array(
            'href' => $repo->getURI(),
          ),
          $repo->getMonogram()));

      $list->addItem($item);
    }

    $result = new PhabricatorApplicationSearchResultView();
    $result->setObjectList($list);

    return $result;
  }

}
