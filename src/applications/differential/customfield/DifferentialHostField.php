<?php

final class DifferentialHostField
  extends DifferentialCustomField {

  public function getFieldKey() {
    return 'differential:host';
  }

  public function getFieldName() {
    return pht('Chủ');
  }

  public function getFieldDescription() {
    return pht('Hiển thị các máy chủ địa phương nơi khác đến từ.');
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
    $host = $diff->getSourceMachine();
    if (!$host) {
      return null;
    }

    return $host;
  }

}
