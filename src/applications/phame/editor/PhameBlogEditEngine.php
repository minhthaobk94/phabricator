<?php

final class PhameBlogEditEngine
  extends PhabricatorEditEngine {

  const ENGINECONST = 'phame.blog';

  public function getEngineName() {
    return pht('Blogs');
  }

  public function getEngineApplicationClass() {
    return 'PhabricatorPhameApplication';
  }

  public function getSummaryHeader() {
    return pht('Cấu hình Phame Blog Forms');
  }

  public function getSummaryText() {
    return pht('Cấu hình như thế nào blog trong Phame được tạo ra và chỉnh sửa.');
  }

  protected function newEditableObject() {
    return PhameBlog::initializeNewBlog($this->getViewer());
  }

  protected function newObjectQuery() {
    return id(new PhameBlogQuery())
      ->needProfileImage(true);
  }

  protected function getObjectCreateTitleText($object) {
    return pht('Tạo mới Blog');
  }

  protected function getObjectEditTitleText($object) {
    return pht('Sửa %s', $object->getName());
  }

  protected function getObjectEditShortText($object) {
    return $object->getName();
  }

  protected function getObjectCreateShortText() {
    return pht('Tạo mới Blog');
  }

  protected function getObjectName() {
    return pht('Blog');
  }

  protected function getObjectCreateCancelURI($object) {
    return $this->getApplication()->getApplicationURI('blog/');
  }

  protected function getEditorURI() {
    return $this->getApplication()->getApplicationURI('blog/edit/');
  }

  protected function getObjectViewURI($object) {
    return $object->getManageURI();
  }

  protected function getCreateNewObjectPolicy() {
    return $this->getApplication()->getPolicy(
      PhameBlogCreateCapability::CAPABILITY);
  }

  protected function buildCustomEditFields($object) {
    return array(
      id(new PhabricatorTextEditField())
        ->setKey('name')
        ->setLabel(pht('Name'))
        ->setDescription(pht('Blog name.'))
        ->setConduitDescription(pht('Trở về tiêu đề cũ Blog.'))
        ->setConduitTypeDescription(pht('Tiêu đề mới Blog.'))
        ->setTransactionType(PhameBlogTransaction::TYPE_NAME)
        ->setValue($object->getName()),
      id(new PhabricatorTextEditField())
        ->setKey('subtitle')
        ->setLabel(pht('Tiêu đề con'))
        ->setDescription(pht('Tiêu đề con Blog .'))
        ->setConduitDescription(pht('Thay đổi tiêu đề con Blog .'))
        ->setConduitTypeDescription(pht('Tiêu đề con Blog mới.'))
        ->setTransactionType(PhameBlogTransaction::TYPE_SUBTITLE)
        ->setValue($object->getSubtitle()),
     id(new PhabricatorRemarkupEditField())
        ->setKey('description')
        ->setLabel(pht('Mô tả'))
        ->setDescription(pht('Mô tả Blog.'))
        ->setConduitDescription(pht('Thay đổi mô tả Blog.'))
        ->setConduitTypeDescription(pht('Mô tả mới.'))
        ->setTransactionType(PhameBlogTransaction::TYPE_DESCRIPTION)
        ->setValue($object->getDescription()),
      id(new PhabricatorTextEditField())
        ->setKey('domainFullURI')
        ->setLabel(pht('Miền đầy đủ URI'))
        ->setControlInstructions(pht('Tạo miền  URI đầy đủ  nếu bạn có kế hoạch '.
          'phục vụ blog này trên một tên miền khác . Tên trang chủ và  '.
          'Parent Site URI được tùy chọn nhưng hữu ích vì chúng cung cấp các liên kết'.
          'từ blog trở lại trang chủ của bạn.'))
        ->setDescription(pht('Miền đầy đủ Blog URI.'))
        ->setConduitDescription(pht('Thay đổi tên miền đầy đủ Blog URI.'))
        ->setConduitTypeDescription(pht('Tên miền đầy đủ mới Blog URI.'))
        ->setValue($object->getDomainFullURI())
        ->setTransactionType(PhameBlogTransaction::TYPE_FULLDOMAIN),
      id(new PhabricatorTextEditField())
        ->setKey('parentSite')
        ->setLabel(pht('Parent Site Name'))
        ->setDescription(pht('Blog parent site name.'))
        ->setConduitDescription(pht('Thay đổi blog parent site name.'))
        ->setConduitTypeDescription(pht('Tạo mới blog parent site name.'))
        ->setValue($object->getParentSite())
        ->setTransactionType(PhameBlogTransaction::TYPE_PARENTSITE),
      id(new PhabricatorTextEditField())
        ->setKey('parentDomain')
        ->setLabel(pht('Parent Site URI'))
        ->setDescription(pht('Blog parent domain name.'))
        ->setConduitDescription(pht('Thay đổi blog parent domain.'))
        ->setConduitTypeDescription(pht('Tạo mới  blog parent domain.'))
        ->setValue($object->getParentDomain())
        ->setTransactionType(PhameBlogTransaction::TYPE_PARENTDOMAIN),
      id(new PhabricatorSelectEditField())
        ->setKey('status')
        ->setLabel(pht('Trạng thái'))
        ->setTransactionType(PhameBlogTransaction::TYPE_STATUS)
        ->setIsConduitOnly(true)
        ->setOptions(PhameBlog::getStatusNameMap())
        ->setDescription(pht('Hoạt động hoặc lưu trữ.'))
        ->setConduitDescription(pht('Hoạt động hoặc lưu trữ blog.'))
        ->setConduitTypeDescription(pht('Tình trạng Blog mới.'))
        ->setValue($object->getStatus()),
    );
  }

}
