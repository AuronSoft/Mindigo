@props([
    'preset' => 'default',
    'icon' => null,
    'title' => null,
    'message' => null,
])

@php
    $presets = [
        'default' => [
            'icon' => 'heroicon-o-inbox-stack',
            'title' => 'Chưa có dữ liệu',
            'message' => 'Dữ liệu sẽ hiển thị tại đây khi hệ thống có bản ghi mới.',
        ],
        'audit_logs' => [
            'icon' => 'heroicon-o-clipboard-document-list',
            'title' => 'Chưa có nhật ký thao tác',
            'message' => 'Các hành động đăng nhập, đăng xuất và thay đổi cấu hình sẽ được lưu tại đây.',
        ],
        'articles' => [
            'icon' => 'heroicon-o-newspaper',
            'title' => __('blog::app.news.no_articles'),
            'message' => 'Các bài viết mới sẽ xuất hiện tại đây khi hệ thống cập nhật dữ liệu.',
        ],
        'search' => [
            'icon' => 'heroicon-o-document-magnifying-glass',
            'title' => 'Không tìm thấy kết quả',
            'message' => 'Thử đổi từ khóa, bộ lọc hoặc khoảng ngày.',
        ],
        'archive' => [
            'icon' => 'heroicon-o-archive-box',
            'title' => 'Kho lưu trữ đang trống',
            'message' => 'Chưa có bản ghi nào được lưu trữ cho khu vực này.',
        ],
    ];

    $emptyState = $presets[$preset] ?? $presets['default'];
    $icon = $icon ?? $emptyState['icon'];
    $title = $title ?? $emptyState['title'];
    $message = $message ?? $emptyState['message'];
@endphp

<div class="grid justify-items-center gap-3 py-4 text-center">
    <div class="grid h-20 w-20 place-items-center rounded-2xl border border-green-100 bg-green-50 text-green-600">
        <x-dynamic-component :component="$icon" class="h-12 w-12" />
    </div>
    <div>
        <div class="text-sm font-black text-slate-950">{{ $title }}</div>
        @if($message)
            <div class="mx-auto mt-1 max-w-md text-sm font-semibold leading-6 text-slate-500">{{ $message }}</div>
        @endif
    </div>
</div>
