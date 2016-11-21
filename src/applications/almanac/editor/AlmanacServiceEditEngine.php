<?php

final class AlmanacServiceEditEngine
  extends PhabricatorEditEngine {

  const ENGINECONST = 'almanac.service';

  private $serviceType;

  public function setServiceType($service_type) {
    $this->serviceType = $service_type;
    return $this;
  }

  public function getServiceType() {
    return $this->serviceType;
  }

  public function isEngineConfigurable() {
    return false;
  }

  public function getEngineName() {
    return pht('Dịch vụ');
  }

  public function getSummaryHeader() {
    return pht('Chỉnh sửa cấu hình');
  }

  public function getSummaryText() {
    return pht('Tính năng này để chínhr sửa dịch vụ.');
  }

  public function getEngineApplicationClass() {
    return 'PhabricatorAlmanacApplication';
  }

  protected function newEditableObject() {
    $service_type = $this->getServiceType();
    return AlmanacService::initializeNewService($service_type);
  }

  protected function newObjectQuery() {
    return new AlmanacServiceQuery();
  }

  protected function getObjectCreateTitleText($object) {
    return pht('Tạo mới dịch vụ');
  }

  protected function getObjectCreateButtonText($object) {
    return pht('Tạo mới dịch vụ');
  }

  protected function getObjectEditTitleText($object) {
    return pht('Chỉnh sửa dịch vụ: %s', $object->getName());
  }

  protected function getObjectEditShortText($object) {
    return pht('Chỉnh sửa dịch vụ');
  }

  protected function getObjectCreateShortText() {
    return pht('Tạo mới dịch vụ');
  }

  protected function getObjectName() {
    return pht('Dịch vụ');
  }

  protected function getEditorURI() {
    return '/almanac/service/edit/';
  }

  protected function getObjectCreateCancelURI($object) {
    return '/almanac/service/';
  }

  protected function getObjectViewURI($object) {
    return $object->getURI();
  }

  protected function getCreateNewObjectPolicy() {
    return $this->getApplication()->getPolicy(
      AlmanacCreateServicesCapability::CAPABILITY);
  }

  protected function buildCustomEditFields($object) {
    return array(
      id(new PhabricatorTextEditField())
        ->setKey('name')
        ->setLabel(pht('Tên'))
        ->setDescription(pht('Tên dịch vụ.'))
        ->setTransactionType(AlmanacServiceTransaction::TYPE_NAME)
        ->setIsRequired(true)
        ->setValue($object->getName()),
    );
  }

}
