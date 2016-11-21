<?php

final class AlmanacCreateNamespacesCapability
  extends PhabricatorPolicyCapability {

  const CAPABILITY = 'almanac.namespaces';

  public function getCapabilityName() {
    return pht('Có thể tạo mới tên');
  }

  public function describeCapabilityRejection() {
    return pht('Bạn không được phép tạo mới tên.');
  }

}
