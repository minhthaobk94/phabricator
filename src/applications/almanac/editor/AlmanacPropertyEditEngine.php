<?php

abstract class AlmanacPropertyEditEngine
  extends PhabricatorEditEngine {

  private $propertyKey;

  public function setPropertyKey($property_key) {
    $this->propertyKey = $property_key;
    return $this;
  }

  public function getPropertyKey() {
    return $this->propertyKey;
  }

  public function isEngineConfigurable() {
    return false;
  }

  public function isEngineExtensible() {
    return false;
  }

  public function getEngineName() {
    return pht('Thuộc tính');
  }

  public function getSummaryHeader() {
    return pht('Chỉnh sủa cấu hình');
  }

  public function getSummaryText() {
    return pht('Tính năng để chỉnh sửa thuộc tính.');
  }

  public function getEngineApplicationClass() {
    return 'PhabricatorAlmanacApplication';
  }

  protected function newEditableObject() {
    throw new PhutilMethodNotImplementedException();
  }

  protected function getObjectCreateTitleText($object) {
    return pht('Tạo mới thuộc tính');
  }

  protected function getObjectCreateButtonText($object) {
    return pht('Tạo mới thuộc tính');
  }

  protected function getObjectEditTitleText($object) {
    return pht('Chỉnh sửa thuộc tính: %s', $object->getName());
  }

  protected function getObjectEditShortText($object) {
    return pht('Chỉnh sửa thuộc tính');
  }

  protected function getObjectCreateShortText() {
    return pht('Tạo mới thuộc tính');
  }

  protected function buildCustomEditFields($object) {
    $property_key = $this->getPropertyKey();
    $xaction_type = AlmanacTransaction::TYPE_PROPERTY_UPDATE;

    return array(
      id(new PhabricatorTextEditField())
        ->setKey('value')
        ->setMetadataValue('almanac.property', $property_key)
        ->setLabel($property_key)
        ->setTransactionType($xaction_type)
        ->setValue($object->getAlmanacPropertyValue($property_key)),
    );
  }

}
