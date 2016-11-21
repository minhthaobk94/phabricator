<?php

final class PhabricatorListFilterUIExample extends PhabricatorUIExample {

  public function getName() {
    return pht('Danh sách bộ lọc');
  }

  public function getDescription() {
    return pht(
      'Sử dụngUse %s để bố trí các điề u triển cho bộ lọc'.
      'và điều khiển danh sách các đối tượng.',
      phutil_tag('tt', array(), 'AphrontListFilterView'));
  }

  public function renderExample() {

    $filter = new AphrontListFilterView();

    $form = new AphrontFormView();
    $form->setUser($this->getRequest()->getUser());
    $form
      ->appendChild(
        id(new AphrontFormTextControl())
          ->setLabel(pht('Query')))
      ->appendChild(
        id(new AphrontFormSubmitControl())
          ->setValue(pht('Search')));

    $filter->appendChild($form);


    return $filter;
  }
}
