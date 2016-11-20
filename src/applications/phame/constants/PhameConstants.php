<?php

final class PhameConstants extends Phobject {

  const VISIBILITY_DRAFT = 0;
  const VISIBILITY_PUBLISHED = 1;
  const VISIBILITY_ARCHIVED = 2;

  public static function getPhamePostStatusMap() {
    return array(
      self::VISIBILITY_PUBLISHED  => pht('Được phát hành'),
      self::VISIBILITY_DRAFT => pht('Bản nháp'),
      self::VISIBILITY_ARCHIVED => pht('Lưu trữ'),
    );
  }

  public static function getPhamePostStatusName($status) {
    $map = array(
      self::VISIBILITY_PUBLISHED => pht('Được phát hành'),
      self::VISIBILITY_DRAFT => pht('Bản nháp'),
      self::VISIBILITY_ARCHIVED => pht('Lưu trữ'),
    );
    return idx($map, $status, pht('Không biết'));
  }

}
