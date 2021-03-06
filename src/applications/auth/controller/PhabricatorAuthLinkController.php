<?php

final class PhabricatorAuthLinkController
  extends PhabricatorAuthController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $this->getViewer();
    $action = $request->getURIData('action');
    $provider_key = $request->getURIData('pkey');

    $provider = PhabricatorAuthProvider::getEnabledProviderByKey(
      $provider_key);
    if (!$provider) {
      return new Aphront404Response();
    }

    switch ($action) {
      case 'link':
        if (!$provider->shouldAllowAccountLink()) {
          return $this->renderErrorPage(
            pht('Tài khoản không liên kết'),
            array(
              pht('Cung cấp này không được cấu hình để cho phép liên kết.'),
            ));
        }
        break;
      case 'refresh':
        if (!$provider->shouldAllowAccountRefresh()) {
          return $this->renderErrorPage(
            pht('Tài khoản Không thể làm mới,'),
            array(
              pht('Cung cấp này không cho phép làm mới.'),
            ));
        }
        break;
      default:
        return new Aphront400Response();
    }

    $account = id(new PhabricatorExternalAccount())->loadOneWhere(
      'accountType = %s AND accountDomain = %s AND userPHID = %s',
      $provider->getProviderType(),
      $provider->getProviderDomain(),
      $viewer->getPHID());

    switch ($action) {
      case 'link':
        if ($account) {
          return $this->renderErrorPage(
            pht('Tài khoản đã được liên kết'),
            array(
              pht(
                'tài khoản Phabricator của bạn đã được liên kết với một bên ngoài.
                 "Tài khoản cho cung cấp này.'),
            ));
        }
        break;
      case 'refresh':
        if (!$account) {
          return $this->renderErrorPage(
            pht('Không có tài khoản liên kết'),
            array(
              pht(
                'Bạn không có tài khoản liên kết vào nhà cung cấp này, và do đó '.
                 'Không thể làm mới nó.'),
            ));
        }
        break;
      default:
        return new Aphront400Response();
    }

    $panel_uri = '/settings/panel/external/';

    PhabricatorCookies::setClientIDCookie($request);

    switch ($action) {
      case 'link':
        id(new PhabricatorAuthSessionEngine())->requireHighSecuritySession(
          $viewer,
          $request,
          $panel_uri);

        $form = $provider->buildLinkForm($this);
        break;
      case 'refresh':
        $form = $provider->buildRefreshForm($this);
        break;
      default:
        return new Aphront400Response();
    }

    if ($provider->isLoginFormAButton()) {
      require_celerity_resource('auth-css');
      $form = phutil_tag(
        'div',
        array(
          'class' => 'phabricator-link-button pl',
        ),
        $form);
    }

    switch ($action) {
      case 'link':
        $name = pht('Liên kết tài khoản');
        $title = pht('Liên kết %s Tài khoản', $provider->getProviderName());
        break;
      case 'refresh':
        $name = pht('Làm mới tài khoản');
        $title = pht('Làm mới %s Tài khoản', $provider->getProviderName());
        break;
      default:
        return new Aphront400Response();
    }

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->addTextCrumb(pht('Liên kết tài khoản'), $panel_uri);
    $crumbs->addTextCrumb($provider->getProviderName($name));
    $crumbs->setBorder(true);

    return $this->newPage()
      ->setTitle($title)
      ->setCrumbs($crumbs)
      ->appendChild($form);
  }

}
