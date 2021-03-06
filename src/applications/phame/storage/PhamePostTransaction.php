<?php

final class PhamePostTransaction
  extends PhabricatorApplicationTransaction {

  const TYPE_TITLE            = 'phame.post.title';
  const TYPE_SUBTITLE         = 'phame.post.subtitle';
  const TYPE_BODY             = 'phame.post.body';
  const TYPE_VISIBILITY       = 'phame.post.visibility';
  const TYPE_HEADERIMAGE      = 'phame.post.headerimage';
  const TYPE_BLOG             = 'phame.post.blog';

  const MAILTAG_CONTENT       = 'phame-post-content';
  const MAILTAG_SUBSCRIBERS   = 'phame-post-subscribers';
  const MAILTAG_COMMENT       = 'phame-post-comment';
  const MAILTAG_OTHER         = 'phame-post-other';

  public function getApplicationName() {
    return 'phame';
  }

  public function getApplicationTransactionType() {
    return PhabricatorPhamePostPHIDType::TYPECONST;
  }

  public function getApplicationTransactionCommentObject() {
    return new PhamePostTransactionComment();
  }

  public function getRemarkupBlocks() {
    $blocks = parent::getRemarkupBlocks();

    switch ($this->getTransactionType()) {
      case self::TYPE_BODY:
        $blocks[] = $this->getNewValue();
        break;
    }

    return $blocks;
  }

  public function shouldHide() {
    return parent::shouldHide();
  }

  public function getRequiredHandlePHIDs() {
    $phids = parent::getRequiredHandlePHIDs();

    switch ($this->getTransactionType()) {
      case self::TYPE_BLOG:
        $old = $this->getOldValue();
        $new = $this->getNewValue();

        if ($old) {
          $phids[] = $old;
        }

        if ($new) {
          $phids[] = $new;
        }
        break;
    }

    return $phids;
  }


  public function getIcon() {
    $old = $this->getOldValue();
    $new = $this->getNewValue();
    switch ($this->getTransactionType()) {
      case PhabricatorTransactions::TYPE_CREATE:
        return 'fa-plus';
      break;
      case self::TYPE_HEADERIMAGE:
        return 'fa-camera-retro';
      break;
      case self::TYPE_VISIBILITY:
        if ($new == PhameConstants::VISIBILITY_PUBLISHED) {
          return 'fa-globe';
        } else if ($new == PhameConstants::VISIBILITY_ARCHIVED) {
          return 'fa-ban';
        } else {
          return 'fa-eye-slash';
        }
      break;
    }
    return parent::getIcon();
  }

  public function getMailTags() {
    $tags = parent::getMailTags();

    switch ($this->getTransactionType()) {
      case PhabricatorTransactions::TYPE_COMMENT:
        $tags[] = self::MAILTAG_COMMENT;
        break;
      case PhabricatorTransactions::TYPE_SUBSCRIBERS:
        $tags[] = self::MAILTAG_SUBSCRIBERS;
        break;
      case self::TYPE_TITLE:
      case self::TYPE_SUBTITLE:
      case self::TYPE_BODY:
        $tags[] = self::MAILTAG_CONTENT;
        break;
      default:
        $tags[] = self::MAILTAG_OTHER;
        break;
    }
    return $tags;
  }


  public function getTitle() {
    $author_phid = $this->getAuthorPHID();
    $object_phid = $this->getObjectPHID();

    $old = $this->getOldValue();
    $new = $this->getNewValue();

    $type = $this->getTransactionType();
    switch ($type) {
      case PhabricatorTransactions::TYPE_CREATE:
        return pht(
          '%s tác giả bài đăng.',
          $this->renderHandleLink($author_phid));
      case self::TYPE_BLOG:
        return pht(
          '%s di chuyển bài đăng từ "%s" đến "%s".',
          $this->renderHandleLink($author_phid),
          $this->renderHandleLink($old),
          $this->renderHandleLink($new));
      case self::TYPE_TITLE:
        if ($old === null) {
          return pht(
            '%s Tác giả của bài đăng.',
            $this->renderHandleLink($author_phid));
        } else {
          return pht(
            '%s cập nhật tên bài đăng để "%s".',
            $this->renderHandleLink($author_phid),
            $new);
        }
        break;
      case self::TYPE_SUBTITLE:
        if ($old === null) {
          return pht(
            '%s thiết lập tiêu đề con bài đăng để "%s".',
            $this->renderHandleLink($author_phid),
            $new);
        } else {
          return pht(
            '%s capaj nhật tiêu đề con của bài đăng để "%s".',
            $this->renderHandleLink($author_phid),
            $new);
        }
        break;
      case self::TYPE_BODY:
        return pht(
          '%s cập nhật bài đăng của blog.',
          $this->renderHandleLink($author_phid));
        break;
      case self::TYPE_HEADERIMAGE:
        return pht(
          '%s cập nhật ảnh của tiêu đề.',
          $this->renderHandleLink($author_phid));
        break;
      case self::TYPE_VISIBILITY:
        if ($new == PhameConstants::VISIBILITY_DRAFT) {
          return pht(
            '%s	đánh dấu bài này như một dự thảo.',
            $this->renderHandleLink($author_phid));
        } else if ($new == PhameConstants::VISIBILITY_ARCHIVED) {
          return pht(
            '%s lưu bài đăng này.',
            $this->renderHandleLink($author_phid));
        } else {
          return pht(
          '%s Công khai bài đăng này.',
          $this->renderHandleLink($author_phid));
        }
        break;
    }

    return parent::getTitle();
  }

  public function getTitleForFeed() {
    $author_phid = $this->getAuthorPHID();
    $object_phid = $this->getObjectPHID();

    $old = $this->getOldValue();
    $new = $this->getNewValue();

    $type = $this->getTransactionType();
    switch ($type) {
      case PhabricatorTransactions::TYPE_CREATE:
        return pht(
          '%s tác giả %s.',
          $this->renderHandleLink($author_phid),
          $this->renderHandleLink($object_phid));
      case self::TYPE_BLOG:
        return pht(
          '%s di chuyển bài đăng "%s" từ "%s" đến "%s".',
          $this->renderHandleLink($author_phid),
          $this->renderHandleLink($object_phid),
          $this->renderHandleLink($old),
          $this->renderHandleLink($new));
      case self::TYPE_TITLE:
        if ($old === null) {
          return pht(
            '%s tác giả %s.',
            $this->renderHandleLink($author_phid),
            $this->renderHandleLink($object_phid));
        } else {
          return pht(
            '%s cập nhật tên từ  %s.',
            $this->renderHandleLink($author_phid),
            $this->renderHandleLink($object_phid));
        }
        break;
      case self::TYPE_SUBTITLE:
        return pht(
            '%s cập nhật tiêu đề con từ  %s.',
          $this->renderHandleLink($author_phid),
          $this->renderHandleLink($object_phid));
        break;
      case self::TYPE_BODY:
        return pht(
          '%s cập nhật bài đăng từ  %s.',
          $this->renderHandleLink($author_phid),
          $this->renderHandleLink($object_phid));
        break;
      case self::TYPE_HEADERIMAGE:
        return pht(
          '%s capaj nhật tiêu để hình ảnh cho bài đăng %s.',
          $this->renderHandleLink($author_phid),
          $this->renderHandleLink($object_phid));
        break;
      case self::TYPE_VISIBILITY:
        if ($new == PhameConstants::VISIBILITY_DRAFT) {
          return pht(
            '%s được đánh dấu  %s như phác thảo.',
            $this->renderHandleLink($author_phid),
            $this->renderHandleLink($object_phid));
        } else if ($new == PhameConstants::VISIBILITY_ARCHIVED) {
          return pht(
            '%s được đánh dấu %s như lưu trữ.',
            $this->renderHandleLink($author_phid),
            $this->renderHandleLink($object_phid));
        } else {
          return pht(
            '%s công khai %s.',
            $this->renderHandleLink($author_phid),
            $this->renderHandleLink($object_phid));
        }
        break;
    }

    return parent::getTitleForFeed();
  }

  public function getRemarkupBodyForFeed(PhabricatorFeedStory $story) {
    $old = $this->getOldValue();

    switch ($this->getTransactionType()) {
      case self::TYPE_BODY:
        if ($old === null) {
          return $this->getNewValue();
        }
      break;
    }

    return null;
  }

  public function getColor() {
    switch ($this->getTransactionType()) {
      case PhabricatorTransactions::TYPE_CREATE:
        return PhabricatorTransactions::COLOR_GREEN;
    }
    return parent::getColor();
  }

  public function hasChangeDetails() {
    switch ($this->getTransactionType()) {
      case self::TYPE_BODY:
        return ($this->getOldValue() !== null);
    }

    return parent::hasChangeDetails();
  }

  public function renderChangeDetails(PhabricatorUser $viewer) {
    switch ($this->getTransactionType()) {
      case self::TYPE_BODY:
        $old = $this->getOldValue();
        $new = $this->getNewValue();

        return $this->renderTextCorpusChangeDetails(
          $viewer,
          $old,
          $new);
    }

    return parent::renderChangeDetails($viewer);
  }

}
