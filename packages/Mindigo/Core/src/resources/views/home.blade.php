@extends('core::layouts.home')

@section('content')

<div class="min-h-screen bg-white flex flex-col">

    {{-- Navbar --}}
    <nav class="border-b border-gray-100 bg-white sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex items-center justify-between px-10 py-3">
            <a href="#" class="flex items-center gap-2">
                <div class="w-9 h-9 bg-green-500 rounded-xl flex items-center justify-center">
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                        <path d="M11 3C7 3 4 6 4 10c0 2.5 1.2 4.7 3 6l-1 3h10l-1-3c1.8-1.3 3-3.5 3-6 0-4-3-7-7-7z" fill="white"/>
                        <circle cx="8.5" cy="10" r="1.5" fill="#16a34a"/>
                        <circle cx="13.5" cy="10" r="1.5" fill="#16a34a"/>
                    </svg>
                </div>
                <span class="text-xl font-black text-green-600 tracking-tight">mindigo</span>
            </a>
            <div class="hidden md:flex items-center gap-1">
                <a href="#" class="text-green-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-green-50 transition">Tính năng</a>
                <a href="#" class="text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-green-50 hover:text-green-600 transition">Khám phá đề thi</a>
                <a href="#" class="text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-green-50 hover:text-green-600 transition">Luyện thi THPT</a>
                <a href="#" class="text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-green-50 hover:text-green-600 transition">Quản lý lớp học</a>
                <a href="#" class="text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-green-50 hover:text-green-600 transition">Bảng giá</a>
                <a href="#" class="text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-green-50 hover:text-green-600 transition">Tin tức</a>
                <a href="#" class="text-gray-600 font-bold text-sm px-4 py-2 rounded-xl hover:bg-green-50 hover:text-green-600 transition">Liên hệ</a>
            </div>
            <a href="#" class="bg-green-500 hover:bg-green-400 active:bg-green-600 text-white font-black text-sm px-5 py-2.5 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 active:shadow-none active:translate-y-1 transition-all">
                Đăng nhập
            </a>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="bg-green-50 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-green-200 rounded-full opacity-30 translate-x-1/3 -translate-y-1/3 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-green-200 rounded-full opacity-20 -translate-x-1/3 translate-y-1/3 pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-10 py-20 flex flex-col lg:flex-row items-center gap-16 relative z-10">

            {{-- LEFT --}}
            <div class="flex-1 flex flex-col items-start gap-5">
                <span class="bg-white border border-green-200 text-green-700 text-xs font-black px-4 py-1.5 rounded-full">
                    #1 Nền tảng thi trắc nghiệm online tốt nhất
                </span>
                <div>
                    <h1 class="text-5xl font-black text-gray-900 leading-tight">
                        Có một cách đơn giản hơn để
                    </h1>
                    <h2 class="text-5xl font-black text-green-600 leading-tight flex items-center gap-1">
                        <span id="typewriter">học tập ôn thi</span><span class="inline-block w-0.5 h-11 bg-green-500 animate-pulse ml-0.5"></span>
                    </h2>
                    <h3 class="text-5xl font-black text-gray-900 leading-tight">
                        trắc nghiệm online
                    </h3>
                </div>
                <div class="w-16 h-1 bg-green-500 rounded-full"></div>
                <p class="text-gray-500 font-semibold text-base leading-relaxed max-w-lg">
                    Tạo câu hỏi và đề thi nhanh với những giải pháp thông minh. mindigo tận dụng sức mạnh công nghệ để nâng cao trình độ học tập của bạn.
                </p>
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-2">
                        <img src="https://api.dicebear.com/9.x/personas/svg?seed=Mia&backgroundColor=d1fae5" class="w-9 h-9 rounded-full border-2 border-white object-cover bg-green-200" alt="user">
                        <img src="https://api.dicebear.com/9.x/personas/svg?seed=Linh&backgroundColor=bbf7d0" class="w-9 h-9 rounded-full border-2 border-white object-cover bg-green-300" alt="user">
                        <img src="https://api.dicebear.com/9.x/personas/svg?seed=Nam&backgroundColor=86efac" class="w-9 h-9 rounded-full border-2 border-white object-cover bg-green-400" alt="user">
                        <img src="https://api.dicebear.com/9.x/personas/svg?seed=Hoa&backgroundColor=4ade80" class="w-9 h-9 rounded-full border-2 border-white object-cover bg-green-500" alt="user">
                    </div>
                    <span class="text-sm font-bold text-gray-600">Hơn <strong class="text-green-600">200.000+</strong> khách hàng đã yêu thích sử dụng</span>
                </div>
                <div class="flex gap-0.5 text-yellow-400 text-2xl -mt-1">★★★★★</div>
                <div class="flex gap-3 mt-1 flex-wrap">
                    <a href="#" class="flex items-center gap-2 bg-green-500 hover:bg-green-400 active:bg-green-600 text-white font-black text-sm px-7 py-4 rounded-2xl shadow-[0_5px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 active:shadow-none active:translate-y-1.5 transition-all">
                        <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><circle cx="8" cy="8" r="6.5" stroke="white" stroke-width="1.5"/><path d="M6.5 5.5l5 2.5-5 2.5V5.5z" fill="white"/></svg>
                        Tạo đề thi ngay
                    </a>
                    <a href="#" class="flex items-center gap-2 bg-white hover:bg-green-50 text-green-600 font-black text-sm px-7 py-4 rounded-2xl border-2 border-green-200 hover:border-green-400 transition-all">
                        <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><circle cx="6.5" cy="6.5" r="5" stroke="#16a34a" stroke-width="1.5"/><path d="M10.5 10.5l3 3" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round"/></svg>
                        Tìm kiếm đề thi
                    </a>
                </div>
            </div>

            {{-- RIGHT --}}
            <div class="flex-1 relative flex items-center justify-center min-h-[480px]">

                {{-- AI badge --}}
                <div class="absolute top-2 left-6 bg-white border-2 border-green-200 rounded-2xl px-4 py-2.5 flex items-center gap-3 shadow-lg z-20 animate-bounce" style="animation-duration:3s">
                    <div class="w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center text-white font-black text-base">AI</div>
                    <span class="text-green-600 font-black text-sm">✦ ✦</span>
                </div>

                {{-- Feature pill --}}
                <div class="absolute top-0 right-0 bg-green-500 text-white text-xs font-black px-4 py-2 rounded-xl shadow-md z-20 flex items-center gap-1.5">
                    <svg width="14" height="14" fill="none" viewBox="0 0 14 14"><rect x="1" y="1" width="12" height="12" rx="3" stroke="white" stroke-width="1.5"/><path d="M4 7h6M4 4.5h3M4 9.5h4" stroke="white" stroke-width="1.2" stroke-linecap="round"/></svg>
                    Tính năng nâng cao
                </div>

                {{-- Main card --}}
                <div class="bg-white rounded-3xl shadow-2xl border border-green-100 w-full overflow-hidden mt-10">
                    <div class="bg-gray-50 border-b border-gray-100 px-4 py-2.5 flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                        <div class="flex-1 bg-white rounded-lg h-5 mx-4 border border-gray-200 flex items-center px-3">
                            <span class="text-[10px] text-gray-400 font-medium">app.mindigo.vn/de-thi/tao-moi</span>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex-1 bg-green-50 border border-dashed border-green-300 rounded-xl px-3 py-2.5 text-xs font-bold text-green-700 flex items-center gap-2 hover:bg-green-100 transition cursor-pointer">
                                <svg width="14" height="14" fill="none" viewBox="0 0 14 14"><path d="M7 1v7M4.5 3.5L7 1l2.5 2.5" stroke="#16a34a" stroke-width="1.4" stroke-linecap="round"/><rect x="1" y="10" width="12" height="3" rx="1.5" fill="#16a34a" opacity=".2"/></svg>
                                Upload tài liệu đề thi
                            </div>
                            <button class="bg-green-500 hover:bg-green-400 transition text-white text-xs font-black px-4 py-2.5 rounded-xl whitespace-nowrap shadow-[0_3px_0_#15803d]">Duyệt kết quả</button>
                        </div>
                        <div class="flex gap-2 mb-5">
                            <span class="bg-red-50 text-red-400 border border-red-100 text-xs font-black px-3 py-1 rounded-lg">↩ Trả về</span>
                            <span class="bg-green-500 text-white text-xs font-black px-3 py-1 rounded-lg shadow-[0_2px_0_#15803d]">✓ Lưu đề thi</span>
                        </div>
                        <div class="flex gap-5">
                            <div class="w-44 shrink-0 space-y-3">
                                <div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-wide mb-2">Danh sách phần thi</p>
                                    <div class="space-y-1.5">
                                        <div class="bg-green-500 text-white text-xs font-black px-3 py-1.5 rounded-lg text-center shadow-[0_2px_0_#15803d]">Phần 1</div>
                                        <div class="bg-gray-100 text-gray-500 text-xs font-semibold px-2 py-1.5 rounded-lg text-center leading-tight hover:bg-gray-200 transition cursor-pointer">Part 2: Advanced</div>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-wide mb-2">Mục lục câu hỏi (10)</p>
                                    <div class="grid grid-cols-5 gap-1">
                                        @foreach(range(1,10) as $n)
                                        <div class="w-7 h-7 rounded-md {{ $n <= 3 ? 'bg-green-500 text-white shadow-[0_2px_0_#15803d]' : 'bg-gray-100 text-gray-400 hover:bg-gray-200' }} flex items-center justify-center text-[10px] font-black transition cursor-pointer">{{ $n }}</div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="bg-green-50 rounded-xl p-3 border border-green-100">
                                    <p class="text-[10px] font-black text-green-700 mb-2">Tiến độ</p>
                                    <div class="w-full bg-green-100 rounded-full h-1.5 mb-1">
                                        <div class="bg-green-500 h-1.5 rounded-full" style="width:30%"></div>
                                    </div>
                                    <p class="text-[10px] text-green-600 font-bold">3/10 câu hoàn thành</p>
                                </div>
                            </div>
                            <div class="flex-1 space-y-3 min-w-0">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-wide">Danh sách câu hỏi</p>
                                <div class="bg-gray-50 rounded-xl p-3 space-y-1.5 border border-transparent hover:border-green-200 transition">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-xs font-black text-gray-700">Câu 1 <span class="text-gray-400 font-semibold">(Một đáp án)</span></p>
                                        <span class="bg-green-100 text-green-600 text-[10px] font-black px-2 py-0.5 rounded-full">✓ Có đáp án</span>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-500 italic">The bacterium E.coli:</p>
                                    <div class="flex items-center gap-1.5 text-xs text-gray-500"><span class="text-red-400 font-black">✗</span> Absolutely aerobic</div>
                                    <div class="flex items-center gap-1.5 text-xs text-gray-500"><span class="text-red-400 font-black">✗</span> Gram-negative cocc...</div>
                                    <div class="flex items-center gap-1.5 text-xs text-gray-500"><span class="text-red-400 font-black">✗</span> Negative indole test</div>
                                    <div class="flex items-center gap-1.5 text-xs text-green-600 font-semibold"><span class="text-green-500 font-black">✓</span> Negative Vosges-Proskauer</div>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 border border-transparent hover:border-green-200 transition">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-xs font-black text-gray-700">Câu 2 <span class="text-gray-400 font-semibold">(Nhiều đáp án)</span></p>
                                        <span class="bg-green-100 text-green-600 text-[10px] font-black px-2 py-0.5 rounded-full">✓ Có đáp án</span>
                                    </div>
                                    <p class="text-xs text-gray-500 leading-relaxed">The steps for quantifying E.coli are arranged in order:</p>
                                    <div class="flex items-center gap-1.5 text-xs text-gray-500 mt-1.5"><span class="text-red-400 font-black">✗</span> Prepare the medium</div>
                                    <div class="flex items-center gap-1.5 text-xs text-green-600 font-semibold"><span class="text-green-500 font-black">✓</span> Serial dilution method</div>
                                    <div class="flex items-center gap-1.5 text-xs text-green-600 font-semibold"><span class="text-green-500 font-black">✓</span> Count colonies after 24h</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Phone mockup --}}
                <div class="absolute -bottom-4 -left-8 z-10" style="transform: rotate(-6deg) rotateY(25deg); width: 160px; perspective: 800px;">
                    <div class="relative rounded-[2.8rem] p-1 shadow-2xl" style="background: linear-gradient(145deg, #e2e8f0, #cbd5e1); box-shadow: 0 20px 45px rgba(0,0,0,0.18), inset 0 1px 0 rgba(255,255,255,0.9);">
                        <div class="absolute top-16 h-5 bg-slate-300 rounded-l-full" style="left:-4px; width:4px;"></div>
                        <div class="absolute top-24 h-8 bg-slate-300 rounded-l-full" style="left:-4px; width:4px;"></div>
                        <div class="absolute top-36 h-8 bg-slate-300 rounded-l-full" style="left:-4px; width:4px;"></div>
                        <div class="absolute top-20 h-10 bg-slate-300 rounded-r-full" style="right:-4px; width:4px;"></div>
                        <div class="bg-white overflow-hidden" style="border-radius: 2.4rem; max-height: 300px;">
                            <div class="flex justify-center pt-3 pb-1 bg-white">
                                <div class="bg-gray-900 rounded-full flex items-center justify-center gap-1.5 px-3" style="height:18px; width:60px">
                                    <div class="w-1.5 h-1.5 rounded-full bg-gray-600"></div>
                                    <div class="w-2.5 h-2.5 rounded-full bg-gray-700"></div>
                                </div>
                            </div>
                            <div class="flex justify-between items-center px-4 py-0.5">
                                <span class="text-[9px] font-black text-gray-700">9:41</span>
                                <div class="flex items-center gap-1">
                                    <svg width="10" height="8" fill="#374151" viewBox="0 0 10 8"><rect x="0" y="4" width="2" height="4" rx="0.5"/><rect x="2.5" y="2.5" width="2" height="5.5" rx="0.5"/><rect x="5" y="1" width="2" height="7" rx="0.5"/><rect x="7.5" y="0" width="2" height="8" rx="0.5"/></svg>
                                    <svg width="14" height="8" fill="none" viewBox="0 0 14 8"><rect x="0.5" y="0.5" width="11" height="7" rx="1.5" stroke="#374151" stroke-width="1"/><rect x="1.5" y="1.5" width="8" height="5" rx="1" fill="#374151"/></svg>
                                </div>
                            </div>
                            <div class="bg-white px-3 py-2 mt-1 flex items-center justify-between border-b border-gray-100">
                                <p class="text-gray-800 text-[10px] font-black">Thêm câu hỏi</p>
                                <div class="w-4 h-4 bg-red-400 rounded-full flex items-center justify-center">
                                    <span class="text-white text-[8px] font-black">✕</span>
                                </div>
                            </div>
                            <div class="p-2.5 space-y-2 bg-white">
                                <div class="flex gap-1.5">
                                    <div class="flex-1">
                                        <p class="text-[7px] font-black text-gray-400 mb-0.5">Loại câu hỏi</p>
                                        <div class="bg-gray-50 border border-gray-200 rounded-lg px-1.5 py-1 flex items-center justify-between">
                                            <span class="text-[8px] font-bold text-gray-700">Một đáp án</span>
                                            <span class="text-gray-400 text-[7px]">▾</span>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-[7px] font-black text-gray-400 mb-0.5">Mức độ</p>
                                        <div class="bg-gray-50 border border-gray-200 rounded-lg px-1.5 py-1 flex items-center justify-between">
                                            <span class="text-[8px] font-bold text-gray-700">Trung bình</span>
                                            <span class="text-gray-400 text-[7px]">▾</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                                    <p class="text-[7px] font-black text-gray-400 px-2 pt-1.5 pb-0.5">Loại câu hỏi</p>
                                    @foreach(['Một đáp án', 'Nhiều đáp án', 'Đúng sai', 'Nối', 'Điền từ', 'Đọc hiểu'] as $type)
                                    <div class="px-2 py-1 {{ $loop->first ? 'bg-green-50' : '' }} flex items-center justify-between border-t border-gray-50">
                                        <span class="text-[8px] font-bold {{ $loop->first ? 'text-green-600' : 'text-gray-600' }}">{{ $type }}</span>
                                        @if($loop->first)<span class="text-green-500 text-[8px]">✓</span>@endif
                                    </div>
                                    @endforeach
                                    <div class="px-2 py-1 border-t border-gray-100 flex items-center gap-1.5">
                                        <span class="text-[8px] font-bold text-gray-600">Tự luận</span>
                                        <span class="bg-green-500 text-white text-[6px] font-black px-1 py-0.5 rounded-full">PRO</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center py-2 bg-white">
                                <div class="w-12 h-1 bg-gray-300 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="absolute bottom-10 right-2 text-green-400 text-3xl pointer-events-none select-none" style="animation:floatStar 4s ease-in-out infinite">✦</div>
                <div class="absolute bottom-2 right-14 text-green-300 text-xl pointer-events-none select-none" style="animation:floatStar 3s .6s ease-in-out infinite">✦</div>
            </div>
        </div>
    </section>

    {{-- Trust section --}}
    <section class="py-20 px-10 border-t border-gray-100 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-14">
                <p class="text-green-600 font-black text-3xl leading-snug">Được cộng đồng sinh viên, trường đại học và</p>
                <p class="text-green-600 font-black text-3xl leading-snug">doanh nghiệp trên cả nước tin cậy</p>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-10 mb-20">
                @foreach([
                    ['BMTU', 'bg-red-50'],
                    ['NEU', 'bg-red-50'],
                    ['ĐHOGHN', 'bg-green-50'],
                    ['HVQY', 'bg-green-50'],
                    ['VMU', 'bg-blue-50'],
                    ['ĐH HÀ NỘI', 'bg-yellow-100'],
                    ['BÌNH DƯƠNG', 'bg-green-50'],
                    ['VUTM', 'bg-yellow-50'],
                    ['ĐH ĐIỆN LỰC', 'bg-blue-50'],
                    ['HÀ NỘI 1912', 'bg-red-50'],
                ] as [$name, $bg])
                <div class="w-20 h-20 rounded-full {{ $bg }} border border-gray-100 flex items-center justify-center text-[10px] font-black text-gray-600 text-center leading-tight shadow-sm">
                    {{ $name }}
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

    {{-- Feature section --}}
    <section class="py-20 px-10 bg-green-50 border-t border-green-100">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <p class="text-green-600 font-black text-3xl">Nền tảng học tập linh hoạt và dễ sử dụng</p>
            </div>
            <div class="flex flex-col lg:flex-row items-center gap-20">
                <div class="flex-1 flex flex-col gap-6">
                    <span class="bg-green-500 text-white text-xs font-black px-3 py-1 rounded-lg w-fit">NHANH</span>
                    <h2 class="text-4xl font-black text-gray-900 leading-tight">
                        <span class="text-green-600">Tự động</span> tạo câu hỏi và<br>đề thi trắc nghiệm
                    </h2>
                    <div class="flex flex-col gap-5">
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><rect x="1" y="2" width="14" height="12" rx="2" stroke="#16a34a" stroke-width="1.3"/><path d="M4 7h8M4 10h5" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                            </div>
                            <p class="text-gray-500 text-sm leading-relaxed">Tạo đề nhanh với vài cú nhấp chuột. Bằng cách nhập file tài liệu định dạng WORD hoặc PDF, AI sẽ giúp bạn tạo đề chính xác 100% trong vài phút.</p>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><path d="M8 1v5M8 10v5M1 8h5M10 8h5" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                            </div>
                            <p class="text-gray-500 text-sm leading-relaxed">Tối ưu trải nghiệm, tiết kiệm thời gian, công sức, đảm bảo tính khách quan và có thêm thời gian nghiên cứu, học tập.</p>
                        </div>
                    </div>
                    <a href="#" class="bg-green-500 hover:bg-green-400 text-white font-black text-sm px-7 py-3.5 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 transition-all w-fit">
                        Bắt đầu ngay
                    </a>
                </div>

                <div class="flex-1 relative min-h-[420px] flex items-center justify-center">
                    {{-- Card sau --}}
                    <div class="absolute top-0 right-0 bg-white rounded-2xl shadow-xl border border-gray-100 w-96 p-5 opacity-80 rotate-1 z-0">
                        <div class="bg-gray-50 border-b border-gray-100 flex items-center gap-1.5 pb-2 mb-4">
                            <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-yellow-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                            <span class="text-[10px] text-gray-400 ml-2 font-medium">Nhập văn bản</span>
                            <span class="text-[10px] text-gray-400 ml-auto font-medium">Duyệt kết quả</span>
                        </div>
                        <div class="bg-gray-50 rounded-lg h-24 mb-4 border border-dashed border-gray-200 flex items-center justify-center">
                            <span class="text-xs text-gray-300">Nhập văn bản đề thi của bạn...</span>
                        </div>
                        <div class="flex gap-2">
                            <div class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-2 py-2">
                                <p class="text-[9px] text-gray-400 mb-0.5">Số câu</p>
                                <p class="text-xs font-bold text-gray-700">15 câu hỏi</p>
                            </div>
                            <div class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-2 py-2">
                                <p class="text-[9px] text-gray-400 mb-0.5">Loại</p>
                                <p class="text-xs font-bold text-gray-700">Một đáp án</p>
                            </div>
                            <div class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-2 py-2">
                                <p class="text-[9px] text-gray-400 mb-0.5">Mức độ</p>
                                <p class="text-xs font-bold text-gray-700">Trung bình</p>
                            </div>
                        </div>
                    </div>

                    {{-- AI chip --}}
                    <div class="absolute left-0 top-1/3 z-30 w-16 h-16 bg-green-500 rounded-2xl flex items-center justify-center shadow-xl" style="animation:floatStar 3s ease-in-out infinite">
                        <span class="text-white font-black text-lg">AI</span>
                    </div>

                    {{-- Card trước --}}
                    <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 w-96 mt-28 z-10">
                        <div class="bg-gray-50 border-b border-gray-100 px-4 py-2.5 flex items-center gap-1.5 rounded-t-2xl">
                            <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-yellow-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                            <span class="text-[10px] text-gray-400 ml-2 font-medium">Nhập văn bản</span>
                            <span class="text-[10px] text-gray-400 ml-auto font-medium">Duyệt kết quả</span>
                        </div>
                        <div class="p-4">
                            <div class="flex gap-2 mb-3">
                                <span class="bg-red-100 text-red-400 text-xs font-black px-2 py-0.5 rounded">Trả về</span>
                                <span class="bg-green-500 text-white text-xs font-black px-2 py-0.5 rounded">Lưu đề thi</span>
                            </div>
                            <div class="flex gap-4">
                                <div class="w-28 shrink-0">
                                    <p class="text-[9px] font-black text-gray-400 mb-1">Danh sách phần thi</p>
                                    <div class="bg-green-500 text-white text-[9px] font-black px-2 py-1 rounded text-center mb-1">Phần 1: Từ vựng</div>
                                    <p class="text-[9px] font-black text-gray-400 mt-2 mb-1">Mục lục câu hỏi (19)</p>
                                    <div class="grid grid-cols-5 gap-0.5">
                                        @foreach(range(1,10) as $n)
                                        <div class="w-4 h-4 rounded text-[7px] font-bold flex items-center justify-center {{ $n <= 3 ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-400' }}">{{ $n }}</div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="flex-1 space-y-2">
                                    <p class="text-[9px] font-black text-gray-400">Danh sách câu hỏi</p>
                                    <div class="bg-gray-50 rounded-lg p-2">
                                        <p class="text-[9px] font-black text-gray-700 mb-1">Câu 1 (Một đáp án)</p>
                                        <p class="text-[9px] text-gray-500 italic mb-1">What is the plural form of "child"?</p>
                                        <div class="flex items-center gap-1 text-[9px] text-gray-500"><span class="text-red-400">✗</span> Childs</div>
                                        <div class="flex items-center gap-1 text-[9px] text-green-600 font-bold"><span class="text-green-500">✓</span> Children</div>
                                        <div class="mt-1 bg-green-50 rounded px-1.5 py-1 text-[8px] text-green-700 font-bold">The plural form of "child" is "children"</div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-2">
                                        <p class="text-[9px] font-black text-gray-700 mb-1">Câu 2 (Một đáp án)</p>
                                        <p class="text-[9px] text-gray-500 italic">Choose the correct word: "I ___ to school by bus."</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute bottom-4 left-8 text-green-400 text-2xl" style="animation:floatStar 4s ease-in-out infinite">✦</div>
                    <div class="absolute top-4 right-4 text-green-300 text-lg" style="animation:floatStar 3s .5s ease-in-out infinite">✦</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Personalization section --}}
    <section class="py-20 px-10 bg-white border-t border-gray-100">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col lg:flex-row items-center gap-20">

                {{-- LEFT: Phone mockups --}}
                <div class="flex-1 relative flex items-center justify-center min-h-[520px]">

                    {{-- Floating label: Dễ sử dụng --}}
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 z-30 bg-white border border-green-100 rounded-2xl px-4 py-2.5 flex items-center gap-2.5 shadow-lg">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><circle cx="8" cy="5" r="3" stroke="#16a34a" stroke-width="1.3"/><path d="M2 14c0-3 2.686-5 6-5s6 2 6 5" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                        </div>
                        <span class="text-green-700 font-black text-xs">Dễ sử dụng</span>
                    </div>

                    {{-- Floating label: Thân thiện --}}
                    <div class="absolute right-2 top-1/3 z-30 bg-white border border-green-100 rounded-2xl px-4 py-2.5 flex items-center gap-2.5 shadow-lg">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><path d="M8 2C4.686 2 2 4.686 2 8s2.686 6 6 6 6-2.686 6-6-2.686-6-6-6z" stroke="#16a34a" stroke-width="1.3"/><path d="M5 9.5s.8 1.5 3 1.5 3-1.5 3-1.5" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/><circle cx="6" cy="7" r="0.8" fill="#16a34a"/><circle cx="10" cy="7" r="0.8" fill="#16a34a"/></svg>
                        </div>
                        <span class="text-green-700 font-black text-xs">Thân thiện</span>
                    </div>

                    {{-- Decorative --}}
                    <div class="absolute top-8 left-20 w-4 h-4 bg-green-400 rounded-full opacity-50 pointer-events-none" style="animation:floatStar 3s ease-in-out infinite"></div>
                    <div class="absolute bottom-12 left-10 w-3 h-3 bg-green-300 rotate-45 opacity-60 pointer-events-none" style="animation:floatStar 4s .5s ease-in-out infinite"></div>
                    <div class="absolute bottom-6 right-20 text-green-400 text-3xl pointer-events-none" style="animation:floatStar 4s ease-in-out infinite">✦</div>
                    <div class="absolute top-10 right-10 text-green-300 text-xl pointer-events-none" style="animation:floatStar 3s .8s ease-in-out infinite">✦</div>

                    {{-- Phone 1 (Khám phá) --}}
                    <div class="relative z-10 mr-[-28px] mt-8" style="transform: rotate(-5deg); width: 195px;">
                        <div class="relative rounded-[2.6rem] shadow-2xl" style="background: linear-gradient(160deg, #d1fae5 0%, #6ee7b7 50%, #34d399 100%); padding: 3px;">
                            <div class="absolute top-14 h-6 rounded-l-full" style="left:-4px; width:4px; background:#86efac;"></div>
                            <div class="absolute top-24 h-9 rounded-l-full" style="left:-4px; width:4px; background:#86efac;"></div>
                            <div class="absolute top-36 h-9 rounded-l-full" style="left:-4px; width:4px; background:#86efac;"></div>
                            <div class="absolute top-20 h-12 rounded-r-full" style="right:-4px; width:4px; background:#86efac;"></div>
                            <div class="bg-white overflow-hidden" style="border-radius: 2.3rem; max-height: 390px;">
                                {{-- Notch --}}
                                <div class="flex justify-center pt-3 pb-0 bg-white">
                                    <div class="bg-gray-900 rounded-full flex items-center gap-1.5 px-3" style="height:18px; width:62px;">
                                        <div class="w-1.5 h-1.5 rounded-full bg-gray-600"></div>
                                        <div class="w-3 h-3 rounded-full bg-gray-700"></div>
                                    </div>
                                </div>
                                {{-- Status --}}
                                <div class="flex justify-between items-center px-4 py-1">
                                    <span class="text-[9px] font-black text-gray-700">15:16</span>
                                    <div class="flex items-center gap-1">
                                        <svg width="10" height="8" fill="#374151" viewBox="0 0 10 8"><rect x="0" y="4" width="2" height="4" rx="0.5"/><rect x="2.5" y="2.5" width="2" height="5.5" rx="0.5"/><rect x="5" y="1" width="2" height="7" rx="0.5"/><rect x="7.5" y="0" width="2" height="8" rx="0.5"/></svg>
                                        <svg width="14" height="8" fill="none" viewBox="0 0 14 8"><rect x="0.5" y="0.5" width="11" height="7" rx="1.5" stroke="#374151" stroke-width="1"/><rect x="1.5" y="1.5" width="8" height="5" rx="1" fill="#374151"/></svg>
                                    </div>
                                </div>
                                {{-- App header --}}
                                <div style="background: linear-gradient(135deg, #16a34a, #15803d);" class="px-3 py-2.5">
                                    <p class="text-white text-[10px] font-black text-center mb-1.5">Khám phá</p>
                                    <div class="flex gap-1 border-b border-green-700">
                                        <button class="text-white text-[8px] font-black border-b-2 border-white pb-1 px-2">Đề thi</button>
                                        <button class="text-green-300 text-[8px] px-2 pb-1">Khóa học</button>
                                    </div>
                                </div>
                                <div class="px-2.5 py-2 bg-green-50 flex-1">
                                    {{-- Search --}}
                                    <div class="bg-white rounded-lg px-2 py-1.5 flex items-center gap-1.5 border border-green-100 mb-2 shadow-sm">
                                        <svg width="9" height="9" fill="none" viewBox="0 0 9 9"><circle cx="4" cy="4" r="3" stroke="#16a34a" stroke-width="1"/><path d="M6.5 6.5l1.5 1.5" stroke="#16a34a" stroke-width="1" stroke-linecap="round"/></svg>
                                        <span class="text-[8px] text-gray-400">Tìm kiếm đề thi...</span>
                                    </div>
                                    {{-- Filter --}}
                                    <div class="flex gap-1 mb-2">
                                        <span class="text-white text-[7px] font-black px-2 py-0.5 rounded-full shadow-sm" style="background:#16a34a;">Mới nhất</span>
                                        <span class="bg-white text-gray-500 text-[7px] px-2 py-0.5 rounded-full border border-gray-200">Nổi bật nhất</span>
                                    </div>
                                    {{-- Card 1 --}}
                                    <div class="bg-white rounded-xl overflow-hidden shadow-md mb-2 border border-green-50">
                                        <div class="h-16 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);">
                                            <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg, white 0, white 1px, transparent 0, transparent 50%); background-size: 8px 8px;"></div>
                                            <div class="text-center relative z-10">
                                                <p class="text-white text-[8px] font-black">ĐỀ THI MÔN HÓA</p>
                                                <p class="text-green-200 text-[7px] font-bold">THPTQG 2023</p>
                                            </div>
                                        </div>
                                        <div class="p-2">
                                            <p class="text-[8px] font-black text-gray-700 leading-tight mb-1">Đề minh họa THPTQG môn Hóa học năm 2023 - Bộ GD&ĐT</p>
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[7px] text-gray-400">61 câu</span>
                                                    <span class="text-gray-300">·</span>
                                                    <span class="text-[7px] text-gray-400">45 phút</span>
                                                </div>
                                                <div class="flex gap-0.5 text-yellow-400 text-[8px]">★★★★★</div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Card 2 --}}
                                    <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-green-50">
                                        <div class="h-10 flex items-center px-2 justify-between" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                                            <p class="text-white text-[7px] font-black">Ôn thi THPT Quốc Gia</p>
                                            <div class="w-5 h-5 bg-white/20 rounded-full flex items-center justify-center">
                                                <svg width="8" height="8" fill="white" viewBox="0 0 8 8"><path d="M3 1l3 3-3 3" stroke="white" stroke-width="1.2" stroke-linecap="round" fill="none"/></svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Bottom nav --}}
                                <div class="flex justify-around py-2.5 border-t border-gray-100 bg-white">
                                    <div class="flex flex-col items-center gap-0.5">
                                        <div class="w-5 h-5 bg-green-100 rounded-md flex items-center justify-center">
                                            <svg width="10" height="10" fill="none" viewBox="0 0 10 10"><rect x="1" y="1" width="3.5" height="3.5" rx="0.7" fill="#16a34a"/><rect x="5.5" y="1" width="3.5" height="3.5" rx="0.7" fill="#16a34a" opacity=".4"/><rect x="1" y="5.5" width="3.5" height="3.5" rx="0.7" fill="#16a34a" opacity=".4"/><rect x="5.5" y="5.5" width="3.5" height="3.5" rx="0.7" fill="#16a34a" opacity=".4"/></svg>
                                        </div>
                                        <span class="text-[6px] text-green-600 font-black">Khám phá</span>
                                    </div>
                                    <div class="flex flex-col items-center gap-0.5 opacity-40">
                                        <div class="w-5 h-5 bg-gray-100 rounded-md"></div>
                                        <span class="text-[6px] text-gray-400">Đề thi</span>
                                    </div>
                                    <div class="flex flex-col items-center gap-0.5 opacity-40">
                                        <div class="w-5 h-5 bg-gray-100 rounded-md"></div>
                                        <span class="text-[6px] text-gray-400">Lớp học</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Phone 2 (Chi tiết đề thi) --}}
                    <div class="relative z-20 ml-[-15px] mt-[-30px]" style="transform: rotate(4deg); width: 195px;">
                        <div class="relative rounded-[2.6rem] shadow-2xl" style="background: linear-gradient(160deg, #d1fae5 0%, #6ee7b7 50%, #34d399 100%); padding: 3px;">
                            <div class="absolute top-14 h-6 rounded-l-full" style="left:-4px; width:4px; background:#86efac;"></div>
                            <div class="absolute top-24 h-9 rounded-l-full" style="left:-4px; width:4px; background:#86efac;"></div>
                            <div class="absolute top-20 h-12 rounded-r-full" style="right:-4px; width:4px; background:#86efac;"></div>
                            <div class="bg-white overflow-hidden" style="border-radius: 2.3rem; max-height: 390px;">
                                {{-- Notch --}}
                                <div class="flex justify-center pt-3 pb-0 bg-white">
                                    <div class="bg-gray-900 rounded-full flex items-center gap-1.5 px-3" style="height:18px; width:62px;">
                                        <div class="w-1.5 h-1.5 rounded-full bg-gray-600"></div>
                                        <div class="w-3 h-3 rounded-full bg-gray-700"></div>
                                    </div>
                                </div>
                                {{-- Status --}}
                                <div class="flex justify-between items-center px-4 py-1">
                                    <span class="text-[9px] font-black text-gray-700">15:24</span>
                                    <div class="flex items-center gap-1">
                                        <svg width="10" height="8" fill="#374151" viewBox="0 0 10 8"><rect x="0" y="4" width="2" height="4" rx="0.5"/><rect x="2.5" y="2.5" width="2" height="5.5" rx="0.5"/><rect x="5" y="1" width="2" height="7" rx="0.5"/><rect x="7.5" y="0" width="2" height="8" rx="0.5"/></svg>
                                        <svg width="14" height="8" fill="none" viewBox="0 0 14 8"><rect x="0.5" y="0.5" width="11" height="7" rx="1.5" stroke="#374151" stroke-width="1"/><rect x="1.5" y="1.5" width="8" height="5" rx="1" fill="#374151"/></svg>
                                    </div>
                                </div>
                                {{-- Header --}}
                                <div class="px-3 py-2 flex items-center gap-2 border-b border-gray-100">
                                    <span class="text-green-500 text-sm font-black">‹</span>
                                    <span class="text-[10px] font-black text-gray-700">Chi tiết đề thi</span>
                                </div>
                                {{-- Exam banner --}}
                                <div class="mx-2.5 mt-2 rounded-xl overflow-hidden shadow-md">
                                    <div class="p-3 flex items-center gap-2" style="background: linear-gradient(135deg, #16a34a, #15803d);">
                                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shrink-0 border border-white/30">
                                            <svg width="20" height="20" fill="none" viewBox="0 0 20 20"><rect x="3" y="2" width="14" height="16" rx="2" stroke="white" stroke-width="1.5"/><path d="M6 7h8M6 10h6M6 13h4" stroke="white" stroke-width="1.2" stroke-linecap="round"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-white text-[9px] font-black leading-tight">50 CÂU PHÁT ÂM</p>
                                            <p class="text-green-200 text-[8px] font-bold">THPTQG</p>
                                        </div>
                                    </div>
                                    <div class="px-3 py-1.5 flex items-center gap-3" style="background:#15803d;">
                                        <span class="text-green-100 text-[7px]">📚 3.4k</span>
                                        <span class="text-green-100 text-[7px]">⏱ 27ph</span>
                                        <span class="text-green-100 text-[7px]">❓ 50 câu</span>
                                    </div>
                                </div>
                                <div class="px-2.5 py-2">
                                    {{-- Action buttons --}}
                                    <div class="flex gap-1.5 mb-2">
                                        <button class="flex-1 text-white text-[8px] font-black py-1.5 rounded-lg shadow-[0_2px_0_#15803d]" style="background:#16a34a;">Bắt đầu</button>
                                        <button class="w-8 h-7 bg-green-50 border border-green-200 rounded-lg flex items-center justify-center">
                                            <svg width="12" height="12" fill="none" viewBox="0 0 12 12"><path d="M6 2v8M2 6h8" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                                        </button>
                                        <button class="w-8 h-7 bg-green-50 border border-green-200 rounded-lg flex items-center justify-center">
                                            <svg width="12" height="12" fill="none" viewBox="0 0 12 12"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0z" stroke="#16a34a" stroke-width="1.2"/></svg>
                                        </button>
                                    </div>
                                    <p class="text-[8px] text-gray-500 leading-relaxed mb-2">Tổng hợp 50 câu trong âm thường gặp THPTQG</p>
                                    {{-- Tabs --}}
                                    <div class="flex gap-1 border-b border-gray-100 mb-2">
                                        <button class="text-green-600 text-[7px] font-black border-b-2 border-green-500 pb-1 px-1">Nội dung đề thi</button>
                                        <button class="text-gray-400 text-[7px] pb-1 px-1">Kết quả thi</button>
                                        <button class="text-gray-400 text-[7px] pb-1 px-1">Danh sách</button>
                                    </div>
                                    <div class="bg-green-50 rounded-lg p-2 border border-green-100">
                                        <p class="text-[8px] font-black text-gray-600 mb-0.5">Phần 1</p>
                                        <p class="text-[8px] text-gray-500 mb-0.5">Câu 1</p>
                                        <p class="text-[8px] text-gray-400 italic">Question 1: Choose the word...</p>
                                    </div>
                                </div>
                                <div class="flex justify-center py-2 bg-white">
                                    <div class="w-12 h-1 bg-gray-300 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- RIGHT: Text content --}}
                <div class="flex-1 flex flex-col gap-6">
                    <span class="bg-green-500 text-white text-xs font-black px-3 py-1 rounded-lg w-fit">TỐI ƯU</span>
                    <h2 class="text-4xl font-black text-gray-900 leading-tight">
                        Thân thiện dễ sử dụng,<br>
                        <span class="text-green-600">cá nhân hóa</span> việc học tập
                    </h2>
                    <div class="flex flex-col gap-5">
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><rect x="1" y="2" width="14" height="12" rx="2" stroke="#16a34a" stroke-width="1.3"/><path d="M4 7h8M4 10h5" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                            </div>
                            <p class="text-gray-500 text-sm leading-relaxed">Giao diện EduQuiz được thiết kế trực quan, thân thiện giúp người dùng thực hiện thao tác nhanh chóng và sử dụng được tối đa các tính năng hữu ích trên nền tảng.</p>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><circle cx="8" cy="5" r="3" stroke="#16a34a" stroke-width="1.3"/><path d="M2 14c0-3 2.686-5 6-5s6 2 6 5" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                            </div>
                            <p class="text-gray-500 text-sm leading-relaxed">Bằng cách cá nhân hóa, mỗi người dùng có trải nghiệm riêng và xây dựng lộ trình học tập phù hợp cho chính mình. Từ đó, kích thích tư duy độc lập, sáng tạo và đạt kết quả học tập tốt.</p>
                        </div>
                    </div>
                    <a href="#" class="bg-green-500 hover:bg-green-400 text-white font-black text-sm px-7 py-3.5 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 transition-all w-fit">
                        Bắt đầu ngay
                    </a>
                </div>

            </div>
        </div>
    </section>

    {{-- Virtual Exam Room section --}}
    <section class="py-20 px-10 bg-green-50 border-t border-green-100">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col lg:flex-row items-center gap-20">

                {{-- LEFT: Text content --}}
                <div class="flex-1 flex flex-col gap-6">
                    <span class="bg-green-500 text-white text-xs font-black px-3 py-1 rounded-lg w-fit">HIỆU QUẢ</span>
                    <h2 class="text-4xl font-black text-gray-900 leading-tight">
                        <span class="text-green-600">Phòng thi ảo</span> trực tuyến
                    </h2>
                    <div class="flex flex-col gap-5">
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><rect x="1" y="2" width="14" height="12" rx="2" stroke="#16a34a" stroke-width="1.3"/><path d="M4 7h8M4 10h5" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                            </div>
                            <p class="text-gray-500 text-sm leading-relaxed">Nền tảng cho phép tổ chức các kỳ thi, kiểm tra một cách an toàn, bảo mật và hiệu quả. Người làm bài thi có thể tham gia thi từ bất kỳ đâu có kết nối internet, không cần phải di chuyển đến địa điểm thi cụ thể.</p>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><path d="M2 10V6a6 6 0 1112 0v4" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/><rect x="1" y="10" width="4" height="5" rx="1" stroke="#16a34a" stroke-width="1.3"/><rect x="11" y="10" width="4" height="5" rx="1" stroke="#16a34a" stroke-width="1.3"/></svg>
                            </div>
                            <p class="text-gray-500 text-sm leading-relaxed">EduQuiz sử dụng công nghệ tiên tiến để mô phỏng phòng thi truyền thống, đồng thời mang lại nhiều lợi ích vượt trội so với phương pháp thi cũ.</p>
                        </div>
                    </div>
                    <a href="#" class="bg-green-500 hover:bg-green-400 text-white font-black text-sm px-7 py-3.5 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 transition-all w-fit">
                        Bắt đầu ngay
                    </a>
                </div>

                {{-- RIGHT: Desktop mockups --}}
                <div class="flex-1 relative flex items-end justify-center min-h-[460px]">

                    {{-- Decorative --}}
                    <div class="absolute top-4 left-1/3 w-4 h-4 bg-green-400 rounded-full opacity-50 pointer-events-none" style="animation:floatStar 3s ease-in-out infinite"></div>
                    <div class="absolute bottom-4 right-4 text-green-400 text-3xl pointer-events-none" style="animation:floatStar 4s ease-in-out infinite">✦</div>
                    <div class="absolute top-8 right-12 text-green-300 text-xl pointer-events-none" style="animation:floatStar 3s .6s ease-in-out infinite">✦</div>
                    <div class="absolute bottom-16 left-0 w-3 h-3 bg-green-300 rotate-45 opacity-60 pointer-events-none" style="animation:floatStar 4s .4s ease-in-out infinite"></div>

                    {{-- Browser mockup 1 (back - Phòng thi ảnh) --}}
                    <div class="absolute top-0 right-0 w-[420px] bg-white rounded-2xl shadow-2xl border border-gray-100 z-0 rotate-1">
                        {{-- Browser bar --}}
                        <div class="bg-gray-50 border-b border-gray-100 px-4 py-2.5 flex items-center gap-2 rounded-t-2xl">
                            <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-yellow-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                            <div class="flex-1 bg-white rounded-md h-5 mx-3 border border-gray-200 flex items-center px-2 gap-1.5">
                                <svg width="8" height="8" fill="none" viewBox="0 0 8 8"><rect x="0.5" y="0.5" width="7" height="7" rx="1" stroke="#d1d5db"/></svg>
                                <span class="text-[9px] text-gray-400">app.mindigo.vn/phong-thi</span>
                            </div>
                        </div>
                        {{-- Content --}}
                        <div class="p-3">
                            {{-- Top bar --}}
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 bg-green-500 rounded-md flex items-center justify-center">
                                        <span class="text-white text-[7px] font-black">M</span>
                                    </div>
                                    <span class="text-[9px] font-black text-gray-700">Kỳ thi giữa kỳ 1</span>
                                    <span class="bg-green-100 text-green-600 text-[7px] font-black px-1.5 py-0.5 rounded-full">● Đang diễn ra</span>
                                </div>
                                <button class="bg-green-500 text-white text-[8px] font-black px-2.5 py-1 rounded-lg shadow-[0_2px_0_#15803d]">Vào thi</button>
                            </div>
                            {{-- Search --}}
                            <div class="flex gap-2 mb-3">
                                <div class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 flex items-center gap-1.5">
                                    <svg width="9" height="9" fill="none" viewBox="0 0 9 9"><circle cx="4" cy="4" r="3" stroke="#9ca3af" stroke-width="1"/><path d="M6.5 6.5l1.5 1.5" stroke="#9ca3af" stroke-width="1" stroke-linecap="round"/></svg>
                                    <span class="text-[8px] text-gray-400">Tìm kiếm thí sinh...</span>
                                </div>
                                <div class="flex gap-1">
                                    <button class="bg-green-500 text-white text-[8px] font-black px-2.5 py-1.5 rounded-lg shadow-[0_2px_0_#15803d]">Màn lưới</button>
                                    <button class="bg-gray-100 text-gray-500 text-[8px] font-semibold px-2.5 py-1.5 rounded-lg">Danh sách</button>
                                </div>
                            </div>
                            {{-- Room grid --}}
                            <div class="grid grid-cols-4 gap-2">
                                @foreach([
                                    ['bg-amber-100', '1'],
                                    ['bg-green-100', '2'],
                                    ['bg-blue-100', '3'],
                                    ['bg-orange-100', '4'],
                                ] as [$bg, $room])
                                <div class="rounded-xl overflow-hidden border border-gray-100 shadow-sm">
                                    <div class="h-16 {{ $bg }} flex items-center justify-center relative">
                                        <svg width="28" height="20" fill="none" viewBox="0 0 28 20">
                                            <rect x="0" y="4" width="28" height="16" rx="2" fill="#d1d5db"/>
                                            <rect x="3" y="7" width="22" height="10" rx="1" fill="#f9fafb"/>
                                            <rect x="5" y="9" width="7" height="6" rx="0.5" fill="#e5e7eb"/>
                                            <rect x="16" y="9" width="7" height="6" rx="0.5" fill="#e5e7eb"/>
                                            <rect x="10" y="0" width="8" height="4" rx="1" fill="#9ca3af"/>
                                            <rect x="12" y="18" width="4" height="2" rx="0.5" fill="#9ca3af"/>
                                        </svg>
                                        <span class="absolute top-1 right-1.5 text-[7px] font-black text-gray-500">{{ $room }}</span>
                                    </div>
                                    <div class="p-1.5 bg-white">
                                        <p class="text-[7px] font-black text-gray-700">Phòng {{ $room }}</p>
                                        <div class="flex items-center justify-between mt-0.5">
                                            <span class="text-[6px] text-gray-400">30 thí sinh</span>
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block"></span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            {{-- Info rows --}}
                            <div class="mt-3 space-y-1">
                                @foreach([
                                    ['Toán học', '11/07/2024', '120', '115', 'bg-green-500', 'Đang diễn ra'],
                                    ['Vật lý', '10/12/2024', '60', '0', 'bg-green-400', 'Sắp diễn ra'],
                                    ['Hóa học', '05/09/2024', '143', '0', 'bg-orange-400', 'Ngưng diễn'],
                                ] as [$subject, $date, $total, $done, $btnBg, $btnLabel])
                                <div class="flex items-center gap-2 text-[8px] py-1.5 border-b border-gray-50">
                                    <span class="text-gray-700 font-bold w-16 truncate">{{ $subject }}</span>
                                    <span class="text-gray-400 w-20">{{ $date }}</span>
                                    <span class="text-gray-500 w-8 text-center">{{ $total }}</span>
                                    <span class="text-gray-500 w-8 text-center">{{ $done }}</span>
                                    <span class="{{ $btnBg }} text-white text-[7px] font-black px-2 py-0.5 rounded-md ml-auto">{{ $btnLabel }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Browser mockup 2 (front - Quản lý kỳ thi table) --}}
                    <div class="relative z-10 w-[400px] mt-36 -ml-10 bg-white rounded-2xl shadow-2xl border border-gray-100 -rotate-1">
                        <div class="bg-gray-50 border-b border-gray-100 px-4 py-2.5 flex items-center gap-2 rounded-t-2xl">
                            <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-yellow-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                            <div class="flex-1 bg-white rounded-md h-5 mx-3 border border-gray-200 flex items-center px-2 gap-1.5">
                                <svg width="8" height="8" fill="none" viewBox="0 0 8 8"><rect x="0.5" y="0.5" width="7" height="7" rx="1" stroke="#d1d5db"/></svg>
                                <span class="text-[9px] text-gray-400">app.mindigo.vn/quan-ly-ky-thi</span>
                            </div>
                        </div>
                        <div class="p-3">
                            {{-- Header --}}
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="text-[10px] font-black text-gray-800">Quản lý kỳ thi</span>
                                <div class="flex gap-1.5">
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-2 py-1 flex items-center gap-1">
                                        <svg width="8" height="8" fill="none" viewBox="0 0 8 8"><circle cx="3.5" cy="3.5" r="2.5" stroke="#9ca3af" stroke-width="1"/><path d="M5.5 5.5l2 2" stroke="#9ca3af" stroke-width="1" stroke-linecap="round"/></svg>
                                        <span class="text-[8px] text-gray-400">Tìm kiếm kỳ thi...</span>
                                    </div>
                                    <button class="bg-green-500 text-white text-[8px] font-black px-2.5 py-1 rounded-lg shadow-[0_2px_0_#15803d]">Thêm mới</button>
                                </div>
                            </div>
                            {{-- Table header --}}
                            <div class="grid gap-1 mb-1" style="grid-template-columns: 2fr 2fr 1fr 1fr 1fr 1.5fr 1fr;">
                                @foreach(['Tên kỳ thi', 'Thời gian', 'Số câu', 'Tham gia', 'Hoàn thành', 'Trạng thái', 'Hành động'] as $h)
                                <span class="text-[7px] font-black text-gray-400 uppercase">{{ $h }}</span>
                                @endforeach
                            </div>
                            {{-- Table rows --}}
                            @foreach([
                                ['Tổng ôn - Kỳ 1', '15/07/2024 - 20/07/2024', '7', '119', '98', 'Đang diễn ra', 'green'],
                                ['Thi Thật - THPT QG', '10/12/2024 - 11/12/2024', '40', '0', '4', 'Sắp diễn ra', 'blue'],
                                ['Đợt kiểm tra số 1', '05/09/2024 - 10/09/2024', '8', '143', '0', 'Sắp diễn ra', 'blue'],
                                ['Tổng - Ôn luyện thi', '20/07/2024 - 30/08/2024', '7', '169', '0', 'Ngưng', 'orange'],
                                ['Ôn tập nâng cao', '12/09/2024 - 20/09/2024', '5', '103', '148', 'Kết thúc', 'red'],
                            ] as [$name, $date, $questions, $joined, $done, $status, $color])
                            <div class="grid gap-1 py-1.5 border-b border-gray-50 items-center" style="grid-template-columns: 2fr 2fr 1fr 1fr 1fr 1.5fr 1fr;">
                                <span class="text-[8px] font-bold text-gray-700 truncate">{{ $name }}</span>
                                <span class="text-[7px] text-gray-400 leading-tight">{{ $date }}</span>
                                <span class="text-[8px] text-gray-500 text-center">{{ $questions }}</span>
                                <span class="text-[8px] text-gray-500 text-center">{{ $joined }}</span>
                                <span class="text-[8px] text-gray-500 text-center">{{ $done }}</span>
                                <span class="text-[7px] font-black px-1.5 py-0.5 rounded-md text-center
                                    {{ $color === 'green' ? 'bg-green-500 text-white' : '' }}
                                    {{ $color === 'blue' ? 'bg-blue-400 text-white' : '' }}
                                    {{ $color === 'orange' ? 'bg-orange-400 text-white' : '' }}
                                    {{ $color === 'red' ? 'bg-red-400 text-white' : '' }}
                                ">{{ $status }}</span>
                                <div class="flex gap-1 justify-center">
                                    <button class="w-5 h-5 bg-green-50 border border-green-100 rounded flex items-center justify-center">
                                        <svg width="9" height="9" fill="none" viewBox="0 0 9 9"><path d="M1 8l1.5-1.5M7.5 1.5l-5 5L1 8l1.5-1.5 5-5z" stroke="#16a34a" stroke-width="0.9" stroke-linecap="round"/></svg>
                                    </button>
                                    <button class="w-5 h-5 bg-red-50 border border-red-100 rounded flex items-center justify-center">
                                        <svg width="9" height="9" fill="none" viewBox="0 0 9 9"><path d="M2 2l5 5M7 2L2 7" stroke="#f87171" stroke-width="0.9" stroke-linecap="round"/></svg>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                            {{-- Pagination --}}
                            <div class="flex items-center justify-between mt-2.5">
                                <span class="text-[7px] text-gray-400">Số hàng hiển thị: <strong>10</strong> · xem tổng số: <strong>10</strong></span>
                                <div class="flex items-center gap-1">
                                    <button class="w-5 h-5 bg-gray-100 rounded text-[8px] text-gray-400">‹</button>
                                    <button class="w-5 h-5 bg-green-500 rounded text-[8px] text-white font-black shadow-[0_1px_0_#15803d]">1</button>
                                    <button class="w-5 h-5 bg-gray-100 rounded text-[8px] text-gray-400">›</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- Anytime Anywhere section --}}
    <section class="py-20 px-10 bg-white border-t border-gray-100">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col lg:flex-row items-center gap-20">

                {{-- LEFT: Desktop + Phone mockups --}}
                <div class="flex-1 relative flex items-center justify-center min-h-[480px]">

                    {{-- Dashed orbit circle --}}
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-[380px] h-[380px] rounded-full border-2 border-dashed border-green-200 opacity-60"></div>
                    </div>

                    {{-- Clock decoration --}}
                    <div class="absolute top-2 right-16 z-30" style="animation:floatStar 3s ease-in-out infinite">
                        <div class="w-16 h-16 bg-white rounded-full shadow-xl border-4 border-gray-100 flex items-center justify-center relative">
                            <div class="w-12 h-12 rounded-full border-4 border-pink-300 bg-pink-50 flex items-center justify-center">
                                <div class="relative w-6 h-6">
                                    {{-- Hour hand --}}
                                    <div class="absolute bottom-1/2 left-1/2 w-0.5 h-2.5 bg-gray-700 rounded-full origin-bottom" style="transform: translateX(-50%) rotate(-30deg)"></div>
                                    {{-- Minute hand --}}
                                    <div class="absolute bottom-1/2 left-1/2 w-0.5 h-3 bg-gray-500 rounded-full origin-bottom" style="transform: translateX(-50%) rotate(120deg)"></div>
                                    <div class="absolute top-1/2 left-1/2 w-1.5 h-1.5 bg-gray-700 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                                </div>
                            </div>
                            <div class="absolute -top-2 -left-1 w-3 h-3 bg-pink-300 rounded-full border-2 border-white"></div>
                            <div class="absolute -top-2 -right-1 w-3 h-3 bg-pink-300 rounded-full border-2 border-white"></div>
                        </div>
                    </div>

                    {{-- Decorative dots --}}
                    <div class="absolute top-12 left-12 w-3 h-3 bg-green-300 rounded-full opacity-60 pointer-events-none" style="animation:floatStar 3s ease-in-out infinite"></div>
                    <div class="absolute bottom-10 left-16 w-2 h-2 bg-green-400 rounded-full opacity-50 pointer-events-none" style="animation:floatStar 4s .5s ease-in-out infinite"></div>
                    <div class="absolute bottom-6 right-10 text-green-400 text-2xl pointer-events-none" style="animation:floatStar 4s ease-in-out infinite">✦</div>

                    {{-- Desktop browser mockup --}}
                    <div class="relative z-10 w-[400px]">
                        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
                            {{-- Browser bar --}}
                            <div class="bg-gray-50 border-b border-gray-100 px-4 py-2.5 flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-yellow-400"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                                <div class="flex items-center gap-1.5 ml-2">
                                    <div class="w-5 h-5 bg-green-500 rounded-md flex items-center justify-center">
                                        <span class="text-white text-[7px] font-black">M</span>
                                    </div>
                                    <span class="text-[9px] font-black text-gray-600">mindigo</span>
                                </div>
                                <div class="flex-1 bg-white rounded-md h-5 mx-2 border border-gray-200 flex items-center px-2">
                                    <span class="text-[8px] text-gray-400">app.mindigo.vn/lam-bai-thi</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 12 12"><circle cx="6" cy="4" r="2.5" stroke="#16a34a" stroke-width="1"/><path d="M2 11c0-2.209 1.79-4 4-4s4 1.791 4 4" stroke="#16a34a" stroke-width="1" stroke-linecap="round"/></svg>
                                    </div>
                                    <span class="text-[8px] font-bold text-gray-600 hidden sm:block">Khánh Phương</span>
                                </div>
                            </div>
                            <div class="flex" style="height: 290px;">
                                {{-- Left sidebar --}}
                                <div class="w-48 border-r border-gray-100 p-3 flex flex-col gap-3 bg-gray-50/50 overflow-hidden">
                                    <div>
                                        <p class="text-[8px] font-black text-gray-700 mb-0.5">Tiếng Anh chuyên ngành nâng cao</p>
                                        <p class="text-[7px] text-gray-400">Chủ đề: Thi thử</p>
                                        <p class="text-[7px] text-gray-400 mb-2">Thời gian còn lại</p>
                                        <div class="text-green-600 font-black text-sm">24:47</div>
                                    </div>
                                    <div class="flex gap-1">
                                        <button class="flex-1 bg-green-500 text-white text-[7px] font-black py-1 rounded-md shadow-[0_1px_0_#15803d]">Nộp bài</button>
                                        <button class="flex-1 bg-blue-500 text-white text-[7px] font-black py-1 rounded-md">Máy tính</button>
                                    </div>
                                    <div>
                                        <p class="text-[7px] font-black text-gray-500 mb-1.5">Danh sách phần thi (2)</p>
                                        <div class="space-y-1.5">
                                            <div class="bg-white rounded-lg p-2 border border-green-100 shadow-sm">
                                                <div class="flex items-center gap-1.5 mb-0.5">
                                                    <div class="w-3.5 h-3.5 bg-green-500 rounded-full flex items-center justify-center"><svg width="7" height="7" fill="none" viewBox="0 0 7 7"><path d="M1.5 3.5l1.5 1.5 2.5-3" stroke="white" stroke-width="1" stroke-linecap="round"/></svg></div>
                                                    <p class="text-[7px] font-black text-gray-700 leading-tight">Phần 1: Grammar and Vocabulary (Ngữ p...</p>
                                                </div>
                                                <p class="text-[6px] text-gray-400 ml-5">Tiến độ hoàn thành: 3/15</p>
                                            </div>
                                            <div class="bg-white rounded-lg p-2 border border-gray-100">
                                                <div class="flex items-center gap-1.5 mb-0.5">
                                                    <div class="w-3.5 h-3.5 bg-gray-200 rounded-full"></div>
                                                    <p class="text-[7px] font-bold text-gray-500 leading-tight">Phần 2: Reading Comprehension (Đọc hi...</p>
                                                </div>
                                                <p class="text-[6px] text-gray-400 ml-5">Tiến độ hoàn thành: 0/15</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Main content --}}
                                <div class="flex-1 overflow-hidden">
                                    <div class="flex h-full">
                                        {{-- Questions --}}
                                        <div class="flex-1 p-3 overflow-hidden space-y-3">
                                            {{-- Question 1 --}}
                                            <div class="bg-gray-50 rounded-xl p-2.5 border border-gray-100">
                                                <div class="flex justify-between items-center mb-1.5">
                                                    <p class="text-[8px] font-black text-gray-700">Câu 1</p>
                                                    <span class="text-[7px] text-gray-400">Một lựa chọn</span>
                                                </div>
                                                <p class="text-[8px] text-gray-600 italic mb-2">She ___ to the store yesterday.</p>
                                                <div class="space-y-1">
                                                    @foreach(['a. goes', 'b. going', 'c. went', 'd. go'] as $i => $opt)
                                                    <div class="flex items-center gap-1.5 py-0.5 px-1.5 rounded-md {{ $i === 2 ? 'bg-green-50 border border-green-200' : '' }}">
                                                        <div class="w-3 h-3 rounded-full border {{ $i === 2 ? 'border-green-500 bg-green-500' : 'border-gray-300' }} flex items-center justify-center shrink-0">
                                                            @if($i === 2)<div class="w-1.5 h-1.5 bg-white rounded-full"></div>@endif
                                                        </div>
                                                        <span class="text-[7px] {{ $i === 2 ? 'text-green-700 font-bold' : 'text-gray-500' }}">{{ $opt }}</span>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            {{-- Question 2 --}}
                                            <div class="bg-gray-50 rounded-xl p-2.5 border border-gray-100">
                                                <div class="flex justify-between items-center mb-1.5">
                                                    <p class="text-[8px] font-black text-gray-700">Câu 2</p>
                                                    <span class="text-[7px] text-gray-400">Một lựa chọn</span>
                                                </div>
                                                <p class="text-[8px] text-gray-600 italic mb-2">They ___ very happy about the news.</p>
                                                <div class="space-y-1">
                                                    @foreach(['a. was', 'b. were', 'c. is', 'd. been'] as $i => $opt)
                                                    <div class="flex items-center gap-1.5 py-0.5 px-1.5 rounded-md {{ $i === 1 ? 'bg-green-50 border border-green-200' : '' }}">
                                                        <div class="w-3 h-3 rounded-full border {{ $i === 1 ? 'border-green-500 bg-green-500' : 'border-gray-300' }} flex items-center justify-center shrink-0">
                                                            @if($i === 1)<div class="w-1.5 h-1.5 bg-white rounded-full"></div>@endif
                                                        </div>
                                                        <span class="text-[7px] {{ $i === 1 ? 'text-green-700 font-bold' : 'text-gray-500' }}">{{ $opt }}</span>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            {{-- Question 3 preview --}}
                                            <div class="bg-gray-50 rounded-xl p-2.5 border border-gray-100">
                                                <p class="text-[8px] font-black text-gray-700 mb-1">Câu 3</p>
                                                <p class="text-[8px] text-gray-600 italic">The cat is ___ the table.</p>
                                            </div>
                                        </div>
                                        {{-- Question navigator --}}
                                        <div class="w-24 border-l border-gray-100 p-2 bg-gray-50/50">
                                            <p class="text-[7px] font-black text-gray-400 mb-1.5">Mục lục câu hỏi</p>
                                            <div class="grid grid-cols-4 gap-1 mb-2">
                                                @foreach(range(1,8) as $n)
                                                <div class="w-4.5 h-4.5 rounded text-[6px] font-bold flex items-center justify-center
                                                    {{ $n <= 2 ? 'bg-green-500 text-white shadow-[0_1px_0_#15803d]' : 'bg-white border border-gray-200 text-gray-400' }}">{{ $n }}</div>
                                                @endforeach
                                            </div>
                                            <div class="bg-white rounded-lg p-1.5 border border-green-100">
                                                <p class="text-[6px] font-black text-green-700 mb-1">Mục lục câu hỏi</p>
                                                <div class="flex gap-1 flex-wrap">
                                                    @foreach(['1','2','3','4','5','6','7','8'] as $i => $n)
                                                    <div class="w-4 h-4 rounded text-[6px] font-bold flex items-center justify-center
                                                        {{ $i < 2 ? 'bg-green-500 text-white' : ($i === 4 ? 'bg-orange-400 text-white' : 'bg-gray-100 text-gray-400') }}">{{ $n }}</div>
                                                    @endforeach
                                                </div>
                                                <div class="mt-1.5 space-y-0.5">
                                                    <div class="flex items-center gap-1"><div class="w-2 h-2 bg-green-500 rounded-sm"></div><span class="text-[6px] text-gray-500">Đã trả lời</span></div>
                                                    <div class="flex items-center gap-1"><div class="w-2 h-2 bg-orange-400 rounded-sm"></div><span class="text-[6px] text-gray-500">Đánh dấu</span></div>
                                                    <div class="flex items-center gap-1"><div class="w-2 h-2 bg-gray-100 border border-gray-200 rounded-sm"></div><span class="text-[6px] text-gray-500">Chưa trả lời</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Phone mockup (overlapping) --}}
                    <div class="absolute bottom-0 right-4 z-20" style="transform: rotate(4deg); width: 120px;">
                        <div class="relative rounded-[2rem] shadow-2xl" style="background: linear-gradient(160deg, #d1fae5, #6ee7b7, #34d399); padding: 2px;">
                            <div class="absolute top-10 h-5 rounded-l-full" style="left:-3px; width:3px; background:#86efac;"></div>
                            <div class="absolute top-18 h-7 rounded-l-full" style="left:-3px; width:3px; background:#86efac;"></div>
                            <div class="absolute top-14 h-8 rounded-r-full" style="right:-3px; width:3px; background:#86efac;"></div>
                            <div class="bg-white overflow-hidden" style="border-radius: 1.8rem;">
                                <div class="flex justify-center pt-2 pb-0">
                                    <div class="bg-gray-900 rounded-full" style="height:12px; width:40px;"></div>
                                </div>
                                <div class="flex justify-between items-center px-3 py-0.5">
                                    <span class="text-[7px] font-black text-gray-700">17:30</span>
                                    <svg width="8" height="6" fill="#374151" viewBox="0 0 10 8"><rect x="0" y="4" width="2" height="4" rx="0.5"/><rect x="2.5" y="2.5" width="2" height="5.5" rx="0.5"/><rect x="5" y="1" width="2" height="7" rx="0.5"/><rect x="7.5" y="0" width="2" height="8" rx="0.5"/></svg>
                                </div>
                                {{-- Phone header --}}
                                <div class="bg-green-600 px-2 py-1.5">
                                    <p class="text-white text-[7px] font-black">Phần 1</p>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <div class="flex-1 bg-green-700 rounded-full h-1">
                                            <div class="bg-white h-1 rounded-full" style="width: 40%"></div>
                                        </div>
                                        <span class="text-green-200 text-[6px]">2/5</span>
                                    </div>
                                </div>
                                <div class="p-2 space-y-2">
                                    {{-- Câu hỏi --}}
                                    <div>
                                        <p class="text-[7px] font-black text-gray-600 mb-0.5">Câu 2</p>
                                        <p class="text-[6px] text-gray-500 italic mb-1.5">Choose the correct answer for the question below...</p>
                                        <div class="space-y-1">
                                            <div class="bg-green-50 border border-green-300 rounded-md px-1.5 py-1 flex items-center gap-1">
                                                <div class="w-2.5 h-2.5 rounded-full bg-green-500 flex items-center justify-center"><div class="w-1 h-1 bg-white rounded-full"></div></div>
                                                <span class="text-[6px] text-green-700 font-bold">a. were</span>
                                            </div>
                                            <div class="bg-red-50 border border-red-200 rounded-md px-1.5 py-1 flex items-center gap-1">
                                                <div class="w-2.5 h-2.5 rounded-full border border-red-300"></div>
                                                <span class="text-[6px] text-gray-500">Nhấp để xem đáp án còn lại</span>
                                            </div>
                                        </div>
                                        <div class="mt-1.5 bg-green-50 border border-green-100 rounded-md p-1.5">
                                            <p class="text-[6px] text-green-700 font-bold leading-tight">Giải thích: "were" dùng với chủ ngữ số nhiều trong quá khứ</p>
                                        </div>
                                    </div>
                                    {{-- Nav buttons --}}
                                    <div class="flex gap-1">
                                        <button class="flex-1 bg-gray-100 text-gray-500 text-[6px] font-bold py-1 rounded-md">← Trước</button>
                                        <button class="flex-1 bg-green-500 text-white text-[6px] font-black py-1 rounded-md shadow-[0_1px_0_#15803d]">Tiếp →</button>
                                    </div>
                                </div>
                                <div class="flex justify-center py-1.5">
                                    <div class="w-8 h-0.5 bg-gray-300 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- RIGHT: Text content --}}
                <div class="flex-1 flex flex-col gap-6">
                    <span class="bg-green-500 text-white text-xs font-black px-3 py-1 rounded-lg w-fit">LINH HOẠT</span>
                    <h2 class="text-4xl font-black text-gray-900 leading-tight">
                        Học tập <span class="text-green-600">mọi lúc mọi nơi</span>
                    </h2>
                    <div class="flex flex-col gap-5">
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><path d="M8 1C4.134 1 1 4.134 1 8s3.134 7 7 7 7-3.134 7-7-3.134-7-7-7z" stroke="#16a34a" stroke-width="1.3"/><path d="M1 8h14M8 1a10.5 10.5 0 010 14M8 1a10.5 10.5 0 000 14" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                            </div>
                            <p class="text-gray-500 text-sm leading-relaxed">Bất kể bạn đang ở đâu, chỉ cần có thiết bị kết nối internet, bạn đều có thể học tập.</p>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><rect x="1" y="3" width="10" height="8" rx="1.5" stroke="#16a34a" stroke-width="1.3"/><path d="M3 11v1.5M8 11v1.5M1.5 12.5h8" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/><rect x="11" y="6" width="4" height="6" rx="1" stroke="#16a34a" stroke-width="1.3"/><path d="M13 13v1" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                            </div>
                            <p class="text-gray-500 text-sm leading-relaxed">Bạn có thể học trên điện thoại thông minh, máy tính bảng, laptop hoặc máy tính để bàn.</p>
                        </div>
                    </div>
                    <a href="#" class="bg-green-500 hover:bg-green-400 text-white font-black text-sm px-7 py-3.5 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 transition-all w-fit">
                        Bắt đầu ngay
                    </a>
                </div>

            </div>
        </div>
    </section>

    {{-- Testimonials section --}}
    <section class="py-20 bg-green-50 border-t border-green-100 overflow-hidden">
        <div class="max-w-7xl mx-auto px-10">
            {{-- Title --}}
            <div class="text-center mb-12">
                <h2 class="text-3xl font-black text-green-600">Phản hồi của khách hàng</h2>
            </div>

            {{-- Center rating card + floating avatars --}}
            <div class="relative flex items-center justify-center mb-14 h-36">
                {{-- Floating avatars left --}}
                <div class="absolute left-1/4 -top-2 w-16 h-16 rounded-2xl overflow-hidden shadow-lg border-2 border-white rotate-3">
                    <img src="https://api.dicebear.com/9.x/personas/svg?seed=Thu&backgroundColor=d1fae5" class="w-full h-full object-cover bg-green-100" alt="user">
                </div>
                <div class="absolute left-1/3 top-8 w-14 h-14 rounded-2xl overflow-hidden shadow-lg border-2 border-white -rotate-2">
                    <img src="https://api.dicebear.com/9.x/personas/svg?seed=Linh&backgroundColor=bbf7d0" class="w-full h-full object-cover bg-green-200" alt="user">
                </div>

                {{-- Floating avatars right --}}
                <div class="absolute right-1/4 -top-2 w-16 h-16 rounded-2xl overflow-hidden shadow-lg border-2 border-white -rotate-3">
                    <img src="https://api.dicebear.com/9.x/personas/svg?seed=Hoa&backgroundColor=86efac" class="w-full h-full object-cover bg-green-300" alt="user">
                </div>
                <div class="absolute right-1/3 top-8 w-14 h-14 rounded-2xl overflow-hidden shadow-lg border-2 border-white rotate-2">
                    <img src="https://api.dicebear.com/9.x/personas/svg?seed=Mai&backgroundColor=4ade80" class="w-full h-full object-cover bg-green-400" alt="user">
                </div>

                {{-- Decorative shapes --}}
                <div class="absolute left-8 top-4 text-green-400 text-4xl opacity-60 pointer-events-none" style="animation:floatStar 4s ease-in-out infinite">✦</div>
                <div class="absolute right-8 top-0 text-green-300 text-3xl opacity-50 pointer-events-none" style="animation:floatStar 3s .5s ease-in-out infinite">✦</div>
                <div class="absolute left-16 bottom-0 w-4 h-4 bg-green-300 rounded-full opacity-50 pointer-events-none" style="animation:floatStar 3s ease-in-out infinite"></div>
                <div class="absolute right-20 bottom-2 w-3 h-3 bg-green-400 rotate-45 opacity-40 pointer-events-none" style="animation:floatStar 4s .3s ease-in-out infinite"></div>

                {{-- Center rating card --}}
                <div class="relative z-10 bg-white rounded-2xl shadow-xl border border-green-100 px-8 py-4 flex items-center gap-5">
                    <div class="w-14 h-14 rounded-full overflow-hidden border-2 border-green-200 shrink-0">
                        <img src="https://api.dicebear.com/9.x/personas/svg?seed=Khanh&backgroundColor=d1fae5" class="w-full h-full object-cover bg-green-100" alt="user">
                    </div>
                    <div>
                        <div class="flex gap-1 text-yellow-400 text-xl mb-1.5">★★★★★</div>
                        <p class="text-gray-700 font-black text-sm">200,000+ khách hàng</p>
                        <div class="w-full bg-green-100 rounded-full h-1.5 mt-1.5">
                            <div class="bg-green-500 h-1.5 rounded-full" style="width: 92%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Scrolling testimonials --}}
            <div class="relative">
                {{-- Fade edges --}}
                <div class="absolute left-0 top-0 bottom-0 w-24 z-10 pointer-events-none" style="background: linear-gradient(to right, #f0fdf4, transparent)"></div>
                <div class="absolute right-0 top-0 bottom-0 w-24 z-10 pointer-events-none" style="background: linear-gradient(to left, #f0fdf4, transparent)"></div>

                <div class="flex gap-5 overflow-hidden">
                    <div class="flex gap-5 animate-marquee" style="animation: marquee 30s linear infinite;">
                        @foreach([
                            ['Nguyễn Thư', 'Đại học Thương mại', 'Thu', 'Đã sử dụng EduQuiz suốt và thực sự rất hài lòng. Tính năng làm bài thi giúp tiết kiệm được nhiều thời gian ôn luyện. Nhìn chung thì các bài tập đa dạng và giao diện trực quan khiến việc học trở nên thú vị hơn.'],
                            ['Gia Khánh', 'Đại học Thương mại', 'Khanh', 'Mình đã sử dụng EduQuiz suốt một thời gian dài và thật sự rất hài lòng. Tính năng thi thử giúp mình tiết kiệm được rất nhiều thời gian ôn tập, các bài tập đa dạng, giao diện trực quan khiến việc học trở nên hiệu quả.'],
                            ['Nguyễn Hà', 'Trường THPT Lê Quý Đôn - Hà Đông', 'Ha', 'Phần mềm này rất hay, giúp mình học tập và nâng cao kỹ năng ghi nhớ, lại còn rất dễ dùng nữa ai cũng có thể dùng được. Nói chung là rất hữu ích, các bạn học sinh/sinh viên nên sử dụng.'],
                            ['Hùng Mai', 'Đại học Kinh doanh và Công nghệ Hà Nội', 'Hung', 'Trong quá trình sử dụng EduQuiz để học thi, EduQuiz đã giúp em dễ dàng ghi nhớ được những kiến thức vốn rất hàn lâm. Hơn nữa, em được tiếp cận với bộ đề thi đa dạng và giao diện làm bài trực quan.'],
                            ['Su Trà', 'Học viện Công nghệ - Bưu chính Viễn thông', 'Su', 'Em mới biết EduQuiz gần đây khi tìm kiếm đề ôn tập. EduQuiz đã giúp em rất nhiều trong việc đang hôm em cần tìm đáp án và câu hỏi ở nhiều chủ đề khác nhau.'],
                            ['Minh Tuấn', 'Đại học Bách Khoa Hà Nội', 'Tuan', 'EduQuiz thực sự là công cụ học tập tuyệt vời. Mình đặc biệt thích tính năng tạo đề thi tự động từ tài liệu, tiết kiệm rất nhiều thời gian chuẩn bị cho các kỳ thi.'],
                        ] as [$name, $school, $seed, $review])
                        <div class="bg-white rounded-2xl border-2 border-green-100 shadow-md p-5 shrink-0 w-72 hover:border-green-300 hover:shadow-lg transition-all">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-green-200 shrink-0">
                                    <img src="https://api.dicebear.com/9.x/personas/svg?seed={{ $seed }}&backgroundColor=d1fae5" class="w-full h-full object-cover bg-green-100" alt="{{ $name }}">
                                </div>
                                <div>
                                    <p class="text-sm font-black text-gray-800">{{ $name }}</p>
                                    <p class="text-xs text-gray-400 leading-tight">{{ $school }}</p>
                                </div>
                            </div>
                            <div class="flex gap-0.5 text-yellow-400 text-sm mb-2">★★★★★</div>
                            <p class="text-gray-500 text-xs leading-relaxed">
                                {{ Str::limit($review, 120) }}
                                @if(strlen($review) > 120)
                                <a href="#" class="text-green-600 font-black"> Xem thêm</a>
                                @endif
                            </p>
                        </div>
                        @endforeach
                        {{-- Duplicate for seamless loop --}}
                        @foreach([
                            ['Nguyễn Thư', 'Đại học Thương mại', 'Thu', 'Đã sử dụng EduQuiz suốt và thực sự rất hài lòng. Tính năng làm bài thi giúp tiết kiệm được nhiều thời gian ôn luyện. Nhìn chung thì các bài tập đa dạng và giao diện trực quan khiến việc học trở nên thú vị hơn.'],
                            ['Gia Khánh', 'Đại học Thương mại', 'Khanh', 'Mình đã sử dụng EduQuiz suốt một thời gian dài và thật sự rất hài lòng. Tính năng thi thử giúp mình tiết kiệm được rất nhiều thời gian ôn tập, các bài tập đa dạng, giao diện trực quan.'],
                            ['Nguyễn Hà', 'Trường THPT Lê Quý Đôn - Hà Đông', 'Ha', 'Phần mềm này rất hay, giúp mình học tập và nâng cao kỹ năng ghi nhớ, lại còn rất dễ dùng nữa ai cũng có thể dùng được. Nói chung là rất hữu ích.'],
                            ['Hùng Mai', 'Đại học Kinh doanh và Công nghệ Hà Nội', 'Hung', 'Trong quá trình sử dụng EduQuiz để học thi, EduQuiz đã giúp em dễ dàng ghi nhớ được những kiến thức vốn rất hàn lâm và giao diện làm bài trực quan.'],
                            ['Su Trà', 'Học viện Công nghệ - Bưu chính Viễn thông', 'Su', 'Em mới biết EduQuiz gần đây khi tìm kiếm đề ôn tập. EduQuiz đã giúp em rất nhiều trong việc tìm đáp án và câu hỏi ở nhiều chủ đề khác nhau.'],
                            ['Minh Tuấn', 'Đại học Bách Khoa Hà Nội', 'Tuan', 'EduQuiz thực sự là công cụ học tập tuyệt vời. Mình đặc biệt thích tính năng tạo đề thi tự động từ tài liệu, tiết kiệm rất nhiều thời gian chuẩn bị.'],
                        ] as [$name, $school, $seed, $review])
                        <div class="bg-white rounded-2xl border-2 border-green-100 shadow-md p-5 shrink-0 w-72 hover:border-green-300 hover:shadow-lg transition-all">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-green-200 shrink-0">
                                    <img src="https://api.dicebear.com/9.x/personas/svg?seed={{ $seed }}&backgroundColor=d1fae5" class="w-full h-full object-cover bg-green-100" alt="{{ $name }}">
                                </div>
                                <div>
                                    <p class="text-sm font-black text-gray-800">{{ $name }}</p>
                                    <p class="text-xs text-gray-400 leading-tight">{{ $school }}</p>
                                </div>
                            </div>
                            <div class="flex gap-0.5 text-yellow-400 text-sm mb-2">★★★★★</div>
                            <p class="text-gray-500 text-xs leading-relaxed">
                                {{ Str::limit($review, 120) }}
                                @if(strlen($review) > 120)
                                <a href="#" class="text-green-600 font-black"> Xem thêm</a>
                                @endif
                            </p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
@keyframes floatStar {
    0%,100% { transform: translateY(0) rotate(0deg); }
    50%      { transform: translateY(-12px) rotate(20deg); }
}

@keyframes marquee {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
.animate-marquee {
    display: flex;
    width: max-content;
}
</style>

<script>
(function(){
    const words = ['học tập ôn thi', 'tổ chức kỳ thi', 'tự động tạo câu hỏi', 'tạo nhanh đề thi'];
    let wordIndex = 0;
    let charIndex = 0;
    let deleting = false;
    const el = document.getElementById('typewriter');
    if (!el) return;

    function type() {
        const current = words[wordIndex];
        if (!deleting) {
            el.textContent = current.substring(0, charIndex + 1);
            charIndex++;
            if (charIndex === current.length) {
                setTimeout(() => { deleting = true; type(); }, 1500);
                return;
            }
            setTimeout(type, 80);
        } else {
            el.textContent = current.substring(0, charIndex - 1);
            charIndex--;
            if (charIndex === 0) {
                deleting = false;
                wordIndex = (wordIndex + 1) % words.length;
                setTimeout(type, 300);
                return;
            }
            setTimeout(type, 40);
        }
    }
    type();
})();
</script>

@endsection