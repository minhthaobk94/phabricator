<?php

final class AlmanacInterfaceDeleteController
  extends AlmanacDeviceController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();

    $id = $request->getURIData('id');
    $interface = id(new AlmanacInterfaceQuery())
      ->setViewer($viewer)
      ->withIDs(array($id))
      ->requireCapabilities(
        array(
          PhabricatorPolicyCapability::CAN_VIEW,
          PhabricatorPolicyCapability::CAN_EDIT,
        ))
      ->executeOne();
    if (!$interface) {
      return new Aphront404Response();
    }

    $device = $interface->getDevice();
    $device_uri = $device->getURI();

    if ($interface->loadIsInUse()) {
      return $this->newDialog()
        ->setTitle(pht('Giao diện sử dụng'))
        ->appendParagraph(
          pht(
            'Bạn không thể xóa giao diện này. Vì nó đang được sử  dụng'))
        ->addCancelButton($device_uri);
    }

    if ($request->isFormPost()) {
      $type_interface = AlmanacDeviceTransaction::TYPE_INTERFACE;

      $xactions = array();

      $v_old = array(
        'id' => $interface->getID(),
      ) + $interface->toAddress()->toDictionary();

      $xactions[] = id(new AlmanacDeviceTransaction())
        ->setTransactionType($type_interface)
        ->setOldValue($v_old)
        ->setNewValue(null);

      $editor = id(new AlmanacDeviceEditor())
        ->setActor($viewer)
        ->setContentSourceFromRequest($request)
        ->setContinueOnNoEffect(true)
        ->setContinueOnMissingFields(true);

      $editor->applyTransactions($device, $xactions);

      return id(new AphrontRedirectResponse())->setURI($device_uri);
    }

    return $this->newDialog()
      ->setTitle(pht('Delete Interface'))
      ->appendParagraph(
        pht(
          'Xóa giao diện %s trên thiết bị %s?',
          phutil_tag('strong', array(), $interface->renderDisplayAddress()),
          phutil_tag('strong', array(), $device->getName())))
      ->addCancelButton($device_uri)
      ->addSubmitButton(pht('Xóa'));
  }

}
