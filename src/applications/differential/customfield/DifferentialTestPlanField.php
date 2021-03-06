<?php

final class DifferentialTestPlanField
  extends DifferentialCoreCustomField {

  public function getFieldKey() {
    return 'differential:test-plan';
  }

  public function getFieldKeyForConduit() {
    return 'testPlan';
  }

  public function getFieldName() {
    return pht('Kế hoạch kiểm tra');
  }

  public function getFieldDescription() {
    return pht('Hành động thực hiện để xác minh hành vi của sự thay đổi.');
  }

  protected function readValueFromRevision(
    DifferentialRevision $revision) {
    if (!$revision->getID()) {
      return null;
    }
    return $revision->getTestPlan();
  }

  protected function writeValueToRevision(
    DifferentialRevision $revision,
    $value) {
    $revision->setTestPlan($value);
  }

  protected function isCoreFieldRequired() {
    return PhabricatorEnv::getEnvConfig('differential.require-test-plan-field');
  }

  public function canDisableField() {
    return true;
  }

  protected function getCoreFieldRequiredErrorString() {
    return pht(
      'Bạn phải cung cấp một kế hoạch kiểm tra. Mô tả các hành động mà bạn thực hiện.
       Để xác minh hành vi của các thay đổi này.');
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
      '%s cập nhật các kế hoạch thử nghiệm cho phiên bản này.',
      $xaction->renderHandleLink($author_phid));
  }

  public function getApplicationTransactionTitleForFeed(
    PhabricatorApplicationTransaction $xaction) {

    $object_phid = $xaction->getObjectPHID();
    $author_phid = $xaction->getAuthorPHID();
    $old = $xaction->getOldValue();
    $new = $xaction->getNewValue();

    return pht(
      '%s cập nhật các kế hoạch kiểm tra cho%s.',
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
      $document->addField('plan', $this->getValue());
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
    return PHUIPropertyListView::ICON_TESTPLAN;
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

  public function getCommitMessageLabels() {
    return array(
      'Test Plan',
      'Testplan',
      'Tested',
      'Tests',
    );
  }

  public function validateCommitMessageValue($value) {
    if (!strlen($value) && $this->isCoreFieldRequired()) {
      throw new DifferentialFieldValidationException(
        $this->getCoreFieldRequiredErrorString());
    }
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

    $test_plan = $this->getValue();
    if (!strlen(trim($test_plan))) {
      return;
    }

    $body->addRemarkupSection(pht('KẾ HOẠCH KIỂM TRA '), $test_plan);
  }


}
