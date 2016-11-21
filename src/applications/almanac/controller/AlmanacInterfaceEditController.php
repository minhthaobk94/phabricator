<?php

final class AlmanacInterfaceEditController
  extends AlmanacDeviceController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();

    $id = $request->getURIData('id');
    if ($id) {
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

      $is_new = false;
      $title = pht('Chỉnh sửa giao diện');
      $save_button = pht('Lưu thay đổi');
    } else {
      $device = id(new AlmanacDeviceQuery())
        ->setViewer($viewer)
        ->withIDs(array($request->getStr('deviceID')))
        ->requireCapabilities(
          array(
            PhabricatorPolicyCapability::CAN_VIEW,
            PhabricatorPolicyCapability::CAN_EDIT,
          ))
        ->executeOne();
      if (!$device) {
        return new Aphront404Response();
      }

      $interface = AlmanacInterface::initializeNewInterface();
      $is_new = true;

      $title = pht('Tạo mới giao diện');
      $save_button = pht('Tạo mới');
    }

    $device_uri = $device->getURI();
    $cancel_uri = $device_uri;

    $v_network = $interface->getNetworkPHID();

    $v_address = $interface->getAddress();
    $e_address = true;

    $v_port = $interface->getPort();

    $validation_exception = null;

    if ($request->isFormPost()) {
      $v_network = $request->getStr('networkPHID');
      $v_address = $request->getStr('address');
      $v_port = $request->getStr('port');

      $type_interface = AlmanacDeviceTransaction::TYPE_INTERFACE;

      $address = AlmanacAddress::newFromParts($v_network, $v_address, $v_port);

      $xaction = id(new AlmanacDeviceTransaction())
        ->setTransactionType($type_interface)
        ->setNewValue($address->toDictionary());

      if ($interface->getID()) {
        $xaction->setOldValue(array(
          'id' => $interface->getID(),
        ) + $interface->toAddress()->toDictionary());
      } else {
        $xaction->setOldValue(array());
      }

      $xactions = array();
      $xactions[] = $xaction;

      $editor = id(new AlmanacDeviceEditor())
        ->setActor($viewer)
        ->setContentSourceFromRequest($request)
        ->setContinueOnNoEffect(true)
        ->setContinueOnMissingFields(true);

      try {
        $editor->applyTransactions($device, $xactions);

        $device_uri = $device->getURI();
        return id(new AphrontRedirectResponse())->setURI($device_uri);
      } catch (PhabricatorApplicationTransactionValidationException $ex) {
        $validation_exception = $ex;
        $e_address = $ex->getShortMessage($type_interface);
      }
    }

    $networks = id(new AlmanacNetworkQuery())
      ->setViewer($viewer)
      ->execute();

    $form = id(new AphrontFormView())
      ->setUser($viewer)
      ->appendChild(
        id(new AphrontFormSelectControl())
          ->setLabel(pht('Mạng'))
          ->setName('networkPHID')
          ->setValue($v_network)
          ->setOptions(mpull($networks, 'getName', 'getPHID')))
      ->appendChild(
        id(new AphrontFormTextControl())
          ->setLabel(pht('Địa chỉ'))
          ->setName('address')
          ->setValue($v_address)
          ->setError($e_address))
      ->appendChild(
        id(new AphrontFormTextControl())
          ->setLabel(pht('Cổng'))
          ->setName('port')
          ->setValue($v_port)
          ->setError($e_address))
      ->appendChild(
        id(new AphrontFormSubmitControl())
          ->addCancelButton($cancel_uri)
          ->setValue($save_button));

    $box = id(new PHUIObjectBoxView())
      ->setValidationException($validation_exception)
      ->setHeaderText(pht('Giao diện'))
      ->setBackground(PHUIObjectBoxView::BLUE_PROPERTY)
      ->setForm($form);

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->addTextCrumb($device->getName(), $device_uri);
    if ($is_new) {
      $crumbs->addTextCrumb(pht('Tạo mới giao diện'));
      $header = id(new PHUIHeaderView())
        ->setHeader(pht('Tạo mới giao diện'))
        ->setHeaderIcon('fa-plus-square');
    } else {
      $crumbs->addTextCrumb(pht('Chỉnh sửa giao diện'));
      $header = id(new PHUIHeaderView())
        ->setHeader(pht('Chỉnh sửa giao diện'))
        ->setHeaderIcon('fa-pencil');
    }
    $crumbs->setBorder(true);

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
