<?php

final class PhabricatorMetaMTAActor extends Phobject {

  const STATUS_DELIVERABLE = 'deliverable';
  const STATUS_UNDELIVERABLE = 'undeliverable';

  const REASON_NONE = 'none';
  const REASON_UNLOADABLE = 'unloadable';
  const REASON_UNMAILABLE = 'unmailable';
  const REASON_NO_ADDRESS = 'noaddress';
  const REASON_DISABLED = 'disabled';
  const REASON_MAIL_DISABLED = 'maildisabled';
  const REASON_EXTERNAL_TYPE = 'exernaltype';
  const REASON_RESPONSE = 'response';
  const REASON_SELF = 'self';
  const REASON_MAILTAGS = 'mailtags';
  const REASON_BOT = 'bot';
  const REASON_FORCE = 'force';
  const REASON_FORCE_HERALD = 'force-herald';
  const REASON_ROUTE_AS_NOTIFICATION = 'route-as-notification';
  const REASON_ROUTE_AS_MAIL = 'route-as-mail';

  private $phid;
  private $emailAddress;
  private $name;
  private $status = self::STATUS_DELIVERABLE;
  private $reasons = array();

  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  public function getName() {
    return $this->name;
  }

  public function setEmailAddress($email_address) {
    $this->emailAddress = $email_address;
    return $this;
  }

  public function getEmailAddress() {
    return $this->emailAddress;
  }

  public function setPHID($phid) {
    $this->phid = $phid;
    return $this;
  }

  public function getPHID() {
    return $this->phid;
  }

  public function setUndeliverable($reason) {
    $this->reasons[] = $reason;
    $this->status = self::STATUS_UNDELIVERABLE;
    return $this;
  }

  public function setDeliverable($reason) {
    $this->reasons[] = $reason;
    $this->status = self::STATUS_DELIVERABLE;
    return $this;
  }

  public function isDeliverable() {
    return ($this->status === self::STATUS_DELIVERABLE);
  }

  public function getDeliverabilityReasons() {
    return $this->reasons;
  }

  public static function isDeliveryReason($reason) {
    switch ($reason) {
      case self::REASON_NONE:
      case self::REASON_FORCE:
      case self::REASON_FORCE_HERALD:
      case self::REASON_ROUTE_AS_MAIL:
        return true;
      default:
        // All other reasons cause the message to not be delivered.
        return false;
    }
  }

  public static function getReasonName($reason) {
    $names = array(
      self::REASON_NONE => pht('Trống'),
      self::REASON_DISABLED => pht('ẩn người nhận'),
      self::REASON_BOT => pht('Nhan Bot'),
      self::REASON_NO_ADDRESS => pht('Không địa chỉ'),
      self::REASON_EXTERNAL_TYPE => pht('Người nhận bên ngoài'),
      self::REASON_UNMAILABLE => pht('Không thể gửi thư'),
      self::REASON_RESPONSE => pht('Trả lời tương tự'),
      self::REASON_SELF => pht('Tự thư'),
      self::REASON_MAIL_DISABLED => pht('Ân mail'),
      self::REASON_MAILTAGS => pht('Mail Tags'),
      self::REASON_UNLOADABLE => pht('Nhận được Bad'),
      self::REASON_FORCE => pht('Mail bắc buộc'),
      self::REASON_FORCE_HERALD => pht('Bắc buộc bởi Herald'),
      self::REASON_ROUTE_AS_NOTIFICATION => pht('Route như thông báo'),
      self::REASON_ROUTE_AS_MAIL => pht('Route nhw Mail'),
    );

    return idx($names, $reason, pht('Không biết ("%s")', $reason));
  }

  public static function getReasonDescription($reason) {
    $descriptions = array(
      self::REASON_NONE => pht(
        'No special rules affected this mail.'),
      self::REASON_DISABLED => pht(
        'This user is disabled; disabled users do not receive mail.'),
      self::REASON_BOT => pht(
        'This user is a bot; bot accounts do not receive mail.'),
      self::REASON_NO_ADDRESS => pht(
        'Unable to load an email address for this PHID.'),
      self::REASON_EXTERNAL_TYPE => pht(
        'Only external accounts of type "email" are deliverable; this '.
        'account has a different type.'),
      self::REASON_UNMAILABLE => pht(
        'This PHID type does not correspond to a mailable object.'),
      self::REASON_RESPONSE => pht(
        'This message is a response to another email message, and this '.
        'recipient received the original email message, so we are not '.
        'sending them this substantially similar message (for example, '.
        'the sender used "Reply All" instead of "Reply" in response to '.
        'mail from Phabricator).'),
      self::REASON_SELF => pht(
        'This recipient is the user whose actions caused delivery of '.
        'this message, but they have set preferences so they do not '.
        'receive mail about their own actions (Settings > Email '.
        'Preferences > Self Actions).'),
      self::REASON_MAIL_DISABLED => pht(
        'This recipient has disabled all email notifications '.
        '(Settings > Email Preferences > Email Notifications).'),
      self::REASON_MAILTAGS => pht(
        'This mail has tags which control which users receive it, and '.
        'this recipient has not elected to receive mail with any of '.
        'the tags on this message (Settings > Email Preferences).'),
      self::REASON_UNLOADABLE => pht(
        'Unable to load user record for this PHID.'),
      self::REASON_FORCE => pht(
        'Delivery of this mail is forced and ignores deliver preferences. '.
        'Mail which uses forced delivery is usually related to account '.
        'management or authentication. For example, password reset email '.
        'ignores mail preferences.'),
      self::REASON_FORCE_HERALD => pht(
        'This recipient was added by a "Send me an Email" rule in Herald, '.
        'which overrides some delivery settings.'),
      self::REASON_ROUTE_AS_NOTIFICATION => pht(
        'This message was downgraded to a notification by outbound mail '.
        'rules in Herald.'),
      self::REASON_ROUTE_AS_MAIL => pht(
        'This message was upgraded to email by outbound mail rules '.
        'in Herald.'),
    );

    return idx($descriptions, $reason, pht('Không hiểu lý do ("%s")', $reason));
  }


}
