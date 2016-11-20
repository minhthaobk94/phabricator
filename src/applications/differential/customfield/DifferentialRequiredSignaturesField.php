<?php

final class DifferentialRequiredSignaturesField
  extends DifferentialCoreCustomField {

  public function getFieldKey() {
    return 'differential:required-signatures';
  }

  public function getFieldName() {
    return pht('Chữ ký bắt buộc');
  }

  public function getFieldDescription() {
    return pht('Hiển thị cần thỏa thuận pháp lý.');
  }

  public function shouldAppearInPropertyView() {
    return true;
  }

  public function shouldAppearInEditView() {
    return false;
  }

  protected function readValueFromRevision(DifferentialRevision $revision) {
    return self::loadForRevision($revision);
  }

  public static function loadForRevision($revision) {
    $app_legalpad = 'PhabricatorLegalpadApplication';
    if (!PhabricatorApplication::isClassInstalled($app_legalpad)) {
      return array();
    }

    if (!$revision->getPHID()) {
      return array();
    }

    $phids = PhabricatorEdgeQuery::loadDestinationPHIDs(
      $revision->getPHID(),
      LegalpadObjectNeedsSignatureEdgeType::EDGECONST);

    if ($phids) {

      // NOTE: We're bypassing permissions to pull these. We have to expose
      // some information about signature status in order to implement this
      // field meaningfully (otherwise, we could not tell reviewers that they
      // can't accept the revision yet), but that's OK because the only way to
      // require signatures is with a "Global" Herald rule, which requires a
      // high level of access.

      $signatures = id(new LegalpadDocumentSignatureQuery())
        ->setViewer(PhabricatorUser::getOmnipotentUser())
        ->withDocumentPHIDs($phids)
        ->withSignerPHIDs(array($revision->getAuthorPHID()))
        ->execute();
      $signatures = mpull($signatures, null, 'getDocumentPHID');

      $phids = array_fuse($phids);
      foreach ($phids as $phid) {
        $phids[$phid] = isset($signatures[$phid]);
      }
    }

    return $phids;
  }

  public function getRequiredHandlePHIDsForPropertyView() {
    return array_keys($this->getValue());
  }

  public function renderPropertyViewValue(array $handles) {
    if (!$handles) {
      return null;
    }

    $author_phid = $this->getObject()->getAuthorPHID();
    $viewer_phid = $this->getViewer()->getPHID();

    $viewer_is_author = ($author_phid == $viewer_phid);

    $view = new PHUIStatusListView();
    foreach ($handles as $handle) {
      $item = id(new PHUIStatusItemView())
        ->setTarget($handle->renderLink());

      // NOTE: If the viewer isn't the author, we just show generic document
      // icons, because the granular information isn't very useful and there
      // is no need to disclose it.

      // If the viewer is the author, we show exactly what they need to sign.

      if (!$viewer_is_author) {
        $item->setIcon('fa-file-text-o bluegrey');
      } else {
        if (idx($this->getValue(), $handle->getPHID())) {
          $item->setIcon('fa-check-square-o green');
        } else {
          $item->setIcon('fa-times red');
        }
      }

      $view->addItem($item);
    }

    return $view;
  }

  public function getWarningsForDetailView() {
    if (!$this->haveAnyUnsignedDocuments()) {
      return array();
    }

    return array(
      pht(
        'Tác giả của phiên bản này không có chữ ký tất cả các yêu cầu '.
         'tài liệu hợp pháp. Việc sửa đổi không thể được chấp nhận cho đến khi '.
         'Văn bản được ký kết.'),
    );
  }

  private function haveAnyUnsignedDocuments() {
    foreach ($this->getValue() as $phid => $signed) {
      if (!$signed) {
        return true;
      }
    }

    return false;
  }

  public function getWarningsForRevisionHeader(array $handles) {
    if (!$this->haveAnyUnsignedDocuments()) {
      return array();
    }

    return array(
      pht('Việc sửa đổi này không thể được chấp nhận cho đến khi các yêu cầu pháp lý.Thỏa thuận đã được ký kết.'),
    );
  }

}
