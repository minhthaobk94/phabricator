<?php

final class PhabricatorXHProfSampleListController
  extends PhabricatorXHProfController {

  public function shouldAllowPublic() {
    return true;
  }

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();
    $view = $request->getURIData('view');

    if (!$view) {
      $view = 'all';
    }

    $pager = new PHUIPagerView();
    $pager->setOffset($request->getInt('page'));

    switch ($view) {
      case 'sampled':
        $clause = 'sampleRate > 0';
        $show_type = false;
        break;
      case 'my-runs':
        $clause = qsprintf(
          id(new PhabricatorXHProfSample())->establishConnection('r'),
          'sampleRate = 0 AND userPHID = %s',
          $request->getUser()->getPHID());
        $show_type = false;
        break;
      case 'manual':
        $clause = 'sampleRate = 0';
        $show_type = false;
        break;
      case 'all':
      default:
        $clause = '1 = 1';
        $show_type = true;
        break;
    }

    $samples = id(new PhabricatorXHProfSample())->loadAllWhere(
      '%Q ORDER BY id DESC LIMIT %d, %d',
      $clause,
      $pager->getOffset(),
      $pager->getPageSize() + 1);

    $samples = $pager->sliceResults($samples);
    $pager->setURI($request->getRequestURI(), 'page');

    $list = new PHUIObjectItemListView();
    foreach ($samples as $sample) {
      $file_phid = $sample->getFilePHID();

      $item = id(new PHUIObjectItemView())
        ->setObjectName($sample->getID())
        ->setHeader($sample->getRequestPath())
        ->setHref($this->getApplicationURI('profile/'.$file_phid.'/'))
        ->addAttribute(
          number_format($sample->getUsTotal())." \xCE\xBCs");

      if ($sample->getController()) {
        $item->addAttribute($sample->getController());
      }

      $item->addAttribute($sample->getHostName());

      $rate = $sample->getSampleRate();
      if ($rate == 0) {
        $item->addIcon('flag-6', pht('Thực thi thủ công'));
      } else {
        $item->addIcon('flag-7', pht('Làm mẫu(1/%d)', $rate));
      }

      $item->addIcon(
        'none',
        phabricator_datetime($sample->getDateCreated(), $viewer));

      $list->addItem($item);
    }

    $list->setPager($pager);
    $list->setNoDataString(pht('Không có bất kì mẫu hồ sơ nào'));

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->addTextCrumb(pht(' Mẫu của XHProf'));

    $title = pht('Mẫu của XHProf');

    return $this->newPage()
      ->setTitle($title)
      ->setCrumbs($crumbs)
      ->appendChild($list);

  }
}
