<?php

final class PhabricatorBarePageUIExample extends PhabricatorUIExample {

  public function getName() {
    return pht('Trang trống');
  }

  public function getDescription() {
    return pht('Đây là một trang trống.');
  }

  public function renderExample() {
    $view = new PhabricatorBarePageView();
    $view->appendChild(
      phutil_tag(
        'h1',
        array(),
        $this->getDescription()));

    $response = new AphrontWebpageResponse();
    $response->setContent($view->render());
    return $response;
  }
}
