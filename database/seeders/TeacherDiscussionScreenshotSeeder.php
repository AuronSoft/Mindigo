<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\ClassroomManagement\Models\ClassroomStudent;
use Mindigo\TeacherDiscussion\Models\DiscussionAttachment;
use Mindigo\TeacherDiscussion\Models\DiscussionMessage;
use Mindigo\TeacherDiscussion\Models\DiscussionThread;

class TeacherDiscussionScreenshotSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            DiscussionAttachment::query()->delete();
            DiscussionMessage::withTrashed()->forceDelete();
            DiscussionThread::withTrashed()->forceDelete();

            Storage::disk('public')->deleteDirectory('teacher-discussions/demo');
            Storage::disk('public')->makeDirectory('teacher-discussions/demo');

            $teacher = User::updateOrCreate(
                ['email' => 'teacher@mindigo.com'],
                [
                    'name' => 'Nguyễn Văn Giáo',
                    'password' => Hash::make('123456'),
                    'role' => 'teacher',
                    'gender' => 'male',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );

            $classroom = Classroom::withTrashed()
                ->where('code', '12A3-THPT')
                ->orWhere('slug', 'lop-12a3-luyen-thi-thpt')
                ->first() ?? new Classroom();

            $classroom->forceFill([
                'created_by' => $teacher->id,
                'teacher_id' => $teacher->id,
                'name' => 'Lớp 12A3 - Luyện thi THPT',
                'code' => '12A3-THPT',
                'slug' => 'lop-12a3-luyen-thi-thpt',
                'school_year' => '2025-2026',
                'description' => 'Lớp demo dùng để chụp màn hình trao đổi nội bộ.',
                'status' => 'active',
                'deleted_at' => null,
            ])->save();

            $students = collect([
                ['name' => 'Nguyễn Minh Anh', 'email' => 'minhanh.12a3@mindigo.test'],
                ['name' => 'Trần Bảo', 'email' => 'baotran.12a3@mindigo.test'],
                ['name' => 'Phạm Linh', 'email' => 'linhpham.12a3@mindigo.test'],
                ['name' => 'Lê Quang Huy', 'email' => 'quanghuy.12a3@mindigo.test'],
                ['name' => 'Đỗ Thu Hà', 'email' => 'thuha.12a3@mindigo.test'],
            ])->map(function (array $student) {
                return User::updateOrCreate(
                    ['email' => $student['email']],
                    [
                        'name' => $student['name'],
                        'password' => Hash::make('123456'),
                        'role' => 'student',
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ],
                );
            });

            ClassroomStudent::query()->where('classroom_id', $classroom->id)->delete();

            $students->each(function (User $student) use ($classroom): void {
                ClassroomStudent::updateOrCreate(
                    ['classroom_id' => $classroom->id, 'student_id' => $student->id],
                    ['status' => 'active', 'joined_at' => now()->subMonths(4)],
                );
            });

            $thread = DiscussionThread::create([
                'teacher_id' => $teacher->id,
                'classroom_id' => $classroom->id,
                'last_message_at' => now()->subMinutes(4),
            ]);

            $messages = [
                [$students[0], 'Thầy ơi, hôm nay lớp mình ôn phần nào trước khi làm đề tổng hợp ạ?', -55, []],
                [$teacher, 'Cả lớp tập trung ôn hàm số, số phức và đọc hiểu. Thầy gửi tài liệu trong khung chat này nhé.', -49, [
                    ['file' => 'De_cuong_on_tap_THPT_2026.pdf', 'mime' => 'application/pdf', 'size' => 658432, 'content' => "Mindigo LMS demo PDF\nDe cuong on tap THPT 2026\n"],
                ]],
                [$students[1], 'Em đã xem tài liệu rồi ạ. Phần hàm số có cần làm thêm bài 4 và 5 không thầy?', -42, []],
                [$teacher, 'Có nhé. Bài 4 và 5 là phần bắt buộc. Link bài giảng: https://mindigo.local/lesson/on-tap-ham-so', -35, []],
                [$students[2], 'Nhóm em đã chia xong phần thuyết trình, thầy xem giúp bảng phân công ạ.', -27, [
                    ['file' => 'Bang_phan_cong_nhom.xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'size' => 248832, 'content' => "Student,Task\nMinh Anh,Ham so\nBao Tran,So phuc\n"],
                ]],
                [$teacher, 'Ổn rồi, chỉ cần bổ sung kết luận ở slide cuối. Ảnh minh họa trọng tâm thầy để bên dưới.', -18, [
                    ['file' => 'So_do_tu_duy_on_tap.png', 'mime' => 'image/png', 'size' => 2627095, 'source' => public_path('image/Teacher2.png')],
                ]],
                [$students[3], 'Dạ rõ thầy. Tối nay nhóm em nộp bản hoàn thiện trước 21:00 ạ.', -11, []],
                [$teacher, 'Tốt. Cả lớp nhớ vào phòng live lúc 20:30, thầy sẽ giải nhanh các câu hay sai trước khi làm đề.', -4, []],
            ];

            foreach ($messages as [$sender, $body, $minutes, $attachments]) {
                $createdAt = now()->addMinutes($minutes);
                $message = DiscussionMessage::create([
                    'thread_id' => $thread->id,
                    'sender_id' => $sender->id,
                    'body' => $body,
                    'read_at' => $sender->id === $teacher->id ? now() : null,
                ]);
                $message->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

                foreach ($attachments as $attachment) {
                    $safeName = Str::slug(pathinfo($attachment['file'], PATHINFO_FILENAME));
                    $extension = pathinfo($attachment['file'], PATHINFO_EXTENSION);
                    $path = 'teacher-discussions/demo/' . $message->id . '-' . $safeName . '.' . $extension;

                    if (isset($attachment['source']) && is_file($attachment['source'])) {
                        Storage::disk('public')->put($path, file_get_contents($attachment['source']));
                        $size = filesize($attachment['source']) ?: $attachment['size'];
                    } else {
                        Storage::disk('public')->put($path, $attachment['content']);
                        $size = $attachment['size'];
                    }

                    DiscussionAttachment::create([
                        'message_id' => $message->id,
                        'disk' => 'public',
                        'path' => $path,
                        'original_name' => $attachment['file'],
                        'mime_type' => $attachment['mime'],
                        'size' => $size,
                    ]);
                }
            }

            $thread->forceFill(['last_message_at' => now()->subMinutes(4)])->save();
        });
    }
}
