<?php

final class AlmanacNamespaceEditor
  extends PhabricatorApplicationTransactionEditor {

  public function getEditorApplicationClass() {
    return 'PhabricatorAlmanacApplication';
  }

  public function getEditorObjectsDescription() {
    return pht('Tên Sách lịch');
  }

  protected function supportsSearch() {
    return true;
  }

  public function getTransactionTypes() {
    $types = parent::getTransactionTypes();

    $types[] = AlmanacNamespaceTransaction::TYPE_NAME;
    $types[] = PhabricatorTransactions::TYPE_VIEW_POLICY;
    $types[] = PhabricatorTransactions::TYPE_EDIT_POLICY;

    return $types;
  }

  protected function getCustomTransactionOldValue(
    PhabricatorLiskDAO $object,
    PhabricatorApplicationTransaction $xaction) {
    switch ($xaction->getTransactionType()) {
      case AlmanacNamespaceTransaction::TYPE_NAME:
        return $object->getName();
    }

    return parent::getCustomTransactionOldValue($object, $xaction);
  }

  protected function getCustomTransactionNewValue(
    PhabricatorLiskDAO $object,
    PhabricatorApplicationTransaction $xaction) {

    switch ($xaction->getTransactionType()) {
      case AlmanacNamespaceTransaction::TYPE_NAME:
        return $xaction->getNewValue();
    }

    return parent::getCustomTransactionNewValue($object, $xaction);
  }

  protected function applyCustomInternalTransaction(
    PhabricatorLiskDAO $object,
    PhabricatorApplicationTransaction $xaction) {

    switch ($xaction->getTransactionType()) {
      case AlmanacNamespaceTransaction::TYPE_NAME:
        $object->setName($xaction->getNewValue());
        return;
    }

    return parent::applyCustomInternalTransaction($object, $xaction);
  }

  protected function applyCustomExternalTransaction(
    PhabricatorLiskDAO $object,
    PhabricatorApplicationTransaction $xaction) {

    switch ($xaction->getTransactionType()) {
      case AlmanacNamespaceTransaction::TYPE_NAME:
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
      case AlmanacNamespaceTransaction::TYPE_NAME:
        $missing = $this->validateIsEmptyTextField(
          $object->getName(),
          $xactions);

        if ($missing) {
          $error = new PhabricatorApplicationTransactionValidationError(
            $type,
            pht('Bắt buộc'),
            pht('Phải có'),
            nonempty(last($xactions), null));

          $error->setIsMissingFieldError(true);
          $errors[] = $error;
        } else {
          foreach ($xactions as $xaction) {
            $name = $xaction->getNewValue();

            $message = null;
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

            $other = id(new AlmanacNamespaceQuery())
              ->setViewer(PhabricatorUser::getOmnipotentUser())
              ->withNames(array($name))
              ->executeOne();
            if ($other && ($other->getID() != $object->getID())) {
              $error = new PhabricatorApplicationTransactionValidationError(
                $type,
                pht('Không đặc biệt'),
                pht(
                  'Tên "%s" đã được sử dụng ',
                  $name),
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
                  'Không có quyền tạo mới '.
                  ' "%s" .',
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

  protected function didCatchDuplicateKeyException(
    PhabricatorLiskDAO $object,
    array $xactions,
    Exception $ex) {

    $errors = array();

    $errors[] = new PhabricatorApplicationTransactionValidationError(
      null,
      pht('Không hợp lệ'),
      pht(
        'Tên đã tồn tại'),
      null);

    throw new PhabricatorApplicationTransactionValidationException($errors);
  }

}
