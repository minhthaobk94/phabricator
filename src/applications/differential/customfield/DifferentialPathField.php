<?php

final class DifferentialPathField
  extends DifferentialCustomField {

  public function getFieldKey() {
    return 'differential:path';
  }

  public function getFieldName() {
    return pht('Đường dẫn');
  }

  public function getFieldDescription() {
    return pht('Hiển thị các đường dẫn địa phương nơi khác đến từ.');
  }

  public function shouldDisableByDefault() {
    return true;
  }

  public function shouldAppearInPropertyView() {
    return true;
  }

  public function renderPropertyViewValue(array $handles) {
    return null;
  }

  public function shouldAppearInDiffPropertyView() {
    return true;
  }

  public function renderDiffPropertyViewLabel(DifferentialDiff $diff) {
    return $this->getFieldName();
  }

  public function renderDiffPropertyViewValue(DifferentialDiff $diff) {
    $path = $diff->getSourcePath();
    if (!$path) {
      return null;
    }

    return $path;
  }

}
