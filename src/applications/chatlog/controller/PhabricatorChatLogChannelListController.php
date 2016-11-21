<?php

final class PhabricatorChatLogChannelListController
  extends PhabricatorChatLogController {

  public function shouldAllowPublic() {
    return true;
  }

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();

    $channels = id(new PhabricatorChatLogChannelQuery())
      ->setViewer($viewer)
      ->execute();

    $list = new PHUIObjectItemListView();
    foreach ($channels as $channel) {
        $item = id(new PHUIObjectItemView())
          ->setHeader($channel->getChannelName())
          ->setHref('/chatlog/channel/'.$channel->getID().'/')
          ->addAttribute($channel->getServiceName())
          ->addAttribute($channel->getServiceType());
        $list->addItem($item);
    }

    $crumbs = $this
      ->buildApplicationCrumbs()
      ->addTextCrumb(pht('Danh sách kênh'), $this->getApplicationURI());

    $box = id(new PHUIObjectBoxView())
      ->setHeaderText('Danh sách kênh')
      ->setObjectList($list);

    return $this->newPage()
      ->setTitle(pht('Danh sách kênh'))
      ->setCrumbs($crumbs)
      ->appendChild($box);

  }
}
