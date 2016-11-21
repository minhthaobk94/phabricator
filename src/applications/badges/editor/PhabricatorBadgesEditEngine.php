<?php

final class PhabricatorBadgesEditEngine
  extends PhabricatorEditEngine {

  const ENGINECONST = 'badges.badge';

  public function getEngineName() {
    return pht('Badges');
  }

  public function getEngineApplicationClass() {
    return 'PhabricatorBadgesApplication';
  }

  public function getSummaryHeader() {
    return pht('Cấu hình Badges Forms');
  }

  public function getSummaryText() {
    return pht('Cấu tạo và chỉnh sửa các biểu mẫu trong Badges.');
  }

  protected function newEditableObject() {
    return PhabricatorBadgesBadge::initializeNewBadge($this->getViewer());
  }

  protected function newObjectQuery() {
    return new PhabricatorBadgesQuery();
  }

  protected function getObjectCreateTitleText($object) {
    return pht('Tạo mới Badge');
  }

  protected function getObjectEditTitleText($object) {
    return pht('Sửa Badge: %s', $object->getName());
  }

  protected function getObjectEditShortText($object) {
    return $object->getName();
  }

  protected function getObjectCreateShortText() {
    return pht('Tạo Badge');
  }

  protected function getObjectName() {
    return pht('Badge');
  }

  protected function getObjectCreateCancelURI($object) {
    return $this->getApplication()->getApplicationURI('/');
  }

  protected function getEditorURI() {
    return $this->getApplication()->getApplicationURI('edit/');
  }

  protected function getCommentViewHeaderText($object) {
    return pht('Render Honors');
  }

  protected function getCommentViewButtonText($object) {
    return pht('Chào');
  }

  protected function getObjectViewURI($object) {
    return $object->getViewURI();
  }

  protected function getCreateNewObjectPolicy() {
    return $this->getApplication()->getPolicy(
      PhabricatorBadgesCreateCapability::CAPABILITY);
  }

  protected function buildCustomEditFields($object) {

    return array(
      id(new PhabricatorTextEditField())
        ->setKey('name')
        ->setLabel(pht('Tên'))
        ->setDescription(pht('Tên Badge .'))
        ->setConduitTypeDescription(pht('Tên mới badge .'))
        ->setTransactionType(PhabricatorBadgesTransaction::TYPE_NAME)
        ->setValue($object->getName()),
      id(new PhabricatorTextEditField())
        ->setKey('flavor')
        ->setLabel(pht('Flavor  text  '))
        ->setDescription(pht('Mô tả ngắn về badge.'))
        ->setConduitTypeDescription(pht('Badge flavor mới.'))
        ->setValue($object->getFlavor())
        ->setTransactionType(PhabricatorBadgesTransaction::TYPE_FLAVOR),
      id(new PhabricatorIconSetEditField())
        ->setKey('icon')
        ->setLabel(pht('Biểu tượng'))
        ->setIconSet(new PhabricatorBadgesIconSet())
        ->setTransactionType(PhabricatorBadgesTransaction::TYPE_ICON)
        ->setConduitDescription(pht('Thay đổi biểu tượng badge.'))
        ->setConduitTypeDescription(pht('Biểu tượng badge mới.'))
        ->setValue($object->getIcon()),
      id(new PhabricatorSelectEditField())
        ->setKey('quality')
        ->setLabel(pht('Chất lượng'))
        ->setDescription(pht('Màu sắc và hiếm có của các badge.'))
        ->setConduitTypeDescription(pht('Chất lượng mới của badge.'))
        ->setValue($object->getQuality())
        ->setTransactionType(PhabricatorBadgesTransaction::TYPE_QUALITY)
        ->setOptions(PhabricatorBadgesQuality::getDropdownQualityMap()),
      id(new PhabricatorRemarkupEditField())
        ->setKey('description')
        ->setLabel(pht('Mô tả'))
        ->setDescription(pht('Mô tả Badge.'))
        ->setConduitTypeDescription(pht('Mô tả mới về Badge.'))
        ->setTransactionType(PhabricatorBadgesTransaction::TYPE_DESCRIPTION)
        ->setValue($object->getDescription()),
    );
  }

}
