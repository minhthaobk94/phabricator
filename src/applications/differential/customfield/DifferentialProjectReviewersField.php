<?php

final class DifferentialProjectReviewersField
  extends DifferentialCustomField {

  public function getFieldKey() {
    return 'differential:project-reviewers';
  }

  public function getFieldName() {
    return pht('Nhóm phản biện');
  }

  public function getFieldDescription() {
    return pht('Hiển thị phê bình dự án .');
  }

  public function shouldAppearInPropertyView() {
    return true;
  }

  public function canDisableField() {
    return false;
  }

  public function renderPropertyViewLabel() {
    return $this->getFieldName();
  }

  public function getRequiredHandlePHIDsForPropertyView() {
    return mpull($this->getProjectReviewers(), 'getReviewerPHID');
  }

  public function renderPropertyViewValue(array $handles) {
    $reviewers = $this->getProjectReviewers();
    if (!$reviewers) {
      return null;
    }

    $view = id(new DifferentialReviewersView())
      ->setUser($this->getViewer())
      ->setReviewers($reviewers)
      ->setHandles($handles);

    // TODO: Active diff stuff.

    return $view;
  }

  private function getProjectReviewers() {
    $reviewers = array();
    foreach ($this->getObject()->getReviewerStatus() as $reviewer) {
      if (!$reviewer->isUser()) {
        $reviewers[] = $reviewer;
      }
    }
    return $reviewers;
  }

  public function getProTips() {
    return array(
      pht(
        'Bạn có thể thêm một dự án như một thuê bao hoặc người nhận xét bằng văn bản'.
        '"%s"trong lĩnh vực thích hợp.',
        '#projectname'),
    );
  }

}
