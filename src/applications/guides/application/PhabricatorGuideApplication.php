<?php

final class PhabricatorGuideApplication extends PhabricatorApplication {

  public function getBaseURI() {
    return '/guides/';
  }

  public function getName() {
    return pht('Hướng dẫn');
  }

  public function getShortDescription() {
    return pht('Hướng dẫn ngắn');
  }

  public function getIcon() {
    return 'fa-map-o';
  }

  public function getApplicationGroup() {
    return self::GROUP_UTILITIES;
  }

  public function getRoutes() {
    return array(
      '/guides/' => array(
        '' => 'PhabricatorGuideModuleController',
        '(?P<module>[^/]+)/' => 'PhabricatorGuideModuleController',
       ),
    );
  }

}
