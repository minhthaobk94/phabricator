<?php

final class DifferentialEditPolicyField
  extends DifferentialCoreCustomField {

  public function getFieldKey() {
    return 'differential:edit-policy';
  }

  public function getFieldName() {
    return pht('Thay đổi chính sách');
  }

  public function getFieldDescription() {
    return pht('Điều khiển, người mà có thể thay đổi bản sửa đổi.');
  }

  protected function readValueFromRevision(
    DifferentialRevision $revision) {
    return $revision->getEditPolicy();
  }

  public function readValueFromRequest(AphrontRequest $request) {
    $this->setValue($request->getStr($this->getFieldKey()));
  }

  public function renderEditControl(array $handles) {
    $viewer = $this->getViewer();
    $revision = $this->getObject();

    $policies = id(new PhabricatorPolicyQuery())
      ->setViewer($viewer)
      ->setObject($revision)
      ->execute();

    return id(new AphrontFormPolicyControl())
      ->setUser($viewer)
      ->setCapability(PhabricatorPolicyCapability::CAN_EDIT)
      ->setPolicyObject($revision)
      ->setPolicies($policies)
      ->setName($this->getFieldKey())
      ->setValue($this->getValue())
      ->setError($this->getFieldError());
  }

  public function getApplicationTransactionType() {
    return PhabricatorTransactions::TYPE_EDIT_POLICY;
  }

}
