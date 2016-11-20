<?php

final class DifferentialDefaultViewCapability
  extends PhabricatorPolicyCapability {

  const CAPABILITY = 'differential.default.view';

  public function getCapabilityName() {
    return pht('Xem chính sách mặc định');
  }

  public function shouldAllowPublicPolicySetting() {
    return true;
  }

}
