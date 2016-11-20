<?php

final class DifferentialAuditorsField
  extends DifferentialStoredCustomField {

  public function getFieldKey() {
    return 'phabricator:auditors';
  }

  public function getFieldName() {
    return pht('Người kiểm tra ');
  }

  public function getFieldDescription() {
    return pht('Cho phép các cam kết để kích hoạt các cuộc kiểm toán một cách rõ ràng.');
  }

  public function getValueForStorage() {
    return json_encode($this->getValue());
  }

  public function setValueFromStorage($value) {
    try {
      $this->setValue(phutil_json_decode($value));
    } catch (PhutilJSONParserException $ex) {
      $this->setValue(array());
    }
    return $this;
  }

  public function shouldAppearInCommitMessage() {
    return true;
  }

  public function shouldAllowEditInCommitMessage() {
    return true;
  }

  public function canDisableField() {
    return false;
  }

  public function getRequiredHandlePHIDsForCommitMessage() {
    return nonempty($this->getValue(), array());
  }

  public function parseCommitMessageValue($value) {
    return $this->parseObjectList(
      $value,
      array(
        PhabricatorPeopleUserPHIDType::TYPECONST,
        PhabricatorProjectProjectPHIDType::TYPECONST,
      ));
  }

  public function renderCommitMessageValue(array $handles) {
    return $this->renderObjectList($handles);
  }

}
