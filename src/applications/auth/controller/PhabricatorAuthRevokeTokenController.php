<?php

final class PhabricatorAuthRevokeTokenController
  extends PhabricatorAuthController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $this->getViewer();
    $id = $request->getURIData('id');

    $is_all = ($id === 'all');

    $query = id(new PhabricatorAuthTemporaryTokenQuery())
      ->setViewer($viewer)
      ->withTokenResources(array($viewer->getPHID()));
    if (!$is_all) {
      $query->withIDs(array($id));
    }

    $tokens = $query->execute();
    foreach ($tokens as $key => $token) {
      if (!$token->isRevocable()) {
        // Don't revoke unrevocable tokens.
        unset($tokens[$key]);
      }
    }

    $panel_uri = id(new PhabricatorTokensSettingsPanel())
      ->setViewer($viewer)
      ->setUser($viewer)
      ->getPanelURI();

    if (!$tokens) {
      return $this->newDialog()
        ->setTitle(pht('Mã kết nối không hợp'))
        ->appendParagraph(
          pht('Không có thẻ phù hợp để thu hồi.'))
        ->appendParagraph(
          pht(
            '(Một số loại thẻ không thể bị thu hồi, và bạn không thể thu hồi'.
            'thẻ đã hết hạn.)'))
        ->addCancelButton($panel_uri);
    }

    if ($request->isDialogFormPost()) {
      foreach ($tokens as $token) {
        $token->revokeToken();
      }
      return id(new AphrontRedirectResponse())->setURI($panel_uri);
    }

    if ($is_all) {
      $title = pht('Thu hồi Tokens?');
      $short = pht('Thu hồi Tokens');
      $body = pht(
        'Thực sự thu hồi tất cả các thẻ? Trong số ủy quyền tạm thời khác, '.
        'này sẽ vô hiệu hóa bất kỳ thiết lập lại mật khẩu nổi bật hoặc tài khoản'.
        'liên kết phục hồi.');
    } else {
      $title = pht('Thu hồi Tokens?');
      $short = pht('Thu hồi Tokens');
      $body = pht(
        'Thực sự thu hồi thẻ này? Bất kỳ ủy quyền tạm thời nó cho phép'.
        'sẽ bị vô hiệu.');
    }

    return $this->newDialog()
      ->setTitle($title)
      ->setShortTitle($short)
      ->appendParagraph($body)
      ->addSubmitButton(pht('Thu hồi '))
      ->addCancelButton($panel_uri);
  }


}
