<?php

final class PhabricatorSupportApplication extends PhabricatorApplication {

  public function getName() {
    return pht('Hỗ trợ');
  }

  public function canUninstall() {
    return false;
  }

  public function isUnlisted() {
    return true;
  }

  public function getRoutes() {
    return array(
      '/help/' => array(
        'keyboardshortcut/' => 'PhabricatorHelpKeyboardShortcutController',
      ),
    );
  }

}
