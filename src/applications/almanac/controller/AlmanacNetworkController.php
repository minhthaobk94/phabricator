<?php

abstract class AlmanacNetworkController extends AlmanacController {

  protected function buildApplicationCrumbs() {
    $crumbs = parent::buildApplicationCrumbs();

    $list_uri = $this->getApplicationURI('network/');
    $crumbs->addTextCrumb(pht('Mạng'), $list_uri);

    return $crumbs;
  }

}
