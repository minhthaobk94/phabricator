<?php

final class ExampleApplication extends PhabricatorApplication {

  public function getName() {
    return pht('Example');
  }

  public function getIcon() {
    return 'fa-cog';
  }

  public function getBaseURI() {
    return '/example/';
  }
  
  public function getOverview() {
    return pht(
      'Differential is a **code review application** which allows '.
      'engineers to review, discuss and approve changes to software.');
  }

  public function getRoutes() {
    /*return array(
      '/example/' => array(
        '' => 'ManiphestTaskListController',
        'query/authored/' => 'ManiphestTaskListController',
      ),
    );*/
    return array(
      '/T(?P<id>[1-9]\d*)' => 'ManiphestTaskDetailController',
      '/example/' => array(
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
