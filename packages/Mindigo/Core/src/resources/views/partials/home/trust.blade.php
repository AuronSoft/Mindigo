{{-- Trust section --}}
<section class="py-20 px-10 border-t border-gray-100 bg-white">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14">
            <p class="text-green-600 font-black text-3xl leading-snug">Được cộng đồng sinh viên, trường đại học và</p>
            <p class="text-green-600 font-black text-3xl leading-snug">doanh nghiệp trên cả nước tin cậy</p>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-10 mb-20">
            @foreach([
                ['ĐHQGHN', 'https://upload.wikimedia.org/wikipedia/vi/thumb/4/4c/Logo_%C4%90HQG_H%C3%A0_N%E1%BB%99i.svg/120px-Logo_%C4%90HQG_H%C3%A0_N%E1%BB%99i.svg.png'],
                ['NEU', 'https://upload.wikimedia.org/wikipedia/vi/thumb/7/7b/Logo_NEU.svg/120px-Logo_NEU.svg.png'],
                ['HUST', 'https://upload.wikimedia.org/wikipedia/vi/thumb/d/d0/Logo_DHBKHN.png/120px-Logo_DHBKHN.png'],
                ['UEH', 'https://upload.wikimedia.org/wikipedia/vi/thumb/8/82/Logo_UEH.svg/120px-Logo_UEH.svg.png'],
                ['FTU', 'https://upload.wikimedia.org/wikipedia/vi/thumb/9/97/Logo_FTU.png/120px-Logo_FTU.png'],
                ['HCMUT', 'https://upload.wikimedia.org/wikipedia/vi/thumb/b/be/Logo_DHBK_TPHCM.png/120px-Logo_DHBK_TPHCM.png'],
                ['VNU-HCM', 'https://upload.wikimedia.org/wikipedia/vi/thumb/1/18/VNU-HCM_logo.png/120px-VNU-HCM_logo.png'],
                ['PTIT', 'https://upload.wikimedia.org/wikipedia/vi/thumb/d/d4/Logo_HVBCTT.png/120px-Logo_HVBCTT.png'],
                ['HUFI', 'https://upload.wikimedia.org/wikipedia/vi/thumb/6/64/Logo_HUFI.png/120px-Logo_HUFI.png'],
                ['DLU', 'https://upload.wikimedia.org/wikipedia/vi/thumb/7/7f/Logo_DLU.png/120px-Logo_DLU.png'],
            ] as [$name, $logo])
            <div class="flex flex-col items-center gap-2 group">
                <div class="w-20 h-20 rounded-2xl bg-white border border-gray-100 shadow-sm flex items-center justify-center p-2 group-hover:shadow-md group-hover:border-green-200 transition-all">
                    <img src="{{ $logo }}" alt="{{ $name }}" class="w-full h-full object-contain" onerror="this.parentElement.innerHTML='<span class=\'text-[10px] font-black text-gray-500 text-center leading-tight\'>{{ $name }}</span>'">
                </div>
                <span class="text-[10px] font-bold text-gray-400">{{ $name }}</span>
            </div>
            @endforeach
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                        <svg width="20" height="20" fill="none" viewBox="0 0 20 20"><circle cx="10" cy="7" r="4" stroke="#16a34a" stroke-width="1.5"/><path d="M2 17c0-3.866 3.582-7 8-7s8 3.134 8 7" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <span class="font-black text-gray-800 text-lg">Sinh viên</span>
                </div>
                <p class="text-gray-500 text-sm leading-relaxed mb-5">Nền tảng trao đổi đề thi, tài liệu học tập. Thông qua việc tự tạo đề, sinh viên có thể tự học với bộ tài liệu phù hợp đồng thời chia sẻ cho nhóm học tập.</p>
                <a href="#" class="text-green-600 font-black text-sm flex items-center gap-1 hover:gap-2 transition-all">Bắt đầu <svg width="14" height="14" fill="none" viewBox="0 0 14 14"><path d="M3 7h8M8 4l3 3-3 3" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            </div>
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                        <svg width="20" height="20" fill="none" viewBox="0 0 20 20"><rect x="2" y="3" width="16" height="12" rx="2" stroke="#16a34a" stroke-width="1.5"/><path d="M7 18h6M10 15v3" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <span class="font-black text-gray-800 text-lg">Giảng viên</span>
                </div>
                <p class="text-gray-500 text-sm leading-relaxed mb-5">Thao tác tạo đề đơn giản, chính xác cùng phương pháp đánh giá hiệu quả, giúp giảng viên dễ dàng quản lý bài giảng và chất lượng giảng dạy.</p>
                <a href="#" class="text-green-600 font-black text-sm flex items-center gap-1 hover:gap-2 transition-all">Bắt đầu <svg width="14" height="14" fill="none" viewBox="0 0 14 14"><path d="M3 7h8M8 4l3 3-3 3" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            </div>
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                        <svg width="20" height="20" fill="none" viewBox="0 0 20 20"><path d="M3 17V8l7-5 7 5v9" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="7" y="11" width="6" height="6" rx="1" stroke="#16a34a" stroke-width="1.5"/></svg>
                    </div>
                    <span class="font-black text-gray-800 text-lg">Trung tâm đào tạo</span>
                </div>
                <p class="text-gray-500 text-sm leading-relaxed mb-5">Nền tảng hỗ trợ chi tiết về kỹ thuật giúp các doanh nghiệp nhanh chóng tổ chức và sắp xếp các nội dung đào tạo cho cán bộ công nhân viên.</p>
                <a href="#" class="text-green-600 font-black text-sm flex items-center gap-1 hover:gap-2 transition-all">Bắt đầu <svg width="14" height="14" fill="none" viewBox="0 0 14 14"><path d="M3 7h8M8 4l3 3-3 3" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            </div>
        </div>
    </div>
</section>