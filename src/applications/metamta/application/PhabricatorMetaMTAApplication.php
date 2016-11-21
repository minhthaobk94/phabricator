<?php

final class PhabricatorMetaMTAApplication extends PhabricatorApplication {

  public function getName() {
    return pht('Mail');
  }

  public function getBaseURI() {
    return '/mail/';
  }

  public function getIcon() {
    return 'fa-send';
  }

  public function getShortDescription() {
    return pht('Gửi và nhận Email');
  }

  public function getFlavorText() {
    return pht('Mỗi chương trình cố gắng để mở rộng cho đến khi nó có thể đọc mail.');
  }

  public function getApplicationGroup() {
    return self::GROUP_ADMIN;
  }

  public function canUninstall() {
    return false;
  }

  public function getTypeaheadURI() {
    return '/mail/';
  }

  public function getRoutes() {
    return array(
      '/mail/' => array(
        '(query/(?P<queryKey>[^/]+)/)?' =>
          'PhabricatorMetaMTAMailListController',
        'detail/(?P<id>[1-9]\d*)/' => 'PhabricatorMetaMTAMailViewController',
        'sendgrid/' => 'PhabricatorMetaMTASendGridReceiveController',
        'mailgun/'  => 'PhabricatorMetaMTAMailgunReceiveController',
      ),
    );
  }

  public function getTitleGlyph() {
    return '@';
  }

}
