<?php

final class PHUIActionPanelExample extends PhabricatorUIExample {

  public function getName() {
    return pht('Bảng hoạt động');
  }

  public function getDescription() {
    return pht('Một bảng điều khiển với khuynh hướng mạnh mẽ sẽ kích thích HOẠT ĐỘNG!');
  }

  public function renderExample() {

    $view = id(new AphrontMultiColumnView())
      ->setFluidLayout(true);

    /* Action Panels */
    $panel1 = id(new PHUIActionPanelView())
      ->setIcon('fa-book')
      ->setHeader(pht('Đọc tài liệu'))
      ->setHref('#')
      ->setSubHeader(pht('Đọc là cách thông dụng để hiểu điều gì đó.'))
      ->setState(PHUIActionPanelView::COLOR_BLUE);
    $view->addColumn($panel1);

    $panel2 = id(new PHUIActionPanelView())
      ->setIcon('fa-server')
      ->setHeader(pht('Khởi động các trương hợp'))
      ->setHref('#')
      ->setSubHeader(pht("Có thể đây là thứ bạn thích."))
      ->setState(PHUIActionPanelView::COLOR_RED);
    $view->addColumn($panel2);

    $panel3 = id(new PHUIActionPanelView())
      ->setIcon('fa-group')
      ->setHeader(pht('Code with bạn'))
      ->setHref('#')
      ->setSubHeader(pht('Viết code sẽ vui hơn khi code với bạn!'))
      ->setState(PHUIActionPanelView::COLOR_YELLOW);
    $view->addColumn($panel3);

    $panel4 = id(new PHUIActionPanelView())
      ->setIcon('fa-cloud-download')
      ->setHeader(pht('Tải dữ liệu'))
      ->setHref('#')
      ->setSubHeader(pht('Cần một bảng sao lưu của tất cả các meme kitten của bạn?'))
      ->setState(PHUIActionPanelView::COLOR_PINK);
    $view->addColumn($panel4);

    $view2 = id(new AphrontMultiColumnView())
      ->setFluidLayout(true);

    /* Action Panels */
    $panel1 = id(new PHUIActionPanelView())
      ->setIcon('fa-credit-card')
      ->setHeader(pht('Tính toán cân bằng '))
      ->setHref('#')
      ->setSubHeader(pht('Lần cuối thanh toán của bạn là 2,245.12 vào ngày 12 tháng 12, 2014.'))
      ->setState(PHUIActionPanelView::COLOR_GREEN);
    $view2->addColumn($panel1);

    $panel2 = id(new PHUIActionPanelView())
      ->setBigText(true)
      ->setHeader(pht('Người dùng trường hợp'))
      ->setHref('#')
      ->setSubHeader(
        pht('148'));
    $view2->addColumn($panel2);

    $panel3 = id(new PHUIActionPanelView())
      ->setBigText(true)
      ->setHeader(pht('Cửa sổ bảo trì tiếp theo'))
      ->setHref('#')
      ->setSubHeader(
        pht('March 12'))
      ->setState(PHUIActionPanelView::COLOR_ORANGE);
    $view2->addColumn($panel3);

    $panel4 = id(new PHUIActionPanelView())
      ->setBigText(true)
      ->setHeader(pht('Dòng code'))
      ->setHref('#')
      ->setSubHeader(pht('1,113,377'))
      ->setState(PHUIActionPanelView::COLOR_INDIGO);
    $view2->addColumn($panel4);

    $view = phutil_tag_div('mlb', $view);

    return phutil_tag_div('ml', array($view, $view2));
  }
}
