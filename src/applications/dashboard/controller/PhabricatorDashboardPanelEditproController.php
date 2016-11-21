<?php

final class PhabricatorDashboardPanelEditproController
  extends PhabricatorDashboardController {

  public function handleRequest(AphrontRequest $request) {
    $engine = id(new PhabricatorDashboardPanelEditEngine())
      ->setController($this);

    $id = $request->getURIData('id');
    if (!$id) {
      $list_uri = $this->getApplicationURI('panel/');

      $panel_type = $request->getStr('panelType');
      $panel_types = PhabricatorDashboardPanelType::getAllPanelTypes();
      if (empty($panel_types[$panel_type])) {
        return $this->buildPanelTypeResponse($list_uri);
      }

      $engine
        ->addContextParameter('panelType', $panel_type)
        ->setPanelType($panel_type);
    }

    return $engine->buildResponse();
  }

  private function buildPanelTypeResponse($cancel_uri) {
    $panel_types = PhabricatorDashboardPanelType::getAllPanelTypes();

    $viewer = $this->getViewer();
    $request = $this->getRequest();

    $e_type = null;
    $errors = array();
    if ($request->isFormPost()) {
      $e_type = pht('Bắt buộc');
      $errors[] = pht(
        'Để tạo mới một thẻ bảng điều khiển, phải chọn 1 loại');
    }

    $type_control = id(new AphrontFormRadioButtonControl())
      ->setLabel(pht('Loại thẻ'))
      ->setName('panelType')
      ->setError($e_type);

    foreach ($panel_types as $key => $type) {
      $type_control->addButton(
        $key,
        $type->getPanelTypeName(),
        $type->getPanelTypeDescription());
    }

    $form = id(new AphrontFormView())
      ->setUser($viewer)
      ->appendRemarkupInstructions(
        pht('Chọn 1 loại thẻ dể tạo:'))
      ->appendChild($type_control);

    if ($request->isAjax()) {
      return $this->newDialog()
        ->setTitle(pht('Thêm mới một thẻ'))
        ->setWidth(AphrontDialogView::WIDTH_FORM)
        ->setErrors($errors)
        ->appendForm($form)
        ->addCancelButton($cancel_uri)
        ->addSubmitButton(pht('Tiếp tục'));
    }

    $form->appendChild(
      id(new AphrontFormSubmitControl())
        ->setValue(pht('Tiếp tục'))
        ->addCancelButton($cancel_uri));

    $title = pht('Tạo bảng điều khiển');
    $header_icon = 'fa-plus-square';

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->addTextCrumb(
      pht('Panels'),
      $this->getApplicationURI('panel/'));
    $crumbs->addTextCrumb(pht('Thẻ mới'));
    $crumbs->setBorder(true);

    $box = id(new PHUIObjectBoxView())
      ->setHeaderText(pht('Thẻ')
      ->setFormErrors($errors)
      ->setBackground(PHUIObjectBoxView::BLUE_PROPERTY)
      ->setForm($form);

    $header = id(new PHUIHeaderView())
      ->setHeader($title)
      ->setHeaderIcon($header_icon);

    $view = id(new PHUITwoColumnView())
      ->setHeader($header)
      ->setFooter($box);

    return $this->newPage()
      ->setTitle($title)
      ->setCrumbs($crumbs)
      ->appendChild($view);
  }

}
