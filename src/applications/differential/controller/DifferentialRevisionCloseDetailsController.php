<?php

final class DifferentialRevisionCloseDetailsController
  extends DifferentialController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $this->getViewer();

    $xaction = id(new PhabricatorObjectQuery())
      ->withPHIDs(array($request->getURIData('phid')))
      ->setViewer($viewer)
      ->executeOne();
    if (!$xaction) {
      return new Aphront404Response();
    }

    $obj_phid = $xaction->getObjectPHID();
    $obj_handle = id(new PhabricatorHandleQuery())
      ->setViewer($viewer)
      ->withPHIDs(array($obj_phid))
      ->executeOne();

    $body = $this->getRevisionMatchExplanation(
      $xaction->getMetadataValue('revisionMatchData'),
      $obj_handle);

    $dialog = id(new AphrontDialogView())
      ->setUser($viewer)
      ->setTitle(pht('Commit bản giải thích gần đây'))
      ->appendParagraph($body)
      ->addCancelButton($obj_handle->getURI());

    return id(new AphrontDialogResponse())->setDialog($dialog);
  }

  private function getRevisionMatchExplanation(
    $revision_match_data,
    PhabricatorObjectHandle $obj_handle) {

    if (!$revision_match_data) {
      return pht(
        'Commit này được tạo ra trước khi chức năng này được xây dựng '.
        'Do đó, thông tin này không dùng được.');
    }

    $body_why = array();
    if ($revision_match_data['usedURI']) {
      return pht(
        'Chúng tôi đã tìm thấy trường "%s" với giá trị "%s" trên tin nhắn commit, '.
        'và tên miền trên URI khớp với bản cài đặt này, cho nên '.
        'chúng tôi dẫn commit này tới %s.',
        'Differential Revision',
        $revision_match_data['foundURI'],
        phutil_tag(
          'a',
          array(
            'href' => $obj_handle->getURI(),
          ),
          $obj_handle->getName()));
    } else if ($revision_match_data['foundURI']) {
      $body_why[] = pht(
        'Chúng tôi đã tìm thấy trường "%s" với giá trị "%s" trên tin nhắn commit, '.
        'nhưng tên miền trên URI không khớp với tên miền được cấu hình cho bản cài đặt này,'.
        '"%s", cho nên chúng tôi bỏ qua nó dưới giả thuyết rằng '.
        'Nó liên quan tới bản sửa đổi của bên thứ 3.',
        'Differential Revision',
        $revision_match_data['foundURI'],
        $revision_match_data['validDomain']);
    } else {
      $body_why[] = pht(
        'Chúng tôi không tìm thấy trường "%s" trên tin nhắn commit.',
        'Differential Revision');
    }

    switch ($revision_match_data['matchHashType']) {
      case ArcanistDifferentialRevisionHash::HASH_GIT_TREE:
        $hash_info = true;
        $hash_type = 'tree';
        break;
      case ArcanistDifferentialRevisionHash::HASH_GIT_COMMIT:
      case ArcanistDifferentialRevisionHash::HASH_MERCURIAL_COMMIT:
        $hash_info = true;
        $hash_type = 'commit';
        break;
      default:
        $hash_info = false;
        break;
    }
    if ($hash_info) {
      $diff_link = phutil_tag(
        'a',
        array(
          'href' => $obj_handle->getURI(),
        ),
        $obj_handle->getName());
      $body_why[] = pht(
        'Commit này và điểm khác biệt đã kích hoạt của %s giống với %s hàm băm '.
        '(%s) cho nên chúng tôi dẫn commit này tới %s.',
        $diff_link,
        $hash_type,
        $revision_match_data['matchHashValue'],
        $diff_link);
    }

    return phutil_implode_html("\n", $body_why);

  }
}
