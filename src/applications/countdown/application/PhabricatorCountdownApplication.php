<?php

final class PhabricatorCountdownApplication extends PhabricatorApplication {

  public function getBaseURI() {
    return '/countdown/';
  }

  public function getIcon() {
    return 'fa-rocket';
  }

  public function getName() {
    return pht('Đếm ngược');
  }

  public function getShortDescription() {
    return pht('Đếm ngược đến sự kiện');
  }

  public function getTitleGlyph() {
    return "\xE2\x9A\xB2";
  }

  public function getFlavorText() {
    return pht('Sử dụng đầy đủ tính năng của ALU của bạn.');
  }

  public function getApplicationGroup() {
    return self::GROUP_UTILITIES;
  }

  public function getRemarkupRules() {
    return array(
      new PhabricatorCountdownRemarkupRule(),
    );
  }

  public function getRoutes() {
    return array(
      '/C(?P<id>[1-9]\d*)' => 'PhabricatorCountdownViewController',
      '/countdown/' => array(
        '(?:query/(?P<queryKey>[^/]+)/)?'
          => 'PhabricatorCountdownListController',
        '(?P<id>[1-9]\d*)/'
          => 'PhabricatorCountdownViewController',
        'comment/(?P<id>[1-9]\d*)/'
          => 'PhabricatorCountdownCommentController',
        $this->getEditRoutePattern('edit/')
          => 'PhabricatorCountdownEditController',
        'delete/(?P<id>[1-9]\d*)/'
          => 'PhabricatorCountdownDeleteController',
      ),
    );
  }

  protected function getCustomCapabilities() {
    return array(
      PhabricatorCountdownDefaultViewCapability::CAPABILITY => array(
        'caption' => pht('Xem dạng mặc định chính sách đối với đồng hồ đếm ngược mới.'),
        'template' => PhabricatorCountdownCountdownPHIDType::TYPECONST,
        'capability' => PhabricatorPolicyCapability::CAN_VIEW,
      ),
      PhabricatorCountdownDefaultEditCapability::CAPABILITY => array(
        'caption' => pht('Mặc định chỉnh sửa chính sách đếm ngược mới.'),
        'template' => PhabricatorCountdownCountdownPHIDType::TYPECONST,
        'capability' => PhabricatorPolicyCapability::CAN_EDIT,
      ),
    );
  }

}
