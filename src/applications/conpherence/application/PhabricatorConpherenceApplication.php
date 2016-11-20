<?php

final class PhabricatorConpherenceApplication extends PhabricatorApplication {

  public function getBaseURI() {
    return '/conpherence/';
  }

  public function getName() {
    return pht('Trao đổi');
  }

  public function getShortDescription() {
    return pht('Nói chuyện với người khác');
  }

  public function getIcon() {
    return 'fa-comments';
  }

  public function getTitleGlyph() {
    return "\xE2\x9C\x86";
  }

  public function getRemarkupRules() {
    return array(
      new ConpherenceThreadRemarkupRule(),
    );
  }

  public function getRoutes() {
    return array(
      '/Z(?P<id>[1-9]\d*)'
        => 'ConpherenceViewController',
      '/conpherence/' => array(
        ''
          => 'ConpherenceListController',
        'thread/(?P<id>[1-9]\d*)/'
          => 'ConpherenceListController',
        'threadsearch/(?P<id>[1-9]\d*)/'
          => 'ConpherenceThreadSearchController',
        '(?P<id>[1-9]\d*)/'
          => 'ConpherenceViewController',
        '(?P<id>[1-9]\d*)/(?P<messageID>[1-9]\d*)/'
          => 'ConpherenceViewController',
        'columnview/'
          => 'ConpherenceColumnViewController',
        'new/'
          => 'ConpherenceNewRoomController',
        'picture/(?P<id>[1-9]\d*)/'
          => 'ConpherenceRoomPictureController',
        'search/(?:query/(?P<queryKey>[^/]+)/)?'
          => 'ConpherenceRoomListController',
        'panel/'
          => 'ConpherenceNotificationPanelController',
        'participant/(?P<id>[1-9]\d*)/'
          => 'ConpherenceParticipantController',
        'update/(?P<id>[1-9]\d*)/'
          => 'ConpherenceUpdateController',
      ),
    );
  }

  public function getQuicksandURIPatternBlacklist() {
    return array(
      '/conpherence/.*',
      '/Z\d+',
    );
  }

  public function getMailCommandObjects() {

    // TODO: Conpherence threads don't currently support any commands directly,
    // so the documentation page we end up generating is empty and funny
    // looking. Add support here once we support "!add", "!leave", "!topic",
    // or whatever else.

    return array();
  }

}