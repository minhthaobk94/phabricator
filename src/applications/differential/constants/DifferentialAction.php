<?php

final class DifferentialAction extends Phobject {

  const ACTION_CLOSE          = 'commit';
  const ACTION_COMMENT        = 'none';
  const ACTION_ACCEPT         = 'accept';
  const ACTION_REJECT         = 'reject';
  const ACTION_RETHINK        = 'rethink';
  const ACTION_ABANDON        = 'abandon';
  const ACTION_REQUEST        = 'request_review';
  const ACTION_RECLAIM        = 'reclaim';
  const ACTION_UPDATE         = 'update';
  const ACTION_RESIGN         = 'resign';
  const ACTION_SUMMARIZE      = 'summarize';
  const ACTION_TESTPLAN       = 'testplan';
  const ACTION_CREATE         = 'create';
  const ACTION_ADDREVIEWERS   = 'add_reviewers';
  const ACTION_ADDCCS         = 'add_ccs';
  const ACTION_CLAIM          = 'claim';
  const ACTION_REOPEN         = 'reopen';

  public static function getBasicStoryText($action, $author_name) {
    switch ($action) {
      case self::ACTION_COMMENT:
        $title = pht(
          '%s được bình luận trên sự thay đổi này.',
          $author_name);
        break;
      case self::ACTION_ACCEPT:
        $title = pht(
          '%s được chấp nhận.',
          $author_name);
        break;
      case self::ACTION_REJECT:
        $title = pht(
          '%s được yêu cầu thay đổi .',
          $author_name);
        break;
      case self::ACTION_RETHINK:
        $title = pht(
          '%s thay đổi kế hoạch sửa đổi này.',
          $author_name);
        break;
      case self::ACTION_ABANDON:
        $title = pht(
          '%s bỏ phiên bản này.',
          $author_name);
        break;
      case self::ACTION_CLOSE:
        $title = pht(
          '%s đóng phiên bản này.',
          $author_name);
        break;
      case self::ACTION_REQUEST:
        $title = pht(
          '%s yêu cầu xem xét lại các sửa đổi này.',
          $author_name);
        break;
      case self::ACTION_RECLAIM:
        $title = pht(
          '%s thay đổi sửa đổi này.',
          $author_name);
        break;
      case self::ACTION_UPDATE:
        $title = pht(
          '%s cập nhật phiên bản này.',
          $author_name);
        break;
      case self::ACTION_RESIGN:
        $title = pht(
          '%s từ bỏ từ phiên bản này.',
          $author_name);
        break;
      case self::ACTION_SUMMARIZE:
        $title = pht(
          '%s tóm tắt sửa đổi này.',
          $author_name);
        break;
      case self::ACTION_TESTPLAN:
        $title = pht(
          '%s giải thích các kế hoạch thử nghiệm cho phiên bản này.',
          $author_name);
        break;
      case self::ACTION_CREATE:
        $title = pht(
          '%s tạo ra phiên bản này.',
          $author_name);
        break;
      case self::ACTION_ADDREVIEWERS:
        $title = pht(
          '%s nhận xét bổ sung vào phiên bản này.',
          $author_name);
        break;
      case self::ACTION_ADDCCS:
        $title = pht(
          '%s thêm CCs vào phiên bản này.',
          $author_name);
        break;
      case self::ACTION_CLAIM:
        $title = pht(
          '%s lệnh sửa đổi này',
          $author_name);
        break;
      case self::ACTION_REOPEN:
        $title = pht(
          '%s mở trở lại phiên bản này.',
          $author_name);
        break;
      case DifferentialTransaction::TYPE_INLINE:
        $title = pht(
          '%s thêm một bình luận.',
          $author_name);
        break;
      default:
        $title = pht('Ghosts đã xảy ra với phiên bản này.');
        break;
    }
    return $title;
  }

  public static function getActionVerb($action) {
    $verbs = array(
      self::ACTION_COMMENT        => pht('Bình luận'),
      self::ACTION_ACCEPT         => pht("Chấp nhận sự sửa đổi \xE2\x9C\x94"),
      self::ACTION_REJECT         => pht("Yêu cầu thay đổi \xE2\x9C\x98"),
      self::ACTION_RETHINK        => pht("Kế hoạch thay đổi \xE2\x9C\x98"),
      self::ACTION_ABANDON        => pht('Bỏ sự thay đổi '),
      self::ACTION_REQUEST        => pht('Yêu cầu xem xét'),
      self::ACTION_RECLAIM        => pht('Khai phá sự thay đổi'),
      self::ACTION_RESIGN         => pht('Từ bỏ sự xem xét'),
      self::ACTION_ADDREVIEWERS   => pht('Thêm xem xét'),
      self::ACTION_ADDCCS         => pht('Thêm đăng ký'),
      self::ACTION_CLOSE          => pht('Đóng sự thay đổi'),
      self::ACTION_CLAIM          => pht('Lệnh sự thay đổi'),
      self::ACTION_REOPEN         => pht('Mở lại'),
    );

    if (!empty($verbs[$action])) {
      return $verbs[$action];
    } else {
      return pht('brazenly %s', $action);
    }
  }

  public static function allowReviewers($action) {
    if ($action == self::ACTION_ADDREVIEWERS ||
        $action == self::ACTION_REQUEST ||
        $action == self::ACTION_RESIGN) {
      return true;
    }
    return false;
  }

}
