<?php

final class PhabricatorBadgesApplication extends PhabricatorApplication {

  public function getName() {
    return pht('Danh hiệu');
  }

  public function getBaseURI() {
    return '/badges/';
  }

  public function getShortDescription() {
    return pht('Thành tựu và tiếng tăm');
  }

  public function getIcon() {
    return 'fa-trophy';
  }

  public function getFlavorText() {
    return pht('Xây dựng lòng tự trọng qua trò chơi điện tử ứng dụng hóa.');
  }

  public function getApplicationGroup() {
    return self::GROUP_UTILITIES;
  }

  public function canUninstall() {
    return true;
  }

  public function isPrototype() {
    return true;
  }

  public function getRoutes() {
    return array(
      '/badges/' => array(
        '(?:query/(?P<queryKey>[^/]+)/)?'
          => 'PhabricatorBadgesListController',
        'award/(?:(?P<id>\d+)/)?'
          => 'PhabricatorBadgesAwardController',
        'create/'
          => 'PhabricatorBadgesEditController',
        'comment/(?P<id>[1-9]\d*)/'
          => 'PhabricatorBadgesCommentController',
        $this->getEditRoutePattern('edit/')
            => 'PhabricatorBadgesEditController',
        'archive/(?:(?P<id>\d+)/)?'
          => 'PhabricatorBadgesArchiveController',
        'view/(?:(?P<id>\d+)/)?'
          => 'PhabricatorBadgesViewController',
        'recipients/(?P<id>[1-9]\d*)/'
          => 'PhabricatorBadgesEditRecipientsController',
        'recipients/(?P<id>[1-9]\d*)/remove/'
          => 'PhabricatorBadgesRemoveRecipientsController',

      ),
    );
  }

  protected function getCustomCapabilities() {
    return array(
      PhabricatorBadgesCreateCapability::CAPABILITY => array(
        'default' => PhabricatorPolicies::POLICY_ADMIN,
        'caption' => pht('Mặc định tạo ra chính sách cho phù hiệu.'),
      ),
      PhabricatorBadgesDefaultEditCapability::CAPABILITY => array(
        'default' => PhabricatorPolicies::POLICY_ADMIN,
        'caption' => pht('Chỉnh sửa chính sách mặc định cho phù hiệu.'),
        'template' => PhabricatorBadgesPHIDType::TYPECONST,
      ),
    );
  }

}
