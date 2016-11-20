<?php

final class PhabricatorAuthSSHKeyGenerateController
  extends PhabricatorAuthSSHKeyController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $this->getViewer();

    $key = $this->newKeyForObjectPHID($request->getStr('objectPHID'));
    if (!$key) {
      return new Aphront404Response();
    }

    $cancel_uri = $key->getObject()->getSSHPublicKeyManagementURI($viewer);

    $token = id(new PhabricatorAuthSessionEngine())->requireHighSecuritySession(
      $viewer,
      $request,
      $cancel_uri);

    if ($request->isFormPost()) {
      $default_name = $key->getObject()->getSSHKeyDefaultName();

      $keys = PhabricatorSSHKeyGenerator::generateKeypair();
      list($public_key, $private_key) = $keys;

      $file = PhabricatorFile::buildFromFileDataOrHash(
        $private_key,
        array(
          'name' => $default_name.'.key',
          'ttl' => time() + (60 * 10),
          'viewPolicy' => $viewer->getPHID(),
        ));

      $public_key = PhabricatorAuthSSHPublicKey::newFromRawKey($public_key);

      $type = $public_key->getType();
      $body = $public_key->getBody();
      $comment = pht('Tạo');

      $entire_key = "{$type} {$body} {$comment}";

      $type_create = PhabricatorTransactions::TYPE_CREATE;
      $type_name = PhabricatorAuthSSHKeyTransaction::TYPE_NAME;
      $type_key = PhabricatorAuthSSHKeyTransaction::TYPE_KEY;

      $xactions = array();

      $xactions[] = id(new PhabricatorAuthSSHKeyTransaction())
        ->setTransactionType(PhabricatorTransactions::TYPE_CREATE);

      $xactions[] = id(new PhabricatorAuthSSHKeyTransaction())
        ->setTransactionType($type_name)
        ->setNewValue($default_name);

      $xactions[] = id(new PhabricatorAuthSSHKeyTransaction())
        ->setTransactionType($type_key)
        ->setNewValue($entire_key);

      $editor = id(new PhabricatorAuthSSHKeyEditor())
        ->setActor($viewer)
        ->setContentSourceFromRequest($request)
        ->applyTransactions($key, $xactions);

      // NOTE: We're disabling workflow on submit so the download works. We're
      // disabling workflow on cancel so the page reloads, showing the new
      // key.

      return $this->newDialog()
        ->setTitle(pht('Tải khóa chính'))
        ->setDisableWorkflowOnCancel(true)
        ->setDisableWorkflowOnSubmit(true)
        ->setSubmitURI($file->getDownloadURI())
        ->appendParagraph(
          pht(
            'Một cặp khóa đã được tạo ra, và khóa công khai đã được '.
            'thêm vào như là một chìa khóa công nhận. Sử dụng các nút dưới đây để tải về'.
            'khóa chính.'))
        ->appendParagraph(
          pht(
            'Sau khi bạn tải về các khóa riêng, nó sẽ bị phá hủy.Bạn sẽ không thể lấy nó nếu bạn bị mất bản sao của bạn.'))
        ->addSubmitButton(pht('Tải khóa chính'))
        ->addCancelButton($cancel_uri, pht('Xong'));
    }

    try {
      PhabricatorSSHKeyGenerator::assertCanGenerateKeypair();

      return $this->newDialog()
        ->setTitle(pht('Tạo KeyPair'))
        ->addHiddenInput('objectPHID', $key->getObject()->getPHID())
        ->appendParagraph(
          pht(
            'Quy trình này sẽ tạo ra một cặp khóa SSH mới, thêm công chúng '.
            'quan trọng, và cho phép bạn tải về các khóa riêng.'))
        ->appendParagraph(
          pht('Phabricator sẽ không giữ lại một bản sao của khóa riêng.'))
        ->addSubmitButton(pht('Tạo KeyPair'))
        ->addCancelButton($cancel_uri);
    } catch (Exception $ex) {
      return $this->newDialog()
        ->setTitle(pht('Ẩn Tạo KeyPair'))
        ->appendParagraph($ex->getMessage())
        ->addCancelButton($cancel_uri);
    }
  }

}
