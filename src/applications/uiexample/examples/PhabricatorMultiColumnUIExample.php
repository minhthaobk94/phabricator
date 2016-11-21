<?php

final class PhabricatorMultiColumnUIExample extends PhabricatorUIExample {

  public function getName() {
    return pht('Bố trí giao diện đa cột');
  }

  public function getDescription() {
    return pht(
      'Một kho chứa tốt cho 1-7 cột có chỗ một cách công bằng. '.
      'Trộn lần và biến đổi các bố trí.');
  }

  public function renderExample() {
    $request = $this->getRequest();
    $user = $request->getUser();

    $column1 = phutil_tag(
      'div',
        array(
          'class' => 'pm',
          'style' => 'border: 1px solid green;',
        ),
        'Bruce Campbell');

    $column2 = phutil_tag(
      'div',
        array(
          'class' => 'pm',
          'style' => 'border: 1px solid blue;',
        ),
        'Army of Darkness');

    $head1 = id(new PHUIHeaderView())
      ->setHeader(pht('2 cột đã được trộn'));
    $layout1 = id(new AphrontMultiColumnView())
      ->addColumn($column1)
      ->addColumn($column2)
      ->setGutter(AphrontMultiColumnView::GUTTER_MEDIUM);

    $head2 = id(new PHUIHeaderView())
      ->setHeader(pht('2 cột đã được nhập'));
    $layout2 = id(new AphrontMultiColumnView())
      ->addColumn($column1)
      ->addColumn($column2)
      ->setFluidLayout(true)
      ->setGutter(AphrontMultiColumnView::GUTTER_MEDIUM);

    $head3 = id(new PHUIHeaderView())
      ->setHeader(pht('4 cột đã được trộn'));
    $layout3 = id(new AphrontMultiColumnView())
      ->addColumn($column1)
      ->addColumn($column2)
      ->addColumn($column1)
      ->addColumn($column2)
      ->setGutter(AphrontMultiColumnView::GUTTER_SMALL);

    $head4 = id(new PHUIHeaderView())
      ->setHeader(pht('4 cột đã được nhập'));
    $layout4 = id(new AphrontMultiColumnView())
      ->addColumn($column1)
      ->addColumn($column2)
      ->addColumn($column1)
      ->addColumn($column2)
      ->setFluidLayout(true)
      ->setGutter(AphrontMultiColumnView::GUTTER_SMALL);

    $sunday = hsprintf('<strong>Sunday</strong><br /><br />Watch Football'.
      '<br />Code<br />Eat<br />Sleep');

    $monday = hsprintf('<strong>Monday</strong><br /><br />Code'.
      '<br />Eat<br />Sleep');

    $tuesday = hsprintf('<strong>Tuesday</strong><br />'.
      '<br />Code<br />Eat<br />Sleep');

    $wednesday = hsprintf('<strong>Wednesday</strong><br /><br />Code'.
      '<br />Eat<br />Sleep');

    $thursday = hsprintf('<strong>Thursday</strong><br />'.
      '<br />Code<br />Eat<br />Sleep');

    $friday = hsprintf('<strong>Friday</strong><br /><br />Code'.
      '<br />Eat<br />Sleep');

    $saturday = hsprintf('<strong>Saturday</strong><br /><br />StarCraft II'.
      '<br />All<br />Damn<br />Day');

    $head5 = id(new PHUIHeaderView())
      ->setHeader(pht('7 cột đã được nhập'));
    $layout5 = id(new AphrontMultiColumnView())
      ->addColumn($sunday)
      ->addColumn($monday)
      ->addColumn($tuesday)
      ->addColumn($wednesday)
      ->addColumn($thursday)
      ->addColumn($friday)
      ->addColumn($saturday)
      ->setFluidLayout(true)
      ->setBorder(true);

    $shipping = id(new PHUIFormLayoutView())
      ->setUser($user)
      ->setFullWidth(true)
      ->appendChild(
        id(new AphrontFormTextControl())
        ->setLabel(pht('Têb'))
        ->setDisableAutocomplete(true)
        ->setSigil('name-input'))
      ->appendChild(
        id(new AphrontFormTextControl())
        ->setLabel(pht('Địa chỉ'))
        ->setDisableAutocomplete(true)
        ->setSigil('address-input'))
      ->appendChild(
        id(new AphrontFormTextControl())
        ->setLabel(pht('Thành phố/Bang'))
        ->setDisableAutocomplete(true)
        ->setSigil('city-input'))
      ->appendChild(
        id(new AphrontFormTextControl())
        ->setLabel(pht('Quê hương'))
        ->setDisableAutocomplete(true)
        ->setSigil('country-input'))
      ->appendChild(
        id(new AphrontFormTextControl())
        ->setLabel(pht('Mã code bưu điện'))
        ->setDisableAutocomplete(true)
        ->setSigil('postal-input'));

    $cc = id(new PHUIFormLayoutView())
      ->setUser($user)
      ->setFullWidth(true)
      ->appendChild(
        id(new AphrontFormTextControl())
        ->setLabel(pht('Mã số thẻ'))
        ->setDisableAutocomplete(true)
        ->setSigil('number-input')
        ->setError(''))
      ->appendChild(
        id(new AphrontFormTextControl())
        ->setLabel(pht('CVC'))
        ->setDisableAutocomplete(true)
        ->setSigil('cvc-input')
        ->setError(''))
      ->appendChild(
        id(new PhortuneMonthYearExpiryControl())
        ->setLabel(pht('Thời gian hết hạn'))
        ->setUser($user)
        ->setError(''));

    $shipping_title = pht('Địa chỉ ship');
    $billing_title = pht('Địa chỉ thanh toán');
    $cc_title = pht('Thông tin thanh toán');

    $head6 = id(new PHUIHeaderView())
      ->setHeader(pht("Thả ga mua sắm thôi nào!"));
    $layout6 = id(new AphrontMultiColumnView())
      ->addColumn(hsprintf('<h1>%s</h1>%s', $shipping_title, $shipping))
      ->addColumn(hsprintf('<h1>%s</h1>%s', $billing_title, $shipping))
      ->addColumn(hsprintf('<h1>%s</h1>%s', $cc_title, $cc))
      ->setFluidLayout(true)
      ->setBorder(true);

    $wrap1 = phutil_tag(
      'div',
        array(
          'class' => 'ml',
        ),
        $layout1);

    $wrap2 = phutil_tag(
      'div',
        array(
          'class' => 'ml',
        ),
        $layout2);

    $wrap3 = phutil_tag(
      'div',
        array(
          'class' => 'ml',
        ),
        $layout3);

    $wrap4 = phutil_tag(
      'div',
        array(
          'class' => 'ml',
        ),
        $layout4);

    $wrap5 = phutil_tag(
      'div',
        array(
          'class' => 'ml',
        ),
        $layout5);

    $wrap6 = phutil_tag(
      'div',
        array(
          'class' => 'ml',
        ),
        $layout6);

    return phutil_tag(
      'div',
        array(),
        array(
          $head1,
          $wrap1,
          $head2,
          $wrap2,
          $head3,
          $wrap3,
          $head4,
          $wrap4,
          $head5,
          $wrap5,
          $head6,
          $wrap6,
        ));
  }
}
