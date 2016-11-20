<?php

final class PhameBlogSearchConduitAPIMethod
  extends PhabricatorSearchEngineAPIMethod {

  public function getAPIMethodName() {
    return 'phame.blog.search';
  }

  public function newSearchEngine() {
    return new PhameBlogSearchEngine();
  }

  public function getMethodSummary() {
    return pht('Đọc thông tin về blogs.');
  }

}
