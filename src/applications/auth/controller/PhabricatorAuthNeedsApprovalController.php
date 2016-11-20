<?php

final class PhabricatorAuthNeedsApprovalController
  extends PhabricatorAuthController {

  public function shouldRequireLogin() {
    return false;
  }

  public function shouldRequireEmailVerification() {
    return false;
  }

  public function shouldRequireEnabledUser() {
    return false;
  }

  public function handleRequest(AphrontRequest $request) {
    $viewer = $this->getViewer();

    $wait_for_approval = pht(
      "Tài khoản của bạn đã được tạo ra, nhưng cần phải được phê duyệt bởi một ".
      " người quản trị. Bạn sẽ nhận được một email khi tài khoản của bạn được chấp thuận.");

    $dialog = id(new AphrontDialogView())
      ->setUser($viewer)
      ->setTitle(pht('Chờ phê duyệt'))
      ->appendChild($wait_for_approval)
      ->addCancelButton('/', pht('Chờ kiên nhẫn'));

    return $this->newPage()
      ->setTitle(pht('Chờ để chấp thuận'))
      ->appendChild($dialog);

  }

}
