<?php

final class PhabricatorAuthConfirmLinkController
  extends PhabricatorAuthController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $this->getViewer();
    $accountkey = $request->getURIData('akey');

    $result = $this->loadAccountForRegistrationOrLinking($accountkey);
    list($account, $provider, $response) = $result;

    if ($response) {
      return $response;
    }

    if (!$provider->shouldAllowAccountLink()) {
      return $this->renderError(pht('Tài khoản này không thể kết nối.'));
    }

    $panel_uri = '/settings/panel/external/';

    if ($request->isFormPost()) {
      $account->setUserPHID($viewer->getPHID());
      $account->save();

      $this->clearRegistrationCookies();

      // TODO: Send the user email about the new account link.

      return id(new AphrontRedirectResponse())->setURI($panel_uri);
    }

    // TODO: Provide more information about the external account. Clicking
    // through this form blindly is dangerous.

    // TODO: If the user has password authentication, require them to retype
    // their password here.

    $dialog = id(new AphrontDialogView())
      ->setUser($viewer)
      ->setTitle(pht('Xác nhận  %s tài khoản liên kết', $provider->getProviderName()))
      ->addCancelButton($panel_uri)
      ->addSubmitButton(pht('Xác nhận tài khản liên kết'));

    $form = id(new PHUIFormLayoutView())
      ->setFullWidth(true)
      ->appendChild(
        phutil_tag(
          'div',
          array(
            'class' => 'aphront-form-instructions',
          ),
          pht(
            'Xác nhận liên kết với tài khoản này % s. Tài khoản này sẽ được '.
             'Có thể đăng nhập vào tài khoản của bạn Phabricator.',
            $provider->getProviderName())))
      ->appendChild(
        id(new PhabricatorAuthAccountView())
          ->setUser($viewer)
          ->setExternalAccount($account)
          ->setAuthProvider($provider));

    $dialog->appendChild($form);

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->addTextCrumb(pht('Xác nhận liên kết'), $panel_uri);
    $crumbs->addTextCrumb($provider->getProviderName());
    $crumbs->setBorder(true);

    return $this->newPage()
      ->setTitle(pht('Xác nhận liên kết tài khoản ngoài'))
      ->setCrumbs($crumbs)
      ->appendChild($dialog);
  }


}
