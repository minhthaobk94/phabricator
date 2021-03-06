<?php

final class PhabricatorDoorkeeperApplication extends PhabricatorApplication {

  public function canUninstall() {
    return false;
  }

  public function isLaunchable() {
    return false;
  }

  public function getName() {
    return pht('Giữ cửa');
  }

  public function getIcon() {
    return 'fa-recycle';
  }

  public function getShortDescription() {
    return pht('Kết nối với phần mềm khác');
  }

  public function getRemarkupRules() {
    return array(
      new DoorkeeperAsanaRemarkupRule(),
      new DoorkeeperJIRARemarkupRule(),
    );
  }

  public function getRoutes() {
    return array(
      '/doorkeeper/' => array(
        'tags/' => 'DoorkeeperTagsController',
      ),
    );
  }

}
