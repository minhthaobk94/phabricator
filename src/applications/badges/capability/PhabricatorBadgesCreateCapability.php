<?php

final class PhabricatorBadgesCreateCapability
  extends PhabricatorPolicyCapability {

  const CAPABILITY = 'badges.default.create';

  public function getCapabilityName() {
    return pht('Có thể tạo danh hiệu');
  }

  public function describeCapabilityRejection() {
    return pht('Bạn không có quyền tạo phù hiệu.');
  }

}
