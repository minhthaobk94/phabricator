<?php

final class PhabricatorUIExamplesApplication extends PhabricatorApplication {

  public function getBaseURI() {
    return '/uiexample/';
  }

  public function getShortDescription() {
    return pht('Phát triển mẫu UI');
  }

  public function getName() {
    return pht('Mẫu UI');
  }

  public function getIcon() {
    return 'fa-magnet';
  }

  public function getTitleGlyph() {
    return "\xE2\x8F\x9A";
  }

  public function getFlavorText() {
    return pht('Một bộ sưu tập nghệ thuật');
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
