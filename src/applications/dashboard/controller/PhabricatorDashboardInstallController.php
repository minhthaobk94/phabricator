<?php

final class PhabricatorDashboardInstallController
  extends PhabricatorDashboardController {

  private $id;

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();
    $this->id = $request->getURIData('id');

    $dashboard = id(new PhabricatorDashboardQuery())
      ->setViewer($viewer)
      ->withIDs(array($this->id))
      ->executeOne();
    if (!$dashboard) {
      return new Aphront404Response();
    }
    $dashboard_phid = $dashboard->getPHID();

    $object_phid = $request->getStr('objectPHID', $viewer->getPHID());
    switch ($object_phid) {
      case PhabricatorHomeApplication::DASHBOARD_DEFAULT:
        if (!$viewer->getIsAdmin()) {
          return new Aphront404Response();
        }
        break;
      default:
        $object = id(new PhabricatorObjectQuery())
          ->setViewer($viewer)
          ->requireCapabilities(
            array(
              PhabricatorPolicyCapability::CAN_VIEW,
              PhabricatorPolicyCapability::CAN_EDIT,
            ))
          ->withPHIDs(array($object_phid))
          ->executeOne();
        if (!$object) {
          return new Aphront404Response();
        }
        break;
    }

    $installer_phid = $viewer->getPHID();
    $application_class = $request->getStr(
      'applicationClass',
      'PhabricatorHomeApplication');

    if ($request->isFormPost()) {
      $dashboard_install = id(new PhabricatorDashboardInstall())
        ->loadOneWhere(
          'objectPHID = %s AND applicationClass = %s',
          $object_phid,
          $application_class);
      if (!$dashboard_install) {
        $dashboard_install = id(new PhabricatorDashboardInstall())
          ->setObjectPHID($object_phid)
          ->setApplicationClass($application_class);
      }
      $dashboard_install
        ->setInstallerPHID($installer_phid)
        ->setDashboardPHID($dashboard_phid)
        ->save();
      return id(new AphrontRedirectResponse())
        ->setURI($this->getRedirectURI($application_class, $object_phid));
    }

    $dialog = $this->newDialog()
      ->setTitle(pht('Install Dashboard'))
      ->addHiddenInput('objectPHID', $object_phid)
      ->addCancelButton($this->getCancelURI($application_class, $object_phid))
      ->addSubmitButton(pht('Install Dashboard'));

    switch ($application_class) {
      case 'PhabricatorHomeApplication':
        if ($viewer->getPHID() == $object_phid) {
          if ($viewer->getIsAdmin()) {
            $dialog->setWidth(AphrontDialogView::WIDTH_FORM);

            $form = id(new AphrontFormView())
              ->setUser($viewer)
              ->appendRemarkupInstructions(
                pht('Chọn nơi cài đặt bàng điều khiển này.'))
              ->appendChild(
                id(new AphrontFormRadioButtonControl())
                  ->setName('objectPHID')
                  ->setValue(PhabricatorHomeApplication::DASHBOARD_DEFAULT)
                  ->addButton(
                    PhabricatorHomeApplication::DASHBOARD_DEFAULT,
                    pht('Mặc định cho tất cả người dùng'),
                    pht(
                      'Cài đặt bảng điều khiển như là mặc định '.
                      'cho mọi người dùng. Người dùng có thể sử dụng BĐK cá nhân '.
                      'để thay thể. Mọi người dùng có thể chỉnh sửa '.
                      'BĐK cá nhân có thể thay đổi.'))
                  ->addButton(
                    $viewer->getPHID(),
                    pht('Bảng điều khiển trang chủ cá nhân'),
                    pht(
                      'Cài đặt Bảng điều khiển này như bảng điều khiển cá nhân '.
                      'Chỉ bạn có quyền thay đổi.')));

            $dialog->appendChild($form->buildLayoutView());
          } else {
            $dialog->appendParagraph(
              pht('Cài đặt bảng điều khiển này trên trang chủ của bạn?'));
          }
        } else {
          $dialog->appendParagraph(
            pht(
              'Cài đặt bảng điều khiển này như bảng điều khiển trang chủ %s?',
              phutil_tag(
                'strong',
                array(),
                $viewer->renderHandle($object_phid))));
        }
        break;
      default:
        throw new Exception(
          pht(
            'Không biết ứng dụng này "%s"!',
            $application_class));
    }

    return $dialog;
  }

  private function getCancelURI($application_class, $object_phid) {
    $uri = null;
    switch ($application_class) {
      case 'PhabricatorHomeApplication':
        $uri = '/dashboard/view/'.$this->id.'/';
        break;
    }
    return $uri;
  }

  private function getRedirectURI($application_class, $object_phid) {
    $uri = null;
    switch ($application_class) {
      case 'PhabricatorHomeApplication':
        $uri = '/';
        break;
    }
    return $uri;
  }

}
