{{-- Contact Section --}}
<section id="section-contact" class="hidden">
    <div class="max-w-7xl mx-auto px-10 py-20">

        {{-- Header --}}
        <div class="text-center mb-16">
            <span class="inline-block bg-green-50 text-green-700 text-xs font-bold px-4 py-1.5 rounded-full mb-4">LIÊN HỆ</span>
            <h1 class="text-4xl font-black text-gray-900 mb-4">Chúng tôi luôn sẵn sàng <span class="text-green-500">lắng nghe bạn</span></h1>
            <p class="text-gray-500 text-sm max-w-lg mx-auto leading-relaxed">Dù bạn có câu hỏi về sản phẩm, cần hỗ trợ kỹ thuật hay muốn hợp tác — đội ngũ Mindigo luôn ở đây để giúp bạn.</p>
        </div>

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-16">

            {{-- Form --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm">
                <h2 class="text-lg font-black text-gray-800 mb-6">Gửi tin nhắn cho chúng tôi</h2>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 mb-1.5 block">Họ và tên</label>
                        <input type="text" placeholder="Nguyễn Văn A" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100 transition" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 mb-1.5 block">Email</label>
                        <input type="email" placeholder="email@example.com" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100 transition" />
                    </div>
                </div>
                <div class="mb-4">
                    <label class="text-xs font-bold text-gray-500 mb-1.5 block">Số điện thoại</label>
                    <input type="tel" placeholder="0912 345 678" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100 transition" />
                </div>
                <div class="mb-4">
                    <label class="text-xs font-bold text-gray-500 mb-1.5 block">Chủ đề</label>
                    <select class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100 transition text-gray-600">
                        <option>Hỗ trợ kỹ thuật</option>
                        <option>Tư vấn sản phẩm</option>
                        <option>Hợp tác kinh doanh</option>
                        <option>Báo lỗi</option>
                        <option>Khác</option>
                    </select>
                </div>
                <div class="mb-6">
                    <label class="text-xs font-bold text-gray-500 mb-1.5 block">Nội dung</label>
                    <textarea rows="4" placeholder="Nhập nội dung bạn muốn liên hệ..." class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-400 focus:ring-2 focus:ring-green-100 transition resize-none"></textarea>
                </div>
                <button class="w-full bg-green-500 hover:bg-green-400 active:bg-green-600 text-white font-black text-sm py-3 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 active:shadow-none active:translate-y-1 transition-all">
                    Gửi tin nhắn
                </button>
            </div>

            {{-- Info --}}
            <div class="flex flex-col gap-4">

                {{-- Brand card --}}
                <div class="bg-green-500 rounded-2xl overflow-hidden text-white relative">
                    {{-- Ảnh học tập --}}
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&q=80" 
                        alt="Học tập cùng Mindigo"
                        class="w-full h-40 object-cover opacity-60" />
                    {{-- Nội dung đè lên ảnh --}}
                    <div class="p-6">
                        <div class="text-xs opacity-80 mb-1">Nền tảng thi trắc nghiệm #1 Việt Nam</div>
                        <div class="text-2xl font-black mb-2">Mindigo</div>
                        <p class="text-sm opacity-85 leading-relaxed mb-4">Giúp hơn 200.000+ học sinh, sinh viên và giảng viên tạo đề thi, ôn luyện và quản lý lớp học hiệu quả hơn mỗi ngày.</p>
                        <div class="flex gap-2 flex-wrap">
                            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">200.000+ người dùng</span>
                            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">AI-powered</span>
                            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">Made in Vietnam</span>
                        </div>
                    </div>
                </div>

                {{-- Info cards --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-4 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-black text-gray-800 mb-0.5">Địa chỉ</div>
                        <div class="text-xs text-gray-500 leading-relaxed">Tầng 5, 97 Võ Văn Tần, Phường 6, Quận 3, TP. Hồ Chí Minh</div>
                    </div>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl p-4 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-black text-gray-800 mb-0.5">Hotline hỗ trợ</div>
                        <div class="text-xs text-gray-500">1800 6868 (miễn phí)</div>
                        <div class="text-xs text-gray-400 mt-0.5">Thứ 2 – Thứ 6: 8:00 – 18:00</div>
                    </div>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl p-4 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-black text-gray-800 mb-0.5">Email</div>
                        <div class="text-xs text-green-500">support@mindigo.vn</div>
                        <div class="text-xs text-gray-400 mt-0.5">Phản hồi trong vòng 24 giờ</div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Map --}}
        <div class="mb-16">
            <div class="text-xs font-bold text-green-700 bg-green-50 rounded-full px-4 py-1.5 inline-block mb-4">BẢN ĐỒ</div>
            <h2 class="text-xl font-black text-gray-800 mb-6">Tìm chúng tôi tại đây</h2>
            <div class="rounded-2xl overflow-hidden border border-gray-100">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4946508904!2d106.6884!3d10.7769!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDQ2JzM3LjAiTiAxMDbCsDQxJzE4LjIiRQ!5e0!3m2!1svi!2svn!4v1234567890"
                    width="100%" height="300" style="border:0; display:block;" allowfullscreen loading="lazy">
                </iframe>
            </div>
        </div>

        {{-- FAQ --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-8">
            <div class="text-xs font-bold text-green-700 bg-green-50 rounded-full px-4 py-1.5 inline-block mb-4">FAQ</div>
            <h2 class="text-xl font-black text-gray-800 mb-6">Câu hỏi thường gặp</h2>
            <div class="divide-y divide-gray-100">
                @foreach([
                    ['q' => 'Mindigo có miễn phí không?', 'a' => 'Mindigo có gói miễn phí với đầy đủ tính năng cơ bản. Bạn có thể nâng cấp lên gói Pro để trải nghiệm các tính năng nâng cao như tạo đề bằng AI, phòng thi ảo và quản lý lớp học không giới hạn.'],
                    ['q' => 'Tôi có thể tạo đề thi từ file Word/PDF không?', 'a' => 'Có! Mindigo hỗ trợ tải lên file Word (.docx) và PDF. AI sẽ tự động phân tích nội dung và tạo ra bộ câu hỏi trắc nghiệm chính xác trong vài phút.'],
                    ['q' => 'Mindigo có hỗ trợ tổ chức thi trực tuyến không?', 'a' => 'Có. Tính năng Phòng thi ảo cho phép bạn tổ chức kỳ thi với hàng trăm thí sinh cùng lúc, theo dõi tiến độ theo thời gian thực và xuất kết quả chi tiết.'],
                    ['q' => 'Làm sao để được hỗ trợ kỹ thuật?', 'a' => 'Bạn có thể liên hệ qua hotline 1800 6868 (miễn phí), email support@mindigo.vn hoặc gửi tin nhắn trực tiếp qua form trên trang này.'],
                    ['q' => 'Mindigo có phù hợp với trường đại học và doanh nghiệp không?', 'a' => 'Hoàn toàn phù hợp! Mindigo đang phục vụ hàng nghìn trường đại học, trung tâm đào tạo và doanh nghiệp trên cả nước.'],
                ] as $faq)
                <div x-data="{ open: false }" class="py-4 cursor-pointer" @click="open = !open">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-black text-gray-800">{{ $faq['q'] }}</span>
                        <svg class="w-4 h-4 text-green-500 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div x-show="open" x-transition class="text-xs text-gray-500 leading-relaxed mt-3">{{ $faq['a'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>