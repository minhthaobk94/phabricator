<?php

final class PhabricatorBadgesArchiveController
  extends PhabricatorBadgesController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();
    $id = $request->getURIData('id');

    $badge = id(new PhabricatorBadgesQuery())
      ->setViewer($viewer)
      ->withIDs(array($id))
      ->requireCapabilities(
        array(
          PhabricatorPolicyCapability::CAN_VIEW,
          PhabricatorPolicyCapability::CAN_EDIT,
        ))
      ->executeOne();
    if (!$badge) {
      return new Aphront404Response();
    }

    $view_uri = $this->getApplicationURI('view/'.$badge->getID().'/');

    if ($request->isFormPost()) {
      if ($badge->isArchived()) {
        $new_status = PhabricatorBadgesBadge::STATUS_ACTIVE;
      } else {
        $new_status = PhabricatorBadgesBadge::STATUS_ARCHIVED;
      }

      $xactions = array();

      $xactions[] = id(new PhabricatorBadgesTransaction())
        ->setTransactionType(PhabricatorBadgesTransaction::TYPE_STATUS)
        ->setNewValue($new_status);

      id(new PhabricatorBadgesEditor())
        ->setActor($viewer)
        ->setContentSourceFromRequest($request)
        ->setContinueOnNoEffect(true)
        ->setContinueOnMissingFields(true)
        ->applyTransactions($badge, $xactions);

      return id(new AphrontRedirectResponse())->setURI($view_uri);
    }

    if ($badge->isArchived()) {
      $title = pht('Kích hoạt Badge');
      $body = pht('Badge này sẽ được tái hạ vào dịch vụ.');
      $button = pht('Kích hoạt Badge');
    } else {
      $title = pht('Hoàn thành Badge');
      $body = pht(
        'Badge chuyên dụng này, một khi một biểu tượng của phân biệt cài đặt này, '.
        'được bỏ ngay từ dịch vụ, nhưng sẽ không bao giờ xa'.
        'trái tim chúng ta . Godspeed.');
      $button = pht('Hoàn thành Badge');
    }

    return $this->newDialog()
      ->setTitle($title)
      ->appendChild($body)
      ->addCancelButton($view_uri)
      ->addSubmitButton($button);
  }

}
