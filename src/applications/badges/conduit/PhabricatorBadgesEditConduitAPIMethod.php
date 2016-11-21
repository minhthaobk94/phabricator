<?php

final class PhabricatorBadgesEditConduitAPIMethod
  extends PhabricatorEditEngineAPIMethod {

  public function getAPIMethodName() {
    return 'badges.edit';
  }

  public function newEditEngine() {
    return new PhabricatorBadgesEditEngine();
  }

  public function getMethodSummary() {
    return pht(
      'Áp dụng các giao dịch để tạo ra một huy hiệu mới hoặc chỉnh sửa một hiện.');
  }

}
