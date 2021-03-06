<?php

final class PhabricatorNotificationUIExample extends PhabricatorUIExample {

  public function getName() {
    return pht('Thông báo');
  }

  public function getDescription() {
    return pht(
      'Sử dụng %s để tạo thông báo.',
      phutil_tag('tt', array(), 'JX.Notification'));
  }

  public function renderExample() {
    require_celerity_resource('phabricator-notification-css');
    Javelin::initBehavior('phabricator-notification-example');

    $content = javelin_tag(
      'a',
      array(
        'sigil' => 'notification-example',
        'class' => 'button green',
      ),
      pht('Show Notification'));

    $content = hsprintf('<div style="padding: 1em 3em;">%s</div>', $content);

    return $content;
  }
}
