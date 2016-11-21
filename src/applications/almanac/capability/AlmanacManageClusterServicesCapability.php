<?php

final class AlmanacManageClusterServicesCapability
  extends PhabricatorPolicyCapability {

  const CAPABILITY = 'almanac.cluster';

  public function getCapabilityName() {
    return pht('Có thể quản lý cụm dịch vụ');
  }

  public function describeCapabilityRejection() {
    return pht(
      'Bạn không có quyền quản lý cumj dịch vụ.');
  }

}
