<?php

final class PhabricatorUIExamplesApplication extends PhabricatorApplication {

  public function getBaseURI() {
    return '/uiexample/';
  }

  public function getShortDescription() {
    return pht('Giao diện người dùng phát triển ví dụ');
  }

  public function getName() {
    return pht('UIExamples');
  }

  public function getIcon() {
    return 'fa-magnet';
  }

  public function getTitleGlyph() {
    return "\xE2\x8F\x9A";
  }

  public function getFlavorText() {
    return pht('Một bộ sưu tập nghệ thuật hiện đại.');
  }

  public function getApplicationGroup() {
    return self::GROUP_DEVELOPER;
  }

  public function isPrototype() {
    return true;
  }

  public function getApplicationOrder() {
    return 0.110;
  }

  public function getRoutes() {
    return array(
      '/uiexample/' => array(
        '' => 'PhabricatorUIExampleRenderController',
        'view/(?P<class>[^/]+)/' => 'PhabricatorUIExampleRenderController',
      ),
    );
  }

}
