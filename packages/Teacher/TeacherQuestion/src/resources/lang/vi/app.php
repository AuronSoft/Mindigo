<?php

return [
    'title' => 'Ngân hàng câu hỏi ',
    'subtitle' => 'Soạn và quản lý ngân hàng câu hỏi cá nhân.',
    'create' => 'Thêm câu hỏi',
    'edit' => 'Chỉnh sửa',
    'back' => 'Quay lại',
    'save' => 'Lưu câu hỏi',

    // Stats
    'stat_total' => 'Tổng câu hỏi',
    'stat_approved' => 'Đã duyệt',
    'stat_reviewing' => 'Chờ duyệt',
    'stat_draft' => 'Bản nháp',

    // Status
    'approved' => 'Đã duyệt',
    'reviewing' => 'Chờ duyệt',
    'draft' => 'Bản nháp',
    'rejected' => 'Từ chối',

    // Difficulty
    'easy' => 'Dễ',
    'medium' => 'Trung bình',
    'hard' => 'Khó',

    // Question types
    'single_choice' => 'Một đáp án',
    'multiple_choice' => 'Nhiều đáp án',
    'true_false' => 'Đúng/Sai',
    'short_answer' => 'Điền vào chỗ trống',
    'essay' => 'Tự luận',

    // Actions
    'submit_review' => 'Gửi duyệt',
    'submit_confirm' => 'Gửi câu hỏi để admin duyệt? Sau khi gửi bạn không thể chỉnh sửa.',
    'submit_title' => 'Gửi câu hỏi duyệt',
    'delete' => 'Xóa câu hỏi',
    'delete_title' => 'Xóa câu hỏi',
    'delete_confirm' => 'Bạn có chắc muốn xóa câu hỏi này?',
    'cancel' => 'Hủy',

    // Filters
    'all_types' => 'Tất cả loại',
    'all_difficulties' => 'Tất cả độ khó',
    'all_statuses' => 'Tất cả trạng thái',
    'all_folders' => 'Tất cả thư mục',
    'search_ph' => 'Tìm theo nội dung, môn học...',

    // Detail
    'question_content' => 'Nội dung câu hỏi',
    'correct_answers' => 'Đáp án đúng',
    'explanation' => 'Giải thích',
    'meta' => 'Thông tin',
    'col_type' => 'Loại',
    'col_subject' => 'Môn học',
    'col_difficulty' => 'Độ khó',
    'col_status' => 'Trạng thái',
    'col_folder' => 'Thư mục',
    'no_folder' => 'Chưa có thư mục',
    'no_explanation' => 'Chưa có giải thích',
    'no_tags' => 'Chưa có tag',

    // Empty
    'empty_title' => 'Bạn chưa có câu hỏi nào',
    'empty_desc' => 'Thêm câu hỏi đầu tiên vào ngân hàng của bạn.',

    // Import
    'import' => 'Nhập từ file',
    'import_title' => 'Nhập câu hỏi hàng loạt',
    'import_subtitle' => 'Tải lên file CSV, TXT hoặc JSON để thêm nhiều câu hỏi cùng lúc.',
    'import_file_desc' => 'Kéo thả hoặc nhấn để chọn file .csv / .txt / .json / .docx — tối đa 10 MB', // chỉnh
    'import_folder' => 'Thư mục đích',
    'import_folder_ph' => 'Lấy từ file (mặc định)',
    'import_status' => 'Trạng thái sau khi nhập',
    'import_draft' => 'Bản nháp — kiểm tra trước khi gửi',
    'import_reviewing' => 'Gửi duyệt ngay',
    'import_submit' => 'Nhập câu hỏi',
    'import_format' => 'Cột CSV bắt buộc',
    'imported' => 'Đã nhập thành công :count câu hỏi.',

    // Import format table
    'fmt_col' => 'Cột',
    'fmt_required_hd' => 'Bắt buộc',
    'fmt_example_hd' => 'Giá trị ví dụ',
    'fmt_badge_req' => 'Bắt buộc',
    'fmt_badge_opt' => 'Tùy chọn',
    'fmt_file_types' => 'CSV · TXT · JSON · DOCX', // chỉnh
    'fmt_docx_note' => 'File .docx cần theo đúng template: "Câu N: nội dung", "A. đáp án", "Đáp án: A"', // thêm
    'fmt_separator' => 'Dùng dấu | để tách nhiều giá trị trong một cột. File JSON có thể là mảng hoặc object với key questions.',
    'ex_content' => 'Câu hỏi của bạn ở đây',
    'ex_subject' => 'Toán, Văn, Anh...',
    'ex_topic' => 'Chủ đề cụ thể',
    'ex_options' => 'A: Đáp án 1 | B: Đáp án 2 | C: Đáp án 3',
    'ex_answers' => 'A (single) hoặc A,B (multiple)',
    'ex_explanation' => 'Giải thích đáp án đúng',
    'ex_folder' => 'Tên thư mục (tạo mới nếu chưa có)',
    'ex_tags' => 'tag1 | tag2 | tag3',

    // Messages
    'created' => 'Đã tạo câu hỏi.',
    'updated' => 'Đã cập nhật câu hỏi.',
    'submitted' => 'Đã gửi câu hỏi để duyệt.',
    'deleted' => 'Đã xóa câu hỏi.',
];
