<?php

final class AlmanacCreateServicesCapability
  extends PhabricatorPolicyCapability {

  const CAPABILITY = 'almanac.services';

  public function getCapabilityName() {
    return pht('Có thể tạo mới dịch vụ');
  }

  public function describeCapabilityRejection() {
    return pht('Bạn không có quyền tạo mới dịch vụ.');
  }

}
