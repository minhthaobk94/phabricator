<?php

final class AlmanacBindingsSearchEngineAttachment
  extends AlmanacSearchEngineAttachment {

  public function getAttachmentName() {
    return pht('Ràng buộc');
  }

  public function getAttachmentDescription() {
    return pht('Lấy ràng buộc.');
  }

  public function willLoadAttachmentData($query, $spec) {
    $query->needProperties(true);
    $query->needBindings(true);
  }

  public function getAttachmentForObject($object, $data, $spec) {
    $bindings = array();
    foreach ($object->getBindings() as $binding) {
      $bindings[] = $this->getAlmanacBindingDictionary($binding);
    }

    return array(
      'bindings' => $bindings,
    );
  }

}
