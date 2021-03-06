<?php

final class PhameBlogHeaderPictureController
  extends PhameBlogController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();
    $id = $request->getURIData('id');

    $blog = id(new PhameBlogQuery())
      ->setViewer($viewer)
      ->withIDs(array($id))
      ->needHeaderImage(true)
      ->requireCapabilities(
        array(
          PhabricatorPolicyCapability::CAN_VIEW,
          PhabricatorPolicyCapability::CAN_EDIT,
        ))
      ->executeOne();
    if (!$blog) {
      return new Aphront404Response();
    }

    $blog_uri = '/phame/blog/manage/'.$id;

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
          'Bạn phải chọn một tập tin khi tải lên một tiêu đề blog mới.');
      }

      if (!$errors && !$delete_header) {
        if (!$file->isTransformableImage()) {
          $e_file = pht('Không được hỗ trợ');
          $errors[] = pht(
            'Máy chủ này chỉ hỗ trợ các định dạng hình ảnh: %s.',
            implode(', ', $supported_formats));
        }
      }

      if (!$errors) {
        if ($delete_header) {
          $new_value = null;
        } else {
          $file->attachToObject($blog->getPHID());
          $new_value = $file->getPHID();
        }

        $xactions = array();
        $xactions[] = id(new PhameBlogTransaction())
          ->setTransactionType(PhameBlogTransaction::TYPE_HEADERIMAGE)
          ->setNewValue($new_value);

        $editor = id(new PhameBlogEditor())
          ->setActor($viewer)
          ->setContentSourceFromRequest($request)
          ->setContinueOnMissingFields(true)
          ->setContinueOnNoEffect(true);

        $editor->applyTransactions($blog, $xactions);

        return id(new AphrontRedirectResponse())->setURI($blog_uri);
      }
    }

    $title = pht('Sửa tiêu đề của blog');

    $upload_form = id(new AphrontFormView())
      ->setUser($viewer)
      ->setEncType('multipart/form-data')
      ->appendChild(
        id(new AphrontFormFileControl())
          ->setName('header')
          ->setLabel(pht('Tải lên tiêu đề'))
          ->setError($e_file)
          ->setCaption(
            pht('Được hỗ trợ các định dạng : %s', implode(', ', $supported_formats))))
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
          ->addCancelButton($blog_uri)
          ->setValue(pht('Tải lên tiêu đề ')));

    $upload_box = id(new PHUIObjectBoxView())
      ->setHeaderText(pht('Tải lên tiêu đề mới'))
      ->setBackground(PHUIObjectBoxView::BLUE_PROPERTY)
      ->setForm($upload_form);

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->addTextCrumb(
      pht('Blogs'),
      $this->getApplicationURI('blog/'));
    $crumbs->addTextCrumb(
      $blog->getName(),
      $this->getApplicationURI('blog/view/'.$id));
    $crumbs->addTextCrumb(pht('Tiêu đề Blog'));
    $crumbs->setBorder(true);

    $header = id(new PHUIHeaderView())
      ->setHeader(pht('Sửa tiêu đề Blog'))
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
