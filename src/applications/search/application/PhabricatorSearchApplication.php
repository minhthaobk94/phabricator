<?php

final class PhabricatorSearchApplication extends PhabricatorApplication {

  public function getBaseURI() {
    return '/search/';
  }

  public function getName() {
    return pht('Tìm kiếm');
  }

  public function getShortDescription() {
    return pht('Tìm kiếm toàn bộ');
  }

  public function getFlavorText() {
    return pht('Tìm kiếm ở đường ống lớn.');
  }

  public function getIcon() {
    return 'fa-search';
  }

  public function isLaunchable() {
    return false;
  }

  public function getRoutes() {
    return array(
      '/search/' => array(
        '(?:query/(?P<queryKey>[^/]+)/)?' => 'PhabricatorSearchController',
        'index/(?P<phid>[^/]+)/' => 'PhabricatorSearchIndexController',
        'hovercard/'
          => 'PhabricatorSearchHovercardController',
        'edit/(?P<queryKey>[^/]+)/' => 'PhabricatorSearchEditController',
        'delete/(?P<queryKey>[^/]+)/(?P<engine>[^/]+)/'
          => 'PhabricatorSearchDeleteController',
        'order/(?P<engine>[^/]+)/' => 'PhabricatorSearchOrderController',
        'rel/(?P<relationshipKey>[^/]+)/(?P<sourcePHID>[^/]+)/'
          => 'PhabricatorSearchRelationshipController',
        'source/(?P<relationshipKey>[^/]+)/(?P<sourcePHID>[^/]+)/'
          => 'PhabricatorSearchRelationshipSourceController',
      ),
    );
  }

}
