<?php

final class DifferentialChangesSinceLastUpdateField
  extends DifferentialCustomField {

  public function getFieldKey() {
    return 'differential:changes-since-last-update';
  }

  public function getFieldName() {
    return pht('Thay đổi Kể từ Cập nhật cuối');
  }

  public function getFieldDescription() {
    return pht('Liên kết để thay đổi kể từ lần cập nhật cuối trong email.');
  }

  public function shouldAppearInTransactionMail() {
    return true;
  }

  public function updateTransactionMailBody(
    PhabricatorMetaMTAMailBody $body,
    PhabricatorApplicationTransactionEditor $editor,
    array $xactions) {

    if ($editor->getIsNewObject()) {
      return;
    }

    if ($editor->getIsCloseByCommit()) {
      return;
    }

    $xaction = $editor->getDiffUpdateTransaction($xactions);
    if (!$xaction) {
      return;
    }

    $original = id(new DifferentialDiffQuery())
      ->setViewer($this->getViewer())
      ->withPHIDs(array($xaction->getOldValue()))
      ->executeOne();
    if (!$original) {
      return;
    }

    $revision = $this->getObject();
    $current = $revision->getActiveDiff();

    $old_id = $original->getID();
    $new_id = $current->getID();

    $uri = '/'.$revision->getMonogram().'?vs='.$old_id.'&id='.$new_id;
    $uri = PhabricatorEnv::getProductionURI($uri);

    $body->addLinkSection(pht('THAY ĐỔI TỪ CẬP NHẬT CUỐI'), $uri);
  }

}
