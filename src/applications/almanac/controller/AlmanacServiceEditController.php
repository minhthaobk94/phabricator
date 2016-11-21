<?php

final class AlmanacServiceEditController
  extends AlmanacServiceController {

  public function handleRequest(AphrontRequest $request) {
    $engine = id(new AlmanacServiceEditEngine())
      ->setController($this);

    $id = $request->getURIData('id');
    if (!$id) {
      $this->requireApplicationCapability(
        AlmanacCreateServicesCapability::CAPABILITY);

      $list_uri = $this->getApplicationURI('service/');

      $service_type = $request->getStr('serviceType');
      $service_types = AlmanacServiceType::getAllServiceTypes();
      if (empty($service_types[$service_type])) {
        return $this->buildServiceTypeResponse($list_uri);
      }

      $engine
        ->addContextParameter('serviceType', $service_type)
        ->setServiceType($service_type);
    }

    return $engine->buildResponse();
  }

  private function buildServiceTypeResponse($cancel_uri) {
    $service_types = AlmanacServiceType::getAllServiceTypes();

    $request = $this->getRequest();
    $viewer = $this->getViewer();

    $e_service = null;
    $errors = array();
    if ($request->isFormPost()) {
      $e_service = pht('Bắt buộc');
      $errors[] = pht(
        'Để tạo mới một dịch vụ, bạn phải chọn lọai dịch vụ.');
    }

    list($can_cluster, $cluster_link) = $this->explainApplicationCapability(
      AlmanacManageClusterServicesCapability::CAPABILITY,
      pht('Bạn có quyền tạo mới cụm dịch vụ.'),
      pht('Bạn không có quyền tạo mới cụm dịch vụ.'));

    $type_control = id(new AphrontFormRadioButtonControl())
      ->setLabel(pht('Loại dịch vụ'))
      ->setName('serviceType')
      ->setError($e_service);

    foreach ($service_types as $service_type) {
      $is_cluster = $service_type->isClusterServiceType();
      $is_disabled = ($is_cluster && !$can_cluster);

      if ($is_cluster) {
        $extra = $cluster_link;
      } else {
        $extra = null;
      }

      $type_control->addButton(
        $service_type->getServiceTypeConstant(),
        $service_type->getServiceTypeName(),
        array(
          $service_type->getServiceTypeDescription(),
          $extra,
        ),
        $is_disabled ? 'disabled' : null,
        $is_disabled);
    }

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->addTextCrumb(pht('Tạo mới dịch vụ'));
    $crumbs->setBorder(true);

    $title = pht('Chọn loại dịch vụ');
    $header = id(new PHUIHeaderView())
      ->setHeader(pht('Tạo mới'))
      ->setHeaderIcon('fa-plus-square');

    $form = id(new AphrontFormView())
      ->setUser($viewer)
      ->appendChild($type_control)
      ->appendChild(
          id(new AphrontFormSubmitControl())
            ->setValue(pht('Tiếp tục'))
            ->addCancelButton($cancel_uri));

    $box = id(new PHUIObjectBoxView())
      ->setFormErrors($errors)
      ->setHeaderText(pht('Dịch vụ'))
      ->setBackground(PHUIObjectBoxView::BLUE_PROPERTY)
      ->setForm($form);

    $view = id(new PHUITwoColumnView())
      ->setHeader($header)
      ->setFooter(array(
        $box,
      ));

    return $this->newPage()
      ->setTitle($title)
      ->setCrumbs($crumbs)
      ->appendChild($view);

  }

}
