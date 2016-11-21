<?php

final class AlmanacConsoleController extends AlmanacController {

  public function shouldAllowPublic() {
    return true;
  }

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();

    $menu = id(new PHUIObjectItemListView())
      ->setUser($viewer);

    $menu->addItem(
      id(new PHUIObjectItemView())
        ->setHeader(pht('Thiết bị'))
        ->setHref($this->getApplicationURI('device/'))
        ->setImageIcon('fa-server')
        ->addAttribute(
          pht(
            'Tạo một kho chứa máy chủ vật lý và máy chủ ảo cũng như thiết bị')));

    $menu->addItem(
      id(new PHUIObjectItemView())
        ->setHeader(pht('Services'))
        ->setHref($this->getApplicationURI('service/'))
        ->setImageIcon('fa-plug')
        ->addAttribute(
          pht(
            'Tạo mới và cập nhật dịch vụ, bản đồ thể hiện trên thiết bị ')));

    $menu->addItem(
      id(new PHUIObjectItemView())
        ->setHeader(pht('Mạng'))
        ->setHref($this->getApplicationURI('network/'))
        ->setImageIcon('fa-globe')
        ->addAttribute(
          pht(
            'Quản lý mạng công cộng và mạng riêng.')));

    $menu->addItem(
      id(new PHUIObjectItemView())
        ->setHeader(pht('Tên'))
        ->setHref($this->getApplicationURI('namespace/'))
        ->setImageIcon('fa-asterisk')
        ->addAttribute(
          pht('Ai có thể tạo mới tên của dịch vụ và thiết bị.')));

    $docs_uri = PhabricatorEnv::getDoclink(
      'Almanac User Guide');

    $menu->addItem(
      id(new PHUIObjectItemView())
        ->setHeader(pht('Documentation'))
        ->setHref($docs_uri)
        ->setImageIcon('fa-book')
        ->addAttribute(pht('Tìm kiếm tài liệu liên quan.')));

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->addTextCrumb(pht('Giao diện điều khiển'));
    $crumbs->setBorder(true);

    $box = id(new PHUIObjectBoxView())
      ->setObjectList($menu);

    $header = id(new PHUIHeaderView())
      ->setHeader(pht('Giao diện điều khiển'))
      ->setHeaderIcon('fa-server');

    $view = id(new PHUITwoColumnView())
      ->setHeader($header)
      ->setFooter(array(
        $box,
      ));

    return $this->newPage()
      ->setTitle(pht('Giao diện điều khiển'))
      ->setCrumbs($crumbs)
      ->appendChild($view);

  }

}
