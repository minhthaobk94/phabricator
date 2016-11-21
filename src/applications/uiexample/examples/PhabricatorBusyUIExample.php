<?php

final class PhabricatorBusyUIExample extends PhabricatorUIExample {

  public function getName() {
    return pht('Bận');
  }

  public function getDescription() {
    return pht('Bận.');
  }

  public function renderExample() {
    Javelin::initBehavior('phabricator-busy-example');
    return null;
  }
}
