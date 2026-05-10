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