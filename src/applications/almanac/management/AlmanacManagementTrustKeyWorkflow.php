<?php

final class AlmanacManagementTrustKeyWorkflow
  extends AlmanacManagementWorkflow {

  protected function didConstruct() {
    $this
      ->setName('trust-key')
      ->setSynopsis(pht('Đánh dấu khóa công cộng.'))
      ->setArguments(
        array(
          array(
            'name' => 'id',
            'param' => 'id',
            'help' => pht('ID khóa.'),
          ),
        ));
  }

  public function execute(PhutilArgumentParser $args) {
    $console = PhutilConsole::getConsole();

    $id = $args->getArg('id');
    if (!$id) {
      throw new PhutilArgumentUsageException(
        pht('Trùng khóa'));
    }

    $key = id(new PhabricatorAuthSSHKeyQuery())
      ->setViewer($this->getViewer())
      ->withIDs(array($id))
      ->executeOne();
    if (!$key) {
      throw new PhutilArgumentUsageException(
        pht('Không có khóa nào tồn tại với ID "%s".', $id));
    }

    if (!$key->getIsActive()) {
      throw new PhutilArgumentUsageException(
        pht('Khóa "%s" không hoạt động.', $id));
    }

    if ($key->getIsTrusted()) {
      throw new PhutilArgumentUsageException(
        pht('Khóa ID %s được xác nhận.', $id));
    }

    if (!($key->getObject() instanceof AlmanacDevice)) {
      throw new PhutilArgumentUsageException(
        pht('Chỉ tin vào mối quan hệ với thiết bị.'));
    }

    $handle = id(new PhabricatorHandleQuery())
      ->setViewer($this->getViewer())
      ->withPHIDs(array($key->getObject()->getPHID()))
      ->executeOne();

    $console->writeOut(
      "**<bg:red> %s </bg>**\n\n%s\n\n%s\n\n%s",
      pht('IMPORTANT!'),
      phutil_console_wrap(
        pht(
          'Trusting a public key gives anyone holding the corresponding '.
          'private key complete, unrestricted access to all data in '.
          'Phabricator. The private key will be able to sign requests that '.
          'skip policy and security checks.')),
      phutil_console_wrap(
        pht(
          'This is an advanced feature which should normally be used only '.
          'when building a Phabricator cluster. This feature is very '.
          'dangerous if misused.')),
      pht('Khóa này có liên quan tới thiết bị "%s".', $handle->getName()));

    $prompt = pht(
      'Thực sự đáng tin cậy?');
    if (!phutil_console_confirm($prompt)) {
      throw new PhutilArgumentUsageException(
        pht('Luồng làm việc người dùng.'));
    }

    $key->setIsTrusted(1);
    $key->save();

    $console->writeOut(
      "**<bg:green> %s </bg>** %s\n",
      pht('TRUSTED'),
      pht('Khóa %s đã được đánh dấu.', $id));
  }

}
