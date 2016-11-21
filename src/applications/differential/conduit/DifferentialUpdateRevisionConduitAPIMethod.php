<?php

final class DifferentialUpdateRevisionConduitAPIMethod
  extends DifferentialConduitAPIMethod {

  public function getAPIMethodName() {
    return 'differential.updaterevision';
  }

  public function getMethodDescription() {
    return pht('Cập nhật phiên bản sự khác biệt.');
  }

  protected function defineParamTypes() {
    return array(
      'id'        => 'required revisionid',
      'diffid'    => 'required diffid',
      'fields'    => 'required dict',
      'message'   => 'required string',
    );
  }

  protected function defineReturnType() {
    return 'nonempty dict';
  }

  protected function defineErrorTypes() {
    return array(
      'ERR_BAD_DIFF'     => pht('ID khác nhau kém.'),
      'ERR_BAD_REVISION' => pht('ID sửa đổi kém.'),
      'ERR_WRONG_USER'   => pht('Bạn không phải tác giả cho sự sửa đổi này.'),
      'ERR_CLOSED'       => pht('Sự sửa đổi này đã bị đóng.'),
    );
  }

  protected function execute(ConduitAPIRequest $request) {
    $viewer = $request->getUser();

    $diff = id(new DifferentialDiffQuery())
      ->setViewer($viewer)
      ->withIDs(array($request->getValue('diffid')))
      ->executeOne();
    if (!$diff) {
      throw new ConduitException('ERR_BAD_DIFF');
    }

    $revision = id(new DifferentialRevisionQuery())
      ->setViewer($request->getUser())
      ->withIDs(array($request->getValue('id')))
      ->needReviewerStatus(true)
      ->needActiveDiffs(true)
      ->requireCapabilities(
        array(
          PhabricatorPolicyCapability::CAN_VIEW,
          PhabricatorPolicyCapability::CAN_EDIT,
        ))
      ->executeOne();
    if (!$revision) {
      throw new ConduitException('ERR_BAD_REVISION');
    }

    if ($revision->getStatus() == ArcanistDifferentialRevisionStatus::CLOSED) {
      throw new ConduitException('ERR_CLOSED');
    }

    $this->applyFieldEdit(
      $request,
      $revision,
      $diff,
      $request->getValue('fields', array()),
      $request->getValue('message'));

    return array(
      'revisionid'  => $revision->getID(),
      'uri'         => PhabricatorEnv::getURI('/D'.$revision->getID()),
    );
  }

}
