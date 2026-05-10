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
        <div class="flex-1 relative flex items-center justify-center min-h-120">

            {{-- Floating AI badge --}}
            <div class="absolute -top-4 left-6 bg-white border-2 border-green-200 rounded-2xl px-4 py-2.5 flex items-center gap-3 z-20"
                style="box-shadow: 0 8px 32px rgba(22,163,74,0.18), 0 2px 8px rgba(0,0,0,0.08); animation: floatBadge 3s ease-in-out infinite;">
                <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center text-white font-black text-base shadow-inner">AI</div>
                <div>
                    <p class="text-green-700 font-black text-xs leading-none">Powered by AI</p>
                    <p class="text-green-400 text-[10px] font-semibold mt-0.5">✦ Tạo đề thi tự động</p>
                </div>
            </div>

            {{-- Stats pill top right --}}
            <div class="absolute top-0 right-0 bg-green-500 text-white text-xs font-black px-4 py-2 rounded-2xl z-20 flex items-center gap-2"
                style="box-shadow: 0 4px 0 #15803d, 0 8px 20px rgba(22,163,74,0.3);">
                <svg width="14" height="14" fill="none" viewBox="0 0 14 14">
                    <rect x="1" y="1" width="12" height="12" rx="3" stroke="white" stroke-width="1.5"/>
                    <path d="M4 7h6M4 4.5h3M4 9.5h4" stroke="white" stroke-width="1.2" stroke-linecap="round"/>
                </svg>
                Tính năng nâng cao
            </div>

            {{-- Main card with 3D effect --}}
            <div class="bg-white rounded-3xl w-full overflow-hidden mt-10"
                style="
                box-shadow:
                    0 2px 0 #d1fae5,
                    0 6px 0 #bbf7d0,
                    0 12px 0 #86efac,
                    0 20px 40px rgba(22,163,74,0.15),
                    0 40px 80px rgba(0,0,0,0.08);
                border: 1.5px solid #d1fae5;
                transform: perspective(1200px) rotateX(2deg);
                transform-style: preserve-3d;
                ">

                {{-- Browser bar --}}
                <div class="bg-gray-50 border-b border-gray-100 px-4 py-2.5 flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-400 shadow-sm"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-400 shadow-sm"></div>
                    <div class="w-3 h-3 rounded-full bg-green-400 shadow-sm"></div>
                    <div class="flex-1 bg-white rounded-lg h-6 mx-4 border border-gray-200 flex items-center px-3 gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                        <span class="text-[10px] text-gray-400 font-medium">app.mindigo.vn/de-thi/tao-moi</span>
                    </div>
                </div>

                <div class="p-5">
                    {{-- Upload bar --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex-1 bg-green-50 border-2 border-dashed border-green-300 rounded-xl px-3 py-2.5 text-xs font-bold text-green-700 flex items-center gap-2 hover:bg-green-100 transition cursor-pointer"
                            style="box-shadow: inset 0 2px 4px rgba(22,163,74,0.06);">
                            <svg width="14" height="14" fill="none" viewBox="0 0 14 14">
                                <path d="M7 1v7M4.5 3.5L7 1l2.5 2.5" stroke="#16a34a" stroke-width="1.4" stroke-linecap="round"/>
                                <rect x="1" y="10" width="12" height="3" rx="1.5" fill="#16a34a" opacity=".2"/>
                            </svg>
                            Upload tài liệu đề thi
                        </div>
                        <button class="text-white text-xs font-black px-4 py-2.5 rounded-xl whitespace-nowrap transition hover:brightness-110"
                                style="background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 4px 0 #15803d, 0 6px 12px rgba(22,163,74,0.3);">
                            Duyệt kết quả
                        </button>
                    </div>

                    {{-- Action pills --}}
                    <div class="flex gap-2 mb-5">
                        <span class="bg-red-50 text-red-400 border border-red-100 text-xs font-black px-3 py-1 rounded-lg">↩ Trả về</span>
                        <span class="text-white text-xs font-black px-3 py-1 rounded-lg"
                            style="background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 3px 0 #15803d;">
                            ✓ Lưu đề thi
                        </span>
                    </div>

                    <div class="flex gap-5">

                        {{-- Left sidebar --}}
                        <div class="w-44 shrink-0 space-y-3">
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-wide mb-2">Danh sách phần thi</p>
                                <div class="space-y-1.5">
                                    <div class="text-white text-xs font-black px-3 py-1.5 rounded-lg text-center"
                                        style="background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 3px 0 #15803d, 0 6px 12px rgba(22,163,74,0.25);">
                                        Phần 1
                                    </div>
                                    <div class="bg-gray-100 text-gray-500 text-xs font-semibold px-2 py-1.5 rounded-lg text-center leading-tight hover:bg-gray-200 transition cursor-pointer">
                                        Part 2: Advanced
                                    </div>
                                </div>
                            </div>

                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-wide mb-2">Mục lục câu hỏi (10)</p>
                                <div class="grid grid-cols-5 gap-1">
                                    @foreach(range(1,10) as $n)
                                    <div class="w-7 h-7 rounded-md flex items-center justify-center text-[10px] font-black transition cursor-pointer"
                                        style="{{ $n <= 3
                                        ? 'background: linear-gradient(135deg, #22c55e, #16a34a); color: white; box-shadow: 0 2px 0 #15803d, 0 4px 8px rgba(22,163,74,0.3);'
                                        : 'background: #f3f4f6; color: #9ca3af;' }}">
                                        {{ $n }}
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Progress --}}
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-3 border border-green-100"
                                style="box-shadow: inset 0 1px 3px rgba(22,163,74,0.08);">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-[10px] font-black text-green-700">Tiến độ</p>
                                    <p class="text-[10px] font-black text-green-500">30%</p>
                                </div>
                                <div class="w-full bg-green-100 rounded-full h-2 mb-1.5 overflow-hidden">
                                    <div class="h-2 rounded-full" style="width:30%; background: linear-gradient(90deg, #22c55e, #16a34a); box-shadow: 0 0 6px rgba(22,163,74,0.5);"></div>
                                </div>
                                <p class="text-[10px] text-green-600 font-bold">3/10 câu hoàn thành</p>
                            </div>
                        </div>

                        {{-- Question list --}}
                        <div class="flex-1 space-y-3 min-w-0">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-wide">Danh sách câu hỏi</p>

                            {{-- Question 1 --}}
                            <div class="rounded-xl p-3 space-y-1.5 border border-green-100 transition"
                                style="background: linear-gradient(135deg, #f0fdf4, #f7fef9); box-shadow: 0 2px 8px rgba(22,163,74,0.08);">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-xs font-black text-gray-700">Câu 1 <span class="text-gray-400 font-semibold">(Một đáp án)</span></p>
                                    <span class="bg-green-100 text-green-700 text-[10px] font-black px-2 py-0.5 rounded-full border border-green-200">✓ Có đáp án</span>
                                </div>
                                <p class="text-xs font-semibold text-gray-600 italic">The bacterium E.coli:</p>
                                <div class="flex items-center gap-1.5 text-xs text-gray-400"><span class="w-4 h-4 rounded-full bg-red-100 text-red-400 font-black flex items-center justify-center text-[9px]">✗</span> Absolutely aerobic</div>
                                <div class="flex items-center gap-1.5 text-xs text-gray-400"><span class="w-4 h-4 rounded-full bg-red-100 text-red-400 font-black flex items-center justify-center text-[9px]">✗</span> Gram-negative cocc...</div>
                                <div class="flex items-center gap-1.5 text-xs text-gray-400"><span class="w-4 h-4 rounded-full bg-red-100 text-red-400 font-black flex items-center justify-center text-[9px]">✗</span> Negative indole test</div>
                                <div class="flex items-center gap-1.5 text-xs text-green-600 font-semibold"><span class="w-4 h-4 rounded-full bg-green-100 text-green-500 font-black flex items-center justify-center text-[9px]">✓</span> Negative Vosges-Proskauer</div>
                            </div>

                            {{-- Question 2 --}}
                            <div class="rounded-xl p-3 border border-gray-100 hover:border-green-200 transition"
                                style="background: #fafafa; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-xs font-black text-gray-700">Câu 2 <span class="text-gray-400 font-semibold">(Nhiều đáp án)</span></p>
                                    <span class="bg-green-100 text-green-700 text-[10px] font-black px-2 py-0.5 rounded-full border border-green-200">✓ Có đáp án</span>
                                </div>
                                <p class="text-xs text-gray-500 leading-relaxed">The steps for quantifying E.coli are arranged in order:</p>
                                <div class="flex items-center gap-1.5 text-xs text-gray-400 mt-1.5"><span class="w-4 h-4 rounded-full bg-red-100 text-red-400 font-black flex items-center justify-center text-[9px]">✗</span> Prepare the medium</div>
                                <div class="flex items-center gap-1.5 text-xs text-green-600 font-semibold"><span class="w-4 h-4 rounded-full bg-green-100 text-green-500 font-black flex items-center justify-center text-[9px]">✓</span> Serial dilution method</div>
                                <div class="flex items-center gap-1.5 text-xs text-green-600 font-semibold"><span class="w-4 h-4 rounded-full bg-green-100 text-green-500 font-black flex items-center justify-center text-[9px]">✓</span> Count colonies after 24h</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Phone mockup --}}
            <div class="absolute -bottom-4 -left-20 z-10"style="transform: perspective(800px) rotateY(20deg) rotateX(4deg) rotate(-6deg); width: 160px;">
                <div class="relative rounded-[2.8rem] p-1"
                    style="background: linear-gradient(145deg, #e2e8f0, #cbd5e1); box-shadow: 0 25px 50px rgba(0,0,0,0.22), 0 8px 0 #94a3b8, inset 0 1px 0 rgba(255,255,255,0.9);">
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
                                    <span class="text-white text-[6px] font-black px-1 py-0.5 rounded-full" style="background: linear-gradient(135deg, #22c55e, #16a34a);">PRO</span>
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