<?php

/**
 * This field doesn't do anything, it just parses the "git-svn-id" field which
 * `git svn` inserts into commit messages so that we don't end up mangling
 * some other field.
 */
final class DifferentialGitSVNIDField
  extends DifferentialCustomField {

  public function getFieldKey() {
    return 'differential:git-svn-id';
  }

  public function getFieldKeyForConduit() {
    return 'gitSVNID';
  }

  public function getFieldName() {
    return pht('git-svn-id');
  }

  public function getFieldDescription() {
    return pht(
      'Chuyển đổi  "%s" trường m Git/SVN có thể tiêm vào bài viết cam kết.',
      'git-svn-id');
  }

  public function canDisableField() {
    return false;
  }

  public function shouldAppearInCommitMessage() {
    return true;
  }

  public function shouldAllowEditInCommitMessage() {
    return false;
  }

  public function renderCommitMessageValue(array $handles) {
    return null;
  }

}
