<?php

final class PhabricatorRemarkupUIExample extends PhabricatorUIExample {

  public function getName() {
    return pht('Đánh dấu ');
  }

  public function getDescription() {
    return pht(
      'Thể hiện sự xuất hiện trực quan của các phần tử đánh dấu khác nhau.');
  }

  public function renderExample() {
    $viewer = $this->getRequest()->getUser();

    $content = pht(<<<EOCONTENT
Đây là vài **nội dung đánh dấu** sử dụng ~~chính xác một kiểu~~ //nhiều kiểu//.

  - Trái cây
    - Táo
    - Chuối
    - Dâu tây
  - Rau
    1. Cà rốt
    2. Cần tây

GHI CHÚ: Đây là một ghi chú.

(GHI CHÚ) Đây là một ghi chú.

CẢNH BÁO: Đây là một cảnh báo.

(CẢNH BÁO): Đây cũng là một cảnh báo.

QUAN TRỌNG: Đây thực sự không quan trọng lắm.

(QUAN TRỌNG) Đây cũng không quan trọng.

EOCONTENT
);

    $remarkup = new PHUIRemarkupView($viewer, $content);

    $frame = id(new PHUIBoxView())
      ->addPadding(PHUI::PADDING_LARGE)
      ->appendChild($remarkup);

    return id(new PHUIObjectBoxView())
      ->setHeaderText(pht('Ví dụ đánh dấu'))
      ->appendChild($frame);
  }

}
