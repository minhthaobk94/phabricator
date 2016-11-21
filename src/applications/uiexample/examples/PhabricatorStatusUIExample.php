<?php

final class PhabricatorStatusUIExample extends PhabricatorUIExample {

  public function getName() {
    return pht('Danh sách trạng thái');
  }

  public function getDescription() {
    return pht(
      'Sử dụng để %s để trình bày các mối quan hệ với các đối tượng.',
      phutil_tag('tt', array(), 'PHUIStatusListView'));
  }

  public function renderExample() {
    $out = array();

    $view = new PHUIStatusListView();

    $view->addItem(
      id(new PHUIStatusItemView())
        ->setIcon(PHUIStatusItemView::ICON_ACCEPT, 'green', pht('Yum'))
        ->setTarget(pht('Táo'))
        ->setNote(pht('Bạn có thể ăn chúng.')));

    $view->addItem(
      id(new PHUIStatusItemView())
        ->setIcon(PHUIStatusItemView::ICON_ADD, 'blue', pht('Has Peel'))
        ->setTarget(pht('Chuối'))
        ->setNote(pht('Đi kèm trong một chùm.'))
        ->setHighlighted(true));

    $view->addItem(
      id(new PHUIStatusItemView())
        ->setIcon(PHUIStatusItemView::ICON_WARNING, 'dark', pht('Caution'))
        ->setTarget(pht('Trái lựu'))
        ->setNote(pht('Rất nhiều hạt.Coi chừng')));

    $view->addItem(
      id(new PHUIStatusItemView())
        ->setIcon(PHUIStatusItemView::ICON_REJECT, 'red', pht('Bleh!'))
        ->setTarget(pht('Quả bí'))
        ->setNote(pht('Nhầy nhụa và gộp. Kinh quá')));

    $out[] = id(new PHUIHeaderView())
      ->setHeader(pht('Trái cây và rau'));

    $out[] = id(new PHUIBoxView())
      ->addMargin(PHUI::MARGIN_LARGE)
      ->addPadding(PHUI::PADDING_LARGE)
      ->setBorder(true)
      ->appendChild($view);


    $view = new PHUIStatusListView();

    $manifest = array(
      PHUIStatusItemView::ICON_ACCEPT => 'PHUIStatusItemView::ICON_ACCEPT',
      PHUIStatusItemView::ICON_REJECT => 'PHUIStatusItemView::ICON_REJECT',
      PHUIStatusItemView::ICON_LEFT => 'PHUIStatusItemView::ICON_LEFT',
      PHUIStatusItemView::ICON_RIGHT => 'PHUIStatusItemView::ICON_RIGHT',
      PHUIStatusItemView::ICON_UP => 'PHUIStatusItemView::ICON_UP',
      PHUIStatusItemView::ICON_DOWN => 'PHUIStatusItemView::ICON_DOWN',
      PHUIStatusItemView::ICON_QUESTION => 'PHUIStatusItemView::ICON_QUESTION',
      PHUIStatusItemView::ICON_WARNING => 'PHUIStatusItemView::ICON_WARNING',
      PHUIStatusItemView::ICON_INFO => 'PHUIStatusItemView::ICON_INFO',
      PHUIStatusItemView::ICON_ADD => 'PHUIStatusItemView::ICON_ADD',
      PHUIStatusItemView::ICON_MINUS => 'PHUIStatusItemView::ICON_MINUS',
      PHUIStatusItemView::ICON_OPEN => 'PHUIStatusItemView::ICON_OPEN',
      PHUIStatusItemView::ICON_CLOCK => 'PHUIStatusItemView::ICON_CLOCK',
    );

    foreach ($manifest as $icon => $label) {

      $view->addItem(
        id(new PHUIStatusItemView())
          ->setIcon($icon, 'indigo')
          ->setTarget($label));
    }

    $out[] = id(new PHUIHeaderView())
      ->setHeader(pht('All Icons'));

    $out[] = id(new PHUIBoxView())
      ->addMargin(PHUI::MARGIN_LARGE)
      ->addPadding(PHUI::PADDING_LARGE)
      ->setBorder(true)
      ->appendChild($view);

    return $out;
  }
}
