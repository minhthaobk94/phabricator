<?php

final class AlmanacDeviceEditor
  extends AlmanacEditor {

  public function getEditorObjectsDescription() {
    return pht('Thiết bị Sách lịch');
  }

  public function getTransactionTypes() {
    $types = parent::getTransactionTypes();

    $types[] = AlmanacDeviceTransaction::TYPE_NAME;
    $types[] = AlmanacDeviceTransaction::TYPE_INTERFACE;
    $types[] = PhabricatorTransactions::TYPE_VIEW_POLICY;
    $types[] = PhabricatorTransactions::TYPE_EDIT_POLICY;

    return $types;
  }

  protected function getCustomTransactionOldValue(
    PhabricatorLiskDAO $object,
    PhabricatorApplicationTransaction $xaction) {
    switch ($xaction->getTransactionType()) {
      case AlmanacDeviceTransaction::TYPE_NAME:
        return $object->getName();
    }

    return parent::getCustomTransactionOldValue($object, $xaction);
  }

  protected function getCustomTransactionNewValue(
    PhabricatorLiskDAO $object,
    PhabricatorApplicationTransaction $xaction) {

    switch ($xaction->getTransactionType()) {
      case AlmanacDeviceTransaction::TYPE_NAME:
      case AlmanacDeviceTransaction::TYPE_INTERFACE:
        return $xaction->getNewValue();
    }

    return parent::getCustomTransactionNewValue($object, $xaction);
  }

  protected function applyCustomInternalTransaction(
    PhabricatorLiskDAO $object,
    PhabricatorApplicationTransaction $xaction) {

    switch ($xaction->getTransactionType()) {
      case AlmanacDeviceTransaction::TYPE_NAME:
        $object->setName($xaction->getNewValue());
        return;
      case AlmanacDeviceTransaction::TYPE_INTERFACE:
        return;
    }

    return parent::applyCustomInternalTransaction($object, $xaction);
  }

  protected function applyCustomExternalTransaction(
    PhabricatorLiskDAO $object,
    PhabricatorApplicationTransaction $xaction) {

    switch ($xaction->getTransactionType()) {
      case AlmanacDeviceTransaction::TYPE_NAME:
        return;
      case AlmanacDeviceTransaction::TYPE_INTERFACE:
        $old = $xaction->getOldValue();
        if ($old) {
          $interface = id(new AlmanacInterfaceQuery())
            ->setViewer($this->requireActor())
            ->withIDs(array($old['id']))
            ->executeOne();
          if (!$interface) {
            throw new Exception(pht('Không tải được giao diện!'));
          }
        } else {
          $interface = AlmanacInterface::initializeNewInterface()
            ->setDevicePHID($object->getPHID());
        }

        $new = $xaction->getNewValue();
        if ($new) {
          $interface
            ->setNetworkPHID($new['networkPHID'])
            ->setAddress($new['address'])
            ->setPort((int)$new['port']);

          if (idx($new, 'phid')) {
            $interface->setPHID($new['phid']);
          }

          $interface->save();
        } else {
          $interface->delete();
        }
        return;
    }

    return parent::applyCustomExternalTransaction($object, $xaction);
  }

  protected function validateTransaction(
    PhabricatorLiskDAO $object,
    $type,
    array $xactions) {

    $errors = parent::validateTransaction($object, $type, $xactions);

    switch ($type) {
      case AlmanacDeviceTransaction::TYPE_NAME:
        $missing = $this->validateIsEmptyTextField(
          $object->getName(),
          $xactions);

        if ($missing) {
          $error = new PhabricatorApplicationTransactionValidationError(
            $type,
            pht('Bắt buộc'),
            pht('Tên thiết bị là bắt buộc.'),
            nonempty(last($xactions), null));

          $error->setIsMissingFieldError(true);
          $errors[] = $error;
        } else {
          foreach ($xactions as $xaction) {
            $message = null;
            $name = $xaction->getNewValue();

            try {
              AlmanacNames::validateName($name);
            } catch (Exception $ex) {
              $message = $ex->getMessage();
            }

            if ($message !== null) {
              $error = new PhabricatorApplicationTransactionValidationError(
                $type,
                pht('Invalid'),
                $message,
                $xaction);
              $errors[] = $error;
              continue;
            }

            $other = id(new AlmanacDeviceQuery())
              ->setViewer(PhabricatorUser::getOmnipotentUser())
              ->withNames(array($name))
              ->executeOne();
            if ($other && ($other->getID() != $object->getID())) {
              $error = new PhabricatorApplicationTransactionValidationError(
                $type,
                pht('Trùng'),
                pht('Thiết bị Sách lịch phải có tên riêng.'),
                $xaction);
              $errors[] = $error;
              continue;
            }

            if ($name === $object->getName()) {
              continue;
            }

            $namespace = AlmanacNamespace::loadRestrictedNamespace(
              $this->getActor(),
              $name);
            if ($namespace) {
              $error = new PhabricatorApplicationTransactionValidationError(
                $type,
                pht('Hạn chế'),
                pht(
                  'Bạn không có quyền tạo mới thiết bị '.
                  'trong "%s".',
                  $namespace->getName()),
                $xaction);
              $errors[] = $error;
              continue;
            }
          }
        }

        break;
      case AlmanacDeviceTransaction::TYPE_INTERFACE:
        // We want to make sure that all the affected networks are visible to
        // the actor, any edited interfaces exist, and that the actual address
        // components are valid.

        $network_phids = array();
        foreach ($xactions as $xaction) {
          $old = $xaction->getOldValue();
          $new = $xaction->getNewValue();
          if ($old) {
            $network_phids[] = $old['networkPHID'];
          }
          if ($new) {
            $network_phids[] = $new['networkPHID'];

            $address = $new['address'];
            if (!strlen($address)) {
              $error = new PhabricatorApplicationTransactionValidationError(
                $type,
                pht('Không hợp lệ'),
                pht('Giao diện phải là một địa chỉ.'),
                $xaction);
              $errors[] = $error;
            } else {
              // TODO: Validate addresses, but IPv6 addresses are not trival
              // to validate.
            }

            $port = $new['port'];
            if (!strlen($port)) {
              $error = new PhabricatorApplicationTransactionValidationError(
                $type,
                pht('Không hợp lệ'),
                pht('Giao diện phải có cổng.'),
                $xaction);
              $errors[] = $error;
            } else if ((int)$port < 1 || (int)$port > 65535) {
              $error = new PhabricatorApplicationTransactionValidationError(
                $type,
                pht('Không hợp lệ'),
                pht(
                  'Cổng từ 1 đến 65535.'),
                $xaction);
              $errors[] = $error;
            }

            $phid = idx($new, 'phid');
            if ($phid) {
              $interface_phid_type = AlmanacInterfacePHIDType::TYPECONST;
              if (phid_get_type($phid) !== $interface_phid_type) {
                $error = new PhabricatorApplicationTransactionValidationError(
                  $type,
                  pht('Không hợp lệ'),
                  pht(
                    'PHIDs phải là một loại '.
                    'AlmanacInterfacePHIDType.'),
                  $xaction);
                $errors[] = $error;
              }
            }
          }
        }

        if ($network_phids) {
          $networks = id(new AlmanacNetworkQuery())
            ->setViewer($this->requireActor())
            ->withPHIDs($network_phids)
            ->execute();
          $networks = mpull($networks, null, 'getPHID');
        } else {
          $networks = array();
        }

        $addresses = array();
        foreach ($xactions as $xaction) {
          $old = $xaction->getOldValue();
          if ($old) {
            $network = idx($networks, $old['networkPHID']);
            if (!$network) {
              $error = new PhabricatorApplicationTransactionValidationError(
                $type,
                pht('Không hợp lệ'),
                pht(
                  'Bạn không được sửa giao diện thuộc mạng không tồn tại hoặc hạn chế.'),
                $xaction);
              $errors[] = $error;
            }

            $addresses[] = $old['id'];
          }

          $new = $xaction->getNewValue();
          if ($new) {
            $network = idx($networks, $new['networkPHID']);
            if (!$network) {
              $error = new PhabricatorApplicationTransactionValidationError(
                $type,
                pht('Không hợp lệ'),
                pht(
                  'Không thể thêm '.
                  'Mạng hạn chế hoặc không hợp lệ.'),
                $xaction);
              $errors[] = $error;
            }
          }
        }

        if ($addresses) {
          $interfaces = id(new AlmanacInterfaceQuery())
            ->setViewer($this->requireActor())
            ->withDevicePHIDs(array($object->getPHID()))
            ->withIDs($addresses)
            ->execute();
          $interfaces = mpull($interfaces, null, 'getID');
        } else {
          $interfaces = array();
        }

        foreach ($xactions as $xaction) {
          $old = $xaction->getOldValue();
          if ($old) {
            $interface = idx($interfaces, $old['id']);
            if (!$interface) {
              $error = new PhabricatorApplicationTransactionValidationError(
                $type,
                pht('Không hợp lệ'),
                pht('Giao diện không hợp lệ hoặc hạn chế'),
                $xaction);
              $errors[] = $error;
              continue;
            }

            $new = $xaction->getNewValue();
            if (!$new) {
              if ($interface->loadIsInUse()) {
                $error = new PhabricatorApplicationTransactionValidationError(
                  $type,
                  pht('Đang sử dụng'),
                  pht('Không thể xóa.'),
                  $xaction);
                $errors[] = $error;
              }
            }
          }
        }
      break;
    }

    return $errors;
  }

}
