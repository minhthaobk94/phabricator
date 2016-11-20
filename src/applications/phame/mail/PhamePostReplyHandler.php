<?php

final class PhamePostReplyHandler
  extends PhabricatorApplicationTransactionReplyHandler {

  public function validateMailReceiver($mail_receiver) {
    if (!($mail_receiver instanceof PhamePost)) {
      throw new Exception(
        pht('Thư nhận không phải là %s.', 'PhamePost'));
    }
  }

  public function getObjectPrefix() {
    return PhabricatorPhamePostPHIDType::TYPECONST;
  }

}
