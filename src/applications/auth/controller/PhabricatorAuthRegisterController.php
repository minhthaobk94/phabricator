<?php

final class PhabricatorAuthRegisterController
  extends PhabricatorAuthController {

  public function shouldRequireLogin() {
    return false;
  }

  public function handleRequest(AphrontRequest $request) {
    $viewer = $this->getViewer();
    $account_key = $request->getURIData('akey');

    if ($request->getUser()->isLoggedIn()) {
      return id(new AphrontRedirectResponse())->setURI('/');
    }

    $is_setup = false;
    if (strlen($account_key)) {
      $result = $this->loadAccountForRegistrationOrLinking($account_key);
      list($account, $provider, $response) = $result;
      $is_default = false;
    } else if ($this->isFirstTimeSetup()) {
      list($account, $provider, $response) = $this->loadSetupAccount();
      $is_default = true;
      $is_setup = true;
    } else {
      list($account, $provider, $response) = $this->loadDefaultAccount();
      $is_default = true;
    }

    if ($response) {
      return $response;
    }

    $invite = $this->loadInvite();

    if (!$provider->shouldAllowRegistration()) {
      if ($invite) {
        // If the user has an invite, we allow them to register with any
        // provider, even a login-only provider.
      } else {
        // TODO: This is a routine error if you click "Login" on an external
        // auth source which doesn't allow registration. The error should be
        // more tailored.

        return $this->renderError(
          pht(
            'Các tài khoản bạn đang cố gắng đăng ký với sử dụng một '.
            'cung cấp dịch vụ xác thực ("% s") mà không cho phép'.
            'đăng ký. Người quản trị có thể đã bị vô hiệu hóa gần đây'.
            'đăng ký với nhà cung cấp này.',
            $provider->getProviderName()));
      }
    }

    $user = new PhabricatorUser();

    $default_username = $account->getUsername();
    $default_realname = $account->getRealName();

    $default_email = $account->getEmail();

    if ($invite) {
      $default_email = $invite->getEmailAddress();
    }

    if (!PhabricatorUserEmail::isValidAddress($default_email)) {
      $default_email = null;
    }

    if ($default_email !== null) {
      // We should bypass policy here becase e.g. limiting an application use
      // to a subset of users should not allow the others to overwrite
      // configured application emails
      $application_email = id(new PhabricatorMetaMTAApplicationEmailQuery())
        ->setViewer(PhabricatorUser::getOmnipotentUser())
        ->withAddresses(array($default_email))
        ->executeOne();
      if ($application_email) {
        $default_email = null;
      }
    }

    if ($default_email !== null) {
      // If the account source provided an email, but it's not allowed by
      // the configuration, roadblock the user. Previously, we let the user
      // pick a valid email address instead, but this does not align well with
      // user expectation and it's not clear the cases it enables are valuable.
      // See discussion in T3472.
      if (!PhabricatorUserEmail::isAllowedAddress($default_email)) {
        $debug_email = new PHUIInvisibleCharacterView($default_email);
        return $this->renderError(
          array(
            pht(
              'Các tài khoản bạn đang cố gắng đăng ký với có một không hợp lệ'.
              'địa chỉ email (% s). Phabricator này cài đặt chỉ cho phép '.
              '	đăng ký với địa chỉ email cụ thể:',
              $debug_email),
            phutil_tag('br'),
            phutil_tag('br'),
            PhabricatorUserEmail::describeAllowedAddresses(),
          ));
      }

      // If the account source provided an email, but another account already
      // has that email, just pretend we didn't get an email.

      // TODO: See T3472.

      if ($default_email !== null) {
        $same_email = id(new PhabricatorUserEmail())->loadOneWhere(
          'address = %s',
          $default_email);
        if ($same_email) {
          if ($invite) {
            // We're allowing this to continue. The fact that we loaded the
            // invite means that the address is nonprimary and unverified and
            // we're OK to steal it.
          } else {
            $default_email = null;
          }
        }
      }
    }

    $profile = id(new PhabricatorRegistrationProfile())
      ->setDefaultUsername($default_username)
      ->setDefaultEmail($default_email)
      ->setDefaultRealName($default_realname)
      ->setCanEditUsername(true)
      ->setCanEditEmail(($default_email === null))
      ->setCanEditRealName(true)
      ->setShouldVerifyEmail(false);

    $event_type = PhabricatorEventType::TYPE_AUTH_WILLREGISTERUSER;
    $event_data = array(
      'account' => $account,
      'profile' => $profile,
    );

    $event = id(new PhabricatorEvent($event_type, $event_data))
      ->setUser($user);
    PhutilEventEngine::dispatchEvent($event);

    $default_username = $profile->getDefaultUsername();
    $default_email = $profile->getDefaultEmail();
    $default_realname = $profile->getDefaultRealName();

    $can_edit_username = $profile->getCanEditUsername();
    $can_edit_email = $profile->getCanEditEmail();
    $can_edit_realname = $profile->getCanEditRealName();

    $must_set_password = $provider->shouldRequireRegistrationPassword();

    $can_edit_anything = $profile->getCanEditAnything() || $must_set_password;
    $force_verify = $profile->getShouldVerifyEmail();

    // Automatically verify the administrator's email address during first-time
    // setup.
    if ($is_setup) {
      $force_verify = true;
    }

    $value_username = $default_username;
    $value_realname = $default_realname;
    $value_email = $default_email;
    $value_password = null;

    $errors = array();

    $require_real_name = PhabricatorEnv::getEnvConfig('user.require-real-name');

    $e_username = strlen($value_username) ? null : true;
    $e_realname = $require_real_name ? true : null;
    $e_email = strlen($value_email) ? null : true;
    $e_password = true;
    $e_captcha = true;

    $skip_captcha = false;
    if ($invite) {
      // If the user is accepting an invite, assume they're trustworthy enough
      // that we don't need to CAPTCHA them.
      $skip_captcha = true;
    }

    $min_len = PhabricatorEnv::getEnvConfig('account.minimum-password-length');
    $min_len = (int)$min_len;

    $from_invite = $request->getStr('invite');
    if ($from_invite && $can_edit_username) {
      $value_username = $request->getStr('username');
      $e_username = null;
    }

    if (($request->isFormPost() || !$can_edit_anything) && !$from_invite) {
      $unguarded = AphrontWriteGuard::beginScopedUnguardedWrites();

      if ($must_set_password && !$skip_captcha) {
        $e_captcha = pht('Làm lại');

        $captcha_ok = AphrontFormRecaptchaControl::processCaptcha($request);
        if (!$captcha_ok) {
          $errors[] = pht('Trả về Captcha là không chính xác, hãy thử lại.');
          $e_captcha = pht('Không hợp lệ ');
        }
      }

      if ($can_edit_username) {
        $value_username = $request->getStr('username');
        if (!strlen($value_username)) {
          $e_username = pht('Bắt buộc');
          $errors[] = pht('Tên người dùng bắt buộc.');
        } else if (!PhabricatorUser::validateUsername($value_username)) {
          $e_username = pht('Không hợp lệ');
          $errors[] = PhabricatorUser::describeValidUsername();
        } else {
          $e_username = null;
        }
      }

      if ($must_set_password) {
        $value_password = $request->getStr('password');
        $value_confirm = $request->getStr('confirm');
        if (!strlen($value_password)) {
          $e_password = pht('Bắt buộc');
          $errors[] = pht('Bạn phải chọn mật khẩu.');
        } else if ($value_password !== $value_confirm) {
          $e_password = pht('Không khớp');
          $errors[] = pht('Mật khẩu phải trùng nhau.');
        } else if (strlen($value_password) < $min_len) {
          $e_password = pht('Quá ngắn');
          $errors[] = pht(
            'Mật khẩu quá ngắn (dài tối thiểu là  %d kí tự ).',
            $min_len);
        } else if (
          PhabricatorCommonPasswords::isCommonPassword($value_password)) {

          $e_password = pht('Rất yếu');
          $errors[] = pht(
            'Mật khẩu yếu. Mật khẩu này là một trong những'.
            'hầu hết các mật khẩu phổ biến được sử dụng, và là vô cùng dễ dàng cho '.
            'kẻ tấn công để đoán. Bạn phải chọn một mật khẩu mạnh.');
        } else {
          $e_password = null;
        }
      }

      if ($can_edit_email) {
        $value_email = $request->getStr('email');
        if (!strlen($value_email)) {
          $e_email = pht('Bắt buộc');
          $errors[] = pht('Email là bắt buộc.');
        } else if (!PhabricatorUserEmail::isValidAddress($value_email)) {
          $e_email = pht('Không hợp lệ');
          $errors[] = PhabricatorUserEmail::describeValidAddresses();
        } else if (!PhabricatorUserEmail::isAllowedAddress($value_email)) {
          $e_email = pht('Không cho phép');
          $errors[] = PhabricatorUserEmail::describeAllowedAddresses();
        } else {
          $e_email = null;
        }
      }

      if ($can_edit_realname) {
        $value_realname = $request->getStr('realName');
        if (!strlen($value_realname) && $require_real_name) {
          $e_realname = pht('Bắt buộc');
          $errors[] = pht('Tên thật là bắt buộc.');
        } else {
          $e_realname = null;
        }
      }

      if (!$errors) {
        $image = $this->loadProfilePicture($account);
        if ($image) {
          $user->setProfileImagePHID($image->getPHID());
        }

        try {
          $verify_email = false;

          if ($force_verify) {
            $verify_email = true;
          }

          if ($value_email === $default_email) {
            if ($account->getEmailVerified()) {
              $verify_email = true;
            }

            if ($provider->shouldTrustEmails()) {
              $verify_email = true;
            }

            if ($invite) {
              $verify_email = true;
            }
          }

          $email_obj = null;
          if ($invite) {
            // If we have a valid invite, this email may exist but be
            // nonprimary and unverified, so we'll reassign it.
            $email_obj = id(new PhabricatorUserEmail())->loadOneWhere(
              'address = %s',
              $value_email);
          }
          if (!$email_obj) {
            $email_obj = id(new PhabricatorUserEmail())
              ->setAddress($value_email);
          }

          $email_obj->setIsVerified((int)$verify_email);

          $user->setUsername($value_username);
          $user->setRealname($value_realname);

          if ($is_setup) {
            $must_approve = false;
          } else if ($invite) {
            $must_approve = false;
          } else {
            $must_approve = PhabricatorEnv::getEnvConfig(
              'auth.require-approval');
          }

          if ($must_approve) {
            $user->setIsApproved(0);
          } else {
            $user->setIsApproved(1);
          }

          if ($invite) {
            $allow_reassign_email = true;
          } else {
            $allow_reassign_email = false;
          }

          $user->openTransaction();

            $editor = id(new PhabricatorUserEditor())
              ->setActor($user);

            $editor->createNewUser($user, $email_obj, $allow_reassign_email);
            if ($must_set_password) {
              $envelope = new PhutilOpaqueEnvelope($value_password);
              $editor->changePassword($user, $envelope);
            }

            if ($is_setup) {
              $editor->makeAdminUser($user, true);
            }

            $account->setUserPHID($user->getPHID());
            $provider->willRegisterAccount($account);
            $account->save();

          $user->saveTransaction();

          if (!$email_obj->getIsVerified()) {
            $email_obj->sendVerificationEmail($user);
          }

          if ($must_approve) {
            $this->sendWaitingForApprovalEmail($user);
          }

          if ($invite) {
            $invite->setAcceptedByPHID($user->getPHID())->save();
          }

          return $this->loginUser($user);
        } catch (AphrontDuplicateKeyQueryException $exception) {
          $same_username = id(new PhabricatorUser())->loadOneWhere(
            'userName = %s',
            $user->getUserName());

          $same_email = id(new PhabricatorUserEmail())->loadOneWhere(
            'address = %s',
            $value_email);

          if ($same_username) {
            $e_username = pht('Trùng lặp');
            $errors[] = pht('Một người dùng khác đã có tên này.');
          }

          if ($same_email) {
            // TODO: See T3340.
            $e_email = pht('Trùng lặp');
            $errors[] = pht('Một người dùng khác đã có email này.');
          }

          if (!$same_username && !$same_email) {
            throw $exception;
          }
        }
      }

      unset($unguarded);
    }

    $form = id(new AphrontFormView())
      ->setUser($request->getUser());

    if (!$is_default) {
      $form->appendChild(
        id(new AphrontFormMarkupControl())
          ->setLabel(pht('Tài khoản bên ngoài'))
          ->setValue(
            id(new PhabricatorAuthAccountView())
              ->setUser($request->getUser())
              ->setExternalAccount($account)
              ->setAuthProvider($provider)));
    }


    if ($can_edit_username) {
      $form->appendChild(
        id(new AphrontFormTextControl())
          ->setLabel(pht('Tên người dùng Phabricator'))
          ->setName('username')
          ->setValue($value_username)
          ->setError($e_username));
    } else {
      $form->appendChild(
        id(new AphrontFormMarkupControl())
          ->setLabel(pht('Tên người dùng Phabricator'))
          ->setValue($value_username)
          ->setError($e_username));
    }

    if ($can_edit_realname) {
      $form->appendChild(
        id(new AphrontFormTextControl())
          ->setLabel(pht('Tên thật'))
          ->setName('realName')
          ->setValue($value_realname)
          ->setError($e_realname));
    }

    if ($must_set_password) {
      $form->appendChild(
        id(new AphrontFormPasswordControl())
          ->setLabel(pht('Mật khẩu'))
          ->setName('password')
          ->setError($e_password));
      $form->appendChild(
        id(new AphrontFormPasswordControl())
          ->setLabel(pht('Xác nhận mật khẩu'))
          ->setName('confirm')
          ->setError($e_password)
          ->setCaption(
            $min_len
              ? pht('Chiều dài tối thiểu %d kí tự.', $min_len)
              : null));
    }

    if ($can_edit_email) {
      $form->appendChild(
        id(new AphrontFormTextControl())
          ->setLabel(pht('Email'))
          ->setName('email')
          ->setValue($value_email)
          ->setCaption(PhabricatorUserEmail::describeAllowedAddresses())
          ->setError($e_email));
    }

    if ($must_set_password && !$skip_captcha) {
      $form->appendChild(
        id(new AphrontFormRecaptchaControl())
          ->setLabel(pht('Captcha'))
          ->setError($e_captcha));
    }

    $submit = id(new AphrontFormSubmitControl());

    if ($is_setup) {
      $submit
        ->setValue(pht('Tạo tài khoản Admin'));
    } else {
      $submit
        ->addCancelButton($this->getApplicationURI('start/'))
        ->setValue(pht('Đăng ký tài khoản Phabricator'));
    }


    $form->appendChild($submit);

    $crumbs = $this->buildApplicationCrumbs();

    if ($is_setup) {
      $crumbs->addTextCrumb(pht('Thiết lập tài khoản Admin'));
        $title = pht('Chào mừng đến với Phabricator');
    } else {
      $crumbs->addTextCrumb(pht('Đăng ký'));
      $crumbs->addTextCrumb($provider->getProviderName());
        $title = pht('Đăng ký Phabricator');
    }
    $crumbs->setBorder(true);

    $welcome_view = null;
    if ($is_setup) {
      $welcome_view = id(new PHUIInfoView())
        ->setSeverity(PHUIInfoView::SEVERITY_NOTICE)
        ->setTitle(pht('Chào mừng đến với Phabricator'))
        ->appendChild(
          pht(
            'Cài đặt hoàn tất. Đăng ký tài khoản quản trị của bạn'.
            'dưới đây để đăng nhập. Bạn sẽ có thể cấu hình tùy chọn và thêm '.
            'cơ chế xác thực khác (như LDAP hoặc OAuth) sau này.'));
    }

    $object_box = id(new PHUIObjectBoxView())
      ->setForm($form)
      ->setFormErrors($errors);

    $invite_header = null;
    if ($invite) {
      $invite_header = $this->renderInviteHeader($invite);
    }

    $header = id(new PHUIHeaderView())
      ->setHeader($title);

    $view = id(new PHUITwoColumnView())
      ->setHeader($header)
      ->setFooter(array(
      $welcome_view,
      $invite_header,
      $object_box,
    ));

    return $this->newPage()
      ->setTitle($title)
      ->setCrumbs($crumbs)
      ->appendChild($view);
  }

  private function loadDefaultAccount() {
    $providers = PhabricatorAuthProvider::getAllEnabledProviders();
    $account = null;
    $provider = null;
    $response = null;

    foreach ($providers as $key => $candidate_provider) {
      if (!$candidate_provider->shouldAllowRegistration()) {
        unset($providers[$key]);
        continue;
      }
      if (!$candidate_provider->isDefaultRegistrationProvider()) {
        unset($providers[$key]);
      }
    }

    if (!$providers) {
      $response = $this->renderError(
        pht(
          'Không có nhà cung cấp đăng ký mặc định cấu hình.'));
      return array($account, $provider, $response);
    } else if (count($providers) > 1) {
      $response = $this->renderError(
        pht('Có quá nhiều nhà cung cấp đăng ký mặc định cấu hình.'));
      return array($account, $provider, $response);
    }

    $provider = head($providers);
    $account = $provider->getDefaultExternalAccount();

    return array($account, $provider, $response);
  }

  private function loadSetupAccount() {
    $provider = new PhabricatorPasswordAuthProvider();
    $provider->attachProviderConfig(
      id(new PhabricatorAuthProviderConfig())
        ->setShouldAllowRegistration(1)
        ->setShouldAllowLogin(1)
        ->setIsEnabled(true));

    $account = $provider->getDefaultExternalAccount();
    $response = null;
    return array($account, $provider, $response);
  }

  private function loadProfilePicture(PhabricatorExternalAccount $account) {
    $phid = $account->getProfileImagePHID();
    if (!$phid) {
      return null;
    }

    // NOTE: Use of omnipotent user is okay here because the registering user
    // can not control the field value, and we can't use their user object to
    // do meaningful policy checks anyway since they have not registered yet.
    // Reaching this means the user holds the account secret key and the
    // registration secret key, and thus has permission to view the image.

    $file = id(new PhabricatorFileQuery())
      ->setViewer(PhabricatorUser::getOmnipotentUser())
      ->withPHIDs(array($phid))
      ->executeOne();
    if (!$file) {
      return null;
    }

    $xform = PhabricatorFileTransform::getTransformByKey(
      PhabricatorFileThumbnailTransform::TRANSFORM_PROFILE);
    return $xform->executeTransform($file);
  }

  protected function renderError($message) {
    return $this->renderErrorPage(
      pht('Đăng ký thất bại'),
      array($message));
  }

  private function sendWaitingForApprovalEmail(PhabricatorUser $user) {
    $title = '[Phabricator] '.pht(
      'Người dùng mới "% s" Đang chờ phê duyệt',
      $user->getUsername());

    $body = new PhabricatorMetaMTAMailBody();

    $body->addRawSection(
      pht(
        'Đăng ký mới dùng "% s" đang chờ phê duyệt tài khoản bằng một '.
        'quản trị.',
        $user->getUsername()));

    $body->addLinkSection(
      pht('PHÊ DUYỆT HÀNG ĐỢI'),
      PhabricatorEnv::getProductionURI(
        '/people/query/approval/'));

    $body->addLinkSection(
      pht('ẨN HÀNG ĐỢI PHÊ DUYỆT'),
      PhabricatorEnv::getProductionURI(
        '/config/edit/auth.require-approval/'));

    $admins = id(new PhabricatorPeopleQuery())
      ->setViewer(PhabricatorUser::getOmnipotentUser())
      ->withIsAdmin(true)
      ->execute();

    if (!$admins) {
      return;
    }

    $mail = id(new PhabricatorMetaMTAMail())
      ->addTos(mpull($admins, 'getPHID'))
      ->setSubject($title)
      ->setBody($body->render())
      ->saveAndSend();
  }

}
