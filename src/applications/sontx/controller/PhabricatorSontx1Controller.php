<?php

final class PhabricatorHomeMainController extends PhabricatorSontxController {

  public function shouldAllowPublic() {
    return true;
  }

  public function isGlobalDragAndDropUploadEnabled() {
    return true;
  }

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();

    $dashboard = PhabricatorDashboardInstall::getDashboard(
      $viewer,
      $viewer->getPHID(),
      get_class($this->getCurrentApplication()));

    if (!$dashboard) {
      $dashboard = PhabricatorDashboardInstall::getDashboard(
        $viewer,
        PhabricatorHomeApplication::DASHBOARD_DEFAULT,
        get_class($this->getCurrentApplication()));
    }

    if ($dashboard) {
      $content = id(new PhabricatorDashboardRenderingEngine())
        ->setViewer($viewer)
        ->setDashboard($dashboard)
        ->renderDashboard();
    } else {
      $content = $this->buildMainResponse();
    }

    if (!$request->getURIData('only')) {
      $nav = $this->buildNav();
      $nav->appendChild(
        array(
          $content,
          id(new PhabricatorGlobalUploadTargetView())->setUser($viewer),
        ));
      $content = $nav;
    }

    return $this->newPage()
      ->setTitle('Phabricator')
      ->addClass('phabricator-home')
      ->appendChild($content);

  }

  private function buildMainResponse() {
    require_celerity_resource('phabricator-dashboard-css');
    $viewer = $this->getViewer();

    $has_maniphest = PhabricatorApplication::isClassInstalledForViewer(
      'PhabricatorManiphestApplication',
      $viewer);

    $has_diffusion = PhabricatorApplication::isClassInstalledForViewer(
      'PhabricatorDiffusionApplication',
      $viewer);

    $has_differential = PhabricatorApplication::isClassInstalledForViewer(
      'PhabricatorDifferentialApplication',
      $viewer);

    $revision_panel = null;
    if ($has_differential) {
      $revision_panel = $this->buildRevisionPanel();
    }

    $tasks_panel = null;
    if ($has_maniphest) {
      $tasks_panel = $this->buildTasksPanel();
    }

    $repository_panel = null;
    if ($has_diffusion) {
      $repository_panel = $this->buildRepositoryPanel();
    }

    $feed_panel = $this->buildFeedPanel();

    $dashboard = id(new AphrontMultiColumnView())
      ->setFluidlayout(true)
      ->setGutter(AphrontMultiColumnView::GUTTER_LARGE);

    $main_panel = phutil_tag(
      'div',
      array(
        'class' => 'homepage-panel',
      ),
      array(
        $revision_panel,
        $tasks_panel,
        $repository_panel,
      ));
    $dashboard->addColumn($main_panel, 'thirds');

    $side_panel = phutil_tag(
      'div',
      array(
        'class' => 'homepage-side-panel',
      ),
      array(
        $feed_panel,
      ));
    $dashboard->addColumn($side_panel, 'third');

      $view = id(new PHUIBoxView())
        ->addClass('dashboard-view')
        ->appendChild($dashboard);

      return $view;
  }

  private function buildHomepagePanel($title, $href, $view) {
    $title = phutil_tag(
      'a',
      array(
        'href' => $href,
      ),
      $title);

    $icon = id(new PHUIIconView())
      ->setIcon('fa-search')
      ->setHref($href);

    $header = id(new PHUIHeaderView())
      ->setHeader($title)
      ->addActionItem($icon);

    $box = id(new PHUIObjectBoxView())
      ->setHeader($header);

    if ($view->getObjectList()) {
      $box->setObjectList($view->getObjectList());
    }
    if ($view->getContent()) {
      $box->appendChild($view->getContent());
    }

    return $box;
  }

  private function buildRevisionPanel() {
    $viewer = $this->getViewer();
    if (!$viewer->isLoggedIn()) {
      return null;
    }

    $engine = new DifferentialRevisionSearchEngine();
    $engine->setViewer($viewer);
    $saved = $engine->buildSavedQueryFromBuiltin('active');
    $query = $engine->buildQueryFromSavedQuery($saved);
    $pager = $engine->newPagerForSavedQuery($saved);
    $pager->setPageSize(15);
    $results = $engine->executeQuery($query, $pager);
    $view = $engine->renderResults($results, $saved);

    $title = pht('Active Revisions');
    $href = '/differential/query/active/';

    return $this->buildHomepagePanel($title, $href, $view);
  }

  private function buildTasksPanel() {
    $viewer = $this->getViewer();

    $query = 'assigned';
    $title = pht('Assigned Tasks');
    $href = '/maniphest/query/assigned/';
    if (!$viewer->isLoggedIn()) {
      $query = 'open';
      $title = pht('Open Tasks');
      $href = '/maniphest/query/open/';
    }

    $engine = new ManiphestTaskSearchEngine();
    $engine->setViewer($viewer);
    $saved = $engine->buildSavedQueryFromBuiltin($query);
    $query = $engine->buildQueryFromSavedQuery($saved);
    $pager = $engine->newPagerForSavedQuery($saved);
    $pager->setPageSize(15);
    $results = $engine->executeQuery($query, $pager);
    $view = $engine->renderResults($results, $saved);

    return $this->buildHomepagePanel($title, $href, $view);
  }

  public function buildFeedPanel() {
    $viewer = $this->getViewer();

    $engine = new PhabricatorFeedSearchEngine();
    $engine->setViewer($viewer);
    $saved = $engine->buildSavedQueryFromBuiltin('all');
    $query = $engine->buildQueryFromSavedQuery($saved);
    $pager = $engine->newPagerForSavedQuery($saved);
    $pager->setPageSize(40);
    $results = $engine->executeQuery($query, $pager);
    $view = $engine->renderResults($results, $saved);
    // Low tech NUX.
    if (!$results && ($viewer->getIsAdmin() == 1)) {
      $instance = PhabricatorEnv::getEnvConfig('cluster.instance');
      if (!$instance) {
        $content = pht(<<<EOT
Welcome to Phabricator, here are some links to get you started:
- [[ /config/ | Configure Phabricator ]]
- [[ /guides/ | Quick Start Guide ]]
- [[ /diffusion/ | Create a Repository ]]
- [[ /people/invite/send/ | Invite People ]]
- [[ https://twitter.com/phabricator/ | Follow us on Twitter ]]
EOT
);
      } else {
        $content = pht(<<<EOT
Welcome to Phabricator, here are some links to get you started:
- [[ /guides/ | Quick Start Guide ]]
- [[ /diffusion/ | Create a Repository ]]
- [[ https://twitter.com/phabricator/ | Follow us on Twitter ]]
EOT
);
      }
      $welcome = new PHUIRemarkupView($viewer, $content);

      $list = new PHUIObjectItemListView();
      $view = new PhabricatorApplicationSearchResultView();
      $view->setObjectList($list);
      $view->setNoDataString($welcome);
    }

    $title = pht('Recent Activity');
    $href = '/feed/';

    return $this->buildHomepagePanel($title, $href, $view);
  }

  public function buildRepositoryPanel() {
    $viewer = $this->getViewer();

    $engine = new PhabricatorRepositorySearchEngine();
    $engine->setViewer($viewer);
    $saved = $engine->buildSavedQueryFromBuiltin('active');
    $query = $engine->buildQueryFromSavedQuery($saved);
    $pager = $engine->newPagerForSavedQuery($saved);
    $pager->setPageSize(5);
    $results = $engine->executeQuery($query, $pager);
    $view = $engine->renderResults($results, $saved);

    $title = pht('Active Repositories');
    $href = '/diffusion/';

    return $this->buildHomepagePanel($title, $href, $view);
  }

}
