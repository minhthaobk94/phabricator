<?php

final class BugsApplication extends PhabricatorApplication {

  public function getName() {
<<<<<<< HEAD
    return pht('Theo dõi lỗi');
=======
    return pht('Quản lý lỗi');
>>>>>>> origin/master
  }

  public function getIcon() {
    return 'fa-bug';
  }

  public function getBaseURI() {
    return '/bugstracker/';
  }
  
  public function getOverview() {
    return pht(
      	'Hỗ trợ quản lý issues. '.
	'Tạo bug report để theo dõi.');
  }

  public function getRoutes() {
    return array(
      '/T(?P<id>[1-9]\d*)' => 'ManiphestTaskDetailController',
      '/bugstracker/' => array(
        '(?:query/(?P<queryKey>[^/]+)/)?' => 'ManiphestTaskListController',
        'report/(?:(?P<view>\w+)/)?' => 'ManiphestReportController',
        'batch/' => 'ManiphestBatchEditController',
        'task/' => array(
          $this->getEditRoutePattern('edit/')
            => 'ManiphestTaskEditController',
        ),
        'export/(?P<key>[^/]+)/' => 'ManiphestExportController',
        'subpriority/' => 'ManiphestSubpriorityController',
      ),
    );

  }
}
