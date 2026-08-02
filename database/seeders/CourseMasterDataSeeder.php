<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherCourse\Models\CourseCategory;

class CourseMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Toán học', 'code' => 'MATH', 'color' => 'green'],
            ['name' => 'Ngữ văn', 'code' => 'LIT', 'color' => 'rose'],
            ['name' => 'Tiếng Anh', 'code' => 'ENG', 'color' => 'sky'],
            ['name' => 'Vật lý', 'code' => 'PHY', 'color' => 'amber'],
            ['name' => 'Hóa học', 'code' => 'CHEM', 'color' => 'green'],
            ['name' => 'Sinh học', 'code' => 'BIO', 'color' => 'green'],
            ['name' => 'Lịch sử', 'code' => 'HIS', 'color' => 'amber'],
            ['name' => 'Địa lý', 'code' => 'GEO', 'color' => 'sky'],
            ['name' => 'Tin học', 'code' => 'ICT', 'color' => 'slate'],
            ['name' => 'Giáo dục công dân', 'code' => 'CIV', 'color' => 'rose'],
            ['name' => 'Giáo dục kinh tế và pháp luật', 'code' => 'ECL', 'color' => 'amber'],
            ['name' => 'Công nghệ', 'code' => 'TECH', 'color' => 'slate'],
            ['name' => 'Giáo dục thể chất', 'code' => 'PE', 'color' => 'green'],
            ['name' => 'Âm nhạc', 'code' => 'MUS', 'color' => 'rose'],
            ['name' => 'Mỹ thuật', 'code' => 'ART', 'color' => 'rose'],
            ['name' => 'Khoa học tự nhiên', 'code' => 'SCI', 'color' => 'green'],
            ['name' => 'Khoa học xã hội', 'code' => 'SOC', 'color' => 'amber'],
            ['name' => 'Tiếng Trung', 'code' => 'ZHO', 'color' => 'rose'],
            ['name' => 'Tiếng Nhật', 'code' => 'JPN', 'color' => 'rose'],
            ['name' => 'Tiếng Hàn', 'code' => 'KOR', 'color' => 'sky'],
            ['name' => 'Tiếng Pháp', 'code' => 'FRA', 'color' => 'sky'],
            ['name' => 'Tiếng Đức', 'code' => 'DEU', 'color' => 'amber'],
            ['name' => 'Lập trình C/C++', 'code' => 'CPP', 'color' => 'slate'],
            ['name' => 'Lập trình Java', 'code' => 'JAVA', 'color' => 'amber'],
            ['name' => 'Lập trình Python', 'code' => 'PY', 'color' => 'sky'],
            ['name' => 'Lập trình JavaScript', 'code' => 'JS', 'color' => 'amber'],
            ['name' => 'Lập trình PHP', 'code' => 'PHP', 'color' => 'slate'],
            ['name' => 'Lập trình C# và .NET', 'code' => 'DOTNET', 'color' => 'sky'],
            ['name' => 'Phát triển Web', 'code' => 'WEB', 'color' => 'green'],
            ['name' => 'Phát triển ứng dụng di động', 'code' => 'MOBILE', 'color' => 'green'],
            ['name' => 'Cấu trúc dữ liệu và giải thuật', 'code' => 'DSA', 'color' => 'slate'],
            ['name' => 'Cơ sở dữ liệu', 'code' => 'DB', 'color' => 'sky'],
            ['name' => 'Trí tuệ nhân tạo', 'code' => 'AI', 'color' => 'rose'],
            ['name' => 'Khoa học dữ liệu', 'code' => 'DS', 'color' => 'sky'],
            ['name' => 'An toàn thông tin', 'code' => 'SEC', 'color' => 'rose'],
            ['name' => 'Mạng máy tính', 'code' => 'NET', 'color' => 'green'],
            ['name' => 'Điện toán đám mây', 'code' => 'CLOUD', 'color' => 'sky'],
            ['name' => 'Kỹ thuật phần mềm', 'code' => 'SE', 'color' => 'slate'],
            ['name' => 'Hệ điều hành', 'code' => 'OS', 'color' => 'slate'],
            ['name' => 'Thiết kế UI/UX', 'code' => 'UIUX', 'color' => 'rose'],
            ['name' => 'Marketing', 'code' => 'MKT', 'color' => 'amber'],
            ['name' => 'Digital Marketing', 'code' => 'DMKT', 'color' => 'sky'],
            ['name' => 'Quản trị kinh doanh', 'code' => 'BA', 'color' => 'slate'],
            ['name' => 'Kinh tế học', 'code' => 'ECON', 'color' => 'amber'],
            ['name' => 'Tài chính', 'code' => 'FIN', 'color' => 'green'],
            ['name' => 'Kế toán', 'code' => 'ACC', 'color' => 'green'],
            ['name' => 'Thương mại điện tử', 'code' => 'ECOM', 'color' => 'sky'],
            ['name' => 'Quản trị nhân sự', 'code' => 'HRM', 'color' => 'rose'],
            ['name' => 'Luật học', 'code' => 'LAW', 'color' => 'slate'],
            ['name' => 'Tâm lý học', 'code' => 'PSY', 'color' => 'rose'],
            ['name' => 'Xã hội học', 'code' => 'SOCI', 'color' => 'amber'],
            ['name' => 'Y học cơ sở', 'code' => 'MED', 'color' => 'rose'],
            ['name' => 'Dược học', 'code' => 'PHA', 'color' => 'green'],
            ['name' => 'Điều dưỡng', 'code' => 'NUR', 'color' => 'sky'],
            ['name' => 'Kỹ thuật điện', 'code' => 'EE', 'color' => 'amber'],
            ['name' => 'Kỹ thuật điện tử', 'code' => 'ECE', 'color' => 'green'],
            ['name' => 'Kỹ thuật cơ khí', 'code' => 'ME', 'color' => 'slate'],
            ['name' => 'Kỹ thuật xây dựng', 'code' => 'CE', 'color' => 'amber'],
            ['name' => 'Kiến trúc', 'code' => 'ARCH', 'color' => 'rose'],
            ['name' => 'Logistics và chuỗi cung ứng', 'code' => 'LOG', 'color' => 'sky'],
        ];

        foreach ($subjects as $order => $subject) {
            $model = Subject::withTrashed()->updateOrCreate(['code' => $subject['code']], [
                ...$subject,
                'slug' => Str::slug($subject['name']),
                'status' => 'active',
                'sort_order' => $order + 1,
            ]);
            if ($model->trashed()) {
                $model->restore();
            }
        }

        $categories = [
            'Kiến thức nền tảng',
            'Luyện thi và kiểm tra',
            'Kỹ năng học tập',
            'Ngoại ngữ',
            'Công nghệ và lập trình',
            'Kỹ năng nghề nghiệp',
            'Toán và tư duy logic',
            'Khoa học tự nhiên',
            'Khoa học xã hội và nhân văn',
            'Ngôn ngữ và giao tiếp',
            'Lập trình cơ bản',
            'Phát triển phần mềm',
            'Phát triển Web',
            'Phát triển ứng dụng di động',
            'Dữ liệu và cơ sở dữ liệu',
            'Trí tuệ nhân tạo và Machine Learning',
            'An toàn thông tin',
            'Mạng và hệ thống',
            'Cloud và DevOps',
            'Thiết kế và trải nghiệm người dùng',
            'Kinh doanh và quản trị',
            'Tài chính và đầu tư',
            'Kế toán và kiểm toán',
            'Marketing và truyền thông',
            'Thương mại điện tử',
            'Luật và chính sách công',
            'Tâm lý và phát triển cá nhân',
            'Y tế và chăm sóc sức khỏe',
            'Kỹ thuật điện và điện tử',
            'Cơ khí và tự động hóa',
            'Xây dựng và kiến trúc',
            'Logistics và vận hành',
            'Nghệ thuật và sáng tạo',
            'Âm nhạc và biểu diễn',
            'Thể thao và sức khỏe',
            'Giáo dục tiểu học',
            'Giáo dục THCS',
            'Giáo dục THPT',
            'Giáo dục đại học',
            'Luyện thi vào lớp 10',
            'Luyện thi tốt nghiệp THPT',
            'Luyện thi đại học',
            'Chứng chỉ ngoại ngữ',
            'Chứng chỉ CNTT',
            'Tin học văn phòng',
            'Kỹ năng nghiên cứu',
            'Kỹ năng thuyết trình',
            'Kỹ năng viết học thuật',
            'Quản lý thời gian',
            'Khởi nghiệp và đổi mới sáng tạo',
            'Quản lý dự án',
            'Phân tích nghiệp vụ',
            'Khoa học dữ liệu ứng dụng',
            'Thiết kế đồ họa',
            'Nhiếp ảnh và dựng phim',
            'Nông nghiệp và môi trường',
            'Du lịch và khách sạn',
            'Kỹ năng sống',
            'Định hướng nghề nghiệp',
        ];

        foreach ($categories as $order => $name) {
            CourseCategory::query()->updateOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'is_active' => true,
                'sort_order' => $order + 1,
            ]);
        }
    }
}
