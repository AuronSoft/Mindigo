{{-- Testimonials section --}}
<section class="py-20 bg-green-50 border-t border-green-100 overflow-hidden">
    <div class="max-w-7xl mx-auto px-10">
        {{-- Title --}}
        <div class="text-center mb-12">
            <h2 class="text-3xl font-black text-green-600">@lang('core::app.testimonials.title')</h2>
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
                    <p class="text-gray-700 font-black text-sm">@lang('core::app.testimonials.customers')</p>
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
                    @foreach(__('core::app.testimonials.reviews') as $r)
                    <div class="bg-white rounded-2xl border-2 border-green-100 shadow-md p-5 shrink-0 w-72 hover:border-green-300 hover:shadow-lg transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-green-200 shrink-0">
                                <img src="https://api.dicebear.com/9.x/personas/svg?seed={{ $r['seed'] }}&backgroundColor=d1fae5" class="w-full h-full object-cover bg-green-100" alt="{{ $r['name'] }}">
                            </div>
                            <div>
                                <p class="text-sm font-black text-gray-800">{{ $r['name'] }}</p>
                                <p class="text-xs text-gray-400 leading-tight">{{ $r['school'] }}</p>
                            </div>
                        </div>
                        <div class="flex gap-0.5 text-yellow-400 text-sm mb-2">★★★★★</div>
                        <p class="text-gray-500 text-xs leading-relaxed">
                            {{ Str::limit($r['review'], 120) }}
                            @if(strlen($r['review']) > 120)
                            <a href="#" class="text-green-600 font-black">@lang('core::app.testimonials.read_more')</a>
                            @endif
                        </p>
                    </div>
                    @endforeach
                    {{-- Duplicate for seamless loop --}}
                    @foreach(__('core::app.testimonials.reviews') as $r)
                    <div class="bg-white rounded-2xl border-2 border-green-100 shadow-md p-5 shrink-0 w-72 hover:border-green-300 hover:shadow-lg transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-green-200 shrink-0">
                                <img src="https://api.dicebear.com/9.x/personas/svg?seed={{ $r['seed'] }}&backgroundColor=d1fae5" class="w-full h-full object-cover bg-green-100" alt="{{ $r['name'] }}">
                            </div>
                            <div>
                                <p class="text-sm font-black text-gray-800">{{ $r['name'] }}</p>
                                <p class="text-xs text-gray-400 leading-tight">{{ $r['school'] }}</p>
                            </div>
                        </div>
                        <div class="flex gap-0.5 text-yellow-400 text-sm mb-2">★★★★★</div>
                        <p class="text-gray-500 text-xs leading-relaxed">
                            {{ Str::limit($r['review'], 120) }}
                            @if(strlen($r['review']) > 120)
                            <a href="#" class="text-green-600 font-black">@lang('core::app.testimonials.read_more')</a>
                            @endif
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>