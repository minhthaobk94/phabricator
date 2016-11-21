<?php

final class AlmanacServiceEditor
  extends AlmanacEditor {

  public function getEditorObjectsDescription() {
    return pht('Dịch vụ Sách lịch');
  }

  public function getTransactionTypes() {
    $types = parent::getTransactionTypes();

    $types[] = AlmanacServiceTransaction::TYPE_NAME;

    $types[] = PhabricatorTransactions::TYPE_VIEW_POLICY;
    $types[] = PhabricatorTransactions::TYPE_EDIT_POLICY;

    return $types;
  }

  protected function getCustomTransactionOldValue(
    PhabricatorLiskDAO $object,
    PhabricatorApplicationTransaction $xaction) {
    switch ($xaction->getTransactionType()) {
      case AlmanacServiceTransaction::TYPE_NAME:
        return $object->getName();
    }

    return parent::getCustomTransactionOldValue($object, $xaction);
  }

  protected function getCustomTransactionNewValue(
    PhabricatorLiskDAO $object,
    PhabricatorApplicationTransaction $xaction) {

    switch ($xaction->getTransactionType()) {
      case AlmanacServiceTransaction::TYPE_NAME:
        return $xaction->getNewValue();
    }

    return parent::getCustomTransactionNewValue($object, $xaction);
  }

  protected function applyCustomInternalTransaction(
    PhabricatorLiskDAO $object,
    PhabricatorApplicationTransaction $xaction) {

    switch ($xaction->getTransactionType()) {
      case AlmanacServiceTransaction::TYPE_NAME:
        $object->setName($xaction->getNewValue());
        return;
    }

    return parent::applyCustomInternalTransaction($object, $xaction);
  }

  protected function applyCustomExternalTransaction(
    PhabricatorLiskDAO $object,
    PhabricatorApplicationTransaction $xaction) {

    switch ($xaction->getTransactionType()) {
      case AlmanacServiceTransaction::TYPE_NAME:
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
      case AlmanacServiceTransaction::TYPE_NAME:
        $missing = $this->validateIsEmptyTextField(
          $object->getName(),
          $xactions);

        if ($missing) {
          $error = new PhabricatorApplicationTransactionValidationError(
            $type,
            pht('Yêu cầu'),
            pht('Bắt buộc.'),
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
                pht('Không hợp lệ'),
                $message,
                $xaction);
              $errors[] = $error;
              continue;
            }

            $other = id(new AlmanacServiceQuery())
              ->setViewer(PhabricatorUser::getOmnipotentUser())
              ->withNames(array($name))
              ->executeOne();
            if ($other && ($other->getID() != $object->getID())) {
              $error = new PhabricatorApplicationTransactionValidationError(
                $type,
                pht('Trùng lặp'),
                pht('Dịch vụ không được trùng lặp.'),
                last($xactions));
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
                  'Bạn không có quyền tạo mới dịch vụ '.
                  ' "%s".',
                  $namespace->getName()),
                $xaction);
              $errors[] = $error;
              continue;
            }
          }
        }

        break;
    }

    return $errors;
  }


  protected function validateAllTransactions(
    PhabricatorLiskDAO $object,
    array $xactions) {

    $errors = parent::validateAllTransactions($object, $xactions);

    if ($object->isClusterService()) {
      $can_manage = PhabricatorPolicyFilter::hasCapability(
        $this->getActor(),
        new PhabricatorAlmanacApplication(),
        AlmanacManageClusterServicesCapability::CAPABILITY);
      if (!$can_manage) {
        $errors[] = new PhabricatorApplicationTransactionValidationError(
          null,
          pht('Hạn chế'),
          pht('Bạn không có quyền quản lý cụm dịch vụ.'),
          null);
      }
    }

    return $errors;
  }

}
