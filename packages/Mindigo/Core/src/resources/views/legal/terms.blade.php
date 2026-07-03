@extends('core::layouts.home')

@php
    $effectiveDate = '1 tháng 7 năm 2026';
    $version = '1.0';
    $sections = [
        'acceptance' => '1. Chấp nhận Điều khoản',
        'account' => '2. Tài khoản và Đăng nhập',
        'services' => '3. Phạm vi Dịch vụ',
        'conduct' => '4. Quy tắc Sử dụng',
        'content' => '5. Nội dung và Dữ liệu Học tập',
        'ai' => '6. Sử dụng Trợ lý AI',
        'payments' => '7. Gói dịch vụ và Thanh toán',
        'security' => '8. Bảo mật và Tạm ngưng',
        'third-party' => '9. Dịch vụ Bên thứ ba',
        'ip' => '10. Sở hữu Trí tuệ',
        'liability' => '11. Giới hạn Trách nhiệm',
        'changes' => '12. Cập nhật Điều khoản',
        'contact' => '13. Liên hệ',
    ];
@endphp

@section('content')
<div class="min-h-screen bg-slate-50 text-slate-800">
    <header class="relative overflow-hidden bg-emerald-950">
        <div class="absolute inset-0 opacity-20">
            <img
                src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1800&q=85"
                alt=""
                class="h-full w-full object-cover"
            >
        </div>
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-950 via-emerald-900/95 to-emerald-700/80"></div>
        <div class="absolute -right-24 bottom-[-38%] h-96 w-[52rem] rounded-[100%] bg-white/18 rotate-[-7deg]"></div>

        <div class="relative mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-10">
            <a href="{{ route('home', [], false) }}" class="flex items-center gap-2">
                <svg width="38" height="38" viewBox="0 0 200 220" fill="none" aria-hidden="true">
                    <path d="M48 160 L22 148 L38 158 L16 152 L35 164" fill="#15803d" stroke="#14532d" stroke-width="1"/>
                    <circle cx="105" cy="145" r="90" fill="#22c55e" stroke="#14532d" stroke-width="3"/>
                    <ellipse cx="115" cy="185" rx="55" ry="38" fill="#86efac" stroke="#14532d" stroke-width="2"/>
                    <path d="M95 58 Q85 20 105 8 Q118 22 112 58" fill="#16a34a" stroke="#14532d" stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="M108 55 Q100 18 118 10 Q128 26 120 56" fill="#22c55e" stroke="#14532d" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M52 118 L95 108 L88 128 Z" fill="#14532d"/>
                    <path d="M148 118 L108 108 L114 128 Z" fill="#14532d"/>
                    <circle cx="82" cy="135" r="20" fill="white" stroke="#14532d" stroke-width="2"/>
                    <circle cx="86" cy="138" r="12" fill="#14532d"/>
                    <circle cx="128" cy="135" r="20" fill="white" stroke="#14532d" stroke-width="2"/>
                    <circle cx="132" cy="138" r="12" fill="#14532d"/>
                    <path d="M85 158 Q105 148 130 158 L118 175 Q105 180 92 175 Z" fill="#f59e0b" stroke="#14532d" stroke-width="2"/>
                </svg>
                <span class="text-xl font-black tracking-tight text-white">mindigo</span>
            </a>
            <a href="{{ route('login', [], false) }}" class="rounded-xl bg-white px-5 py-2.5 text-sm font-black text-emerald-700 shadow-[0_4px_0_rgba(20,83,45,0.35)] transition hover:-translate-y-0.5 hover:bg-emerald-50">
                Đăng nhập
            </a>
        </div>

        <div class="relative mx-auto max-w-7xl px-6 pb-20 pt-16 lg:px-10 lg:pb-24">
            <div class="max-w-4xl">
                <span class="inline-flex rounded-full border border-white/30 bg-white/10 px-4 py-2 text-xs font-black uppercase tracking-[0.18em] text-white">
                    Legal Center
                </span>
                <h1 class="mt-8 text-5xl font-black leading-tight text-white md:text-7xl">
                    Điều khoản Sử dụng
                </h1>
                <p class="mt-6 max-w-3xl text-lg font-semibold leading-8 text-emerald-50">
                    Các điều khoản này quy định việc truy cập và sử dụng Mindigo, bao gồm tài khoản Mindigo ID, lớp học trực tuyến, bài thi, nội dung học tập, trợ lý AI và các phương thức đăng nhập Google, Apple, Microsoft.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <span class="rounded-lg border border-white/25 bg-white/10 px-4 py-3 text-sm font-black text-white">Ngày hiệu lực: {{ $effectiveDate }}</span>
                    <span class="rounded-lg border border-white/25 bg-white/10 px-4 py-3 text-sm font-black text-white">Phiên bản: {{ $version }}</span>
                    <span class="rounded-lg border border-white/25 bg-white/10 px-4 py-3 text-sm font-black text-white">Áp dụng cho học viên, giáo viên, quản trị viên và tổ chức sử dụng Mindigo</span>
                </div>
            </div>
        </div>
    </header>

    <main class="mx-auto grid max-w-7xl gap-8 px-6 py-12 lg:grid-cols-[320px_1fr] lg:px-10">
        <aside class="lg:sticky lg:top-6 lg:self-start">
            <nav class="max-h-[calc(100vh-3rem)] overflow-auto rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="mb-4 text-sm font-black uppercase tracking-[0.14em] text-emerald-950">Mục lục</p>
                <div class="space-y-1">
                    @foreach($sections as $id => $label)
                        <a href="#{{ $id }}" class="block rounded-md px-3 py-2.5 text-sm font-bold leading-5 text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-700">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </nav>
        </aside>

        <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm md:p-10">
            <div class="rounded-lg border border-emerald-200 bg-emerald-50/70 p-6 text-base leading-8 text-slate-700">
                <p>
                    Bằng việc tạo tài khoản, đăng nhập hoặc sử dụng Mindigo, bạn xác nhận đã đọc, hiểu và đồng ý bị ràng buộc bởi Điều khoản Sử dụng này cùng Chính sách Bảo mật được công bố trên hệ thống.
                </p>
                <p class="mt-4">
                    Nếu bạn sử dụng Mindigo thay mặt cho trường học, trung tâm, doanh nghiệp hoặc tổ chức khác, bạn cam kết có thẩm quyền chấp nhận các điều khoản này cho tổ chức đó.
                </p>
            </div>

            <section id="acceptance" class="legal-section">
                <h2>1. Chấp nhận Điều khoản</h2>
                <p>Điều khoản này là thỏa thuận giữa bạn và Mindigo về việc truy cập, đăng ký, đăng nhập và sử dụng nền tảng. Nếu bạn không đồng ý với bất kỳ nội dung nào, vui lòng ngừng sử dụng dịch vụ.</p>
                <p>Mindigo có thể cung cấp thêm quy định riêng cho một số tính năng như thi trực tuyến, lớp học, thanh toán, hỗ trợ kỹ thuật hoặc trợ lý AI. Các quy định riêng đó là một phần của Điều khoản này.</p>
            </section>

            <section id="account" class="legal-section">
                <h2>2. Tài khoản và Đăng nhập</h2>
                <p>Bạn có thể đăng nhập bằng Mindigo ID hoặc thông qua nhà cung cấp danh tính bên thứ ba như Google, Apple và Microsoft nếu tính năng này được bật trên hệ thống.</p>
                <ul>
                    <li>Bạn chịu trách nhiệm bảo mật email, thiết bị, mật khẩu, mã OTP, magic link và phiên đăng nhập của mình.</li>
                    <li>Thông tin tài khoản cần chính xác, cập nhật và không mạo danh cá nhân hoặc tổ chức khác.</li>
                    <li>Khi đăng nhập bằng Google, Apple hoặc Microsoft, bạn cũng chịu sự điều chỉnh bởi điều khoản và chính sách của nhà cung cấp tương ứng.</li>
                    <li>Mindigo chỉ yêu cầu các quyền cần thiết để xác thực, tạo phiên đăng nhập và vận hành tài khoản; chúng tôi không yêu cầu mật khẩu tài khoản Google, Apple hoặc Microsoft của bạn.</li>
                </ul>
            </section>

            <section id="services" class="legal-section">
                <h2>3. Phạm vi Dịch vụ</h2>
                <p>Mindigo cung cấp các công cụ phục vụ học tập và quản lý giáo dục, bao gồm lớp học, bài tập, đề thi, ngân hàng câu hỏi, báo cáo học tập, thông báo, hỗ trợ người dùng và các tính năng liên quan.</p>
                <p>Chúng tôi có thể cải tiến, tạm dừng, thay đổi hoặc ngừng một phần tính năng nhằm bảo trì, nâng cấp bảo mật, tuân thủ pháp luật hoặc nâng cao chất lượng dịch vụ.</p>
            </section>

            <section id="conduct" class="legal-section">
                <h2>4. Quy tắc Sử dụng</h2>
                <p>Bạn đồng ý sử dụng Mindigo đúng mục đích học tập, giảng dạy, quản trị đào tạo và tuân thủ pháp luật hiện hành.</p>
                <ul>
                    <li>Không gian lận thi cử, phá hoại lớp học, phát tán mã độc, khai thác lỗ hổng hoặc can thiệp trái phép vào hệ thống.</li>
                    <li>Không đăng tải nội dung vi phạm bản quyền, xúc phạm, phân biệt đối xử, đe dọa, khiêu dâm, bạo lực hoặc trái pháp luật.</li>
                    <li>Không thu thập dữ liệu người dùng khác, bán lại quyền truy cập hoặc sử dụng tự động hóa vượt quá giới hạn hợp lý nếu chưa được Mindigo cho phép.</li>
                    <li>Không sử dụng dịch vụ để tạo, phát tán hoặc hỗ trợ hành vi gian lận học thuật.</li>
                </ul>
            </section>

            <section id="content" class="legal-section">
                <h2>5. Nội dung và Dữ liệu Học tập</h2>
                <p>Bạn hoặc tổ chức của bạn vẫn sở hữu nội dung hợp pháp do bạn tải lên, bao gồm tài liệu, câu hỏi, bài nộp, ghi chú và dữ liệu lớp học. Bạn cấp cho Mindigo quyền xử lý nội dung đó trong phạm vi cần thiết để cung cấp, bảo trì, bảo mật và cải thiện dịch vụ.</p>
                <p>Giáo viên, trường học hoặc tổ chức quản lý có thể có quyền truy cập, đánh giá, xuất báo cáo hoặc xử lý dữ liệu học tập của học viên theo cấu hình và thỏa thuận nội bộ của tổ chức đó.</p>
            </section>

            <section id="ai" class="legal-section">
                <h2>6. Sử dụng Trợ lý AI</h2>
                <p>Các tính năng AI của Mindigo được thiết kế để hỗ trợ học tập, gợi ý ôn luyện, giải thích kiến thức và tăng hiệu quả vận hành. Kết quả do AI tạo ra có thể chưa hoàn toàn chính xác và cần được người dùng kiểm tra trước khi áp dụng.</p>
                <ul>
                    <li>Không nhập dữ liệu nhạy cảm, bí mật kinh doanh hoặc thông tin cá nhân không cần thiết vào công cụ AI.</li>
                    <li>Không dùng AI để gian lận bài kiểm tra, giả mạo danh tính hoặc tạo nội dung vi phạm pháp luật.</li>
                    <li>Mindigo có thể áp dụng bộ lọc an toàn, giới hạn truy cập hoặc ghi nhận nhật ký kỹ thuật để phát hiện lạm dụng.</li>
                </ul>
            </section>

            <section id="payments" class="legal-section">
                <h2>7. Gói dịch vụ và Thanh toán</h2>
                <p>Một số tính năng có thể yêu cầu gói trả phí, giấy phép theo tổ chức hoặc điều kiện sử dụng riêng. Phí dịch vụ, thời hạn, quyền lợi, giới hạn tài khoản và chính sách hoàn tiền sẽ được công bố tại thời điểm đăng ký hoặc trong hợp đồng liên quan.</p>
                <p>Nếu gói dịch vụ hết hạn, một số quyền truy cập có thể bị giới hạn. Dữ liệu có thể được lưu giữ hoặc xóa theo chính sách dữ liệu, cấu hình tổ chức và quy định pháp luật hiện hành.</p>
            </section>

            <section id="security" class="legal-section">
                <h2>8. Bảo mật và Tạm ngưng</h2>
                <p>Mindigo áp dụng các biện pháp bảo mật phù hợp để bảo vệ hệ thống và dữ liệu người dùng. Tuy nhiên, bạn cũng cần chủ động bảo vệ tài khoản, đăng xuất khỏi thiết bị công cộng và thông báo ngay khi phát hiện truy cập bất thường.</p>
                <p>Chúng tôi có quyền tạm ngưng hoặc chấm dứt quyền truy cập nếu có dấu hiệu vi phạm Điều khoản, rủi ro bảo mật, yêu cầu pháp lý hoặc hành vi gây ảnh hưởng đến người dùng khác.</p>
            </section>

            <section id="third-party" class="legal-section">
                <h2>9. Dịch vụ Bên thứ ba</h2>
                <p>Mindigo có thể tích hợp với dịch vụ của bên thứ ba như Google, Apple, Microsoft, cổng thanh toán, hạ tầng email, lưu trữ, phân tích hoặc công cụ hỗ trợ vận hành. Các dịch vụ này có điều khoản và chính sách riêng.</p>
                <p>Khi bạn chọn kết nối hoặc đăng nhập qua bên thứ ba, bạn cho phép Mindigo xử lý thông tin được bên thứ ba chia sẻ trong phạm vi cần thiết để xác thực và cung cấp dịch vụ.</p>
            </section>

            <section id="ip" class="legal-section">
                <h2>10. Sở hữu Trí tuệ</h2>
                <p>Mindigo, biểu trưng, giao diện, mã nguồn, thiết kế, tài liệu hệ thống và các thành phần nền tảng thuộc quyền sở hữu của Mindigo hoặc bên cấp phép hợp pháp. Bạn không được sao chép, phân phối, dịch ngược hoặc tạo sản phẩm phái sinh nếu chưa có chấp thuận bằng văn bản.</p>
                <p>Nội dung học tập của bên thứ ba được sử dụng trong Mindigo phải tuân thủ quyền sở hữu trí tuệ và giấy phép tương ứng.</p>
            </section>

            <section id="liability" class="legal-section">
                <h2>11. Giới hạn Trách nhiệm</h2>
                <p>Mindigo nỗ lực duy trì dịch vụ ổn định, an toàn và hữu ích, nhưng không cam kết dịch vụ luôn không gián đoạn hoặc hoàn toàn không có lỗi. Trong phạm vi pháp luật cho phép, Mindigo không chịu trách nhiệm đối với thiệt hại gián tiếp, mất lợi nhuận, mất dữ liệu do lỗi cấu hình của người dùng hoặc việc sử dụng dịch vụ trái Điều khoản.</p>
                <p>Không nội dung nào trong Điều khoản này loại trừ trách nhiệm không thể loại trừ theo pháp luật hiện hành.</p>
            </section>

            <section id="changes" class="legal-section">
                <h2>12. Cập nhật Điều khoản</h2>
                <p>Mindigo có thể cập nhật Điều khoản để phản ánh thay đổi về sản phẩm, pháp luật, bảo mật hoặc mô hình vận hành. Khi thay đổi quan trọng, chúng tôi sẽ thông báo qua hệ thống, email hoặc kênh phù hợp trước khi áp dụng nếu pháp luật yêu cầu.</p>
                <p>Việc tiếp tục sử dụng Mindigo sau ngày hiệu lực của phiên bản mới được hiểu là bạn đồng ý với Điều khoản đã cập nhật.</p>
            </section>

            <section id="contact" class="legal-section">
                <h2>13. Liên hệ</h2>
                <p>Nếu bạn có câu hỏi về Điều khoản Sử dụng, tài khoản đăng nhập Google, Apple, Microsoft hoặc yêu cầu hỗ trợ pháp lý liên quan đến dịch vụ, vui lòng liên hệ bộ phận hỗ trợ của Mindigo.</p>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                        <p class="text-sm font-black text-slate-900">Hỗ trợ chung</p>
                        <p class="mt-2 text-sm text-slate-600">Email: support@mindigo.vn</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                        <p class="text-sm font-black text-slate-900">Vấn đề tài khoản và dữ liệu</p>
                        <p class="mt-2 text-sm text-slate-600">Email: privacy@mindigo.vn</p>
                    </div>
                </div>
            </section>
        </article>
    </main>

    <footer class="border-t border-slate-200 bg-white px-6 py-8 lg:px-10">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
            <p>© {{ date('Y') }} Mindigo. All rights reserved.</p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('home', [], false) }}" class="font-bold text-slate-600 hover:text-emerald-700">Trang chủ</a>
                <a href="{{ route('login', [], false) }}" class="font-bold text-slate-600 hover:text-emerald-700">Đăng nhập</a>
            </div>
        </div>
    </footer>
</div>

<style>
    html {
        scroll-behavior: smooth;
    }

    .legal-section {
        border-top: 1px solid rgb(226 232 240);
        margin-top: 2rem;
        padding-top: 2rem;
        scroll-margin-top: 2rem;
    }

    .legal-section h2 {
        color: rgb(15 23 42);
        font-size: 1.5rem;
        font-weight: 900;
        line-height: 1.3;
        margin-bottom: 1rem;
    }

    .legal-section p {
        color: rgb(51 65 85);
        font-size: 1rem;
        line-height: 1.9;
        margin-top: 1rem;
    }

    .legal-section ul {
        color: rgb(51 65 85);
        line-height: 1.8;
        list-style: disc;
        margin-top: 1rem;
        padding-left: 1.25rem;
    }

    .legal-section li + li {
        margin-top: 0.5rem;
    }
</style>
@endsection
