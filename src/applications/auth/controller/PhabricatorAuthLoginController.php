<?php

final class PhabricatorAuthLoginController
  extends PhabricatorAuthController {

  private $providerKey;
  private $extraURIData;
  private $provider;

  public function shouldRequireLogin() {
    return false;
  }

  public function shouldAllowRestrictedParameter($parameter_name) {
    // Whitelist the OAuth 'code' parameter.

    if ($parameter_name == 'code') {
      return true;
    }
    return parent::shouldAllowRestrictedParameter($parameter_name);
  }

  public function getExtraURIData() {
    return $this->extraURIData;
  }

  public function handleRequest(AphrontRequest $request) {
    $viewer = $this->getViewer();
    $this->providerKey = $request->getURIData('pkey');
    $this->extraURIData = $request->getURIData('extra');

    $response = $this->loadProvider();
    if ($response) {
      return $response;
    }

    $provider = $this->provider;

    try {
      list($account, $response) = $provider->processLoginRequest($this);
    } catch (PhutilAuthUserAbortedException $ex) {
      if ($viewer->isLoggedIn()) {
        // If a logged-in user cancels, take them back to the external accounts
        // panel.
        $next_uri = '/settings/panel/external/';
      } else {
        // If a logged-out user cancels, take them back to the auth start page.
        $next_uri = '/';
      }

      // User explicitly hit "Cancel".
      $dialog = id(new AphrontDialogView())
        ->setUser($viewer)
        ->setTitle(pht('Hủy xác thực '))
        ->appendChild(
          pht('Bạn hủy xác thực.'))
        ->addCancelButton($next_uri, pht('Tiếp tục'));
      return id(new AphrontDialogResponse())->setDialog($dialog);
    }

    if ($response) {
      return $response;
    }

    if (!$account) {
      throw new Exception(
        pht(
          'Nhà cung cấp Auth thất bại khi tải một tài khoản từ  %s!',
          'processLoginRequest()'));
    }

    if ($account->getUserPHID()) {
      // The account is already attached to a Phabricator user, so this is
      // either a login or a bad account link request.
      if (!$viewer->isLoggedIn()) {
        if ($provider->shouldAllowLogin()) {
          return $this->processLoginUser($account);
        } else {
          return $this->renderError(
            pht(
              'Các tài khoản bên ngoài ("%s")bạn chỉ cần xác thực là'.
              'không được cấu hình để cho phép đăng nhập vào Phabricator này cài đặt. '.
              'Quản trị viên có thể đã bị vô hiệu hóa nó gần đây.',
              $provider->getProviderName()));
        }
      } else if ($viewer->getPHID() == $account->getUserPHID()) {
        // This is either an attempt to re-link an existing and already
        // linked account (which is silly) or a refresh of an external account
        // (e.g., an OAuth account).
        return id(new AphrontRedirectResponse())
          ->setURI('/settings/panel/external/');
      } else {
        return $this->renderError(
          pht(
            'Các tài khoản bên ngoài("%s") bạn chỉ được sử dụng để đăng nhập '.
            'kết hợp với một tài khoản người dùng Phabricator.Đăng nhập vào'.
            'tài khoản Phabricator khác và bỏ liên kết các tài khoản bên ngoài trước'.
            'khi kết nối nó với một tài khoản Phabricator mới.',
            $provider->getProviderName()));
      }
    } else {
      // The account is not yet attached to a Phabricator user, so this is
      // either a registration or an account link request.
      if (!$viewer->isLoggedIn()) {
        if ($provider->shouldAllowRegistration()) {
          return $this->processRegisterUser($account);
        } else {
          return $this->renderError(
            pht(
              'Các tài khoản bên ngoài ("%s") bạn chỉ cần xác thực với là '.
              'không được cấu hình để cho phép đăng ký trên Phabricator này'.
              'cài đặt. Người quản trị có thể đã bị vô hiệu hóa nó gần đây.',
              $provider->getProviderName()));
        }
      } else {

        // If the user already has a linked account of this type, prevent them
        // from linking a second account. This can happen if they swap logins
        // and then refresh the account link. See T6707. We will eventually
        // allow this after T2549.
        $existing_accounts = id(new PhabricatorExternalAccountQuery())
          ->setViewer($viewer)
          ->withUserPHIDs(array($viewer->getPHID()))
          ->withAccountTypes(array($account->getAccountType()))
          ->execute();
        if ($existing_accounts) {
          return $this->renderError(
            pht(
              'Tài khoản Phabricator của bạn đã được kết nối với một bên ngoài '.
              'tài khoản cung cấp này ("% s"), nhưng hiện tại bạn đang đăng nhập '.
              'vào nhà cung cấp với một tài khoản khác. Đăng xuất khỏi các '.
              'dịch vụ bên ngoài, sau đó đăng nhập trở lại với đúng tài khoản '.
              'trước khi làm mới liên kết tài khoản.',
              $provider->getProviderName()));
        }

        if ($provider->shouldAllowAccountLink()) {
          return $this->processLinkUser($account);
        } else {
          return $this->renderError(
            pht(
              'Các tài khoản bên ngoài ccount ("%s") bạn chỉ cần xác thực với là '.
              'không được cấu hình để cho phép liên kết tài khoản trên Phabricator này'.
              'cài đặt, dựng lên. Người quản trị có thể đã bị vô hiệu hóa nó gần đây.',
              $provider->getProviderName()));
        }
      }
    }

    // This should be unreachable, but fail explicitly if we get here somehow.
    return new Aphront400Response();
  }

  private function processLoginUser(PhabricatorExternalAccount $account) {
    $user = id(new PhabricatorUser())->loadOneWhere(
      'phid = %s',
      $account->getUserPHID());

    if (!$user) {
      return $this->renderError(
        pht(
          'Các tài khoản bên ngoài, bạn chỉ cần đăng nhập với không liên quan '.
          'với một người dùng hợp lệ Phabricator.'));
    }

    return $this->loginUser($user);
  }

  private function processRegisterUser(PhabricatorExternalAccount $account) {
    $account_secret = $account->getAccountSecret();
    $register_uri = $this->getApplicationURI('register/'.$account_secret.'/');
    return $this->setAccountKeyAndContinue($account, $register_uri);
  }

  private function processLinkUser(PhabricatorExternalAccount $account) {
    $account_secret = $account->getAccountSecret();
    $confirm_uri = $this->getApplicationURI('confirmlink/'.$account_secret.'/');
    return $this->setAccountKeyAndContinue($account, $confirm_uri);
  }

  private function setAccountKeyAndContinue(
    PhabricatorExternalAccount $account,
    $next_uri) {

    if ($account->getUserPHID()) {
      throw new Exception(pht('Tài khoản đã được đăng ký hoặc liên kết.'));
    }

    // Regenerate the registration secret key, set it on the external account,
    // set a cookie on the user's machine, and redirect them to registration.
    // See PhabricatorAuthRegisterController for discussion of the registration
    // key.

    $registration_key = Filesystem::readRandomCharacters(32);
    $account->setProperty(
      'registrationKey',
      PhabricatorHash::digest($registration_key));

    $unguarded = AphrontWriteGuard::beginScopedUnguardedWrites();
      $account->save();
    unset($unguarded);

    $this->getRequest()->setTemporaryCookie(
      PhabricatorCookies::COOKIE_REGISTRATION,
      $registration_key);

    return id(new AphrontRedirectResponse())->setURI($next_uri);
  }

  private function loadProvider() {
    $provider = PhabricatorAuthProvider::getEnabledProviderByKey(
      $this->providerKey);

    if (!$provider) {
      return $this->renderError(
        pht(
          'Các tài khoản bạn đang cố gắng đăng nhập với sử dụng không tồn tại '.
          'hay vô hiệu hóa nhà cung cấp xác thực (với phím "% s"). Một'.
          'quản trị viên có thể đã bị vô hiệu hóa gần đây nhà cung cấp này.',
          $this->providerKey));
    }

    $this->provider = $provider;

    return null;
  }

  protected function renderError($message) {
    return $this->renderErrorPage(
      pht('Đăng nhập thât bại'),
      array($message));
  }

  public function buildProviderPageResponse(
    PhabricatorAuthProvider $provider,
    $content) {

    $crumbs = $this->buildApplicationCrumbs();

    if ($this->getRequest()->getUser()->isLoggedIn()) {
      $crumbs->addTextCrumb(pht('Liên kết tài khoản'), $provider->getSettingsURI());
    } else {
      $crumbs->addTextCrumb(pht('Đăng nhập'), $this->getApplicationURI('start/'));
    }

    $crumbs->addTextCrumb($provider->getProviderName());
    $crumbs->setBorder(true);

    return $this->newPage()
      ->setTitle(pht('Đăng nhập'))
      ->setCrumbs($crumbs)
      ->appendChild($content);
  }

  public function buildProviderErrorResponse(
    PhabricatorAuthProvider $provider,
    $message) {

    $message = pht(
      'Dịch vụ cung cấp xác thực("%s")gặp phải một lỗi trong khi đăng nhập. %s',
      $provider->getProviderName(),
      $message);

    return $this->renderError($message);
  }

}
