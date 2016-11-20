<?php

final class PhameBlogCreateCapability
  extends PhabricatorPolicyCapability {

  const CAPABILITY = 'phame.blog.default.create';

  public function getCapabilityName() {
    return pht('Có thể tạo blog');
  }

  public function describeCapabilityRejection() {
    return pht('Bạn không được phép tạo blogs.');
  }

}
