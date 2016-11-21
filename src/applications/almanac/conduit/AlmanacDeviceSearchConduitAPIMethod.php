<?php

final class AlmanacDeviceSearchConduitAPIMethod
  extends PhabricatorSearchEngineAPIMethod {

  public function getAPIMethodName() {
    return 'almanac.device.search';
  }

  public function newSearchEngine() {
    return new AlmanacDeviceSearchEngine();
  }

  public function getMethodSummary() {
    return pht('Đọc thông tin về những thiết bị Sách lịch.');
  }

}
