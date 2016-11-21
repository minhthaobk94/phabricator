<?php

final class DifferentialApplyPatchField
  extends DifferentialCustomField {

  public function getFieldKey() {
    return 'differential:apply-patch';
  }

  public function getFieldName() {
    return pht('Ứng dụng bản vá');
  }

  public function getFieldDescription() {
    return pht('Cung cấp hướng dẫn áp dụng một bản vá địa phương.');
  }

  public function shouldAppearInPropertyView() {
    return true;
  }

  public function renderPropertyViewLabel() {
    return $this->getFieldName();
  }

  public function renderPropertyViewValue(array $handles) {
    $mono = $this->getObject()->getMonogram();

    return phutil_tag('tt', array(), "arc patch {$mono}");
  }

}
