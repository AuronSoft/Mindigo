@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-exam-management::app.foundation.foundation_title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/ExamManagement/src/resources/css/app.css',
    ])
@endsection

@section('content')
<main class="exam-foundation-shell">
    <div class="exam-foundation-container">
        <x-exam::page-header
            eyebrow="Exam · Teaching workspace"
            :title="__('Mindigo-exam-management::app.foundation.foundation_title')"
            :description="__('Mindigo-exam-management::app.foundation.foundation_subtitle')"
        >
            <x-slot:actions>
                <x-exam::button variant="secondary"><x-heroicon-o-arrow-up-tray class="h-4 w-4" />Nhập câu hỏi</x-exam::button>
                <x-exam::button><x-heroicon-o-plus class="h-4 w-4" />Tạo đề mới</x-exam::button>
            </x-slot:actions>
        </x-exam::page-header>

        <section class="exam-foundation-stat-grid" aria-label="Tổng quan">
            <x-exam::stat-card label="Đề mẫu" value="24" hint="4 đề được cập nhật tuần này"><x-slot:icon><x-heroicon-o-document-text class="h-5 w-5" /></x-slot:icon></x-exam::stat-card>
            <x-exam::stat-card label="Sắp diễn ra" value="06" hint="Kỳ thi gần nhất lúc 08:00"><x-slot:icon><x-heroicon-o-calendar-days class="h-5 w-5" /></x-slot:icon></x-exam::stat-card>
            <x-exam::stat-card label="Đang diễn ra" value="02" hint="86 học sinh đang làm bài"><x-slot:icon><x-heroicon-o-signal class="h-5 w-5" /></x-slot:icon></x-exam::stat-card>
            <x-exam::stat-card label="Chờ chấm" value="18" hint="Có 7 câu trả lời tự luận"><x-slot:icon><x-heroicon-o-pencil-square class="h-5 w-5" /></x-slot:icon></x-exam::stat-card>
        </section>

        <x-exam::stepper :steps="['Thông tin', 'Cấu trúc', 'Câu hỏi', 'Điểm số', 'Thiết lập', 'Xem trước']" :current="3" />

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(22rem,.65fr)]">
            <x-exam::panel title="Kỳ thi gần đây" description="Theo dõi vòng đời kỳ thi mà bạn đang phụ trách.">
                <x-slot:actions><x-exam::button variant="ghost">Xem tất cả</x-exam::button></x-slot:actions>
                <div class="divide-y divide-slate-100">
                    @foreach([
                        ['Kiểm tra cuối khóa Laravel', 'Lớp BE-K24 · 86 học sinh', 'live', 'Đang diễn ra'],
                        ['Đánh giá năng lực tiếng Anh B1', 'Lớp ENG-12 · 42 học sinh', 'scheduled', 'Đã lên lịch'],
                        ['Ôn tập Đại số học kỳ II', 'Nhóm gia sư Toán · 18 học sinh', 'grading', 'Chờ chấm'],
                    ] as [$title, $meta, $status, $label])
                        <article class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0"><h3 class="truncate text-sm font-black text-slate-900">{{ $title }}</h3><p class="mt-1 text-xs font-semibold text-slate-400">{{ $meta }}</p></div>
                            <x-exam::status-badge :status="$status" :label="$label" />
                        </article>
                    @endforeach
                </div>
            </x-exam::panel>

            <x-exam::panel title="Trạng thái chuẩn" description="Bộ trạng thái dùng thống nhất trong module đề thi.">
                <div class="flex flex-wrap gap-2">
                    <x-exam::status-badge status="draft" label="Bản nháp" />
                    <x-exam::status-badge status="ready" label="Sẵn sàng" />
                    <x-exam::status-badge status="scheduled" label="Đã lên lịch" />
                    <x-exam::status-badge status="live" label="Đang diễn ra" />
                    <x-exam::status-badge status="grading" label="Chờ chấm" />
                    <x-exam::status-badge status="completed" label="Hoàn thành" />
                    <x-exam::status-badge status="archived" label="Đã lưu trữ" />
                </div>
                <div class="mt-5 rounded-xl border border-green-100 bg-green-50 p-4 text-sm font-semibold leading-6 text-green-800">
                    Giáo viên và gia sư sở hữu toàn bộ nghiệp vụ đề thi. Không tồn tại trạng thái “chờ admin duyệt”.
                </div>
            </x-exam::panel>
        </div>

        <x-exam::panel title="Empty state chuẩn" description="Dùng chung cho đề mẫu, kỳ thi, bài chấm và kết quả.">
            <x-exam::empty-state title="Chưa có kỳ thi nào" description="Tạo kỳ thi từ một đề mẫu đã sẵn sàng, sau đó giao cho lớp hoặc chia sẻ bằng public link.">
                <x-slot:icon><x-heroicon-o-academic-cap class="h-7 w-7" /></x-slot:icon>
                <x-slot:actions><x-exam::button>Tạo kỳ thi đầu tiên</x-exam::button></x-slot:actions>
            </x-exam::empty-state>
        </x-exam::panel>
    </div>
</main>
@endsection
