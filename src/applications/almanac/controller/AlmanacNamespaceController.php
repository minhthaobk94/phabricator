<?php

abstract class AlmanacNamespaceController extends AlmanacController {

  protected function buildApplicationCrumbs() {
    $crumbs = parent::buildApplicationCrumbs();

    $list_uri = $this->getApplicationURI('namespace/');
    $crumbs->addTextCrumb(pht('Tên'), $list_uri);

    return $crumbs;
  }

}
