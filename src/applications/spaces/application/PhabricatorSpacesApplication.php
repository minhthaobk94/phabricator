<?php

final class PhabricatorSpacesApplication extends PhabricatorApplication {

  public function getBaseURI() {
    return '/spaces/';
  }

  public function getName() {
    return pht('Không gian');
  }

  public function getShortDescription() {
    return pht('Luật về tên của không gian');
  }

  public function getIcon() {
    return 'fa-th-large';
  }

  public function getTitleGlyph() {
    return "\xE2\x97\x8B";
  }

  public function getFlavorText() {
    return pht('Điều khiển kết nối đến nhóm của đối tượng.');
  }

  public function getApplicationGroup() {
    return self::GROUP_UTILITIES;
  }

  public function canUninstall() {
    return false;
  }

  public function getHelpDocumentationArticles(PhabricatorUser $viewer) {
    return array(
      array(
        'name' => pht('Hướng dẫn sử dung'),
        'href' => PhabricatorEnv::getDoclink('Spaces User Guide'),
      ),
    );
  }

  public function getRemarkupRules() {
    return array(
      new PhabricatorSpacesRemarkupRule(),
    );
  }

  public function getRoutes() {
    return array(
      '/S(?P<id>[1-9]\d*)' => 'PhabricatorSpacesViewController',
      '/spaces/' => array(
        '(?:query/(?P<queryKey>[^/]+)/)?' => 'PhabricatorSpacesListController',
        'create/' => 'PhabricatorSpacesEditController',
        'edit/(?:(?P<id>\d+)/)?' => 'PhabricatorSpacesEditController',
        '(?P<action>activate|archive)/(?P<id>\d+)/'
          => 'PhabricatorSpacesArchiveController',
      ),
    );
  }

  protected function getCustomCapabilities() {
    return array(
      PhabricatorSpacesCapabilityCreateSpaces::CAPABILITY => array(
        'default' => PhabricatorPolicies::POLICY_ADMIN,
      ),
      PhabricatorSpacesCapabilityDefaultView::CAPABILITY => array(
        'caption' => pht('Mặc định luật cho không gian mới tạo.'),
        'template' => PhabricatorSpacesNamespacePHIDType::TYPECONST,
        'capability' => PhabricatorPolicyCapability::CAN_VIEW,
      ),
      PhabricatorSpacesCapabilityDefaultEdit::CAPABILITY => array(
        'caption' => pht('Mặc định chỉnh sửa cho không gian mới tạo.'),
        'default' => PhabricatorPolicies::POLICY_ADMIN,
        'template' => PhabricatorSpacesNamespacePHIDType::TYPECONST,
        'capability' => PhabricatorPolicyCapability::CAN_EDIT,
      ),
    );
  }

}
