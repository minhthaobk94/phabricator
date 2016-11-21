<?php

final class AlmanacPropertiesSearchEngineAttachment
  extends AlmanacSearchEngineAttachment {

  public function getAttachmentName() {
    return pht('Thuộc tính');
  }

  public function getAttachmentDescription() {
    return pht('Lấy thuộc tính từng đối tượng.');
  }

  public function willLoadAttachmentData($query, $spec) {
    $query->needProperties(true);
  }

  public function getAttachmentForObject($object, $data, $spec) {
    $properties = $this->getAlmanacPropertyList($object);

    return array(
      'properties' => $properties,
    );
  }

}
