<?php

final class PhabricatorFactApplication extends PhabricatorApplication {

  public function getShortDescription() {
    return pht('Biểu đồ và phân tích dữ liệu');
  }

  public function getName() {
    return pht('Sự kiện');
  }

  public function getBaseURI() {
    return '/fact/';
  }

  public function getIcon() {
    return 'fa-line-chart';
  }

  public function getApplicationGroup() {
    return self::GROUP_UTILITIES;
  }

  public function isPrototype() {
    return true;
  }

  public function getRoutes() {
    return array(
      '/fact/' => array(
        '' => 'PhabricatorFactHomeController',
        'chart/' => 'PhabricatorFactChartController',
      ),
    );
  }

}
