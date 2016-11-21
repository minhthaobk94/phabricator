<?php

final class PhabricatorSlowvoteApplication extends PhabricatorApplication {

  public function getBaseURI() {
    return '/vote/';
  }

  public function getIcon() {
    return 'fa-bar-chart';
  }

  public function getName() {
    return pht('Bình chọn');
  }

  public function getShortDescription() {
    return pht('Bình chọn ứng xử');
  }

  public function getTitleGlyph() {
    return "\xE2\x9C\x94";
  }

  public function getHelpDocumentationArticles(PhabricatorUser $viewer) {
    return array(
      array(
        'name' => pht('Hướng dẫn sử dụng'),
        'href' => PhabricatorEnv::getDoclink('Slowvote User Guide'),
      ),
    );
  }

  public function getFlavorText() {
    return pht('Thiết kế bởi người commit.');
  }

  public function getApplicationGroup() {
    return self::GROUP_UTILITIES;
  }

  public function getRemarkupRules() {
    return array(
      new SlowvoteRemarkupRule(),
    );
  }

  public function getRoutes() {
    return array(
      '/V(?P<id>[1-9]\d*)' => 'PhabricatorSlowvotePollController',
      '/vote/' => array(
        '(?:query/(?P<queryKey>[^/]+)/)?'
          => 'PhabricatorSlowvoteListController',
        'create/' => 'PhabricatorSlowvoteEditController',
        'edit/(?P<id>[1-9]\d*)/' => 'PhabricatorSlowvoteEditController',
        '(?P<id>[1-9]\d*)/' => 'PhabricatorSlowvoteVoteController',
        'comment/(?P<id>[1-9]\d*)/' => 'PhabricatorSlowvoteCommentController',
        'close/(?P<id>[1-9]\d*)/' => 'PhabricatorSlowvoteCloseController',
      ),
    );
  }

  protected function getCustomCapabilities() {
    return array(
      PhabricatorSlowvoteDefaultViewCapability::CAPABILITY => array(
        'caption' => pht('Mặc định chính sách xem bình chọn.'),
        'template' => PhabricatorSlowvotePollPHIDType::TYPECONST,
        'capability' => PhabricatorPolicyCapability::CAN_VIEW,
      ),
    );
  }

}
