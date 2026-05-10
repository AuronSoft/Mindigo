{{-- Personalization section --}}
<section class="py-20 px-10 bg-white border-t border-gray-100">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col lg:flex-row items-center gap-20">

            {{-- LEFT: Phone mockups --}}
            <div class="flex-1 relative flex items-center justify-center min-h-130">

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
                <div class="relative z-10 -mr-7 mt-8" style="transform: rotate(-5deg); width: 195px;">
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