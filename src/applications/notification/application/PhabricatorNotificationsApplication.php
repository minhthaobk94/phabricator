<?php

final class PhabricatorNotificationsApplication extends PhabricatorApplication {

  public function getName() {
    return pht('Thông báo');
  }

  public function getBaseURI() {
    return '/notification/';
  }

  public function getShortDescription() {
    return pht('Cập nhật và cảnh báo');
  }

  public function getIcon() {
    return 'fa-bell';
  }

  public function getRoutes() {
    return array(
      '/notification/' => array(
        '(?:query/(?P<queryKey>[^/]+)/)?'
          => 'PhabricatorNotificationListController',
        'panel/' => 'PhabricatorNotificationPanelController',
        'individual/' => 'PhabricatorNotificationIndividualController',
        'clear/' => 'PhabricatorNotificationClearController',
        'test/' => 'PhabricatorNotificationTestController',
      ),
    );
  }

  public function isLaunchable() {
    return false;
  }

}
