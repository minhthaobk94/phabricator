<?php

final class DifferentialDiffCreateController extends DifferentialController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $this->getViewer();

    // If we're on the "Update Diff" workflow, load the revision we're going
    // to update.
    $revision = null;
    $revision_id = $request->getURIData('revisionID');
    if ($revision_id) {
      $revision = id(new DifferentialRevisionQuery())
        ->setViewer($viewer)
        ->withIDs(array($revision_id))
        ->requireCapabilities(
          array(
            PhabricatorPolicyCapability::CAN_VIEW,
            PhabricatorPolicyCapability::CAN_EDIT,
          ))
        ->executeOne();
      if (!$revision) {
        return new Aphront404Response();
      }
    }

    $diff = null;
    // This object is just for policy stuff
    $diff_object = DifferentialDiff::initializeNewDiff($viewer);
    $repository_phid = null;
    $errors = array();
    $e_diff = null;
    $e_file = null;
    $validation_exception = null;
    if ($request->isFormPost()) {

      $repository_tokenizer = $request->getArr(
        id(new DifferentialRepositoryField())->getFieldKey());
      if ($repository_tokenizer) {
        $repository_phid = reset($repository_tokenizer);
      }

      if ($request->getFileExists('diff-file')) {
        $diff = PhabricatorFile::readUploadedFileData($_FILES['diff-file']);
      } else {
        $diff = $request->getStr('diff');
      }

      if (!strlen($diff)) {
        $errors[] = pht(
          'Bạn không thể tạo trống khác nhau.Chuyển đổi hoặc tải  '.
          'file chứa khác nhau.');
        $e_diff = pht('Được yêu cầu');
        $e_file = pht('Được yêu cầu');
      }

      if (!$errors) {
        try {
          $call = new ConduitCall(
            'differential.createrawdiff',
            array(
              'diff' => $diff,
              'repositoryPHID' => $repository_phid,
              'viewPolicy' => $request->getStr('viewPolicy'),
            ));
          $call->setUser($viewer);
          $result = $call->execute();

          $diff_id = $result['id'];

          $uri = $this->getApplicationURI("diff/{$diff_id}/");
          $uri = new PhutilURI($uri);
          if ($revision) {
            $uri->setQueryParam('revisionID', $revision->getID());
          }

          return id(new AphrontRedirectResponse())->setURI($uri);
        } catch (PhabricatorApplicationTransactionValidationException $ex) {
          $validation_exception = $ex;
        }
      }
    }

    $form = new AphrontFormView();
    $arcanist_href = PhabricatorEnv::getDoclink('Arcanist User Guide');
    $arcanist_link = phutil_tag(
      'a',
      array(
        'href' => $arcanist_href,
        'target' => '_blank',
      ),
      pht('Learn More'));

    $cancel_uri = $this->getApplicationURI();

    $policies = id(new PhabricatorPolicyQuery())
      ->setViewer($viewer)
      ->setObject($diff_object)
      ->execute();

    $info_view = null;
    if (!$request->isFormPost()) {
      $info_view = id(new PHUIInfoView())
        ->setSeverity(PHUIInfoView::SEVERITY_NOTICE)
        ->setErrors(
          array(
            array(
              pht(
                'Cách tốt nhất để tạo khác là sử dụng công cụ Arcanist '.
                'command-line .'),
              ' ',
              $arcanist_link,
            ),
            pht(
              'Bạn có thể chuyển đổi khác dưới đây, hoặc cập nhật file '.
              'chưa đựng khác nhau  (ví dụ, từ  %s, %s đến %s).',
              phutil_tag('tt', array(), 'svn diff'),
              phutil_tag('tt', array(), 'git diff'),
              phutil_tag('tt', array(), 'hg diff --git')),
          ));
    }

    if ($revision) {
      $title = pht('Cập nhật');
      $header = pht('Cập nhật');
      $button = pht('Tiếp tục');
      $header_icon = 'fa-upload';
    } else {
      $title = pht('Tạo mới');
      $header = pht('Tạo mới');
      $button = pht('Tạo mới');
      $header_icon = 'fa-plus-square';
    }

    $form
      ->setEncType('multipart/form-data')
      ->setUser($viewer);

    if ($revision) {
      $form->appendChild(
        id(new AphrontFormMarkupControl())
          ->setLabel(pht('Updating Revision'))
          ->setValue($viewer->renderHandle($revision->getPHID())));
    }

    if ($repository_phid) {
      $repository_value = array($repository_phid);
    } else {
      $repository_value = array();
    }

    $form
      ->appendChild(
        id(new AphrontFormTextAreaControl())
          ->setLabel(pht('Raw Diff'))
          ->setName('diff')
          ->setValue($diff)
          ->setHeight(AphrontFormTextAreaControl::HEIGHT_VERY_TALL)
          ->setError($e_diff))
      ->appendChild(
        id(new AphrontFormFileControl())
          ->setLabel(pht('Raw Diff từ File'))
          ->setName('diff-file')
          ->setError($e_file))
      ->appendControl(
        id(new AphrontFormTokenizerControl())
          ->setName(id(new DifferentialRepositoryField())->getFieldKey())
          ->setLabel(pht('Repository'))
          ->setDatasource(new DiffusionRepositoryDatasource())
          ->setValue($repository_value)
          ->setLimit(1))
      ->appendChild(
        id(new AphrontFormPolicyControl())
          ->setUser($viewer)
          ->setName('viewPolicy')
          ->setPolicyObject($diff_object)
          ->setPolicies($policies)
          ->setCapability(PhabricatorPolicyCapability::CAN_VIEW))
      ->appendChild(
        id(new AphrontFormSubmitControl())
          ->addCancelButton($cancel_uri)
          ->setValue($button));

    $form_box = id(new PHUIObjectBoxView())
      ->setHeaderText(pht('Diff'))
      ->setValidationException($validation_exception)
      ->setForm($form)
      ->setBackground(PHUIObjectBoxView::BLUE_PROPERTY)
      ->setFormErrors($errors);

    $crumbs = $this->buildApplicationCrumbs();
    if ($revision) {
      $crumbs->addTextCrumb(
        $revision->getMonogram(),
        '/'.$revision->getMonogram());
    }
    $crumbs->addTextCrumb($title);
    $crumbs->setBorder(true);

    $header = id(new PHUIHeaderView())
      ->setHeader($title)
      ->setHeaderIcon($header_icon);

    $view = id(new PHUITwoColumnView())
      ->setHeader($header)
      ->setFooter(array(
        $info_view,
        $form_box,
      ));

    return $this->newPage()
      ->setTitle($title)
      ->setCrumbs($crumbs)
      ->appendChild($view);
  }

}
