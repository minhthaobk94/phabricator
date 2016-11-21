<?php

final class DifferentialSummaryField
  extends DifferentialCoreCustomField {

  public function getFieldKey() {
    return 'differential:summary';
  }

  public function getFieldKeyForConduit() {
    return 'summary';
  }

  public function getFieldName() {
    return pht('Tóm tắt');
  }

  public function getFieldDescription() {
    return pht('Lưu trữ một bản tóm tắt của các phiên bản.');
  }

  protected function readValueFromRevision(
    DifferentialRevision $revision) {
    if (!$revision->getID()) {
      return null;
    }
    return $revision->getSummary();
  }

  protected function writeValueToRevision(
    DifferentialRevision $revision,
    $value) {
    $revision->setSummary($value);
  }

  public function readValueFromRequest(AphrontRequest $request) {
    $this->setValue($request->getStr($this->getFieldKey()));
  }

  public function renderEditControl(array $handles) {
    return id(new PhabricatorRemarkupControl())
      ->setUser($this->getViewer())
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

    return pht(
      '%s cập nhật các bản tóm tắt cho phiên bản này.',
      $xaction->renderHandleLink($author_phid));
  }

  public function getApplicationTransactionTitleForFeed(
    PhabricatorApplicationTransaction $xaction) {

    $object_phid = $xaction->getObjectPHID();
    $author_phid = $xaction->getAuthorPHID();
    $old = $xaction->getOldValue();
    $new = $xaction->getNewValue();

    return pht(
      '%s cập nhật các bản tóm tắt cho %s.',
      $xaction->renderHandleLink($author_phid),
      $xaction->renderHandleLink($object_phid));
  }

  public function getApplicationTransactionHasChangeDetails(
    PhabricatorApplicationTransaction $xaction) {
    return true;
  }

  public function getApplicationTransactionChangeDetails(
    PhabricatorApplicationTransaction $xaction,
    PhabricatorUser $viewer) {
    return $xaction->renderTextCorpusChangeDetails(
      $viewer,
      $xaction->getOldValue(),
      $xaction->getNewValue());
  }

  public function shouldHideInApplicationTransactions(
    PhabricatorApplicationTransaction $xaction) {
    return ($xaction->getOldValue() === null);
  }

  public function shouldAppearInGlobalSearch() {
    return true;
  }

  public function updateAbstractDocument(
    PhabricatorSearchAbstractDocument $document) {
    if (strlen($this->getValue())) {
      $document->addField('body', $this->getValue());
    }
  }

  public function shouldAppearInPropertyView() {
    return true;
  }

  public function renderPropertyViewLabel() {
    return $this->getFieldName();
  }

  public function getStyleForPropertyView() {
    return 'block';
  }

  public function getIconForPropertyView() {
    return PHUIPropertyListView::ICON_SUMMARY;
  }

  public function renderPropertyViewValue(array $handles) {
    if (!strlen($this->getValue())) {
      return null;
    }

    return new PHUIRemarkupView($this->getViewer(), $this->getValue());
  }

  public function getApplicationTransactionRemarkupBlocks(
    PhabricatorApplicationTransaction $xaction) {
    return array($xaction->getNewValue());
  }

  public function shouldAppearInCommitMessage() {
    return true;
  }

  public function shouldAppearInCommitMessageTemplate() {
    return true;
  }

  public function shouldOverwriteWhenCommitMessageIsEdited() {
    return true;
  }

  public function shouldAppearInTransactionMail() {
    return true;
  }

  public function updateTransactionMailBody(
    PhabricatorMetaMTAMailBody $body,
    PhabricatorApplicationTransactionEditor $editor,
    array $xactions) {

    if (!$editor->getIsNewObject()) {
      return;
    }

    $summary = $this->getValue();
    if (!strlen(trim($summary))) {
      return;
    }

    $body->addRemarkupSection(pht('TÓM TẮT SỰ SỬA ĐỔI'), $summary);
  }

}
