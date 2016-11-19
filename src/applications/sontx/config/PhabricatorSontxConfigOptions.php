<?php

final class PhabricatorSontxConfigOptions
  extends PhabricatorApplicationConfigOptions {

  public function getName() {
    return pht('Sontx');
  }

  public function getDescription() {
    return pht('Configure sontx code review.');
  }

  public function getIcon() {
    return 'fa-cog';
  }

  public function getGroup() {
    return 'apps';
  }

  public function getOptions() {
    $caches_href = PhabricatorEnv::getDoclink('Managing Caches');

    $custom_field_type = 'custom:PhabricatorCustomFieldConfigOptionType';
 }

}
