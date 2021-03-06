<?php

final class PhabricatorTokensApplication extends PhabricatorApplication {

  public function getName() {
    return pht('Mã thông báo');
  }

  public function getBaseURI() {
    return '/token/';
  }

  public function getIcon() {
    return 'fa-thumbs-up';
  }

  public function getTitleGlyph() {
    return "\xE2\x99\xA6";
  }

  public function getShortDescription() {
    return pht('Giải thưởng và Có được');
  }

  public function getApplicationGroup() {
    return self::GROUP_UTILITIES;
  }

  public function getRoutes() {
    return array(
      '/token/' => array(
        '' => 'PhabricatorTokenGivenController',
        'given/' => 'PhabricatorTokenGivenController',
        'give/(?<phid>[^/]+)/' => 'PhabricatorTokenGiveController',
        'leaders/' => 'PhabricatorTokenLeaderController',
      ),
    );
  }

  public function getEventListeners() {
    return array(
      new PhabricatorTokenUIEventListener(),
    );
  }

}
