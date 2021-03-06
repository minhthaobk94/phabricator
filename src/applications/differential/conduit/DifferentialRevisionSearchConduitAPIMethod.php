<?php

final class DifferentialRevisionSearchConduitAPIMethod
  extends PhabricatorSearchEngineAPIMethod {

  public function getAPIMethodName() {
    return 'differential.revision.search';
  }

  public function newSearchEngine() {
    return new DifferentialRevisionSearchEngine();
  }

  public function getMethodSummary() {
    return pht('Đọc thông tin về sự sửa đổi.');
  }

}
