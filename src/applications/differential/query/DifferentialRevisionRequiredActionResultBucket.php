<?php

final class DifferentialRevisionRequiredActionResultBucket
  extends DifferentialRevisionResultBucket {

  const BUCKETKEY = 'action';

  const KEY_MUSTREVIEW = 'must-review';
  const KEY_SHOULDREVIEW = 'should-review';

  private $objects;

  public function getResultBucketName() {
    return pht('Bucket by Required Action');
  }

  protected function buildResultGroups(
    PhabricatorSavedQuery $query,
    array $objects) {

    $this->objects = $objects;

    $phids = $query->getEvaluatedParameter('responsiblePHIDs', array());
    if (!$phids) {
      throw new Exception(
        pht(
          'Bạn không có bộ chứa kết quả của hành động cần thiết mà không cần '.
          'xác định "Người sử dụng có trách nhiệm".'));
    }
    $phids = array_fuse($phids);

    $groups = array();

    $groups[] = $this->newGroup()
      ->setName(pht('Phải xem xét'))
      ->setKey(self::KEY_MUSTREVIEW)
      ->setNoDataString(pht('Không có phiên bản được chặn trên đánh giá của bạn.'))
      ->setObjects($this->filterMustReview($phids));

    $groups[] = $this->newGroup()
      ->setName(pht('Sẵn sàng để đánh gía'))
      ->setKey(self::KEY_SHOULDREVIEW)
      ->setNoDataString(pht('Không có phiên bản đang đợi đánh giá của bạn.'))
      ->setObjects($this->filterShouldReview($phids));

    $groups[] = $this->newGroup()
      ->setName(pht('Sẵn sàng để Landd'))
      ->setNoDataString(pht('ko có phiên bản sẵn sàng để land.'))
      ->setObjects($this->filterShouldLand($phids));

    $groups[] = $this->newGroup()
      ->setName(pht('Sẵn sàng để chỉnh sửa'))
      ->setNoDataString(pht('Không có phiên bản để chỉnh sửa.'))
      ->setObjects($this->filterShouldUpdate($phids));

    $groups[] = $this->newGroup()
      ->setName(pht('Đang đợi để đánh giá'))
      ->setNoDataString(pht('không có gì của bạn đang đợi để dánh gía.'))
      ->setObjects($this->filterWaitingForReview($phids));

    $groups[] = $this->newGroup()
      ->setName(pht('Đang đợi tác giả'))
      ->setNoDataString(pht('Ko có phiên bản nào đang đợi tác giả kích hoạt.'))
      ->setObjects($this->filterWaitingOnAuthors($phids));

    // Because you can apply these buckets to queries which include revisions
    // that have been closed, add an "Other" bucket if we still have stuff
    // that didn't get filtered into any of the previous buckets.
    if ($this->objects) {
      $groups[] = $this->newGroup()
        ->setName(pht('Phiên bản khác'))
        ->setObjects($this->objects);
    }

    return $groups;
  }

  private function filterMustReview(array $phids) {
    $blocking = array(
      DifferentialReviewerStatus::STATUS_BLOCKING,
      DifferentialReviewerStatus::STATUS_REJECTED,
      DifferentialReviewerStatus::STATUS_REJECTED_OLDER,
    );
    $blocking = array_fuse($blocking);

    $objects = $this->getRevisionsUnderReview($this->objects, $phids);

    $results = array();
    foreach ($objects as $key => $object) {
      if (!$this->hasReviewersWithStatus($object, $phids, $blocking)) {
        continue;
      }

      $results[$key] = $object;
      unset($this->objects[$key]);
    }

    return $results;
  }

  private function filterShouldReview(array $phids) {
    $reviewing = array(
      DifferentialReviewerStatus::STATUS_ADDED,
      DifferentialReviewerStatus::STATUS_COMMENTED,
    );
    $reviewing = array_fuse($reviewing);

    $objects = $this->getRevisionsUnderReview($this->objects, $phids);

    $results = array();
    foreach ($objects as $key => $object) {
      if (!$this->hasReviewersWithStatus($object, $phids, $reviewing)) {
        continue;
      }

      $results[$key] = $object;
      unset($this->objects[$key]);
    }

    return $results;
  }

  private function filterShouldLand(array $phids) {
    $status_accepted = ArcanistDifferentialRevisionStatus::ACCEPTED;

    $objects = $this->getRevisionsAuthored($this->objects, $phids);

    $results = array();
    foreach ($objects as $key => $object) {
      if ($object->getStatus() != $status_accepted) {
        continue;
      }

      $results[$key] = $object;
      unset($this->objects[$key]);
    }

    return $results;
  }

  private function filterShouldUpdate(array $phids) {
    $statuses = array(
      ArcanistDifferentialRevisionStatus::NEEDS_REVISION,
      ArcanistDifferentialRevisionStatus::CHANGES_PLANNED,
      ArcanistDifferentialRevisionStatus::IN_PREPARATION,
    );
    $statuses = array_fuse($statuses);

    $objects = $this->getRevisionsAuthored($this->objects, $phids);

    $results = array();
    foreach ($objects as $key => $object) {
      if (empty($statuses[$object->getStatus()])) {
        continue;
      }

      $results[$key] = $object;
      unset($this->objects[$key]);
    }

    return $results;
  }

  private function filterWaitingForReview(array $phids) {
    $status_review = ArcanistDifferentialRevisionStatus::NEEDS_REVIEW;

    $objects = $this->getRevisionsAuthored($this->objects, $phids);

    $results = array();
    foreach ($objects as $key => $object) {
      if ($object->getStatus() != $status_review) {
        continue;
      }

      $results[$key] = $object;
      unset($this->objects[$key]);
    }

    return $results;
  }

  private function filterWaitingOnAuthors(array $phids) {
    $statuses = array(
      ArcanistDifferentialRevisionStatus::ACCEPTED,
      ArcanistDifferentialRevisionStatus::NEEDS_REVISION,
      ArcanistDifferentialRevisionStatus::CHANGES_PLANNED,
      ArcanistDifferentialRevisionStatus::IN_PREPARATION,
    );
    $statuses = array_fuse($statuses);

    $objects = $this->getRevisionsNotAuthored($this->objects, $phids);

    $results = array();
    foreach ($objects as $key => $object) {
      if (empty($statuses[$object->getStatus()])) {
        continue;
      }

      $results[$key] = $object;
      unset($this->objects[$key]);
    }

    return $results;
  }

}
