<?php

final class DifferentialGetDiffConduitAPIMethod
  extends DifferentialConduitAPIMethod {

  public function getAPIMethodName() {
    return 'differential.getdiff';
  }

  public function shouldAllowPublic() {
    return true;
  }

  public function getMethodStatus() {
    return self::METHOD_STATUS_DEPRECATED;
  }

  public function getMethodStatusDescription() {
    return pht(
      'Phương pháp này đã được yêu cầu ủng hộ  bởi %s.',
      'differential.querydiffs');
  }


  public function getMethodDescription() {
    return pht(
      'Tải nội dung của khác nhau từ sự khác biệt bởi ID sửa đổi'.
      'or ID khác.');
  }

  protected function defineParamTypes() {
    return array(
      'revision_id' => 'optional id',
      'diff_id'     => 'optional id',
    );
  }

  protected function defineReturnType() {
    return 'nonempty dict';
  }

  protected function defineErrorTypes() {
    return array(
      'ERR_BAD_DIFF' => pht('Không tìm thấy khác nhau.'),
    );
  }

  protected function execute(ConduitAPIRequest $request) {
    $diff_id = $request->getValue('diff_id');

    // If we have a revision ID, we need the most recent diff. Figure that out
    // without loading all the attached data.
    $revision_id = $request->getValue('revision_id');
    if ($revision_id) {
      $diffs = id(new DifferentialDiffQuery())
        ->setViewer($request->getUser())
        ->withRevisionIDs(array($revision_id))
        ->execute();
      if ($diffs) {
        $diff_id = head($diffs)->getID();
      } else {
        throw new ConduitException('ERR_BAD_DIFF');
      }
    }

    $diff = null;
    if ($diff_id) {
      $diff = id(new DifferentialDiffQuery())
        ->setViewer($request->getUser())
        ->withIDs(array($diff_id))
        ->needChangesets(true)
        ->executeOne();
    }

    if (!$diff) {
      throw new ConduitException('ERR_BAD_DIFF');
    }

    return $diff->getDiffDict();
  }

}
