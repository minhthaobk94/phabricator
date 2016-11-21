<?php

final class ManiphestTaskSearchEngine
  extends PhabricatorApplicationSearchEngine {

  private $showBatchControls;
  private $baseURI;
  private $isBoardView;

  public function setIsBoardView($is_board_view) {
    $this->isBoardView = $is_board_view;
    return $this;
  }

  public function getIsBoardView() {
    return $this->isBoardView;
  }

  public function setBaseURI($base_uri) {
    $this->baseURI = $base_uri;
    return $this;
  }

  public function getBaseURI() {
    return $this->baseURI;
  }

  public function setShowBatchControls($show_batch_controls) {
    $this->showBatchControls = $show_batch_controls;
    return $this;
  }

  public function getResultTypeDescription() {
    return pht('Công việc');
  }

  public function getApplicationClassName() {
    return 'PhabricatorManiphestApplication';
  }

  public function newQuery() {
    return id(new ManiphestTaskQuery())
      ->needProjectPHIDs(true);
  }

  protected function buildCustomSearchFields() {
    return array(
      id(new PhabricatorOwnersSearchField())
        ->setLabel(pht('Phân công bởi'))
        ->setKey('assignedPHIDs')
        ->setConduitKey('assigned')
        ->setAliases(array('assigned'))
        ->setDescription(
          pht('Tìm kiếm phân công công việc mà người  tạo là user.')),
      id(new PhabricatorUsersSearchField())
        ->setLabel(pht('Tác giả'))
        ->setKey('authorPHIDs')
        ->setAliases(array('author', 'authors'))
        ->setDescription(
          pht('Tìm kiếm phân công công việc theo tên tác giả.')),
      id(new PhabricatorSearchDatasourceField())
        ->setLabel(pht('Trạng thái'))
        ->setKey('statuses')
        ->setAliases(array('status'))
        ->setDescription(
          pht('Tìm kiếm phân công công việc theo trạng thái.'))
        ->setDatasource(new ManiphestTaskStatusFunctionDatasource()),
      id(new PhabricatorSearchDatasourceField())
        ->setLabel(pht('Độ ưu tiên'))
        ->setKey('priorities')
        ->setAliases(array('priority'))
        ->setDescription(
          pht('Tìm kiếm phân công công việc theo độ ưu tiên.'))
        ->setConduitParameterType(new ConduitIntListParameterType())
        ->setDatasource(new ManiphestTaskPriorityDatasource()),
      id(new PhabricatorSearchTextField())
        ->setLabel(pht('Từ khóa'))
        ->setKey('fulltext'),
      id(new PhabricatorSearchThreeStateField())
        ->setLabel(pht('Mở nguồn'))
        ->setKey('hasParents')
        ->setAliases(array('blocking'))
        ->setOptions(
          pht('(Tất cả)'),
          pht('Chỉ hiển thị phân công công việc với nguồn'),
          pht('Tìm kiếm phân công công việc không với nguồn.')),
      id(new PhabricatorSearchThreeStateField())
        ->setLabel(pht('Mở công việc con'))
        ->setKey('hasSubtasks')
        ->setAliases(array('blocked'))
        ->setOptions(
          pht('(Tất cả)'),
          pht('Chỉ hiển thị công việc với công việc con'),
          pht('Chỉ hiển thị công việc không với công việc con')),
      id(new PhabricatorIDsSearchField())
        ->setLabel(pht('ID nguồn'))
        ->setKey('parentIDs')
        ->setAliases(array('parentID')),
      id(new PhabricatorIDsSearchField())
        ->setLabel(pht('ID công việc con'))
        ->setKey('subtaskIDs')
        ->setAliases(array('subtaskID')),
      id(new PhabricatorSearchSelectField())
        ->setLabel(pht('Nhóm theo'))
        ->setKey('group')
        ->setOptions($this->getGroupOptions()),
      id(new PhabricatorSearchDateField())
        ->setLabel(pht('Được tạo sau'))
        ->setKey('createdStart'),
      id(new PhabricatorSearchDateField())
        ->setLabel(pht('Được tạo trước'))
        ->setKey('createdEnd'),
      id(new PhabricatorSearchDateField())
        ->setLabel(pht('Cập nhật sau'))
        ->setKey('modifiedStart'),
      id(new PhabricatorSearchDateField())
        ->setLabel(pht('Cập nhật trước'))
        ->setKey('modifiedEnd'),
      id(new PhabricatorSearchTextField())
        ->setLabel(pht('Số trang'))
        ->setKey('limit'),
    );
  }

  protected function getDefaultFieldOrder() {
    return array(
      'assignedPHIDs',
      'projectPHIDs',
      'authorPHIDs',
      'subscriberPHIDs',
      'statuses',
      'priorities',
      'fulltext',
      'hasParents',
      'hasSubtasks',
      'parentIDs',
      'subtaskIDs',
      'group',
      'order',
      'ids',
      '...',
      'createdStart',
      'createdEnd',
      'modifiedStart',
      'modifiedEnd',
      'limit',
    );
  }

  protected function getHiddenFields() {
    $keys = array();

    if ($this->getIsBoardView()) {
      $keys[] = 'group';
      $keys[] = 'order';
      $keys[] = 'limit';
    }

    return $keys;
  }

  protected function buildQueryFromParameters(array $map) {
    $query = $this->newQuery();

    if ($map['assignedPHIDs']) {
      $query->withOwners($map['assignedPHIDs']);
    }

    if ($map['authorPHIDs']) {
      $query->withAuthors($map['authorPHIDs']);
    }

    if ($map['statuses']) {
      $query->withStatuses($map['statuses']);
    }

    if ($map['priorities']) {
      $query->withPriorities($map['priorities']);
    }

    if ($map['createdStart']) {
      $query->withDateCreatedAfter($map['createdStart']);
    }

    if ($map['createdEnd']) {
      $query->withDateCreatedBefore($map['createdEnd']);
    }

    if ($map['modifiedStart']) {
      $query->withDateModifiedAfter($map['modifiedStart']);
    }

    if ($map['modifiedEnd']) {
      $query->withDateModifiedBefore($map['modifiedEnd']);
    }

    if ($map['hasParents'] !== null) {
      $query->withOpenParents($map['hasParents']);
    }

    if ($map['hasSubtasks'] !== null) {
      $query->withOpenSubtasks($map['hasSubtasks']);
    }

    if (strlen($map['fulltext'])) {
      $query->withFullTextSearch($map['fulltext']);
    }

    if ($map['parentIDs']) {
      $query->withParentTaskIDs($map['parentIDs']);
    }

    if ($map['subtaskIDs']) {
      $query->withSubtaskIDs($map['subtaskIDs']);
    }

    $group = idx($map, 'group');
    $group = idx($this->getGroupValues(), $group);
    if ($group) {
      $query->setGroupBy($group);
    } else {
      $query->setGroupBy(head($this->getGroupValues()));
    }

    if ($map['ids']) {
      $ids = $map['ids'];
      foreach ($ids as $key => $id) {
        $id = trim($id, ' Tt');
        if (!$id || !is_numeric($id)) {
          unset($ids[$key]);
        } else {
          $ids[$key] = $id;
        }
      }

      if ($ids) {
        $query->withIDs($ids);
      }
    }

    return $query;
  }

  protected function getURI($path) {
    if ($this->baseURI) {
      return $this->baseURI.$path;
    }
    return '/bugstracker/'.$path;
  }

  protected function getBuiltinQueryNames() {
    $names = array();

    if ($this->requireViewer()->isLoggedIn()) {
      //$names['assigned'] = pht('Assigned');
      $names['authored'] = pht('Tác giả');
      //$names['subscribed'] = pht('Subscribed');
    }

    //$names['open'] = pht('Open Tasks');
   // $names['all'] = pht('All Tasks');

    return $names;
  }

  public function buildSavedQueryFromBuiltin($query_key) {

    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);

    $viewer_phid = $this->requireViewer()->getPHID();

    switch ($query_key) {
      case 'all':
        return $query;
      case 'assigned':
        return $query
          ->setParameter('assignedPHIDs', array($viewer_phid))
          ->setParameter(
            'statuses',
            ManiphestTaskStatus::getOpenStatusConstants());
      case 'subscribed':
        return $query
          ->setParameter('subscriberPHIDs', array($viewer_phid))
          ->setParameter(
            'statuses',
            ManiphestTaskStatus::getOpenStatusConstants());
      case 'open':
        return $query
          ->setParameter(
            'statuses',
            ManiphestTaskStatus::getOpenStatusConstants());
      case 'authored':
        return $query
          ->setParameter('authorPHIDs', array($viewer_phid))
          ->setParameter('order', 'created')
          ->setParameter('group', 'none');
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  private function getGroupOptions() {
    return array(
      'priority' => pht('Độ ưu tiên'),
      'assigned' => pht('Phân công'),
      'status'   => pht('Trạng thái'),
      'project'  => pht('Dự án'),
      'none'     => pht('Không'),
    );
  }

  private function getGroupValues() {
    return array(
      'priority' => ManiphestTaskQuery::GROUP_PRIORITY,
      'assigned' => ManiphestTaskQuery::GROUP_OWNER,
      'status'   => ManiphestTaskQuery::GROUP_STATUS,
      'project'  => ManiphestTaskQuery::GROUP_PROJECT,
      'none'     => ManiphestTaskQuery::GROUP_NONE,
    );
  }

  protected function renderResultList(
    array $tasks,
    PhabricatorSavedQuery $saved,
    array $handles) {

    $viewer = $this->requireViewer();

    if ($this->isPanelContext()) {
      $can_edit_priority = false;
      $can_bulk_edit = false;
    } else {
      $can_edit_priority = PhabricatorPolicyFilter::hasCapability(
        $viewer,
        $this->getApplication(),
        ManiphestEditPriorityCapability::CAPABILITY);

      $can_bulk_edit = PhabricatorPolicyFilter::hasCapability(
        $viewer,
        $this->getApplication(),
        ManiphestBulkEditCapability::CAPABILITY);
    }

    $list = id(new ManiphestTaskResultListView())
      ->setUser($viewer)
      ->setTasks($tasks)
      ->setSavedQuery($saved)
      ->setCanEditPriority($can_edit_priority)
      ->setCanBatchEdit($can_bulk_edit)
      ->setShowBatchControls($this->showBatchControls);

    $result = new PhabricatorApplicationSearchResultView();
    $result->setContent($list);

    return $result;
  }

  protected function willUseSavedQuery(PhabricatorSavedQuery $saved) {

    // The 'withUnassigned' parameter may be present in old saved queries from
    // before parameterized typeaheads, and is retained for compatibility. We
    // could remove it by migrating old saved queries.
    $assigned_phids = $saved->getParameter('assignedPHIDs', array());
    if ($saved->getParameter('withUnassigned')) {
      $assigned_phids[] = PhabricatorPeopleNoOwnerDatasource::FUNCTION_TOKEN;
    }
    $saved->setParameter('assignedPHIDs', $assigned_phids);

    // The 'projects' and other parameters may be present in old saved queries
    // from before parameterized typeaheads.
    $project_phids = $saved->getParameter('projectPHIDs', array());

    $old = $saved->getParameter('projects', array());
    foreach ($old as $phid) {
      $project_phids[] = $phid;
    }

    $all = $saved->getParameter('allProjectPHIDs', array());
    foreach ($all as $phid) {
      $project_phids[] = $phid;
    }

    $any = $saved->getParameter('anyProjectPHIDs', array());
    foreach ($any as $phid) {
      $project_phids[] = 'any('.$phid.')';
    }

    $not = $saved->getParameter('excludeProjectPHIDs', array());
    foreach ($not as $phid) {
      $project_phids[] = 'not('.$phid.')';
    }

    $users = $saved->getParameter('userProjectPHIDs', array());
    foreach ($users as $phid) {
      $project_phids[] = 'projects('.$phid.')';
    }

    $no = $saved->getParameter('withNoProject');
    if ($no) {
      $project_phids[] = 'null()';
    }

    $saved->setParameter('projectPHIDs', $project_phids);
  }

  protected function getNewUserBody() {
    $viewer = $this->requireViewer();

    $create_button = id(new ManiphestEditEngine())
      ->setViewer($viewer)
      ->newNUXBUtton(pht('Tạo một phân công công việc'));

    $icon = $this->getApplication()->getIcon();
    $app_name =  $this->getApplication()->getName();
    $view = id(new PHUIBigInfoView())
      ->setIcon($icon)
      ->setTitle(pht('Chào mừng đến với %s', $app_name))
      ->setDescription(
        pht('Dùng Bugs tracker để quản lý bugs, tính năng, công việc cần làm hoặc những thứ khác'.
            'mà bạn cần hoàn thành. Công việc được chỉ định cho bạn sẽ được thể hiện ở đây.'))
      ->addAction($create_button);

    return $view;
  }

}
