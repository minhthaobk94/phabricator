<?php

final class LegalpadDocumentSignatureSearchEngine
  extends PhabricatorApplicationSearchEngine {

  private $document;

  public function getResultTypeDescription() {
    return pht('Chữ ký pháp lý');
  }

  public function getApplicationClassName() {
    return 'PhabricatorLegalpadApplication';
  }

  public function setDocument(LegalpadDocument $document) {
    $this->document = $document;
    return $this;
  }

  public function buildSavedQueryFromRequest(AphrontRequest $request) {
    $saved = new PhabricatorSavedQuery();

    $saved->setParameter(
      'signerPHIDs',
      $this->readUsersFromRequest($request, 'signers'));

    $saved->setParameter(
      'documentPHIDs',
      $this->readPHIDsFromRequest(
        $request,
        'documents',
        array(
          PhabricatorLegalpadDocumentPHIDType::TYPECONST,
        )));

    $saved->setParameter('nameContains', $request->getStr('nameContains'));
    $saved->setParameter('emailContains', $request->getStr('emailContains'));

    return $saved;
  }

  public function buildQueryFromSavedQuery(PhabricatorSavedQuery $saved) {
    $query = id(new LegalpadDocumentSignatureQuery());

    $signer_phids = $saved->getParameter('signerPHIDs', array());
    if ($signer_phids) {
      $query->withSignerPHIDs($signer_phids);
    }

    if ($this->document) {
      $query->withDocumentPHIDs(array($this->document->getPHID()));
    } else {
      $document_phids = $saved->getParameter('documentPHIDs', array());
      if ($document_phids) {
        $query->withDocumentPHIDs($document_phids);
      }
    }

    $name_contains = $saved->getParameter('nameContains');
    if (strlen($name_contains)) {
      $query->withNameContains($name_contains);
    }

    $email_contains = $saved->getParameter('emailContains');
    if (strlen($email_contains)) {
      $query->withEmailContains($email_contains);
    }

    return $query;
  }

  public function buildSearchForm(
    AphrontFormView $form,
    PhabricatorSavedQuery $saved_query) {

    $document_phids = $saved_query->getParameter('documentPHIDs', array());
    $signer_phids = $saved_query->getParameter('signerPHIDs', array());

    if (!$this->document) {
      $form
        ->appendControl(
          id(new AphrontFormTokenizerControl())
            ->setDatasource(new LegalpadDocumentDatasource())
            ->setName('documents')
            ->setLabel(pht('Tài liệu'))
            ->setValue($document_phids));
    }

    $name_contains = $saved_query->getParameter('nameContains', '');
    $email_contains = $saved_query->getParameter('emailContains', '');

    $form
      ->appendControl(
        id(new AphrontFormTokenizerControl())
          ->setDatasource(new PhabricatorPeopleDatasource())
          ->setName('signers')
          ->setLabel(pht('Người ký'))
          ->setValue($signer_phids))
      ->appendChild(
        id(new AphrontFormTextControl())
          ->setLabel(pht('Tên'))
          ->setName('nameContains')
          ->setValue($name_contains))
      ->appendChild(
        id(new AphrontFormTextControl())
          ->setLabel(pht('Email'))
          ->setName('emailContains')
          ->setValue($email_contains));
  }

  protected function getURI($path) {
    if ($this->document) {
      return '/legalpad/signatures/'.$this->document->getID().'/'.$path;
    } else {
      return '/legalpad/signatures/'.$path;
    }
  }

  protected function getBuiltinQueryNames() {
    $names = array(
      'all' => pht('Tất cả chữ ký'),
    );

    return $names;
  }

  public function buildSavedQueryFromBuiltin($query_key) {

    $query = $this->newSavedQuery();
    $query->setQueryKey($query_key);

    switch ($query_key) {
      case 'all':
        return $query;
    }

    return parent::buildSavedQueryFromBuiltin($query_key);
  }

  protected function getRequiredHandlePHIDsForResultList(
    array $signatures,
    PhabricatorSavedQuery $query) {

    return array_merge(
      mpull($signatures, 'getSignerPHID'),
      mpull($signatures, 'getDocumentPHID'));
  }

  protected function renderResultList(
    array $signatures,
    PhabricatorSavedQuery $query,
    array $handles) {
    assert_instances_of($signatures, 'LegalpadDocumentSignature');

    $viewer = $this->requireViewer();

    Javelin::initBehavior('phabricator-tooltips');

    $sig_good = $this->renderIcon(
      'fa-check',
      null,
      pht('Verified, Current'));

    $sig_corp = $this->renderIcon(
      'fa-building-o',
      null,
      pht('Verified, Corporate'));

    $sig_old = $this->renderIcon(
      'fa-clock-o',
      'orange',
      pht('Signed Older Version'));

    $sig_unverified = $this->renderIcon(
      'fa-envelope',
      'red',
      pht('Unverified Email'));

    $sig_exemption = $this->renderIcon(
      'fa-asterisk',
      'indigo',
      pht('Exemption'));

    id(new PHUIIconView())
      ->setIcon('fa-envelope', 'red')
      ->addSigil('has-tooltip')
      ->setMetadata(array('tip' => pht('Email không được kiểm tra')));

    $type_corporate = LegalpadDocument::SIGNATURE_TYPE_CORPORATION;

    $rows = array();
    foreach ($signatures as $signature) {
      $name = $signature->getSignerName();
      $email = $signature->getSignerEmail();

      $document = $signature->getDocument();

      if ($signature->getIsExemption()) {
        $sig_icon = $sig_exemption;
      } else if (!$signature->isVerified()) {
        $sig_icon = $sig_unverified;
      } else if ($signature->getDocumentVersion() != $document->getVersions()) {
        $sig_icon = $sig_old;
      } else if ($signature->getSignatureType() == $type_corporate) {
        $sig_icon = $sig_corp;
      } else {
        $sig_icon = $sig_good;
      }

      $signature_href = $this->getApplicationURI(
        'signature/'.$signature->getID().'/');

      $sig_icon = javelin_tag(
        'a',
        array(
          'href' => $signature_href,
          'sigil' => 'workflow',
        ),
        $sig_icon);

      $signer_phid = $signature->getSignerPHID();

      $rows[] = array(
        $sig_icon,
        $handles[$document->getPHID()]->renderLink(),
        $signer_phid
          ? $handles[$signer_phid]->renderLink()
          : null,
        $name,
        phutil_tag(
          'a',
          array(
            'href' => 'mailto:'.$email,
          ),
          $email),
        phabricator_datetime($signature->getDateCreated(), $viewer),
      );
    }

    $table = id(new AphrontTableView($rows))
      ->setNoDataString(pht('Chữ ký không khớp.'))
      ->setHeaders(
        array(
          '',
          pht('Tài liệu'),
          pht('Tài khoản'),
          pht('Tên'),
          pht('Email'),
          pht('Đã ký'),
        ))
      ->setColumnVisibility(
        array(
          true,

          // Only show the "Document" column if we aren't scoped to a
          // particular document.
          !$this->document,
        ))
      ->setColumnClasses(
        array(
          '',
          '',
          '',
          '',
          'wide',
          'right',
        ));

    $button = null;
    if ($this->document) {
      $document_id = $this->document->getID();

      $button = id(new PHUIButtonView())
          ->setText(pht('Thêm ưu đãi'))
          ->setTag('a')
          ->setHref($this->getApplicationURI('addsignature/'.$document_id.'/'))
          ->setWorkflow(true)
          ->setIcon('fa-pencil');
    }

    if (!$this->document) {
      $table->setNotice(
        pht('Chú ý: Có thể bạn chỉ thấy chữ ký của mình trên tài liệu '.
            'bạn cần có thêm quyền chỉnh sửa.'));
    }

    $result = new PhabricatorApplicationSearchResultView();
    $result->setTable($table);
    if ($button) {
      $result->addAction($button);
    }

    return $result;

  }

  private function renderIcon($icon, $color, $title) {
    Javelin::initBehavior('phabricator-tooltips');

    return array(
      id(new PHUIIconView())
        ->setIcon($icon, $color)
        ->addSigil('has-tooltip')
        ->setMetadata(array('tip' => $title)),
      javelin_tag(
        'span',
        array(
          'aural' => true,
        ),
        $title),
    );
  }

}
