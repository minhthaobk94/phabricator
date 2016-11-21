<?php

final class PhabricatorDashboardPanelArchiveController
  extends PhabricatorDashboardController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();
    $id = $request->getURIData('id');

    $panel = id(new PhabricatorDashboardPanelQuery())
      ->setViewer($viewer)
      ->withIDs(array($id))
      ->requireCapabilities(
        array(
          PhabricatorPolicyCapability::CAN_VIEW,
          PhabricatorPolicyCapability::CAN_EDIT,
        ))
      ->executeOne();
    if (!$panel) {
      return new Aphront404Response();
    }

    $next_uri = '/'.$panel->getMonogram();

    if ($request->isFormPost()) {
      $xactions = array();
      $xactions[] = id(new PhabricatorDashboardPanelTransaction())
        ->setTransactionType(PhabricatorDashboardPanelTransaction::TYPE_ARCHIVE)
        ->setNewValue((int)!$panel->getIsArchived());

      id(new PhabricatorDashboardPanelTransactionEditor())
        ->setActor($viewer)
        ->setContentSourceFromRequest($request)
        ->applyTransactions($panel, $xactions);

      return id(new AphrontRedirectResponse())->setURI($next_uri);
    }

    if ($panel->getIsArchived()) {
      $title = pht('Kích hoạt ?');
      $body = pht(
        'Bảng này sẽ được kích hoạt và xuất hiện trong giao diện khác như '.
        'một bảng điều khiển hoạt động.');
      $submit_text = pht('Activate Panel');
    } else {
      $title = pht('Lưu trữ?');
      $body = pht(
        'Bảng này sẽ được lưu trữ và không còn xuất hiện trong danh sách các thẻ hoạt động ');
      $submit_text = pht('Archive Panel');
    }

    return $this->newDialog()
      ->setTitle($title)
      ->appendParagraph($body)
      ->addSubmitButton($submit_text)
      ->addCancelButton($next_uri);
  }

}
