<?php

final class PhabricatorConduitSearchEngine
  extends PhabricatorApplicationSearchEngine {

  public function getResultTypeDescription() {
    return pht('Conduit Methods');
  }

  public function getApplicationClassName() {
    return 'PhabricatorConduitApplication';
  }

  public function getPageSize(PhabricatorSavedQuery $saved) {
    return PHP_INT_MAX - 1;
  }

  public function buildSavedQueryFromRequest(AphrontRequest $request) {
    $saved = new PhabricatorSavedQuery();

    $saved->setParameter('isStable', $request->getStr('isStable'));
    $saved->setParameter('isUnstable', $request->getStr('isUnstable'));
    $saved->setParameter('isDeprecated', $request->getStr('isDeprecated'));
    $saved->setParameter('nameContains', $request->getStr('nameContains'));

    return $saved;
  }

  public function buildQueryFromSavedQuery(PhabricatorSavedQuery $saved) {
    $query = id(new PhabricatorConduitMethodQuery());

    $query->withIsStable($saved->getParameter('isStable'));
    $query->withIsUnstable($saved->getParameter('isUnstable'));
    $query->withIsDeprecated($saved->getParameter('isDeprecated'));
    $query->withIsInternal(false);

    $contains = $saved->getParameter('nameContains');
    if (strlen($contains)) {
      $query->withNameContains($contains);
    }

    return $query;
  }

  public function buildSearchForm(
    AphrontFormView $form,
    PhabricatorSavedQuery $saved) {

    $form
      ->appendChild(
        id(new AphrontFormTextControl())
          ->setLabel(pht('Tên bao hàm'))
          ->setName('nameContains')
          ->setValue($saved->getParameter('nameContains')));

    $is_stable = $saved->getParameter('isStable');
    $is_unstable = $saved->getParameter('isUnstable');
    $is_deprecated = $saved->getParameter('isDeprecated');
    $form
      ->appendChild(
        id(new AphrontFormCheckboxControl())
          ->setLabel('Stability')
          ->addCheckbox(
            'isStable',
            1,
            hsprintf(
              '<strong>%s</strong>: %s',
              pht('Phương pháp ổn định'),
              pht('Hiện thành lập các phương pháp API với giao diện ổn định.')),
            $is_stable)
          ->addCheckbox(
            'isUnstable',
            1,
            hsprintf(
              '<strong>%s</strong>: %s',
              pht('Phương pháp không ổn định'),
              pht('Hiện phương pháp mới mà có thể thay đổi.')),
            $is_unstable)
          ->addCheckbox(
            'isDeprecated',
            1,
            hsprintf(
              '<strong>%s</strong>: %s',
              pht('Phương pháp phản đối'),
              pht(
                'Hiện phương pháp cũ sẽ được xóa trong một tương lai'.
                'phiên bản của Phabricator.')),
            $is_deprecated));
  }

  protected function getURI($path) {
    return '/conduit/'.$path;
  }

  protected function getBuiltinQueryNames() {
    return array(
      'modern' => pht('Phương pháp hiện đại'),
      'all'    => pht('Tất cả phương pháp'),
    );
  }

  public function buildSavedQueryFromBuiltin($query_key) {
    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);

    switch ($query_key) {
      case 'modern':
        return $query
          ->setParameter('isStable', true)
          ->setParameter('isUnstable', true);
      case 'all':
        return $query
          ->setParameter('isStable', true)
          ->setParameter('isUnstable', true)
          ->setParameter('isDeprecated', true);
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  protected function renderResultList(
    array $methods,
    PhabricatorSavedQuery $query,
    array $handles) {
    assert_instances_of($methods, 'ConduitAPIMethod');

    $viewer = $this->requireViewer();

    $out = array();

    $last = null;
    $list = null;
    foreach ($methods as $method) {
      $app = $method->getApplicationName();
      if ($app !== $last) {
        $last = $app;
        if ($list) {
          $out[] = $list;
        }
        $list = id(new PHUIObjectItemListView());
        $list->setHeader($app);

        $app_object = $method->getApplication();
        if ($app_object) {
          $app_name = $app_object->getName();
        } else {
          $app_name = $app;
        }
      }

      $method_name = $method->getAPIMethodName();

      $item = id(new PHUIObjectItemView())
        ->setHeader($method_name)
        ->setHref($this->getApplicationURI('method/'.$method_name.'/'))
        ->addAttribute($method->getMethodSummary());

      switch ($method->getMethodStatus()) {
        case ConduitAPIMethod::METHOD_STATUS_STABLE:
          break;
        case ConduitAPIMethod::METHOD_STATUS_UNSTABLE:
          $item->addIcon('fa-warning', pht('Không ổn định'));
          $item->setStatusIcon('fa-warning yellow');
          break;
        case ConduitAPIMethod::METHOD_STATUS_DEPRECATED:
          $item->addIcon('fa-warning', pht('Phản đối'));
          $item->setStatusIcon('fa-warning red');
          break;
      }

      $list->addItem($item);
    }

    if ($list) {
      $out[] = $list;
    }

    $result = new PhabricatorApplicationSearchResultView();
    $result->setContent($out);

    return $result;
  }

}
