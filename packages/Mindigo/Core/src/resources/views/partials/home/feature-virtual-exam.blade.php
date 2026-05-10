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