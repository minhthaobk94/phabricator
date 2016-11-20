<?php

final class PhamePostEditConduitAPIMethod
  extends PhabricatorEditEngineAPIMethod {

  public function getAPIMethodName() {
    return 'phame.post.edit';
  }

  public function newEditEngine() {
    return new PhamePostEditEngine();
  }

  public function getMethodSummary() {
    return pht('Tạo mới hoặc chỉnh sửa bài viết trên blog trong  Phame.');
  }

}
