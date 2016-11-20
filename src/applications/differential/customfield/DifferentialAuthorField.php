<?php

final class DifferentialAuthorField
  extends DifferentialCustomField {

  public function getFieldKey() {
    return 'differential:author';
  }

  public function getFieldName() {
    return pht('Tác giả');
  }

  public function getFieldDescription() {
    return pht('Lưu trữ các tác giả sửa đổi.');
  }

  public function canDisableField() {
    return false;
  }

  public function shouldAppearInPropertyView() {
    return false;
  }

  public function renderPropertyViewLabel() {
    return $this->getFieldName();
  }

  public function getRequiredHandlePHIDsForPropertyView() {
    return array($this->getObject()->getAuthorPHID());
  }

  public function renderPropertyViewValue(array $handles) {
    return $handles[$this->getObject()->getAuthorPHID()]->renderHovercardLink();
  }

}
