<?php

final class AlmanacPropertyDeleteController
  extends AlmanacPropertyController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();

    $response = $this->loadPropertyObject();
    if ($response) {
      return $response;
    }

    $object = $this->getPropertyObject();

    $key = $request->getStr('key');
    if (!strlen($key)) {
      return new Aphront404Response();
    }

    $cancel_uri = $object->getURI();

    $builtins = $object->getAlmanacPropertyFieldSpecifications();
    $is_builtin = isset($builtins[$key]);

    if ($is_builtin) {
      $title = pht('Đặt lại thuộc tính');
      $body = pht(
        'Đặt lại thuộc tính "%s" về giá trị mặc định?',
        $key);
      $submit_text = pht('Đặt lại');
    } else {
      $title = pht('Xóa');
      $body = pht(
        'Xóa thuộc tính "%s"?',
        $key);
      $submit_text = pht('Xóa');
    }

    $validation_exception = null;
    if ($request->isFormPost()) {
      $xaction = $object->getApplicationTransactionTemplate()
        ->setTransactionType(AlmanacTransaction::TYPE_PROPERTY_REMOVE)
        ->setMetadataValue('almanac.property', $key);

      $editor = $object->getApplicationTransactionEditor()
        ->setActor($viewer)
        ->setContentSourceFromRequest($request)
        ->setContinueOnNoEffect(true)
        ->setContinueOnMissingFields(true);

      try {
        $editor->applyTransactions($object, array($xaction));
        return id(new AphrontRedirectResponse())->setURI($cancel_uri);
      } catch (PhabricatorApplicationTransactionValidationException $ex) {
        $validation_exception = $ex;
      }
    }

    return $this->newDialog()
      ->setTitle($title)
      ->setValidationException($validation_exception)
      ->addHiddenInput('objectPHID', $object->getPHID())
      ->addHiddenInput('key', $key)
      ->appendParagraph($body)
      ->addCancelButton($cancel_uri)
      ->addSubmitButton($submit_text);
  }

}
