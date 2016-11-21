<?php

final class PhabricatorCommitSearchEngine
  extends PhabricatorApplicationSearchEngine {

  public function getResultTypeDescription() {
    return pht('Commits');
  }

  public function getApplicationClassName() {
    return 'PhabricatorDiffusionApplication';
  }

  public function newQuery() {
    return id(new DiffusionCommitQuery())
      ->needAuditRequests(true)
      ->needCommitData(true);
  }

  protected function buildQueryFromParameters(array $map) {
    $query = $this->newQuery();

    if ($map['needsAuditByPHIDs']) {
      $query->withNeedsAuditByPHIDs($map['needsAuditByPHIDs']);
    }

    if ($map['auditorPHIDs']) {
      $query->withAuditorPHIDs($map['auditorPHIDs']);
    }

    if ($map['commitAuthorPHIDs']) {
      $query->withAuthorPHIDs($map['commitAuthorPHIDs']);
    }

    if ($map['auditStatus']) {
      $query->withAuditStatus($map['auditStatus']);
    }

    if ($map['repositoryPHIDs']) {
      $query->withRepositoryPHIDs($map['repositoryPHIDs']);
    }

    return $query;
  }

  protected function buildCustomSearchFields() {
    return array(
      id(new PhabricatorSearchDatasourceField())
        ->setLabel(pht('Kiểm tra cần thiết'))
        ->setKey('needsAuditByPHIDs')
        ->setAliases(array('needs', 'need'))
        ->setDatasource(new DiffusionAuditorFunctionDatasource()),
      id(new PhabricatorSearchDatasourceField())
        ->setLabel(pht('Người kiểm tra'))
        ->setKey('auditorPHIDs')
        ->setAliases(array('auditor', 'auditors'))
        ->setDatasource(new DiffusionAuditorFunctionDatasource()),
      id(new PhabricatorUsersSearchField())
        ->setLabel(pht('Tác giả'))
        ->setKey('commitAuthorPHIDs')
        ->setAliases(array('author', 'authors')),
      id(new PhabricatorSearchSelectField())
        ->setLabel(pht('Trạng thái kiểm tra'))
        ->setKey('auditStatus')
        ->setAliases(array('status'))
        ->setOptions($this->getAuditStatusOptions()),
      id(new PhabricatorSearchDatasourceField())
        ->setLabel(pht('Repositories'))
        ->setKey('repositoryPHIDs')
        ->setAliases(array('repository', 'repositories'))
        ->setDatasource(new DiffusionRepositoryDatasource()),
    );
  }

  protected function getURI($path) {
    return '/audit/'.$path;
  }

  protected function getBuiltinQueryNames() {
    $names = array();

    if ($this->requireViewer()->isLoggedIn()) {
      $names['need'] = pht('Kiểm tra cần thiết');
      $names['problem'] = pht('Vấn đề Commits');
    }

    $names['open'] = pht('Open Audits');

    if ($this->requireViewer()->isLoggedIn()) {
      $names['authored'] = pht('Người viết Commits');
    }

    $names['all'] = pht('Tất cả Commits');

    return $names;
  }

  public function buildSavedQueryFromBuiltin($query_key) {
    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);
    $viewer = $this->requireViewer();

    $viewer_phid = $viewer->getPHID();
    $status_open = DiffusionCommitQuery::AUDIT_STATUS_OPEN;

    switch ($query_key) {
      case 'all':
        return $query;
      case 'open':
        $query->setParameter('auditStatus', $status_open);
        return $query;
      case 'need':
        $needs_tokens = array(
          $viewer_phid,
          'projects('.$viewer_phid.')',
          'packages('.$viewer_phid.')',
        );

        $query->setParameter('needsAuditByPHIDs', $needs_tokens);
        $query->setParameter('auditStatus', $status_open);
        return $query;
      case 'authored':
        $query->setParameter('commitAuthorPHIDs', array($viewer->getPHID()));
        return $query;
      case 'problem':
        $query->setParameter('commitAuthorPHIDs', array($viewer->getPHID()));
        $query->setParameter(
          'auditStatus',
          DiffusionCommitQuery::AUDIT_STATUS_CONCERN);
        return $query;
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  private function getAuditStatusOptions() {
    return array(
      DiffusionCommitQuery::AUDIT_STATUS_ANY => pht('Bất kì'),
      DiffusionCommitQuery::AUDIT_STATUS_OPEN => pht('Mở'),
      DiffusionCommitQuery::AUDIT_STATUS_CONCERN => pht('Lo ngại'),
      DiffusionCommitQuery::AUDIT_STATUS_ACCEPTED => pht('Chấp nhận'),
      DiffusionCommitQuery::AUDIT_STATUS_PARTIAL => pht('Đa kiểm tra'),
    );
  }

  protected function renderResultList(
    array $commits,
    PhabricatorSavedQuery $query,
    array $handles) {

    assert_instances_of($commits, 'PhabricatorRepositoryCommit');

    $viewer = $this->requireViewer();
    $nodata = pht('Kiểm tra không khớp.');
    $view = id(new PhabricatorAuditListView())
      ->setUser($viewer)
      ->setCommits($commits)
      ->setAuthorityPHIDs(
        PhabricatorAuditCommentEditor::loadAuditPHIDsForUser($viewer))
      ->setNoDataString($nodata);

    $phids = $view->getRequiredHandlePHIDs();
    if ($phids) {
      $handles = id(new PhabricatorHandleQuery())
        ->setViewer($viewer)
        ->withPHIDs($phids)
        ->execute();
    } else {
      $handles = array();
    }

    $view->setHandles($handles);
    $list = $view->buildList();

    $result = new PhabricatorApplicationSearchResultView();
    $result->setContent($list);

    return $result;
  }

  protected function getNewUserBody() {

    $view = id(new PHUIBigInfoView())
      ->setIcon('fa-check-circle-o')
      ->setTitle(pht('Welcome to Audit'))
      ->setDescription(
        pht('Hiển thị commit về code và kiểm tra. '.
            'Kiểm tra mà bạn được phân công sẽ xuất hiện ở đây.'));

      return $view;
  }

}
