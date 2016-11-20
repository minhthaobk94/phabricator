<?php

final class DifferentialTitleField
  extends DifferentialCoreCustomField {

  public function getFieldKey() {
    return 'differential:title';
  }

  public function getFieldKeyForConduit() {
    return 'title';
  }

  public function getFieldName() {
    return pht('Tiêu đề ');
  }

  public function getFieldDescription() {
    return pht('Lưu trữ các tiêu đề sửa đổi.');
  }

  public static function getDefaultTitle() {
    return pht('<<Thay thế dòng này với sửa đổi Tiêu đề của bạn>>');
  }

  protected function readValueFromRevision(
    DifferentialRevision $revision) {
    return $revision->getTitle();
  }

  protected function writeValueToRevision(
    DifferentialRevision $revision,
    $value) {
    $revision->setTitle($value);
  }

  protected function getCoreFieldRequiredErrorString() {
    return pht('Bạn phải chọn một tiêu đề cho phiên bản này.');
  }

  public function readValueFromRequest(AphrontRequest $request) {
    $this->setValue($request->getStr($this->getFieldKey()));
  }

  protected function isCoreFieldRequired() {
    return true;
  }

  public function renderEditControl(array $handles) {
    return id(new AphrontFormTextAreaControl())
      ->setHeight(AphrontFormTextAreaControl::HEIGHT_VERY_SHORT)
      ->setName($this->getFieldKey())
      ->setValue($this->getValue())
      ->setError($this->getFieldError())
      ->setLabel($this->getFieldName());
  }

  public function getApplicationTransactionTitle(
    PhabricatorApplicationTransaction $xaction) {
    $author_phid = $xaction->getAuthorPHID();
    $old = $xaction->getOldValue();
    $new = $xaction->getNewValue();

    if (strlen($old)) {
      return pht(
        '%s đổi tên thành phiên bản này từ "%s" đến "%s".',
        $xaction->renderHandleLink($author_phid),
        $old,
        $new);
    } else {
      return pht(
        '%s tạo ra phiên bản này.',
        $xaction->renderHandleLink($author_phid));
    }
  }

  public function getApplicationTransactionTitleForFeed(
    PhabricatorApplicationTransaction $xaction) {

    $object_phid = $xaction->getObjectPHID();
    $author_phid = $xaction->getAuthorPHID();
    $old = $xaction->getOldValue();
    $new = $xaction->getNewValue();

    if (strlen($old)) {
      return pht(
        '%s đổi tên thành từ %s,  "%s" đến "%s".',
        $xaction->renderHandleLink($author_phid),
        $xaction->renderHandleLink($object_phid),
        $old,
        $new);
    } else {
      return pht(
        '%s tạo %s.',
        $xaction->renderHandleLink($author_phid),
        $xaction->renderHandleLink($object_phid));
    }
  }

  public function shouldAppearInCommitMessage() {
    return true;
  }

  public function shouldOverwriteWhenCommitMessageIsEdited() {
    return true;
  }

  public function validateCommitMessageValue($value) {
    if (!strlen($value)) {
      throw new DifferentialFieldValidationException(
        pht(
          'Bạn phải cung cấp một tiêu đề sửa đổi trong dòng đầu tiên '.
           'Thông điệp cam kết của bạn.'));
    }

    if (preg_match('/^<<.*>>$/', $value)) {
      throw new DifferentialFieldValidationException(
        pht(
          'Thay thế dòng "% s" với một tiêu đề sửa đổi con người có thể đọc được mà '.
           'Mô tả các thay đổi mà bạn đang làm.',
          self::getDefaultTitle()));
    }
  }

}
