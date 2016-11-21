<?php

final class PhabricatorGestureUIExample extends PhabricatorUIExample {

  public function getName() {
    return pht('Thao tác');
  }

  public function getDescription() {
    return pht(
      'Sử dụng %s để lắng nghe các thao tác. Nhớ rằng bạn '.
      'phải ở chế độ làm việc để làm việc này (bạn có thể thu hẹp trình duyệt của bạn '.
      'cửa số nếu bạn đang làm việc trên desktop).',
      phutil_tag('tt', array(), 'touchable'));
  }

  public function renderExample() {
    $id = celerity_generate_unique_node_id();

    Javelin::initBehavior(
      'phabricator-gesture-example',
      array(
        'rootID' => $id,
      ));

    return javelin_tag(
      'div',
      array(
        'sigil' => 'touchable',
        'id' => $id,
        'style' => 'width: 320px; height: 240px; margin: auto;',
      ),
      '');
  }
}
