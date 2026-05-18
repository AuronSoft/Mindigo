{{-- Pricing Section --}}
<section id="section-pricing" class="hidden">
    <div class="max-w-7xl mx-auto px-10 py-20">

        {{-- Header --}}
        <div class="text-center mb-16">
            <span class="inline-block bg-green-50 text-green-700 text-xs font-bold px-4 py-1.5 rounded-full mb-4">BẢNG GIÁ</span>
            <h1 class="text-4xl font-black text-gray-900 mb-4">Gói dịch vụ <span class="text-green-500">phù hợp với bạn</span></h1>
            <p class="text-gray-500 text-sm max-w-lg mx-auto leading-relaxed">Bắt đầu miễn phí, nâng cấp khi bạn sẵn sàng. Không ràng buộc hợp đồng, hủy bất kỳ lúc nào.</p>

            {{-- Toggle tháng/năm --}}
            <div class="inline-flex items-center gap-3 mt-8 bg-gray-100 rounded-2xl p-1.5">
                <button id="toggle-monthly"
                    onclick="setPricingPeriod('monthly')"
                    class="text-sm font-black px-5 py-2 rounded-xl transition-all bg-white text-green-600 shadow-sm">
                    Hàng tháng
                </button>
                <button id="toggle-yearly"
                    onclick="setPricingPeriod('yearly')"
                    class="text-sm font-black px-5 py-2 rounded-xl transition-all text-gray-400 hover:text-gray-600">
                    Hàng năm
                    <span class="ml-1.5 bg-green-500 text-white text-xs font-black px-2 py-0.5 rounded-full">-20%</span>
                </button>
            </div>
        </div>

        {{-- Pricing Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-20">

            {{-- Free --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm flex flex-col hover:shadow-md transition-shadow">
                <div class="mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Miễn phí</div>
                    <div class="flex items-end gap-1 mb-1">
                        <span class="text-4xl font-black text-gray-900">0đ</span>
                    </div>
                    <div class="text-xs text-gray-400">Mãi mãi miễn phí</div>
                </div>

                <div class="flex flex-col gap-3 mb-8 flex-1">
                    @foreach([
                        ['ok' => true,  'text' => 'Tạo tối đa 5 đề thi/tháng'],
                        ['ok' => true,  'text' => 'Tối đa 30 câu hỏi/đề'],
                        ['ok' => true,  'text' => '10 lượt ôn thi/tháng'],
                        ['ok' => true,  'text' => 'Hỗ trợ trắc nghiệm cơ bản'],
                        ['ok' => false, 'text' => 'Tạo đề bằng AI'],
                        ['ok' => false, 'text' => 'Phòng thi ảo'],
                        ['ok' => false, 'text' => 'Quản lý lớp học'],
                        ['ok' => false, 'text' => 'Hỗ trợ ưu tiên 24/7'],
                    ] as $f)
                    <div class="flex items-center gap-3">
                        @if($f['ok'])
                        <div class="w-5 h-5 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm text-gray-700">{{ $f['text'] }}</span>
                        @else
                        <div class="w-5 h-5 rounded-full bg-gray-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <span class="text-sm text-gray-400">{{ $f['text'] }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>

                <a href="#" class="block text-center border-2 border-gray-200 text-gray-600 font-black text-sm py-3 rounded-xl hover:border-green-400 hover:text-green-600 transition-all">
                    Bắt đầu miễn phí
                </a>
            </div>

            {{-- Pro - Best seller --}}
            <div class="bg-green-500 rounded-2xl p-8 shadow-[0_8px_0_#15803d] flex flex-col relative overflow-hidden">
                {{-- Badge --}}
                <div class="absolute top-5 right-5">
                    <span class="bg-white text-green-600 text-xs font-black px-3 py-1 rounded-full shadow-sm">⭐ PHỔ BIẾN NHẤT</span>
                </div>

                {{-- Decoration --}}
                <div class="absolute -bottom-8 -right-8 w-40 h-40 bg-green-400 rounded-full opacity-30"></div>
                <div class="absolute -top-6 -left-6 w-28 h-28 bg-green-600 rounded-full opacity-20"></div>

                <div class="mb-6 relative">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div class="text-xs font-bold text-white/70 uppercase tracking-wider mb-1">Pro</div>
                    <div class="flex items-end gap-1 mb-1">
                        <span class="pricing-price text-4xl font-black text-white" data-monthly="199,000đ" data-yearly="159,000đ">199,000đ</span>
                        <span class="pricing-period text-white/70 text-sm mb-1">/tháng</span>
                    </div>
                    <div class="pricing-yearly-note text-xs text-white/60 hidden">Thanh toán 1,908,000đ/năm — tiết kiệm 480,000đ</div>
                    <div class="pricing-monthly-note text-xs text-white/60">Thanh toán hàng tháng, hủy bất kỳ lúc nào</div>
                </div>

                <div class="flex flex-col gap-3 mb-8 flex-1 relative">
                    @foreach([
                        'Tạo không giới hạn đề thi',
                        'Không giới hạn câu hỏi',
                        'Không giới hạn lượt ôn thi',
                        'Tạo đề bằng AI (GPT-4)',
                        'Upload Word/PDF → AI tạo đề',
                        'Phòng thi ảo không giới hạn',
                        'Quản lý lớp học',
                        'Hỗ trợ ưu tiên 24/7',
                    ] as $f)
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm text-white">{{ $f }}</span>
                    </div>
                    @endforeach
                </div>

                <a href="#" class="relative block text-center bg-white text-green-600 font-black text-sm py-3 rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 active:shadow-none active:translate-y-1 transition-all">
                    Dùng thử 7 ngày miễn phí
                </a>
            </div>

            {{-- Enterprise --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm flex flex-col hover:shadow-md transition-shadow">
                <div class="mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gray-900 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Enterprise</div>
                    <div class="flex items-end gap-1 mb-1">
                        <span class="text-4xl font-black text-gray-900">Liên hệ</span>
                    </div>
                    <div class="text-xs text-gray-400">Báo giá theo quy mô tổ chức</div>
                </div>

                <div class="flex flex-col gap-3 mb-8 flex-1">
                    @foreach([
                        'Tất cả tính năng Pro',
                        'Quản trị viên không giới hạn',
                        'SSO / SAML tích hợp',
                        'API riêng & webhook',
                        'Tùy chỉnh giao diện (White-label)',
                        'SLA cam kết 99.9% uptime',
                        'Onboarding & đào tạo riêng',
                        'Hỗ trợ qua Slack/Teams riêng',
                    ] as $f)
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm text-gray-700">{{ $f }}</span>
                    </div>
                    @endforeach
                </div>

                <a href="#" id="btn-contact-from-pricing" class="block text-center bg-gray-900 text-white font-black text-sm py-3 rounded-xl shadow-[0_4px_0_#374151] hover:shadow-[0_2px_0_#374151] hover:translate-y-0.5 active:shadow-none active:translate-y-1 transition-all">
                    Liên hệ tư vấn
                </a>
            </div>
        </div>

        {{-- Feature comparison table --}}
        <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm mb-16">
            <div class="px-8 py-6 border-b border-gray-100">
                <div class="text-xs font-bold text-green-700 bg-green-50 rounded-full px-4 py-1.5 inline-block mb-2">SO SÁNH CHI TIẾT</div>
                <h2 class="text-xl font-black text-gray-800">Tính năng theo gói</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-8 py-4 text-sm font-black text-gray-500 w-1/2">Tính năng</th>
                            <th class="text-center px-4 py-4 text-sm font-black text-gray-500">Miễn phí</th>
                            <th class="text-center px-4 py-4 text-sm font-black text-green-600 bg-green-50">Pro</th>
                            <th class="text-center px-4 py-4 text-sm font-black text-gray-500">Enterprise</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach([
                            ['name' => 'Số đề thi', 'free' => '5/tháng', 'pro' => 'Không giới hạn', 'ent' => 'Không giới hạn'],
                            ['name' => 'Số câu hỏi/đề', 'free' => '30 câu', 'pro' => 'Không giới hạn', 'ent' => 'Không giới hạn'],
                            ['name' => 'Lượt ôn thi', 'free' => '10/tháng', 'pro' => 'Không giới hạn', 'ent' => 'Không giới hạn'],
                            ['name' => 'Tạo đề bằng AI', 'free' => false, 'pro' => true, 'ent' => true],
                            ['name' => 'Upload Word/PDF', 'free' => false, 'pro' => true, 'ent' => true],
                            ['name' => 'Phòng thi ảo', 'free' => false, 'pro' => true, 'ent' => true],
                            ['name' => 'Quản lý lớp học', 'free' => false, 'pro' => true, 'ent' => true],
                            ['name' => 'API & Webhook', 'free' => false, 'pro' => false, 'ent' => true],
                            ['name' => 'White-label', 'free' => false, 'pro' => false, 'ent' => true],
                            ['name' => 'SSO / SAML', 'free' => false, 'pro' => false, 'ent' => true],
                            ['name' => 'SLA 99.9%', 'free' => false, 'pro' => false, 'ent' => true],
                            ['name' => 'Hỗ trợ kỹ thuật', 'free' => 'Email', 'pro' => 'Ưu tiên 24/7', 'ent' => 'Riêng Slack/Teams'],
                        ] as $row)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-8 py-3.5 text-sm font-bold text-gray-700">{{ $row['name'] }}</td>
                            <td class="px-4 py-3.5 text-center">
                                @if($row['free'] === true)
                                    <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @elseif($row['free'] === false)
                                    <svg class="w-4 h-4 text-gray-200 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                @else
                                    <span class="text-xs text-gray-500">{{ $row['free'] }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center bg-green-50/50">
                                @if($row['pro'] === true)
                                    <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @elseif($row['pro'] === false)
                                    <svg class="w-4 h-4 text-gray-200 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                @else
                                    <span class="text-xs text-green-600 font-bold">{{ $row['pro'] }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($row['ent'] === true)
                                    <svg class="w-5 h-5 text-gray-700 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @elseif($row['ent'] === false)
                                    <svg class="w-4 h-4 text-gray-200 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                @else
                                    <span class="text-xs text-gray-500">{{ $row['ent'] }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Trust badges --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-16">
            @foreach([
                ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'label' => 'Bảo mật SSL', 'sub' => 'Mã hóa 256-bit'],
                ['icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'label' => 'Hủy bất kỳ lúc nào', 'sub' => 'Không ràng buộc'],
                ['icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'label' => 'Thanh toán an toàn', 'sub' => 'VNPay, MoMo, Thẻ'],
                ['icon' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z', 'label' => 'Hỗ trợ 24/7', 'sub' => 'Gói Pro & Enterprise'],
            ] as $badge)
            <div class="bg-white border border-gray-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $badge['icon'] }}"/></svg>
                </div>
                <div>
                    <div class="text-sm font-black text-gray-800">{{ $badge['label'] }}</div>
                    <div class="text-xs text-gray-400">{{ $badge['sub'] }}</div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- FAQ --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-8">
            <div class="text-xs font-bold text-green-700 bg-green-50 rounded-full px-4 py-1.5 inline-block mb-4">FAQ</div>
            <h2 class="text-xl font-black text-gray-800 mb-6">Câu hỏi về bảng giá</h2>
            <div class="divide-y divide-gray-100">
                @foreach([
                    ['q' => 'Tôi có thể dùng thử trước khi mua không?', 'a' => 'Có! Gói Pro có 7 ngày dùng thử miễn phí, không cần thẻ tín dụng. Bạn có thể trải nghiệm toàn bộ tính năng và quyết định sau.'],
                    ['q' => 'Thanh toán hàng năm có ưu đãi gì?', 'a' => 'Thanh toán hàng năm giúp bạn tiết kiệm 20% so với thanh toán hàng tháng. Với gói Pro, bạn tiết kiệm được 480,000đ mỗi năm.'],
                    ['q' => 'Tôi có thể nâng/hạ gói bất kỳ lúc nào không?', 'a' => 'Có. Bạn có thể nâng cấp ngay lập tức. Khi hạ xuống gói thấp hơn, thay đổi sẽ có hiệu lực vào chu kỳ thanh toán tiếp theo.'],
                    ['q' => 'Mindigo có hỗ trợ xuất hóa đơn VAT không?', 'a' => 'Có. Sau khi thanh toán, bạn có thể yêu cầu xuất hóa đơn VAT điện tử qua email support@mindigo.vn trong vòng 5 ngày làm việc.'],
                    ['q' => 'Gói Enterprise phù hợp với tổ chức nào?', 'a' => 'Gói Enterprise phù hợp với trường đại học, trung tâm đào tạo, và doanh nghiệp có từ 50 người dùng trở lên, cần tích hợp SSO, API riêng hoặc white-label.'],
                ] as $faq)
                <div x-data="{ open: false }" class="py-4 cursor-pointer" @click="open = !open">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-black text-gray-800">{{ $faq['q'] }}</span>
                        <svg class="w-4 h-4 text-green-500 transition-transform flex-shrink-0 ml-4" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div x-show="open" x-transition class="text-xs text-gray-500 leading-relaxed mt-3">{{ $faq['a'] }}</div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</section>

<script>
    function setPricingPeriod(period) {
        const btnMonthly = document.getElementById('toggle-monthly');
        const btnYearly  = document.getElementById('toggle-yearly');
        const prices     = document.querySelectorAll('.pricing-price');
        const periods    = document.querySelectorAll('.pricing-period');
        const yearlyNotes  = document.querySelectorAll('.pricing-yearly-note');
        const monthlyNotes = document.querySelectorAll('.pricing-monthly-note');

        if (period === 'yearly') {
            btnYearly.classList.add('bg-white', 'text-green-600', 'shadow-sm');
            btnYearly.classList.remove('text-gray-400');
            btnMonthly.classList.remove('bg-white', 'text-green-600', 'shadow-sm');
            btnMonthly.classList.add('text-gray-400');
            prices.forEach(el => el.textContent = el.dataset.yearly);
            periods.forEach(el => el.textContent = '/tháng');
            yearlyNotes.forEach(el => el.classList.remove('hidden'));
            monthlyNotes.forEach(el => el.classList.add('hidden'));
        } else {
            btnMonthly.classList.add('bg-white', 'text-green-600', 'shadow-sm');
            btnMonthly.classList.remove('text-gray-400');
            btnYearly.classList.remove('bg-white', 'text-green-600', 'shadow-sm');
            btnYearly.classList.add('text-gray-400');
            prices.forEach(el => el.textContent = el.dataset.monthly);
            periods.forEach(el => el.textContent = '/tháng');
            yearlyNotes.forEach(el => el.classList.add('hidden'));
            monthlyNotes.forEach(el => el.classList.remove('hidden'));
        }
    }

    // Nút "Liên hệ tư vấn" trong Enterprise → mở contact
    document.getElementById('btn-contact-from-pricing')?.addEventListener('click', function(e) {
        e.preventDefault();
        const pricingSection = document.getElementById('section-pricing');
        const contactSection = document.getElementById('section-contact');
        const homeSections   = document.getElementById('home-sections');
        const btnPricing     = document.getElementById('btn-pricing');
        const btnContact     = document.getElementById('btn-contact');

        pricingSection.classList.add('hidden');
        homeSections.classList.add('hidden');
        contactSection.classList.remove('hidden');
        if (btnPricing) btnPricing.classList.remove('bg-green-50', 'text-green-600');
        if (btnContact) btnContact.classList.add('bg-green-50', 'text-green-600');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>