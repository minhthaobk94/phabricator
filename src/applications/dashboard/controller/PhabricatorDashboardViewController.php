<?php

final class PhabricatorDashboardViewController
  extends PhabricatorDashboardController {

  private $id;

  public function shouldAllowPublic() {
    return true;
  }

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();
    $this->id = $request->getURIData('id');

    $dashboard = id(new PhabricatorDashboardQuery())
      ->setViewer($viewer)
      ->withIDs(array($this->id))
      ->needPanels(true)
      ->executeOne();
    if (!$dashboard) {
      return new Aphront404Response();
    }

    $title = $dashboard->getName();
    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->setBorder(true);
    $crumbs->addTextCrumb(pht('Bảng điều khiển %d', $dashboard->getID()));

    if ($dashboard->getPanelPHIDs()) {
      $rendered_dashboard = id(new PhabricatorDashboardRenderingEngine())
        ->setViewer($viewer)
        ->setDashboard($dashboard)
        ->renderDashboard();
    } else {
      $rendered_dashboard = $this->buildEmptyView();
    }

    return $this->newPage()
      ->setTitle($title)
      ->setCrumbs($crumbs)
      ->appendChild($rendered_dashboard);
  }

  protected function buildApplicationCrumbs() {
    $crumbs = parent::buildApplicationCrumbs();
    $id = $this->id;

    $crumbs->addAction(
      id(new PHUIListItemView())
        ->setIcon('fa-th')
        ->setName('Quản lý '))
        ->setHref($this->getApplicationURI("manage/{$id}/")));

    return $crumbs;
  }

  public function buildEmptyView() {
    $id = $this->id;
    $manage_uri = $this->getApplicationURI("manage/{$id}/");

    return id(new PHUIInfoView())
      ->setSeverity(PHUIInfoView::SEVERITY_NODATA)
      ->appendChild(
        pht('Bảng điều khiển này chưa có thẻ nào '.
          '. Sử dụng %s để thêm thẻ.',
          phutil_tag(
            'a',
            array('href' => $manage_uri),
            pht('Quản lý'))));
  }

}
