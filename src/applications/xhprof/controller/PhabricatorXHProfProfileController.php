<?php

final class PhabricatorXHProfProfileController
  extends PhabricatorXHProfController {

  public function shouldAllowPublic() {
    return true;
  }

  public function handleRequest(AphrontRequest $request) {
    $phid = $request->getURIData('phid');

    $file = id(new PhabricatorFileQuery())
      ->setViewer($request->getUser())
      ->withPHIDs(array($phid))
      ->executeOne();
    if (!$file) {
      return new Aphront404Response();
    }

    $data = $file->loadFileData();
    try {
      $data = phutil_json_decode($data);
    } catch (PhutilJSONParserException $ex) {
      throw new PhutilProxyException(
        pht('Không thể sắp xếp lại thứ tự cuả XHProf'),
        $ex);
    }

    $symbol = $request->getStr('symbol');

    $is_framed = $request->getBool('frame');

    if ($symbol) {
      $view = new PhabricatorXHProfProfileSymbolView();
      $view->setSymbol($symbol);
    } else {
      $view = new PhabricatorXHProfProfileTopLevelView();
      $view->setFile($file);
      $view->setLimit(100);
    }

    $view->setBaseURI($request->getRequestURI()->getPath());
    $view->setIsFramed($is_framed);
    $view->setProfileData($data);

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->addTextCrumb(pht('%s Hồ sơ', $symbol));

    $title = pht('Hồ sơ');

    return $this->newPage()
      ->setTitle($title)
      ->setCrumbs($crumbs)
      ->setFrameable(true)
      ->setShowChrome(false)
      ->setDisableConsole(true)
      ->appendChild($view);
  }
}
