<?php

final class AlmanacBindingDisableController
  extends AlmanacServiceController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();

    $id = $request->getURIData('id');
    $binding = id(new AlmanacBindingQuery())
      ->setViewer($viewer)
      ->withIDs(array($id))
      ->requireCapabilities(
        array(
          PhabricatorPolicyCapability::CAN_VIEW,
          PhabricatorPolicyCapability::CAN_EDIT,
        ))
      ->executeOne();
    if (!$binding) {
      return new Aphront404Response();
    }

    $id = $binding->getID();
    $is_disable = !$binding->getIsDisabled();
    $done_uri = $binding->getURI();

    if ($is_disable) {
      $disable_title = pht('Vô hiệu hóa ràng buộc');
      $disable_body = pht('Vô hiệu hóa ràng buộc này?');
      $disable_button = pht('Vô hiệu hóa');

      $v_disable = 1;
    } else {
      $disable_title = pht('Kích hoạt ràng buộc');
      $disable_body = pht('Kích hoạt ràng buộc này?');
      $disable_button = pht('Kích hoạt');

      $v_disable = 0;
    }


    if ($request->isFormPost()) {
      $type_disable = AlmanacBindingTransaction::TYPE_DISABLE;

      $xactions = array();

      $xactions[] = id(new AlmanacBindingTransaction())
        ->setTransactionType($type_disable)
        ->setNewValue($v_disable);

      $editor = id(new AlmanacBindingEditor())
        ->setActor($viewer)
        ->setContentSourceFromRequest($request)
        ->setContinueOnNoEffect(true)
        ->setContinueOnMissingFields(true);

      $editor->applyTransactions($binding, $xactions);

      return id(new AphrontRedirectResponse())->setURI($done_uri);
    }

    return $this->newDialog()
      ->setTitle($disable_title)
      ->appendParagraph($disable_body)
      ->addSubmitButton($disable_button)
      ->addCancelButton($done_uri);
  }

}
