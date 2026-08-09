<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\TeacherAssignment\Models\Assignment;
use Mindigo\TeacherAssignment\Models\AssignmentSubmission;
use Illuminate\Support\Collection;

class TeacherAssignmentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::updateOrCreate(
            ['email' => 'teacher@mindigo.com'],
            [
                'name' => 'Nguyen Van Giao',
                'password' => Hash::make('123456'),
                'role' => 'teacher',
                'is_active' => true,
            ]
        );

        $classroom = Classroom::updateOrCreate(
            ['code' => 'ASSIGN-DEMO-12A2'],
            [
                'created_by' => $teacher->id,
                'teacher_id' => $teacher->id,
                'name' => '12A2 - Demo cham bai',
                'slug' => '12a2-demo-cham-bai',
                'school_year' => '2026-2027',
                'description' => 'Lop demo dung de test bai tap, cham bai va ket qua hoc sinh.',
                'status' => 'active',
            ]
        );

        $students = collect([
            ['name' => 'Catherine Sipes IV', 'email' => 'demo.assignment.01@mindigo.test'],
            ['name' => 'Benny Hammes MD', 'email' => 'demo.assignment.02@mindigo.test'],
            ['name' => 'Adele Greenholt', 'email' => 'demo.assignment.03@mindigo.test'],
            ['name' => 'Nguyen Minh Anh', 'email' => 'demo.assignment.04@mindigo.test'],
            ['name' => 'Tran Quoc Bao', 'email' => 'demo.assignment.05@mindigo.test'],
            ['name' => 'Le Thanh Dat', 'email' => 'demo.assignment.06@mindigo.test'],
            ['name' => 'Pham Ha Linh', 'email' => 'demo.assignment.07@mindigo.test'],
            ['name' => 'Hoang Gia Huy', 'email' => 'demo.assignment.08@mindigo.test'],
        ])->map(function (array $student) {
            return User::updateOrCreate(
                ['email' => $student['email']],
                [
                    'name' => $student['name'],
                    'password' => Hash::make('123456'),
                    'role' => 'student',
                    'is_active' => true,
                ]
            );
        })->values();

        $classroom->students()->sync(
            $students->mapWithKeys(fn (User $student) => [
                $student->id => [
                    'status' => 'active',
                    'joined_at' => now()->subDays(20),
                ],
            ])->all()
        );

        $this->seedAssignmentDemo($teacher, $classroom, $students);
        $this->seedResultDemo($teacher, $students);
    }

    private function seedAssignmentDemo(User $teacher, Classroom $classroom, Collection $students): void
    {
        Assignment::withTrashed()
            ->where('teacher_id', $teacher->id)
            ->where('classroom_id', $classroom->id)
            ->get()
            ->each(function (Assignment $assignment): void {
                AssignmentSubmission::where('assignment_id', $assignment->id)->delete();
                $assignment->forceDelete();
            });

        Storage::disk('public')->put(
            'assignments/files/demo-assignment-brief.txt',
            "De bai demo: nop file hoac van ban de giao vien test cham bai.\n"
        );

        $assignment = Assignment::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'classroom_id' => $classroom->id,
                'title' => 'Bai tap demo cham bai',
            ],
            [
                'description' => 'Du lieu mau co hoc sinh chua nop, da nop, nop tre, da cham va da tra bai.',
                'file_path' => ['assignments/files/demo-assignment-brief.txt'],
                'due_date' => now()->addDays(7)->setTime(23, 59),
                'allow_late' => true,
                'late_days' => 3,
                'max_score' => 10,
                'submission_type' => 'both',
                'status' => 'published',
            ]
        );

        AssignmentSubmission::where('assignment_id', $assignment->id)->delete();

        $this->putSubmissionFile('assignments/submissions/demo-minh-anh.txt', 'Bai nop file cua Nguyen Minh Anh.');
        $this->putSubmissionFile('assignments/submissions/demo-quoc-bao.txt', 'Bai nop tre cua Tran Quoc Bao.');
        $this->putSubmissionFile('assignments/submissions/demo-ha-linh.txt', 'Bai da tra cua Pham Ha Linh.');

        $payloads = [
            [
                'student' => $students[3],
                'file_path' => 'assignments/submissions/demo-minh-anh.txt',
                'file_original_name' => 'bai-nop-minh-anh.txt',
                'text_content' => 'Em gui them phan giai thich ngan cho bai lam.',
                'submitted_at' => now()->subDays(1)->setTime(9, 20),
                'is_late' => false,
                'score' => null,
                'feedback' => null,
                'graded_at' => null,
                'status' => 'submitted',
            ],
            [
                'student' => $students[4],
                'file_path' => 'assignments/submissions/demo-quoc-bao.txt',
                'file_original_name' => 'bai-nop-tre-quoc-bao.txt',
                'text_content' => null,
                'submitted_at' => now()->addDays(8)->setTime(8, 15),
                'is_late' => true,
                'score' => null,
                'feedback' => null,
                'graded_at' => null,
                'status' => 'submitted',
            ],
            [
                'student' => $students[5],
                'file_path' => null,
                'file_original_name' => null,
                'text_content' => 'Bai lam dang van ban: em trinh bay cac buoc giai va ket luan cuoi cung.',
                'submitted_at' => now()->subHours(10),
                'is_late' => false,
                'score' => 8.5,
                'feedback' => 'Bai lam ro y, can bo sung vi du o phan cuoi.',
                'graded_at' => now()->subHours(2),
                'status' => 'graded',
            ],
            [
                'student' => $students[6],
                'file_path' => 'assignments/submissions/demo-ha-linh.txt',
                'file_original_name' => 'bai-da-tra-ha-linh.txt',
                'text_content' => 'Em da cap nhat lai phan nhan xet theo yeu cau.',
                'submitted_at' => now()->subDays(2)->setTime(14, 5),
                'is_late' => false,
                'score' => 9,
                'feedback' => 'Da tra bai. Cach trinh bay tot.',
                'graded_at' => now()->subDay(),
                'status' => 'returned',
            ],
        ];

        foreach ($payloads as $payload) {
            $student = $payload['student'];
            unset($payload['student']);

            AssignmentSubmission::create(array_merge($payload, [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ]));
        }
    }

    private function seedResultDemo(User $teacher, Collection $students): void
    {
        $examPayloads = [
            [
                'slug' => 'demo-result-toan-kiem-tra-15p',
                'title' => 'Toan - Kiem tra 15 phut',
                'subject' => 'Toan',
                'topic' => 'Ham so bac nhat',
                'day_offset' => 11,
                'scores' => [92, 76, 64, 88, 55, 81, 97, 43],
            ],
            [
                'slug' => 'demo-result-van-doc-hieu',
                'title' => 'Ngu van - Doc hieu',
                'subject' => 'Ngu van',
                'topic' => 'Doc hieu van ban',
                'day_offset' => 7,
                'scores' => [84, 69, 72, 91, 48, 77, 86, 60],
            ],
            [
                'slug' => 'demo-result-anh-tu-vung',
                'title' => 'Tieng Anh - Tu vung unit 3',
                'subject' => 'Tieng Anh',
                'topic' => 'Vocabulary',
                'day_offset' => 3,
                'scores' => [96, 82, 70, 90, 63, 88, 93, 58],
            ],
            [
                'slug' => 'demo-result-ly-on-tap',
                'title' => 'Vat ly - On tap chuong 2',
                'subject' => 'Vat ly',
                'topic' => 'Chuyen dong',
                'day_offset' => 1,
                'scores' => [89, 74, null, 94, 67, 85, 91, null],
            ],
        ];

        foreach ($examPayloads as $payload) {
            $exam = $this->upsertDemoExam($teacher, $payload);

            ExamAttempt::where('exam_id', $exam->id)
                ->whereIn('user_id', $students->pluck('id'))
                ->delete();

            foreach ($payload['scores'] as $index => $percentage) {
                if ($percentage === null) {
                    continue;
                }

                $submittedAt = now()
                    ->subDays($payload['day_offset'])
                    ->setTime(8 + ($index % 5), 10 + ($index * 4) % 45);

                ExamAttempt::create([
                    'exam_id' => $exam->id,
                    'user_id' => $students[$index]->id,
                    'status' => 'submitted',
                    'started_at' => (clone $submittedAt)->subMinutes(35),
                    'expires_at' => (clone $submittedAt)->addMinutes(10),
                    'submitted_at' => $submittedAt,
                    'score' => round($percentage / 10, 2),
                    'max_score' => 10,
                    'percentage' => $percentage,
                    'passed' => $percentage >= 50,
                    'tab_leave_count' => $percentage < 60 ? 1 : 0,
                    'question_order' => null,
                    'autosave_payload' => null,
                ]);
            }
        }
    }

    private function upsertDemoExam(User $teacher, array $payload): Exam
    {
        $exam = Exam::withTrashed()->firstOrNew(['slug' => $payload['slug']]);

        $exam->fill([
            'created_by' => $teacher->id,
            'title' => $payload['title'],
            'subject' => $payload['subject'],
            'topic' => $payload['topic'],
            'status' => 'published',
            'description' => 'De thi demo dung de test man hinh ket qua hoc sinh.',
            'duration_minutes' => 45,
            'starts_at' => now()->subDays($payload['day_offset'] + 1),
            'ends_at' => now()->addDays(14),
            'max_attempts' => 3,
            'passing_score' => 5,
            'shuffle_questions' => false,
            'shuffle_answers' => false,
            'show_results' => true,
            'audience' => ['roles' => ['student']],
            'generation_config' => null,
            'total_questions' => 10,
            'total_points' => 10,
            'published_at' => now()->subDays($payload['day_offset'] + 1),
        ]);

        $exam->save();

        if (method_exists($exam, 'restore') && $exam->trashed()) {
            $exam->restore();
        }

        return $exam;
    }

    private function putSubmissionFile(string $path, string $content): void
    {
        Storage::disk('public')->put($path, $content."\nGenerated at: ".now()->toDateTimeString()."\n");
    }
}
