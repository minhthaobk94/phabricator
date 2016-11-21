<?php

final class AlmanacCreateDevicesCapability
  extends PhabricatorPolicyCapability {

  const CAPABILITY = 'almanac.devices';

  public function getCapabilityName() {
    return pht('Có thể tạo mới thiết bị');
  }

  public function describeCapabilityRejection() {
    return pht('Bạn không có quyền tạo mới.');
  }

}
