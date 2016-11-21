<?php

final class PhabricatorDashboardAddPanelController
  extends PhabricatorDashboardController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();
    $id = $request->getURIData('id');

    $dashboard = id(new PhabricatorDashboardQuery())
      ->setViewer($viewer)
      ->withIDs(array($id))
      ->requireCapabilities(
        array(
          PhabricatorPolicyCapability::CAN_VIEW,
          PhabricatorPolicyCapability::CAN_EDIT,
        ))
      ->executeOne();
    if (!$dashboard) {
      return new Aphront404Response();
    }

    $redirect_uri = $this->getApplicationURI('manage/'.$dashboard->getID().'/');

    $v_panel = $request->getStr('panel');
    $e_panel = true;
    $errors = array();
    if ($request->isFormPost()) {
      if (strlen($v_panel)) {
        $panel = id(new PhabricatorDashboardPanelQuery())
          ->setViewer($viewer)
          ->withIDs(array($v_panel))
          ->executeOne();
        if (!$panel) {
          $errors[] = pht('Không có thẻ như vậy!');
          $e_panel = pht('Không hợp lệ');
        }
      } else {
        $errors[] = pht('chọn 1 thẻ để thêm.');
        $e_panel = pht('Bắt buộc');
      }

      if (!$errors) {
        PhabricatorDashboardTransactionEditor::addPanelToDashboard(
          $viewer,
          PhabricatorContentSource::newFromRequest($request),
          $panel,
          $dashboard,
          $request->getInt('column', 0));

        return id(new AphrontRedirectResponse())->setURI($redirect_uri);
      }
    }

    $panels = id(new PhabricatorDashboardPanelQuery())
      ->setViewer($viewer)
      ->withArchived(false)
      ->execute();

    if (!$panels) {
      return $this->newDialog()
        ->setTitle(pht('Chưa tồn tại thẻ nào'))
        ->appendParagraph(
          pht(
            'Bạn chưa tạp một bảng điều khiển nào, nên không thể '.
            'thêm một thẻ đã tồn tại nào.'))
        ->appendParagraph(
          pht('Tạo thẻ mới.'))
        ->addCancelButton($redirect_uri);
    }

    $panel_options = array();
    foreach ($panels as $panel) {
      $panel_options[$panel->getID()] = pht(
        '%s %s',
        $panel->getMonogram(),
        $panel->getName());
    }

    $form = id(new AphrontFormView())
      ->setUser($viewer)
      ->addHiddenInput('column', $request->getInt('column'))
      ->appendRemarkupInstructions(
        pht('CHọn 1 thẻ để thêm vào Bảng điều khiển:'))
      ->appendChild(
        id(new AphrontFormSelectControl())
          ->setName('panel')
          ->setLabel(pht('Thẻ'))
          ->setValue($v_panel)
          ->setError($e_panel)
          ->setOptions($panel_options));

    return $this->newDialog()
      ->setTitle(pht('Thêm thẻ'))
      ->setErrors($errors)
      ->appendChild($form->buildLayoutView())
      ->addCancelButton($redirect_uri)
      ->addSubmitButton(pht('Thêm'));
  }

}
