<?php

final class PhamePostSearchConduitAPIMethod
  extends PhabricatorSearchEngineAPIMethod {

  public function getAPIMethodName() {
    return 'phame.post.search';
  }

  public function newSearchEngine() {
    return new PhamePostSearchEngine();
  }

  public function getMethodSummary() {
    return pht('Đọc thông tin về các bài viết trên blogs.');
  }

}
