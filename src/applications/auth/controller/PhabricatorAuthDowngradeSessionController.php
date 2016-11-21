<?php

final class PhabricatorAuthDowngradeSessionController
  extends PhabricatorAuthController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $this->getViewer();

    $panel_uri = '/settings/panel/sessions/';

    $session = $viewer->getSession();
    if ($session->getHighSecurityUntil() < time()) {
      return $this->newDialog()
        ->setTitle(pht('Phục hồi an ninh bình thường'))
        ->appendParagraph(
          pht('Phiên của bạn không còn trong bảo mật cao.'))
        ->addCancelButton($panel_uri, pht('Tiếp tục'));
    }

    if ($request->isFormPost()) {

      id(new PhabricatorAuthSessionEngine())
        ->exitHighSecurity($viewer, $session);

      return id(new AphrontRedirectResponse())
        ->setURI($this->getApplicationURI('session/downgrade/'));
    }

    return $this->newDialog()
      ->setTitle(pht('Bỏ bảo mật cao'))
      ->appendParagraph(
        pht(
          'Bỏ bảo mật cao và trả lại phiên của bạn bình thường'.
          'cấp độ bảo mật?'))
      ->appendParagraph(
        pht(
          'Nếu bạn bỏ bảo mật cao, bạn sẽ cần phải xác thực lại lần sau khi bạn cố gắng để có một hành động an ninh cao '.))
      ->appendParagraph(
        pht(
          'Bên góc, sẽ có thông báo màu đỏ
           "Biến mất.'))
      ->addSubmitButton(pht('Bỏ bảo mật cao'))
      ->addCancelButton($panel_uri, pht('Ở LẠI'));
  }


}
