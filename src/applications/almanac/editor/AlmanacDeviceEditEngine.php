<?php

final class AlmanacDeviceEditEngine
  extends PhabricatorEditEngine {

  const ENGINECONST = 'almanac.device';

  public function isEngineConfigurable() {
    return false;
  }

  public function getEngineName() {
    return pht('Thiết bị Sách lịch');
  }

  public function getSummaryHeader() {
    return pht('Chỉnh sửa cấu hình');
  }

  public function getSummaryText() {
    return pht('Tính năng này được dùng để chỉnh sửa các thiết bị Sách lịch.');
  }

  public function getEngineApplicationClass() {
    return 'PhabricatorAlmanacApplication';
  }

  protected function newEditableObject() {
    return AlmanacDevice::initializeNewDevice();
  }

  protected function newObjectQuery() {
    return new AlmanacDeviceQuery();
  }

  protected function getObjectCreateTitleText($object) {
    return pht('Tạo mới thiết bị');
  }

  protected function getObjectCreateButtonText($object) {
    return pht('Tạo mới thiết bị');
  }

  protected function getObjectEditTitleText($object) {
    return pht('Chỉnh sửa thiết bị: %s', $object->getName());
  }

  protected function getObjectEditShortText($object) {
    return pht('Chỉnh sửa thiết bị');
  }

  protected function getObjectCreateShortText() {
    return pht('Tạo mới');
  }

  protected function getObjectName() {
    return pht('Thiết bị');
  }

  protected function getEditorURI() {
    return '/almanac/device/edit/';
  }

  protected function getObjectCreateCancelURI($object) {
    return '/almanac/device/';
  }

  protected function getObjectViewURI($object) {
    return $object->getURI();
  }

  protected function getCreateNewObjectPolicy() {
    return $this->getApplication()->getPolicy(
      AlmanacCreateDevicesCapability::CAPABILITY);
  }

  protected function buildCustomEditFields($object) {
    return array(
      id(new PhabricatorTextEditField())
        ->setKey('name')
        ->setLabel(pht('Tên'))
        ->setDescription(pht('Tên thiết bị.'))
        ->setTransactionType(AlmanacDeviceTransaction::TYPE_NAME)
        ->setIsRequired(true)
        ->setValue($object->getName()),
    );
  }

}
