<?php

final class PhabricatorPagerUIExample extends PhabricatorUIExample {

  public function getName() {
    return pht('Pager');
  }

  public function getDescription() {
    return pht(
      'Sử dụng %s để tạo một điều khiển được phép'.
      'người dùng đánh số trang thông qua số lượng lớn về nội dung.',
      phutil_tag('tt', array(), 'PHUIPagerView'));
  }

  public function renderExample() {
    $request = $this->getRequest();

    $offset = (int)$request->getInt('offset');
    $page_size = 20;
    $item_count = 173;

    $rows = array();
    for ($ii = $offset; $ii < min($item_count, $offset + $page_size); $ii++) {
      $rows[] = array(
        pht('Item #%d', $ii + 1),
      );
    }

    $table = new AphrontTableView($rows);
    $table->setHeaders(
      array(
        'Item',
      ));
    $panel = new PHUIObjectBoxView();
    $panel->setHeaderText(pht('Ví dụ'));
    $panel->appendChild($table);

    $panel->appendChild(hsprintf(
      '<p class="phabricator-ui-example-note">%s</p>',
      pht(
        'Sử dụng %s để trả lại phần tử trang.',
        phutil_tag('tt', array(), 'PHUIPagerView'))));

    $pager = new PHUIPagerView();
    $pager->setPageSize($page_size);
    $pager->setOffset($offset);
    $pager->setCount($item_count);
    $pager->setURI($request->getRequestURI(), 'offset');
    $panel->appendChild($pager);

    $panel->appendChild(hsprintf(
      '<p class="phabricator-ui-example-note">%s</p>',
      pht('Bạn có thể trình bày nhiều hoặc ít hơn số trang của nội dung.')));

    $many_pages_pager = new PHUIPagerView();
    $many_pages_pager->setPageSize($page_size);
    $many_pages_pager->setOffset($offset);
    $many_pages_pager->setCount($item_count);
    $many_pages_pager->setURI($request->getRequestURI(), 'offset');
    $many_pages_pager->setSurroundingPages(7);
    $panel->appendChild($many_pages_pager);

    $panel->appendChild(hsprintf(
      '<p class="phabricator-ui-example-note">%s</p>',
      pht(
        'Khi nó tốn kém hay phức tạp để hoàn thành '.
        'đếm số thành phần, bạn có thể lựa chọn một hoặc thêm nhiều thành phần và cài đặt'.
        '%s nêus nó tồn tại, tạo một trang không đúng.',
        phutil_tag('tt', array(), 'hasMorePages(true)'))));

    $inexact_pager = new PHUIPagerView();
    $inexact_pager->setPageSize($page_size);
    $inexact_pager->setOffset($offset);
    $inexact_pager->setHasMorePages($offset < ($item_count - $page_size));
    $inexact_pager->setURI($request->getRequestURI(), 'offset');
    $panel->appendChild($inexact_pager);

    return $panel;
  }
}
