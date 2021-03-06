<?php

final class AlmanacCreateNetworksCapability
  extends PhabricatorPolicyCapability {

  const CAPABILITY = 'almanac.networks';

  public function getCapabilityName() {
    return pht('Có thể tạo mới mạng');
  }

  public function describeCapabilityRejection() {
    return pht('Bạn không có quyền tạo mới mạng.');
  }

}
