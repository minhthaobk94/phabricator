<?php

final class PhamePostHeaderPictureController
  extends PhamePostController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();
    $id = $request->getURIData('id');

    $post = id(new PhamePostQuery())
      ->setViewer($viewer)
      ->withIDs(array($id))
      ->needHeaderImage(true)
      ->requireCapabilities(
        array(
          PhabricatorPolicyCapability::CAN_VIEW,
          PhabricatorPolicyCapability::CAN_EDIT,
        ))
      ->executeOne();
    if (!$post) {
      return new Aphront404Response();
    }

    $post_uri = '/phame/post/view/'.$id;

    $supported_formats = PhabricatorFile::getTransformableImageFormats();
    $e_file = true;
    $errors = array();
    $delete_header = ($request->getInt('delete') == 1);

    if ($request->isFormPost()) {
      if ($request->getFileExists('header')) {
        $file = PhabricatorFile::newFromPHPUpload(
          $_FILES['header'],
          array(
            'authorPHID' => $viewer->getPHID(),
            'canCDN' => true,
          ));
      } else if (!$delete_header) {
        $e_file = pht('Required');
        $errors[] = pht(
          'Bạn phải chọn một tập tin khi tải một  tiêu đề  bài đăng mới.');
      }

      if (!$errors && !$delete_header) {
        if (!$file->isTransformableImage()) {
          $e_file = pht('Không được hỗ trợ ');
          $errors[] = pht(
            'Máy chủ này chỉ hỗ trợ các định dạng hình ảnh: %s.',
            implode(', ', $supported_formats));
        }
      }

      if (!$errors) {
        if ($delete_header) {
          $new_value = null;
        } else {
          $file->attachToObject($post->getPHID());
          $new_value = $file->getPHID();
        }

        $xactions = array();
        $xactions[] = id(new PhamePostTransaction())
          ->setTransactionType(PhamePostTransaction::TYPE_HEADERIMAGE)
          ->setNewValue($new_value);

        $editor = id(new PhamePostEditor())
          ->setActor($viewer)
          ->setContentSourceFromRequest($request)
          ->setContinueOnMissingFields(true)
          ->setContinueOnNoEffect(true);

        $editor->applyTransactions($post, $xactions);

        return id(new AphrontRedirectResponse())->setURI($post_uri);
      }
    }

    $title = pht('Sửa tiêu đề bài đăng');

    $upload_form = id(new AphrontFormView())
      ->setUser($viewer)
      ->setEncType('multipart/form-data')
      ->appendChild(
        id(new AphrontFormFileControl())
          ->setName('header')
          ->setLabel(pht('Tải lên tiêu đề'))
          ->setError($e_file)
          ->setCaption(
            pht('Hỗ trợ các định dạng : %s', implode(', ', $supported_formats))))
      ->appendChild(
        id(new AphrontFormCheckboxControl())
          ->setName('delete')
          ->setLabel(pht('Xóa tiêu đề'))
          ->addCheckbox(
            'delete',
            1,
            null,
            null))
      ->appendChild(
        id(new AphrontFormSubmitControl())
          ->addCancelButton($post_uri)
          ->setValue(pht('Tải lên tiêu đề')));

    $upload_box = id(new PHUIObjectBoxView())
      ->setHeaderText(pht('Tải lên tiêu đề mới'))
      ->setBackground(PHUIObjectBoxView::BLUE_PROPERTY)
      ->setForm($upload_form);

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->addTextCrumb(
      $post->getTitle(),
      $this->getApplicationURI('post/view/'.$id));
    $crumbs->addTextCrumb(pht('Đăng tiêu đề'));
    $crumbs->setBorder(true);

    $header = id(new PHUIHeaderView())
      ->setHeader(pht('Sửa tiêu đề bài đăng'))
      ->setHeaderIcon('fa-camera');

    $view = id(new PHUITwoColumnView())
      ->setHeader($header)
      ->setFooter(array(
        $upload_box,
      ));

    return $this->newPage()
      ->setTitle($title)
      ->setCrumbs($crumbs)
      ->appendChild(
        array(
          $view,
      ));

  }
}
