@php $isEditing = $editing ?? false; @endphp

{{-- Tên bài học --}}
<div>
    <label class="mb-1.5 block text-xs font-black text-slate-600">Tên bài học <span class="text-red-500">*</span></label>
    <input type="text" name="name" value="{{ old('name', $lesson->name ?? '') }}"
           placeholder="VD: Bài 1 — Cài đặt môi trường" required
           class="w-full rounded-2xl border {{ $errors->has('name') ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-white' }} px-4 py-2.5 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">
    @error('name') <p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
</div>

{{-- Mô tả ngắn --}}
<div>
    <label class="mb-1.5 block text-xs font-black text-slate-600">Mô tả ngắn</label>
    <input type="text" name="description" value="{{ old('description', $lesson->description ?? '') }}"
           placeholder="Tóm tắt nội dung bài học..."
           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">
    @error('description') <p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
</div>

{{-- Nội dung văn bản --}}
<div>
    <label class="mb-1.5 block text-xs font-black text-slate-600">Nội dung bài học</label>
    <textarea name="content" rows="8"
              placeholder="Nhập nội dung chi tiết bài học, có thể dùng HTML..."
              class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold leading-relaxed text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50 resize-y font-mono">{{ old('content', $lesson->content ?? '') }}</textarea>
    @error('content') <p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
</div>

<div class="grid gap-6 md:grid-cols-2">

    {{-- Upload Video --}}
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="mb-3 flex items-center gap-2">
            <x-heroicon-o-play-circle class="h-5 w-5 text-sky-500" />
            <h4 class="text-sm font-black text-slate-700">Video bài học</h4>
        </div>
        @if($isEditing && !empty($lesson->video_path))
            <div class="mb-3 rounded-xl bg-white border border-slate-200 p-3">
                <p class="text-xs font-bold text-slate-600 mb-1">Video hiện tại:</p>
                <video controls class="w-full max-h-32 rounded-lg bg-slate-900">
                    <source src="{{ asset('storage/' . $lesson->video_path) }}" type="video/mp4">
                </video>
                <label class="mt-2 flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remove_video" value="1" class="h-3.5 w-3.5 accent-red-500">
                    <span class="text-xs font-bold text-red-500">Xóa video hiện tại</span>
                </label>
            </div>
        @endif
        <label class="mb-1.5 block text-xs font-black text-slate-600">{{ $isEditing ? 'Thay thế video' : 'Tải lên video' }}</label>
        <input type="file" name="video" accept="video/mp4,video/mov,video/avi,video/webm"
               class="w-full text-sm font-bold text-slate-600 file:mr-3 file:rounded-full file:border-0 file:bg-sky-50 file:px-3 file:py-1 file:text-xs file:font-black file:text-sky-700">
        <p class="mt-1.5 text-[10px] text-slate-400 font-bold">Định dạng: MP4, MOV, AVI, WebM · Tối đa 500MB</p>
        @error('video') <p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Upload Tài liệu --}}
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="mb-3 flex items-center gap-2">
            <x-heroicon-o-paper-clip class="h-5 w-5 text-orange-500" />
            <h4 class="text-sm font-black text-slate-700">Tài liệu đính kèm</h4>
        </div>
        @if($isEditing && !empty($lesson->attachment_paths) && count($lesson->attachment_paths) > 0)
            <div class="mb-3 space-y-1.5">
                <p class="text-xs font-bold text-slate-600">Tài liệu hiện tại:</p>
                @foreach($lesson->attachment_paths as $att)
                    <div class="flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-3 py-2">
                        <x-heroicon-o-document class="h-4 w-4 shrink-0 text-orange-400" />
                        <span class="flex-1 truncate text-xs font-bold text-slate-700">{{ $att['original_name'] }}</span>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="remove_attachments[]" value="{{ $att['path'] }}" class="h-3 w-3 accent-red-500">
                            <span class="text-[10px] font-black text-red-400">Xóa</span>
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
        <label class="mb-1.5 block text-xs font-black text-slate-600">Thêm tài liệu (nhiều file)</label>
        <input type="file" name="attachments[]" multiple
               accept=".pdf,.doc,.docx,.ppt,.pptx"
               class="w-full text-sm font-bold text-slate-600 file:mr-3 file:rounded-full file:border-0 file:bg-orange-50 file:px-3 file:py-1 file:text-xs file:font-black file:text-orange-700">
        <p class="mt-1.5 text-[10px] text-slate-400 font-bold">Định dạng: PDF, Word, PowerPoint · Tối đa 20MB/file</p>
        @error('attachments') <p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
    </div>
</div>

{{-- Bài tập đính kèm --}}
<div>
    <div class="mb-2 flex items-center gap-2">
        <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-indigo-500" />
        <label class="text-sm font-black text-slate-700">Bài tập đính kèm theo bài học</label>
    </div>
    <select name="assignment_id"
            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">
        <option value="">-- Không đính kèm bài tập --</option>
        @foreach($assignments as $assignment)
            <option value="{{ $assignment->id }}" @selected(old('assignment_id', $lesson->assignment_id ?? null) == $assignment->id)>
                {{ $assignment->title }}
            </option>
        @endforeach
    </select>
    @if($assignments->isEmpty())
        <p class="mt-1.5 text-[11px] font-bold text-slate-400">Bạn chưa có bài tập nào đã xuất bản. Tạo bài tập trước trong mục Bài tập.</p>
    @endif
    @error('assignment_id') <p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
</div>

{{-- Điều kiện học (Prerequisite) --}}
<div>
    <div class="mb-2 flex items-center gap-2">
        <x-heroicon-o-lock-closed class="h-5 w-5 text-slate-400" />
        <label class="text-sm font-black text-slate-700">Điều kiện học (bài học tiên quyết)</label>
    </div>
    <select name="prerequisite_lesson_id"
            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400 focus:ring-2 focus:ring-green-50">
        <option value="">-- Không có điều kiện (học tự do) --</option>
        @foreach($existingLessons as $lessonId => $lessonName)
            <option value="{{ $lessonId }}" @selected(old('prerequisite_lesson_id', $lesson->prerequisite_lesson_id ?? null) == $lessonId)>
                {{ $lessonName }}
            </option>
        @endforeach
    </select>
    <p class="mt-1.5 text-[11px] font-bold text-slate-400">Học viên phải hoàn thành bài học được chọn trước khi học bài này.</p>
    @error('prerequisite_lesson_id') <p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
</div>
