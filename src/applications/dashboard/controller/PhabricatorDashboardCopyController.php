<?php

final class PhabricatorDashboardCopyController
  extends PhabricatorDashboardController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();
    $id = $request->getURIData('id');

    $dashboard = id(new PhabricatorDashboardQuery())
      ->setViewer($viewer)
      ->withIDs(array($id))
      ->needPanels(true)
      ->executeOne();
    if (!$dashboard) {
      return new Aphront404Response();
    }

    $manage_uri = $this->getApplicationURI('manage/'.$dashboard->getID().'/');

    if ($request->isFormPost()) {

      $copy = PhabricatorDashboard::initializeNewDashboard($viewer);
      $copy = PhabricatorDashboard::copyDashboard($copy, $dashboard);

      $copy->setName(pht('Bảng sao của %s', $copy->getName()));

      // Set up all the edges for the new dashboard.

      $xactions = array();
      $xactions[] = id(new PhabricatorDashboardTransaction())
        ->setTransactionType(PhabricatorTransactions::TYPE_EDGE)
        ->setMetadataValue(
          'edge:type',
          PhabricatorDashboardDashboardHasPanelEdgeType::EDGECONST)
        ->setNewValue(
          array(
            '=' => array_fuse($dashboard->getPanelPHIDs()),
          ));

      $editor = id(new PhabricatorDashboardTransactionEditor())
        ->setActor($viewer)
        ->setContentSourceFromRequest($request)
        ->setContinueOnMissingFields(true)
        ->setContinueOnNoEffect(true)
        ->applyTransactions($copy, $xactions);

      $manage_uri = $this->getApplicationURI('edit/'.$copy->getID().'/');
      return id(new AphrontRedirectResponse())->setURI($manage_uri);
    }

    return $this->newDialog()
      ->setTitle(pht('Sao chep'))
      ->appendParagraph(
        pht(
          'Tạo bảng sao của "%s"?',
          phutil_tag('strong', array(), $dashboard->getName())))
      ->addCancelButton($manage_uri)
      ->addSubmitButton(pht('Tạo bảng sao'));
  }

}
