<?php

final class PhabricatorCountdownSearchEngine
  extends PhabricatorApplicationSearchEngine {

  public function getResultTypeDescription() {
    return pht('Countdowns');
  }

  public function getApplicationClassName() {
    return 'PhabricatorCountdownApplication';
  }

  public function newQuery() {
    return new PhabricatorCountdownQuery();
  }

  protected function buildQueryFromParameters(array $map) {
    $query = $this->newQuery();

    if ($map['authorPHIDs']) {
      $query->withAuthorPHIDs($map['authorPHIDs']);
    }

    if ($map['upcoming'] && $map['upcoming'][0] == 'upcoming') {
      $query->withUpcoming();
    }

    return $query;
  }

  protected function buildCustomSearchFields() {
    return array(
      id(new PhabricatorUsersSearchField())
        ->setLabel(pht('Tác giả'))
        ->setKey('authorPHIDs')
        ->setAliases(array('author', 'authors')),
      id(new PhabricatorSearchCheckboxesField())
        ->setKey('upcoming')
        ->setOptions(
          array(
            'upcoming' => pht('Hiện chỉ sắp tới countdowns.'),
          )),
    );
  }

  protected function getURI($path) {
    return '/countdown/'.$path;
  }

  protected function getBuiltinQueryNames() {
    $names = array(
      'upcoming' => pht('Upcoming'),
      'all' => pht('Tất cả'),
    );

    if ($this->requireViewer()->getPHID()) {
      $names['authored'] = pht('Tác giả');
    }

    return $names;
  }

  public function buildSavedQueryFromBuiltin($query_key) {
    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);

    switch ($query_key) {
      case 'all':
        return $query;
      case 'authored':
        return $query->setParameter(
          'authorPHIDs',
          array($this->requireViewer()->getPHID()));
      case 'upcoming':
        return $query->setParameter('upcoming', array('upcoming'));
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  protected function getRequiredHandlePHIDsForResultList(
    array $countdowns,
    PhabricatorSavedQuery $query) {

    return mpull($countdowns, 'getAuthorPHID');
  }

  protected function renderResultList(
    array $countdowns,
    PhabricatorSavedQuery $query,
    array $handles) {

    assert_instances_of($countdowns, 'PhabricatorCountdown');

    $viewer = $this->requireViewer();

    $list = new PHUIObjectItemListView();
    $list->setUser($viewer);
    foreach ($countdowns as $countdown) {
      $id = $countdown->getID();
      $ended = false;
      $epoch = $countdown->getEpoch();
      if ($epoch <= PhabricatorTime::getNow()) {
        $ended = true;
      }

      $item = id(new PHUIObjectItemView())
        ->setUser($viewer)
        ->setObject($countdown)
        ->setObjectName("C{$id}")
        ->setHeader($countdown->getTitle())
        ->setHref($this->getApplicationURI("{$id}/"))
        ->addByline(
          pht(
            'Tạo bởi %s',
            $handles[$countdown->getAuthorPHID()]->renderLink()));

      if ($ended) {
        $item->addAttribute(
          pht('Ra mắt trên %s', phabricator_datetime($epoch, $viewer)));
        $item->setDisabled(true);
      } else {
        $time_left = ($epoch - PhabricatorTime::getNow());
        $num = round($time_left / (60 * 60 * 24));
        $noun = pht('Ngày');
        if ($num < 1) {
          $num = round($time_left / (60 * 60), 1);
          $noun = pht('Giờ');
        }
        $item->setCountdown($num, $noun);
        $item->addAttribute(
          phabricator_datetime($epoch, $viewer));
      }

      $list->addItem($item);
    }

    $result = new PhabricatorApplicationSearchResultView();
    $result->setObjectList($list);
    $result->setNoDataString(pht('Không tìm thấy.'));

    return $result;
  }

  protected function getNewUserBody() {
    $create_button = id(new PHUIButtonView())
      ->setTag('a')
      ->setText(pht('Tạo Countdown'))
      ->setHref('/countdown/edit/')
      ->setColor(PHUIButtonView::GREEN);

    $icon = $this->getApplication()->getIcon();
    $app_name =  $this->getApplication()->getName();
    $view = id(new PHUIBigInfoView())
      ->setIcon($icon)
      ->setTitle(pht('Chào mừng đến với %s', $app_name))
      ->setDescription(
        pht('Theo dõi ngày ra mắt sắp tới với '.
            ' với quầy nhúng.'))
      ->addAction($create_button);

      return $view;
  }

}
