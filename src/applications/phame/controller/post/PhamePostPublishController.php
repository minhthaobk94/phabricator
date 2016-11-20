<?php

final class PhamePostPublishController extends PhamePostController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();

    $id = $request->getURIData('id');
    $post = id(new PhamePostQuery())
      ->setViewer($viewer)
      ->withIDs(array($id))
      ->requireCapabilities(
        array(
          PhabricatorPolicyCapability::CAN_VIEW,
          PhabricatorPolicyCapability::CAN_EDIT,
        ))
      ->executeOne();
    if (!$post) {
      return new Aphront404Response();
    }

    $cancel_uri = $post->getViewURI();

    $action = $request->getURIData('action');
    $is_publish = ($action == 'publish');

    if ($request->isFormPost()) {
      $xactions = array();

      if ($is_publish) {
        $new_value = PhameConstants::VISIBILITY_PUBLISHED;
      } else {
        $new_value = PhameConstants::VISIBILITY_DRAFT;
      }

      $xactions[] = id(new PhamePostTransaction())
        ->setTransactionType(PhamePostTransaction::TYPE_VISIBILITY)
        ->setNewValue($new_value);

      id(new PhamePostEditor())
        ->setActor($viewer)
        ->setContentSourceFromRequest($request)
        ->setContinueOnNoEffect(true)
        ->setContinueOnMissingFields(true)
        ->applyTransactions($post, $xactions);

      return id(new AphrontRedirectResponse())
        ->setURI($cancel_uri);
    }

    if ($is_publish) {
      $title = pht('Xuất bản bài viết');
      $body = pht('Bài viết này sẽ đi trực tiếp khi bạn xuất bản nó.');
      $button = pht('Xuất bản');
    } else {
      $title = pht('Không xuất bản bài viết ');
      $body = pht(
        'Bài này sẽ phục hổi để soạn thảo và tình trạng không còn được nhìn thấy '.
        'to other users.');
      $button = pht('Không xuất bản ');
    }

    return $this->newDialog()
      ->setTitle($title)
      ->appendParagraph($body)
      ->addSubmitButton($button)
      ->addCancelButton($cancel_uri);
  }

}
