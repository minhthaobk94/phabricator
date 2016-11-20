<?php

final class PhabricatorSettingsApplication extends PhabricatorApplication {

  public function getBaseURI() {
    return '/settings/';
  }

  public function getName() {
    return pht('Cài đặt');
  }

  public function getShortDescription() {
    return pht('Sở thích của người sử dụng');
  }

  public function getIcon() {
    return 'fa-wrench';
  }

  public function canUninstall() {
    return false;
  }

  public function isLaunchable() {
    return false;
  }

  public function getRoutes() {
    $panel_pattern = '(?:page/(?P<pageKey>[^/]+)/(?:(?P<formSaved>saved)/)?)?';

    return array(
      '/settings/' => array(
        $this->getQueryRoutePattern() => 'PhabricatorSettingsListController',
        'user/(?P<username>[^/]+)/'.$panel_pattern
          => 'PhabricatorSettingsMainController',
        'builtin/(?P<builtin>global)/'.$panel_pattern
          => 'PhabricatorSettingsMainController',
        'panel/(?P<panel>[^/]+)/'
          => 'PhabricatorSettingsMainController',
        'adjust/' => 'PhabricatorSettingsAdjustController',
        'timezone/(?P<offset>[^/]+)/'
          => 'PhabricatorSettingsTimezoneController',
      ),
    );
  }

  public function getApplicationGroup() {
    return self::GROUP_UTILITIES;
  }

}
