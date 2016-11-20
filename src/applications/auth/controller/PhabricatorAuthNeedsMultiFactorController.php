<?php

final class PhabricatorAuthNeedsMultiFactorController
  extends PhabricatorAuthController {

  public function shouldRequireMultiFactorEnrollment() {
    // Users need access to this controller in order to enroll in multi-factor
    // auth.
    return false;
  }

  public function handleRequest(AphrontRequest $request) {
    $viewer = $this->getViewer();

    $panel = id(new PhabricatorMultiFactorSettingsPanel())
      ->setUser($viewer)
      ->setViewer($viewer)
      ->setOverrideURI($this->getApplicationURI('/multifactor/'))
      ->processRequest($request);

    if ($panel instanceof AphrontResponse) {
      return $panel;
    }

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->addTextCrumb(pht('Thêm đa người dùng, Auth'));

    $viewer->updateMultiFactorEnrollment();

    if (!$viewer->getIsEnrolledInMultiFactor()) {
      $help = id(new PHUIInfoView())
        ->setTitle(pht('Thêm đa người dùng Xác thực đến Tài khoản của bạn'))
        ->setSeverity(PHUIInfoView::SEVERITY_WARNING)
        ->setErrors(
          array(
            pht(
              'Trước khi bạn có thể sử dụng Phabricator, bạn cần phải thêm đa người dùng  '.
              'xác thực tài khoản của bạn.'),
            pht(
              'Xác thực đa người dùng giúp bảo vệ tài khoản của bạn bằng cách '.
              'làm cho nó khó khăn hơn cho tin tặc truy cập hoặc'.
              'có những hành động nhạy cảm.'),
            pht(
              'Để tìm hiểu thêm về xác thực đa người dùng, nhấp vào '.
              '%s nút dưới đây.',
              phutil_tag('strong', array(), pht('Giúp đỡ'))),
            pht(
              'Để thêm một yếu tố xác thực, hãy bấm vào % s nút bên dưới.',
              phutil_tag('strong', array(), pht('Thêm xác thực người dùng'))),
            pht(
              'Để tiếp tục, thêm ít nhất một yếu tố xác thực'.
               'tài khoản của bạn.'),
          ));
    } else {
      $help = id(new PHUIInfoView())
        ->setTitle(pht('Cấu hình xác thực đa người dùng'))
        ->setSeverity(PHUIInfoView::SEVERITY_NOTICE)
        ->setErrors(
          array(
            pht(
              'Bạn đã cấu hình thành công xác thực đa người dùng  '.
              'cho tài khoản của bạn.'),
            pht(
              'Bạn có thể điều chỉnh từ bảng điều khiển Cài đặt sau.'),
            pht(
              'Khi bạn đã sẵn sàng, %s.',
              phutil_tag(
                'strong',
                array(),
                phutil_tag(
                  'a',
                  array(
                    'href' => '/',
                  ),
                  pht('tiếp tục Phabricator')))),
          ));
    }

    $view = array(
      $help,
      $panel,
    );

    return $this->newPage()
      ->setTitle(pht('Thêm xác thực người dùng'))
      ->setCrumbs($crumbs)
      ->appendChild($view);

  }

}
