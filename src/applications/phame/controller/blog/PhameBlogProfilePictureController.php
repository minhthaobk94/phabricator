<?php

final class PhameBlogProfilePictureController
  extends PhameBlogController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();
    $id = $request->getURIData('id');

    $blog = id(new PhameBlogQuery())
      ->setViewer($viewer)
      ->withIDs(array($id))
      ->needProfileImage(true)
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

    if ($request->isFormPost()) {
      $phid = $request->getStr('phid');
      $is_default = false;
      if ($phid == PhabricatorPHIDConstants::PHID_VOID) {
        $phid = null;
        $is_default = true;
      } else if ($phid) {
        $file = id(new PhabricatorFileQuery())
          ->setViewer($viewer)
          ->withPHIDs(array($phid))
          ->executeOne();
      } else {
        if ($request->getFileExists('picture')) {
          $file = PhabricatorFile::newFromPHPUpload(
            $_FILES['picture'],
            array(
              'authorPHID' => $viewer->getPHID(),
              'canCDN' => true,
            ));
        } else {
          $e_file = pht('Required');
          $errors[] = pht(
            'Bạn phải chọn một tập tin khi tải lên một hình ảnh blog mới.');
        }
      }

      if (!$errors && !$is_default) {
        if (!$file->isTransformableImage()) {
          $e_file = pht('Không hỗ trợ');
          $errors[] = pht(
            'Máy chủ này chỉ hỗ trợ các định dạng hình ảnh: %s.',
            implode(', ', $supported_formats));
        } else {
          $xform = PhabricatorFileTransform::getTransformByKey(
            PhabricatorFileThumbnailTransform::TRANSFORM_PROFILE);
          $xformed = $xform->executeTransform($file);
        }
      }

      if (!$errors) {
        if ($is_default) {
          $new_value = null;
        } else {
          $xformed->attachToObject($blog->getPHID());
          $new_value = $xformed->getPHID();
        }

        $xactions = array();
        $xactions[] = id(new PhameBlogTransaction())
          ->setTransactionType(PhameBlogTransaction::TYPE_PROFILEIMAGE)
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

    $title = pht('Sửa hình ảnh Blog');

    $form = id(new PHUIFormLayoutView())
      ->setUser($viewer);

    $default_image = PhabricatorFile::loadBuiltin($viewer, 'blog.png');

    $images = array();

    $current = $blog->getProfileImagePHID();
    $has_current = false;
    if ($current) {
      $files = id(new PhabricatorFileQuery())
        ->setViewer($viewer)
        ->withPHIDs(array($current))
        ->execute();
      if ($files) {
        $file = head($files);
        if ($file->isTransformableImage()) {
          $has_current = true;
          $images[$current] = array(
            'uri' => $file->getBestURI(),
            'tip' => pht('Current Picture'),
          );
        }
      }
    }

    $images[PhabricatorPHIDConstants::PHID_VOID] = array(
      'uri' => $default_image->getBestURI(),
      'tip' => pht('Ảnh mặc định'),
    );

    require_celerity_resource('people-profile-css');
    Javelin::initBehavior('phabricator-tooltips', array());

    $buttons = array();
    foreach ($images as $phid => $spec) {
      $button = javelin_tag(
        'button',
        array(
          'class' => 'grey profile-image-button',
          'sigil' => 'has-tooltip',
          'meta' => array(
            'tip' => $spec['tip'],
            'size' => 300,
          ),
        ),
        phutil_tag(
          'img',
          array(
            'height' => 50,
            'width' => 50,
            'src' => $spec['uri'],
          )));

      $button = array(
        phutil_tag(
          'input',
          array(
            'type'  => 'hidden',
            'name'  => 'phid',
            'value' => $phid,
          )),
        $button,
      );

      $button = phabricator_form(
        $viewer,
        array(
          'class' => 'profile-image-form',
          'method' => 'POST',
        ),
        $button);

      $buttons[] = $button;
    }

    if ($has_current) {
      $form->appendChild(
        id(new AphrontFormMarkupControl())
          ->setLabel(pht('Hình ảnh hiện tại'))
          ->setValue(array_shift($buttons)));
    }

    $form->appendChild(
      id(new AphrontFormMarkupControl())
        ->setLabel(pht('Sử dụng hình ảnh'))
        ->setValue($buttons));

    $form_box = id(new PHUIObjectBoxView())
      ->setHeaderText($title)
      ->setFormErrors($errors)
      ->setBackground(PHUIObjectBoxView::BLUE_PROPERTY)
      ->setForm($form);

    $upload_form = id(new AphrontFormView())
      ->setUser($viewer)
      ->setEncType('multipart/form-data')
      ->appendChild(
        id(new AphrontFormFileControl())
          ->setName('picture')
          ->setLabel(pht('Tải ảnh lên'))
          ->setError($e_file)
          ->setCaption(
            pht('Được hỗ trợ các định dạng: %s', implode(', ', $supported_formats))))
      ->appendChild(
        id(new AphrontFormSubmitControl())
          ->addCancelButton($blog_uri)
          ->setValue(pht('Tải ảnh lên')));

    $upload_box = id(new PHUIObjectBoxView())
      ->setHeaderText(pht('Tải lên ảnh mới'))
      ->setBackground(PHUIObjectBoxView::BLUE_PROPERTY)
      ->setForm($upload_form);

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->addTextCrumb(
      pht('Blogs'),
      $this->getApplicationURI('blog/'));
    $crumbs->addTextCrumb(
      $blog->getName(),
      $this->getApplicationURI('blog/view/'.$id));
    $crumbs->addTextCrumb(pht('Hình ảnh Blog'));
    $crumbs->setBorder(true);

    $header = id(new PHUIHeaderView())
      ->setHeader(pht('Sửa hình ảnh Blog'))
      ->setHeaderIcon('fa-camera');

    $view = id(new PHUITwoColumnView())
      ->setHeader($header)
      ->setFooter(array(
        $form_box,
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
