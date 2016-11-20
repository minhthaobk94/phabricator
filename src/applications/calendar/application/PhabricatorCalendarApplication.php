<?php

final class PhabricatorCalendarApplication extends PhabricatorApplication {

  public function getName() {
    return pht('Lịch');
  }

  public function getShortDescription() {
    return pht('sự kiện sắp tới');
  }

  public function getFlavorText() {
    return pht('Không bao giờ bỏ lỡ  trở lại.');
  }

  public function getBaseURI() {
    return '/calendar/';
  }

  public function getIcon() {
    return 'fa-calendar';
  }

  public function getTitleGlyph() {
    // Unicode has a calendar character but it's in some distant code plane,
    // use "keyboard" since it looks vaguely similar.
    return "\xE2\x8C\xA8";
  }

  public function getApplicationGroup() {
    return self::GROUP_UTILITIES;
  }

  public function isPrototype() {
    return true;
  }

  public function getRemarkupRules() {
    return array(
      new PhabricatorCalendarRemarkupRule(),
    );
  }

  public function getRoutes() {
    return array(
      '/E(?P<id>[1-9]\d*)(?:/(?P<sequence>\d+)/)?'
        => 'PhabricatorCalendarEventViewController',
      '/calendar/' => array(
        '(?:query/(?P<queryKey>[^/]+)/(?:(?P<year>\d+)/'.
          '(?P<month>\d+)/)?(?:(?P<day>\d+)/)?)?'
          => 'PhabricatorCalendarEventListController',
        'event/' => array(
          $this->getEditRoutePattern('edit/')
            => 'PhabricatorCalendarEventEditController',
          'drag/(?P<id>[1-9]\d*)/'
            => 'PhabricatorCalendarEventDragController',
          'cancel/(?P<id>[1-9]\d*)/'
            => 'PhabricatorCalendarEventCancelController',
          '(?P<action>join|decline|accept)/(?P<id>[1-9]\d*)/'
            => 'PhabricatorCalendarEventJoinController',
          'export/(?P<id>[1-9]\d*)/(?P<filename>[^/]*)'
            => 'PhabricatorCalendarEventExportController',
          'availability/(?P<id>[1-9]\d*)/(?P<availability>[^/]+)/'
            => 'PhabricatorCalendarEventAvailabilityController',
        ),
        'export/' => array(
          $this->getQueryRoutePattern()
            => 'PhabricatorCalendarExportListController',
          $this->getEditRoutePattern('edit/')
            => 'PhabricatorCalendarExportEditController',
          '(?P<id>[1-9]\d*)/'
            => 'PhabricatorCalendarExportViewController',
          'ics/(?P<secretKey>[^/]+)/(?P<filename>[^/]*)'
            => 'PhabricatorCalendarExportICSController',
          'disable/(?P<id>[1-9]\d*)/'
            => 'PhabricatorCalendarExportDisableController',
        ),
        'import/' => array(
          $this->getQueryRoutePattern()
            => 'PhabricatorCalendarImportListController',
          $this->getEditRoutePattern('edit/')
            => 'PhabricatorCalendarImportEditController',
          '(?P<id>[1-9]\d*)/'
            => 'PhabricatorCalendarImportViewController',
          'disable/(?P<id>[1-9]\d*)/'
            => 'PhabricatorCalendarImportDisableController',
          'delete/(?P<id>[1-9]\d*)/'
            => 'PhabricatorCalendarImportDeleteController',
          'reload/(?P<id>[1-9]\d*)/'
            => 'PhabricatorCalendarImportReloadController',
          'drop/'
            => 'PhabricatorCalendarImportDropController',
          'log/' => array(
            $this->getQueryRoutePattern()
              => 'PhabricatorCalendarImportLogListController',
          ),
        ),
      ),
    );
  }

  public function getHelpDocumentationArticles(PhabricatorUser $viewer) {
    return array(
      array(
        'name' => pht('Hướng dẫn sử dụng'),
        'href' => PhabricatorEnv::getDoclink('Calendar User Guide'),
      ),
      array(
        'name' => pht('Nhập Sự kiện'),
        'href' => PhabricatorEnv::getDoclink(
          'Calendar User Guide: Importing Events'),
      ),
      array(
        'name' => pht('Xuất Sự kiện'),
        'href' => PhabricatorEnv::getDoclink(
          'Calendar User Guide: Exporting Events'),
      ),
    );
  }

  public function getMailCommandObjects() {
    return array(
      'event' => array(
        'name' => pht('Lệnh Email: Sự kiện'),
        'header' => pht('Tương tác với Lịch sự kiện'),
        'object' => new PhabricatorCalendarEvent(),
        'summary' => pht(
          'This page documents the commands you can use to interact with '.
          'events in Calendar. These commands work when creating new tasks '.
          'via email and when replying to existing tasks.'),
      ),
    );
  }

  protected function getCustomCapabilities() {
    return array(
      PhabricatorCalendarEventDefaultViewCapability::CAPABILITY => array(
        'caption' => pht('Chính sách mặc định cho các sự kiện mới được tạo.'),
        'template' => PhabricatorCalendarEventPHIDType::TYPECONST,
        'capability' => PhabricatorPolicyCapability::CAN_VIEW,
      ),
      PhabricatorCalendarEventDefaultEditCapability::CAPABILITY => array(
        'caption' => pht('Chỉnh sửa chính sách mặc định cho các sự kiện mới được tạo.'),
        'template' => PhabricatorCalendarEventPHIDType::TYPECONST,
        'capability' => PhabricatorPolicyCapability::CAN_EDIT,
      ),
    );
  }

}
