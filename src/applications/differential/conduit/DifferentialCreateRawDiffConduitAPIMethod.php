<?php

final class DifferentialCreateRawDiffConduitAPIMethod
  extends DifferentialConduitAPIMethod {

  public function getAPIMethodName() {
    return 'differential.createrawdiff';
  }

  public function getMethodDescription() {
    return pht('Tạo một Sự khác biệt  khác mới từ một nguồn khác.');
  }

  protected function defineParamTypes() {
    return array(
      'diff' => 'required string',
      'repositoryPHID' => 'optional string',
      'viewPolicy' => 'optional string',
    );
  }

  protected function defineReturnType() {
    return 'nonempty dict';
  }

  protected function execute(ConduitAPIRequest $request) {
    $viewer = $request->getUser();
    $raw_diff = $request->getValue('diff');

    $repository_phid = $request->getValue('repositoryPHID');
    if ($repository_phid) {
      $repository = id(new PhabricatorRepositoryQuery())
        ->setViewer($viewer)
        ->withPHIDs(array($repository_phid))
        ->executeOne();
      if (!$repository) {
        throw new Exception(
          pht('No such repository "%s"!', $repository_phid));
      }
    }

    $parser = new ArcanistDiffParser();
    $changes = $parser->parseDiff($raw_diff);
    $diff = DifferentialDiff::newFromRawChanges($viewer, $changes);

    // We're bounded by doing INSERTs for all the hunks and changesets, so
    // estimate the number of inserts we'll require.
    $size = 0;
    foreach ($diff->getChangesets() as $changeset) {
      $hunks = $changeset->getHunks();
      $size += 1 + count($hunks);
    }

    $raw_limit = 10000;
    if ($size > $raw_limit) {
      throw new Exception(
        pht(
          'Dữ liệu khác  bạn được gửi quá lớn để phân tích (Nó ảnh hưởng  '.
          'nhiều hơn  %s đường dẫn và hunks). Sự khác biệt nên sử dụng một lần  '.
          'cho thay đổi mà nó nhỏ hơn cái mà con người xem xét '.
          '. Xem "Hướng dẫn sử dụng sự khác biệt : Thay đổi ngôn ngữ" trong'.
          'tài liệu thông tin .',
          new PhutilNumber($raw_limit)));
    }

    $diff_data_dict = array(
      'creationMethod' => 'web',
      'authorPHID' => $viewer->getPHID(),
      'repositoryPHID' => $repository_phid,
      'lintStatus' => DifferentialLintStatus::LINT_SKIP,
      'unitStatus' => DifferentialUnitStatus::UNIT_SKIP,
    );

    $xactions = array(
      id(new DifferentialDiffTransaction())
        ->setTransactionType(DifferentialDiffTransaction::TYPE_DIFF_CREATE)
        ->setNewValue($diff_data_dict),
    );

    if ($request->getValue('viewPolicy')) {
      $xactions[] = id(new DifferentialDiffTransaction())
        ->setTransactionType(PhabricatorTransactions::TYPE_VIEW_POLICY)
        ->setNewValue($request->getValue('viewPolicy'));
    }

    id(new DifferentialDiffEditor())
      ->setActor($viewer)
      ->setContentSource($request->newContentSource())
      ->setContinueOnNoEffect(true)
      ->setLookupRepository(false) // respect user choice
      ->applyTransactions($diff, $xactions);

    return $this->buildDiffInfoDictionary($diff);
  }

}
