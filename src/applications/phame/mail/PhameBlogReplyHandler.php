<?php

final class PhameBlogReplyHandler
  extends PhabricatorApplicationTransactionReplyHandler {

  public function validateMailReceiver($mail_receiver) {
    if (!($mail_receiver instanceof PhameBlog)) {
      throw new Exception(
        pht('Thư nhận không phải là  %s.', 'PhameBlog'));
    }
  }

  public function getObjectPrefix() {
    return PhabricatorPhameBlogPHIDType::TYPECONST;
  }

  protected function shouldCreateCommentFromMailBody() {
    return false;
  }

}
