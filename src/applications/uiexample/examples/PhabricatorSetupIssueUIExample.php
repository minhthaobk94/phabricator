<?php

final class PhabricatorSetupIssueUIExample extends PhabricatorUIExample {

  public function getName() {
    return pht('Cài đặt vấn đề');
  }

  public function getDescription() {
    return pht('Cài đặt lỗi và cảnh báo.');
  }

  public function renderExample() {
    $request = $this->getRequest();
    $user = $request->getUser();

    $issue = id(new PhabricatorSetupIssue())
      ->setShortName(pht('Tên ngắn'))
      ->setName(pht('Tên'))
      ->setSummary(pht('Ghi chú'))
      ->setMessage(pht('Thông điệp'))
      ->setIssueKey('example.key')
      ->addCommand('$ # Thêm Command')
      ->addCommand(hsprintf('<tt>$</tt> %s', '$ ls -1 > /dev/null'))
      ->addPHPConfig('php.config.example')
      ->addPhabricatorConfig('test.value')
      ->addPHPExtension('libexample');

    // NOTE: Since setup issues may be rendered before we can build the page
    // chrome, they don't explicitly include resources.
    require_celerity_resource('setup-issue-css');

    $view = id(new PhabricatorSetupIssueView())
      ->setIssue($issue);

    return $view;
  }
}
