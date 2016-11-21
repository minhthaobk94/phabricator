<?php

final class AlmanacServiceSearchConduitAPIMethod
  extends PhabricatorSearchEngineAPIMethod {

  public function getAPIMethodName() {
    return 'almanac.service.search';
  }

  public function newSearchEngine() {
    return new AlmanacServiceSearchEngine();
  }

  public function getMethodSummary() {
    return pht('Đọc thông tin về những thiết bị Sách lịch.');
  }

}
