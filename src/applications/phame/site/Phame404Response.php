<?php

final class Phame404Response extends AphrontHTMLResponse {

  private $page;

  public function setPage(AphrontPageView $page) {
    $this->page = $page;
    return $this;
  }

  public function getPage() {
    return $this->page;
  }

  public function getHTTPResponseCode() {
    return 404;
  }

  public function buildResponseString() {
    require_celerity_resource('phame-css');

    $not_found = phutil_tag(
      'div',
      array(
        'class' => 'phame-404',
      ),
      array(
        phutil_tag('strong', array(), pht('404 Not Found')),
        phutil_tag('br'),
        pht('Bất cứ nơi nào bạn đi, nơi đó có bạn.'),
        phutil_tag('br'),
        pht('Nhưng các trang web mà bạn tìm được ở nơi khác.'),
      ));

    $page = $this->getPage()
      ->setTitle(pht('404 Not Found'))
      ->appendChild($not_found);

    return $page->render();
  }

}
