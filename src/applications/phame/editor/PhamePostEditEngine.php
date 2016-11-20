<?php

final class PhamePostEditEngine
  extends PhabricatorEditEngine {

  private $blog;

  const ENGINECONST = 'phame.post';

  public function getEngineName() {
    return pht('Các bài đăng Blog');
  }

  public function getSummaryHeader() {
    return pht('Cấu hình Blog Post Forms');
  }

  public function getSummaryText() {
    return pht('Cấu hình và chỉnh sửa các bài viết của blog trong Phame.');
  }

  public function setBlog(PhameBlog $blog) {
    $this->blog = $blog;
    return $this;
  }

  public function getEngineApplicationClass() {
    return 'PhabricatorPhameApplication';
  }

  protected function newEditableObject() {
    $viewer = $this->getViewer();

    if ($this->blog) {
      $blog = $this->blog;
    } else {
      $blog = PhameBlog::initializeNewBlog($viewer);
    }

    return PhamePost::initializePost($viewer, $blog);
  }

  protected function newObjectQuery() {
    return new PhamePostQuery();
  }

  protected function getObjectCreateTitleText($object) {
    return pht('Tạo bài đăng mới');
  }

  protected function getObjectEditTitleText($object) {
    return pht('Sửa %s', $object->getTitle());
  }

  protected function getObjectEditShortText($object) {
    return $object->getTitle();
  }

  protected function getObjectCreateShortText() {
    return pht('Tạo bài đăng mới');
  }

  protected function getObjectName() {
    return pht('Đăng');
  }

  protected function getObjectViewURI($object) {
    return $object->getViewURI();
  }

  protected function getEditorURI() {
    return $this->getApplication()->getApplicationURI('post/edit/');
  }

  protected function buildCustomEditFields($object) {
    $blog_phid = $object->getBlog()->getPHID();

    return array(
      id(new PhabricatorHandlesEditField())
        ->setKey('blog')
        ->setLabel(pht('Blog'))
        ->setDescription(pht('Blog để đăng bài viết này để.'))
        ->setConduitDescription(
          pht('Chọn một blog để tạo một bài đăng trên (hoặc di chuyển một bài viết).'))
        ->setConduitTypeDescription(pht('PHID của Blog.'))
        ->setAliases(array('blogPHID'))
        ->setTransactionType(PhamePostTransaction::TYPE_BLOG)
        ->setHandleParameterType(new AphrontPHIDListHTTPParameterType())
        ->setSingleValue($blog_phid)
        ->setIsReorderable(false)
        ->setIsDefaultable(false)
        ->setIsLockable(false)
        ->setIsLocked(true),
      id(new PhabricatorTextEditField())
        ->setKey('title')
        ->setLabel(pht('Tiêu đề '))
        ->setDescription(pht('Đăng tiêu đề.'))
        ->setConduitDescription(pht('Lặp lại tiêu đề.'))
        ->setConduitTypeDescription(pht('Tiêu đề mới.'))
        ->setTransactionType(PhamePostTransaction::TYPE_TITLE)
        ->setValue($object->getTitle()),
      id(new PhabricatorTextEditField())
        ->setKey('subtitle')
        ->setLabel(pht('Tiêu đề con'))
        ->setDescription(pht('Đăng tiêu đề con.'))
        ->setConduitDescription(pht('Thay đổi tiêu đề con.'))
        ->setConduitTypeDescription(pht('Tiêu đề con mới.'))
        ->setTransactionType(PhamePostTransaction::TYPE_SUBTITLE)
        ->setValue($object->getSubtitle()),
      id(new PhabricatorSelectEditField())
        ->setKey('visibility')
        ->setLabel(pht('Tầm nhìn'))
        ->setDescription(pht('Đăng tầm nhìn.'))
        ->setConduitDescription(pht('Thay đổi tầm nhìn.'))
        ->setConduitTypeDescription(pht('Tầm nhìn mới.'))
        ->setTransactionType(PhamePostTransaction::TYPE_VISIBILITY)
        ->setValue($object->getVisibility())
        ->setOptions(PhameConstants::getPhamePostStatusMap()),
      id(new PhabricatorRemarkupEditField())
        ->setKey('body')
        ->setLabel(pht('Thân bài'))
        ->setDescription(pht('Đăng thân bài.'))
        ->setConduitDescription(pht('Thay đổi thân bài.'))
        ->setConduitTypeDescription(pht('Thân bài mới.'))
        ->setTransactionType(PhamePostTransaction::TYPE_BODY)
        ->setValue($object->getBody())
        ->setPreviewPanel(
          id(new PHUIRemarkupPreviewPanel())
            ->setHeader(pht('Đăng Blog'))
            ->setPreviewType(PHUIRemarkupPreviewPanel::DOCUMENT)),
    );
  }

}
