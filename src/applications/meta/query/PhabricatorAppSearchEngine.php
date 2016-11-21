<?php

final class PhabricatorAppSearchEngine
  extends PhabricatorApplicationSearchEngine {

  public function getResultTypeDescription() {
    return pht('Ưng dụng');
  }

  public function getApplicationClassName() {
    return 'PhabricatorApplicationsApplication';
  }

  public function getPageSize(PhabricatorSavedQuery $saved) {
    return INF;
  }

  public function buildSavedQueryFromRequest(AphrontRequest $request) {
    $saved = new PhabricatorSavedQuery();

    $saved->setParameter('name', $request->getStr('name'));

    $saved->setParameter(
      'installed',
      $this->readBoolFromRequest($request, 'installed'));
    $saved->setParameter(
      'prototypes',
      $this->readBoolFromRequest($request, 'prototypes'));
    $saved->setParameter(
      'firstParty',
      $this->readBoolFromRequest($request, 'firstParty'));
    $saved->setParameter(
      'launchable',
      $this->readBoolFromRequest($request, 'launchable'));
    $saved->setParameter(
      'appemails',
      $this->readBoolFromRequest($request, 'appemails'));

    return $saved;
  }

  public function buildQueryFromSavedQuery(PhabricatorSavedQuery $saved) {
    $query = id(new PhabricatorApplicationQuery())
      ->setOrder(PhabricatorApplicationQuery::ORDER_NAME)
      ->withUnlisted(false);

    $name = $saved->getParameter('name');
    if (strlen($name)) {
      $query->withNameContains($name);
    }

    $installed = $saved->getParameter('installed');
    if ($installed !== null) {
      $query->withInstalled($installed);
    }

    $prototypes = $saved->getParameter('prototypes');

    if ($prototypes === null) {
      // NOTE: This is the old name of the 'prototypes' option, see T6084.
      $prototypes = $saved->getParameter('beta');
      $saved->setParameter('prototypes', $prototypes);
    }

    if ($prototypes !== null) {
      $query->withPrototypes($prototypes);
    }

    $first_party = $saved->getParameter('firstParty');
    if ($first_party !== null) {
      $query->withFirstParty($first_party);
    }

    $launchable = $saved->getParameter('launchable');
    if ($launchable !== null) {
      $query->withLaunchable($launchable);
    }

    $appemails = $saved->getParameter('appemails');
    if ($appemails !== null) {
      $query->withApplicationEmailSupport($appemails);
    }

    return $query;
  }

  public function buildSearchForm(
    AphrontFormView $form,
    PhabricatorSavedQuery $saved) {

    $form
      ->appendChild(
        id(new AphrontFormTextControl())
          ->setLabel(pht('Tên bao hàm'))
          ->setName('name')
          ->setValue($saved->getParameter('name')))
      ->appendChild(
        id(new AphrontFormSelectControl())
          ->setLabel(pht('Cài đặt'))
          ->setName('installed')
          ->setValue($this->getBoolFromQuery($saved, 'installed'))
          ->setOptions(
            array(
              '' => pht('Hiển thị tất cả các ứng dụng'),
              'true' => pht('Hiện đã cài đặt ứng dụng'),
              'false' => pht('Hiện gỡ bỏ cài đặt ứng dụng'),
            )))
      ->appendChild(
        id(new AphrontFormSelectControl())
          ->setLabel(pht('Prototypes'))
          ->setName('prototypes')
          ->setValue($this->getBoolFromQuery($saved, 'prototypes'))
          ->setOptions(
            array(
              '' => pht('Hiển thị tất cả các ứng dụng'),
              'true' => pht('Hiện ứng dụng Prototype'),
              'false' => pht('Hiện Phát hành ứng dụng'),
            )))
      ->appendChild(
        id(new AphrontFormSelectControl())
          ->setLabel(pht('Nguồn gốc'))
          ->setName('firstParty')
          ->setValue($this->getBoolFromQuery($saved, 'firstParty'))
          ->setOptions(
            array(
              '' => pht('Hiển thị tất cả các ứng dụng'),
              'true' => pht('Hiện ứng dụng lần đầu '),
              'false' => pht('Ứng dụng chương trình của bên thứ ba'),
            )))
      ->appendChild(
        id(new AphrontFormSelectControl())
          ->setLabel(pht('Launchable'))
          ->setName('launchable')
          ->setValue($this->getBoolFromQuery($saved, 'launchable'))
          ->setOptions(
            array(
              '' => pht('Hiển thị tất cả các ứng dụng'),
              'true' => pht('Hiện ứng dụng launchable'),
              'false' => pht('Hiện ứng dụng không phải launchable'),
            )))
      ->appendChild(
        id(new AphrontFormSelectControl())
          ->setLabel(pht('ứng dụng Emails'))
          ->setName('appemails')
          ->setValue($this->getBoolFromQuery($saved, 'appemails'))
          ->setOptions(
            array(
              '' => pht('Hiển thị tất cả các ứng dụng'),
              'true' => pht('Show Applications w/ App Email Support'),
              'false' => pht('Show Applications w/o App Email Support'),
            )));
  }

  protected function getURI($path) {
    return '/applications/'.$path;
  }

  protected function getBuiltinQueryNames() {
    return array(
      'launcher' => pht('Launcher'),
      'all' => pht('Tất cả các ứng dụng'),
    );
  }

  public function buildSavedQueryFromBuiltin($query_key) {
    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);

    switch ($query_key) {
      case 'launcher':
        return $query
          ->setParameter('installed', true)
          ->setParameter('launchable', true);
      case 'all':
        return $query;
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  protected function renderResultList(
    array $all_applications,
    PhabricatorSavedQuery $query,
    array $handle) {
    assert_instances_of($all_applications, 'PhabricatorApplication');

    $all_applications = msort($all_applications, 'getName');

    if ($query->getQueryKey() == 'launcher') {
      $groups = mgroup($all_applications, 'getApplicationGroup');
    } else {
      $groups = array($all_applications);
    }

    $group_names = PhabricatorApplication::getApplicationGroups();
    $groups = array_select_keys($groups, array_keys($group_names)) + $groups;

    $results = array();
    foreach ($groups as $group => $applications) {
      if (count($groups) > 1) {
        $results[] = phutil_tag(
          'h1',
          array(
            'class' => 'phui-object-item-list-header',
          ),
          idx($group_names, $group, $group));
      }

      $list = new PHUIObjectItemListView();

      foreach ($applications as $application) {
        $icon = $application->getIcon();
        if (!$icon) {
          $icon = 'application';
        }

        $description = $application->getShortDescription();

        $configure = id(new PHUIButtonView())
          ->setTag('a')
          ->setHref('/applications/view/'.get_class($application).'/')
          ->setText(pht('Cấu hình'))
          ->setColor(PHUIButtonView::GREY);

        $name = $application->getName();
        if ($application->isPrototype()) {
          $name = $name.' '.pht('(Prototype)');
        }

        $item = id(new PHUIObjectItemView())
          ->setHeader($name)
          ->setImageIcon($icon)
          ->setSubhead($description)
          ->setLaunchButton($configure);

        if ($application->getBaseURI() && $application->isInstalled()) {
          $item->setHref($application->getBaseURI());
        }

        if (!$application->isInstalled()) {
          $item->addAttribute(pht('Hủy cài đặt'));
          $item->setDisabled(true);
        }

        if (!$application->isFirstParty()) {
          $item->addAttribute(pht('Mở rộng'));
        }

        $list->addItem($item);
      }

      $results[] = $list;
    }

    $result = new PhabricatorApplicationSearchResultView();
    $result->setContent($results);

    return $result;
  }

}
