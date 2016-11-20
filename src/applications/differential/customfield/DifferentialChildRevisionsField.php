<?php

final class DifferentialChildRevisionsField
  extends DifferentialCustomField {

  public function getFieldKey() {
    return 'differential:dependencies';
  }

  public function getFieldName() {
    return pht('Bản sửa đổi con');
  }

  public function canDisableField() {
    return false;
  }

  public function getFieldDescription() {
    return pht('Danh sách các phiên bản này là phụ thuộc vào bằng.');
  }

}
