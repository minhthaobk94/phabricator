<?php

final class AlmanacNetworkEditEngine
  extends PhabricatorEditEngine {

  const ENGINECONST = 'almanac.network';

  public function isEngineConfigurable() {
    return false;
  }

  public function getEngineName() {
    return pht('Mạng');
  }

  public function getSummaryHeader() {
    return pht('Chỉnh sửa cấu hình');
  }

  public function getSummaryText() {
    return pht('Tính năng này để chỉnh sửa mạng.');
  }

  public function getEngineApplicationClass() {
    return 'PhabricatorAlmanacApplication';
  }

  protected function newEditableObject() {
    return AlmanacNetwork::initializeNewNetwork();
  }

  protected function newObjectQuery() {
    return new AlmanacNetworkQuery();
  }

  protected function getObjectCreateTitleText($object) {
    return pht('Tạo mới mạng');
  }

  protected function getObjectCreateButtonText($object) {
    return pht('Tạo mới mạng');
  }

  protected function getObjectEditTitleText($object) {
    return pht('Chỉnh sửa mạng: %s', $object->getName());
  }

  protected function getObjectEditShortText($object) {
    return pht('Chỉnh sửa mạng');
  }

  protected function getObjectCreateShortText() {
    return pht('Tạo mới mạng');
  }

  protected function getObjectName() {
    return pht('Mạng');
  }

  protected function getEditorURI() {
    return '/almanac/network/edit/';
  }

  protected function getObjectCreateCancelURI($object) {
    return '/almanac/network/';
  }

  protected function getObjectViewURI($object) {
    $id = $object->getID();
    return "/almanac/network/{$id}/";
  }

  protected function getCreateNewObjectPolicy() {
    return $this->getApplication()->getPolicy(
      AlmanacCreateNetworksCapability::CAPABILITY);
  }

  protected function buildCustomEditFields($object) {
    return array(
      id(new PhabricatorTextEditField())
        ->setKey('name')
        ->setLabel(pht('Tên'))
        ->setDescription(pht('Tên mạng.'))
        ->setTransactionType(AlmanacNetworkTransaction::TYPE_NAME)
        ->setIsRequired(true)
        ->setValue($object->getName()),
    );
  }

}
