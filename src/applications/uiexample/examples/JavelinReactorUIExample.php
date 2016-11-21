<?php

final class JavelinReactorUIExample extends PhabricatorUIExample {

  public function getName() {
    return pht('Javelin Reactor');
  }

  public function getDescription() {
    return pht('Một vài đoạn code');
  }

  public function renderExample() {
    $rows = array();

    $examples = array(
      array(
        pht('Tương tác nút liên kết tới dòng sự kiện'),
        'ReactorButtonExample',
        'phabricator-uiexample-reactor-button',
        array(),
      ),
      array(
        pht('Tương tác khung kiểm tra liên kêt tới giá trị dao động boolean'),
        'ReactorCheckboxExample',
        'phabricator-uiexample-reactor-checkbox',
        array('checked' => true),
      ),
      array(
        pht('Tương tác tập trung vào phát hiện liên kết tới giá trị dao động boolean'),
        'ReactorFocusExample',
        'phabricator-uiexample-reactor-focus',
        array(),
      ),
      array(
        pht('Khung nhập vào tương tác, với đầu ra bình thường và không dao động'),
        'ReactorInputExample',
        'phabricator-uiexample-reactor-input',
        array('init' => 'Initial value'),
      ),
      array(
        pht('Tương tác về việc phát hiện chuột liên kết tới giá trị dao động boolean'),
        'ReactorMouseoverExample',
        'phabricator-uiexample-reactor-mouseover',
        array(),
      ),
      array(
        pht('Tương tác radio buttons liên kết tới chuỗi giá trị dao động'),
        'ReactorRadioExample',
        'phabricator-uiexample-reactor-radio',
        array(),
      ),
      array(
        pht('Tương tác khung chọn liên kết tới chuỗi giá trị dao động'),
        'ReactorSelectExample',
        'phabricator-uiexample-reactor-select',
        array(),
      ),
      array(
        pht(
          '%s tạo lớp của một phần tử của chuỗi giá trị dao động',
          'sendclass'),
        'ReactorSendClassExample',
        'phabricator-uiexample-reactor-sendclass',
        array(),
      ),
      array(
        pht(
          '%s tạo vài tính chất của một đối tượng vào các giá trị dao động',
          'sendproperties'),
        'ReactorSendPropertiesExample',
        'phabricator-uiexample-reactor-sendproperties',
        array(),
      ),
    );

    foreach ($examples as $example) {
      list($desc, $name, $resource, $params) = $example;
      $template = new AphrontJavelinView();
      $template
        ->setName($name)
        ->setParameters($params)
        ->setCelerityResource($resource);
      $rows[] = array($desc, $template->render());
    }

    $table = new AphrontTableView($rows);

    $panel = new PHUIObjectBoxView();
    $panel->setHeaderText(pht('Example'));
    $panel->appendChild($table);

    return $panel;
  }
}
