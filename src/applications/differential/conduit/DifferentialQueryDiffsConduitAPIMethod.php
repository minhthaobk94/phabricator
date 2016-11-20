<?php

final class DifferentialQueryDiffsConduitAPIMethod
  extends DifferentialConduitAPIMethod {

  public function getAPIMethodName() {
    return 'differential.querydiffs';
  }

  public function getMethodDescription() {
    return pht('Truy vấn sự khác biệt được kết hợp với kịch bản nhất định.');
  }

  protected function defineParamTypes() {
    return array(
      'ids' => 'optional list<uint>',
      'revisionIDs' => 'optional list<uint>',
    );
  }

  protected function defineReturnType() {
    return 'list<dict>';
  }

  protected function execute(ConduitAPIRequest $request) {
    $ids = $request->getValue('ids', array());
    $revision_ids = $request->getValue('revisionIDs', array());

    $diffs = array();
    if ($ids || $revision_ids) {
      $diffs = id(new DifferentialDiffQuery())
        ->setViewer($request->getUser())
        ->withIDs($ids)
        ->withRevisionIDs($revision_ids)
        ->needChangesets(true)
        ->execute();
    }

    return mpull($diffs, 'getDiffDict', 'getID');
  }

}
