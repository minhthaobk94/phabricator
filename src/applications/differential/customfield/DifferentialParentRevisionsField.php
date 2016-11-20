<?php

final class DifferentialParentRevisionsField
  extends DifferentialCustomField {

  public function getFieldKey() {
    return 'differential:depends-on';
  }

  public function getFieldName() {
    return pht('Bản sửa đổi chính');
  }

  public function canDisableField() {
    return false;
  }

  public function getFieldDescription() {
    return pht('Danh sách các sửa đổi này phụ thuộc vào.');
  }

  public function getProTips() {
    return array(
      pht(
        'Tạo một sự phụ thuộc giữa các bản bằng cách viết '.
        '"%s" trong bản tóm tắt của bạn.',
        'Phụ thuộc vào D123'),
    );
  }

  public function shouldAppearInConduitDictionary() {
    // To improve performance, we exclude this field from Conduit results.
    // See T11404 for discussion. In modern "differential.revision.search",
    // this information is available efficiently as an attachment.
    return false;
  }

  public function getConduitDictionaryValue() {
    return PhabricatorEdgeQuery::loadDestinationPHIDs(
      $this->getObject()->getPHID(),
      DifferentialRevisionDependsOnRevisionEdgeType::EDGECONST);
  }

}
