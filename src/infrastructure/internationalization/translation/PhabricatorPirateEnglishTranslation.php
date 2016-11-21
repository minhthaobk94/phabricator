<?php

final class PhabricatorPirateEnglishTranslation
  extends PhutilTranslation {

  public function getLocaleCode() {
    return 'en_P*';
  }

  protected function getTranslations() {
    return array(
      'Search' => 'Tìm kiếm',
      'Review Code' => 'Xem xét code',
      'Tasks and Bugs' => 'Công việc và lỗi',
      'Cancel' => 'Hủy',
      'Advanced Search' => 'Tìm kiếm nâng cao',
      'No search results.' => 'Không tìm thấy kết quả nào!.',
      'Send' => 'Gửi',
      'Partial' => 'Thành phần',
      'Upload' => 'Tải lên',
      'Partial Upload' => 'Tải lên một phần',
      'Submit' => 'Xác nhận!',
      'Create' => 'Tạo mới',
      'Okay' => 'Được rồi!',
      'Edit Query' => 'Sửa truy vấn',
      'Hide Query' => 'Ânr truy vấn',
      'Execute Query' => 'Thực thi lệnh truy vấn',
      'Wiki' => 'Wiki',
      'Blog' => 'Blog',
      'Add Action...' => 'Thêm hành động',
      'Change Subscribers' => 'Thay đổi người đăng ký',
      'Change Projects' => 'Thay đổi dự án',
      'Change Priority' => 'Tha đổi ưu tiên',
      'Change Status' => 'Thay đổi trạng thái',
      'Assign / Claim' => 'Chỉ định/Yêu cầu',
      'Prototype' => 'Nguyên mẫu',
      'Continue' => 'Tiếp tục',
      'Recent Activity' => 'Hoạt động gần đây',
      'Browse and Audit Commits' => 'Duyệt và kiểm tra commits',
      'Upcoming Events' => 'Sự kiện sắp tới',
      'Get Organized' => 'Nhận sắp đặt',
      'Host and Browse Repositories' => 'Lưu trữ và Duyệt Repositories',
      'Chat with Others' => 'Trò chuyện với người khác',
      'Review Recent Activity' => 'Hoạt động xem xét gần đây',
      'Comment' => 'Bình luận',
      'Actions' => 'Hành động',
      'Title' => 'Tiêu đề',
      'Assigned To' => 'Phân công',
      'Status' => 'Trạng thái',
      'Priority' => 'Ưu tiên',
      'Description' => 'Mô tả',
      'Visible To' => 'Thấy bởi',
      'Editable By' => 'Được sửa bởi',
      'Subscribers' => 'Người đăng ký',
      'Projects' => 'Các dự án',
      '%s added a comment.' => '%s Thêm bình luận.',
      '%s edited the task description.' =>
        'Sửa %s mô tả công việc.',
      '%s claimed this task.' =>
        'Yêu cầu %s công việc.',
      '%s created this task.' =>
        'Được tạo bởi  %s công việc này.',
    );
  }
}
