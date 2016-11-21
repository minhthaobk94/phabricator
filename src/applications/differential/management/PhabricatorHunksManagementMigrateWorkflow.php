<?php

final class PhabricatorHunksManagementMigrateWorkflow
  extends PhabricatorHunksManagementWorkflow {

  protected function didConstruct() {
    $this
      ->setName('migrate')
      ->setExamples('**migrate**')
      ->setSynopsis(pht('Di chuyển hunks để lưu trữ mới'))
      ->setArguments(array());
  }

  public function execute(PhutilArgumentParser $args) {
    $saw_any_rows = false;
    $console = PhutilConsole::getConsole();

    $table = new DifferentialLegacyHunk();
    foreach (new LiskMigrationIterator($table) as $hunk) {
      $saw_any_rows = true;

      $id = $hunk->getID();
      $console->writeOut("%s\n", pht('đang cập nhật hunk %d...', $id));

      $new_hunk = id(new DifferentialModernHunk())
        ->setChangesetID($hunk->getChangesetID())
        ->setOldOffset($hunk->getOldOffset())
        ->setOldLen($hunk->getOldLen())
        ->setNewOffset($hunk->getNewOffset())
        ->setNewLen($hunk->getNewLen())
        ->setChanges($hunk->getChanges())
        ->setDateCreated($hunk->getDateCreated())
        ->setDateModified($hunk->getDateModified());

      $hunk->openTransaction();
        $new_hunk->save();
        $hunk->delete();
      $hunk->saveTransaction();

      $old_len = strlen($hunk->getChanges());
      $new_len = strlen($new_hunk->getData());
      if ($old_len) {
        $diff_len = ($old_len - $new_len);
        $console->writeOut(
          "%s\n",
          pht(
            'Đã lưu %s bytes (%s).',
            new PhutilNumber($diff_len),
            sprintf('%.1f%%', 100 * ($diff_len / $old_len))));
      }
    }

    if ($saw_any_rows) {
      $console->writeOut("%s\n", pht('Xong.'));
    } else {
      $console->writeOut("%s\n", pht('Khong có hàng nào để cập nhật.'));
    }
  }

}
