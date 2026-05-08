@extends('core::layouts.home')

@section('content')

<div class="min-h-screen bg-white flex flex-col">
    {{-- Navbar --}}
    <nav class="max-w-5xl mx-auto w-full flex items-center justify-between px-10 py-4">
        <div class="flex items-center gap-2">
        <svg width="45" height="45" viewBox="0 0 45 45" fill="none">
            <ellipse cx="22" cy="28" rx="16" ry="12" fill="#60a5fa"/>
            <ellipse cx="22" cy="26" rx="14" ry="11" fill="#93c5fd"/>
            <ellipse cx="16" cy="22" rx="5" ry="6" fill="#93c5fd"/>
            <ellipse cx="28" cy="22" rx="5" ry="6" fill="#93c5fd"/>
            <ellipse cx="16" cy="21" rx="3" ry="4" fill="#bfdbfe"/>
            <ellipse cx="28" cy="21" rx="3" ry="4" fill="#bfdbfe"/>
            <circle cx="16" cy="20" r="2" fill="#1e40af"/>
            <circle cx="28" cy="20" r="2" fill="#1e40af"/>
            <circle cx="16.8" cy="19.2" r="0.7" fill="white"/>
            <circle cx="28.8" cy="19.2" r="0.7" fill="white"/>
            <ellipse cx="22" cy="27" rx="4" ry="2.5" fill="#60a5fa"/>
            <rect x="18" y="27" width="3" height="1.5" rx="0.5" fill="#1e40af"/>
            <rect x="21.5" y="27" width="3" height="1.5" rx="0.5" fill="#1e40af"/>
            <ellipse cx="22" cy="29" rx="2" ry="1" fill="#bfdbfe"/>
            <ellipse cx="10" cy="35" rx="4" ry="2.5" fill="#60a5fa"/>
            <ellipse cx="34" cy="35" rx="4" ry="2.5" fill="#60a5fa"/>
            <ellipse cx="13" cy="38" rx="3.5" ry="2" fill="#60a5fa"/>
            <ellipse cx="31" cy="38" rx="3.5" ry="2" fill="#60a5fa"/>
            <ellipse cx="8" cy="24" rx="3" ry="2" fill="#93c5fd" transform="rotate(-20 8 24)"/>
            <ellipse cx="36" cy="24" rx="3" ry="2" fill="#93c5fd" transform="rotate(20 36 24)"/>
            <path d="M19 16 Q22 12 25 16" stroke="#60a5fa" stroke-width="1.5" fill="none" stroke-linecap="round"/>
        </svg>
        <span class="text-2xl font-extrabold text-blue-500 tracking-tight">mindigo</span>
        </div>
        <button class="text-xs font-bold text-gray-500 uppercase tracking-widest hover:text-gray-700 transition">
        Ngôn ngữ hiển thị: Tiếng Việt ▾
        </button>
    </nav>

    {{-- Hero --}}
    <div class="flex-1 flex items-center justify-center px-6">
        <div class="flex flex-col lg:flex-row items-center gap-8 lg:gap-20 max-w-4xl w-full">

            <div class="flex-1 flex items-center justify-center relative" style="height: 420px;">

            {{-- Nhân vật văng ra --}}
            <div id="c1" class="absolute z-20" style="opacity:0;top:50%;left:50%;transform:translate(-50%,-50%) scale(0)"><div class="text-4xl" style="filter:drop-shadow(3px 5px 8px rgba(0,0,0,0.25))">🧑‍🦱</div></div>
            <div id="c2" class="absolute z-20" style="opacity:0;top:50%;left:50%;transform:translate(-50%,-50%) scale(0)"><div class="text-4xl" style="filter:drop-shadow(3px 5px 8px rgba(0,0,0,0.25))">👧</div></div>
            <div id="c3" class="absolute z-20" style="opacity:0;top:50%;left:50%;transform:translate(-50%,-50%) scale(0)"><div class="text-3xl" style="filter:drop-shadow(3px 5px 8px rgba(0,0,0,0.25))">👦</div></div>
            <div id="c4" class="absolute z-20" style="opacity:0;top:50%;left:50%;transform:translate(-50%,-50%) scale(0)"><div class="text-3xl" style="filter:drop-shadow(3px 5px 8px rgba(0,0,0,0.25))">👩</div></div>
            <div id="c5" class="absolute z-20" style="opacity:0;top:50%;left:50%;transform:translate(-50%,-50%) scale(0)"><div class="text-3xl" style="filter:drop-shadow(3px 5px 8px rgba(0,0,0,0.25))">🧒</div></div>
            <div id="c6" class="absolute z-20" style="opacity:0;top:50%;left:50%;transform:translate(-50%,-50%) scale(0)"><div class="text-3xl" style="filter:drop-shadow(3px 5px 8px rgba(0,0,0,0.25))">🧔</div></div>

            {{-- Phone --}}
            <div id="phone" style="transform:rotate(-15deg);z-index:10;position:relative;">
                <div class="w-40 h-56 bg-white rounded-3xl border-4 border-blue-400 shadow-2xl flex items-center justify-center">
                <svg width="100" height="100" viewBox="0 0 90 90" fill="none">
                    <ellipse cx="28" cy="28" rx="13" ry="11" fill="#93c5fd" transform="rotate(-15 28 28)"/>
                    <ellipse cx="62" cy="28" rx="13" ry="11" fill="#93c5fd" transform="rotate(15 62 28)"/>
                    <ellipse cx="28" cy="28" rx="8" ry="6" fill="#bfdbfe" transform="rotate(-15 28 28)"/>
                    <ellipse cx="62" cy="28" rx="8" ry="6" fill="#bfdbfe" transform="rotate(15 62 28)"/>
                    <ellipse cx="45" cy="48" rx="33" ry="28" fill="#93c5fd"/>
                    <ellipse cx="45" cy="46" rx="30" ry="25" fill="#bfdbfe"/>
                    <circle cx="33" cy="42" r="10" fill="white"/>
                    <circle cx="57" cy="42" r="10" fill="white"/>
                    <circle id="pl" cx="35" cy="44" r="7" fill="#1e40af"/>
                    <circle id="pr" cx="59" cy="44" r="7" fill="#1e40af"/>
                    <circle cx="37" cy="42" r="3" fill="white"/>
                    <circle cx="61" cy="42" r="3" fill="white"/>
                    <ellipse cx="45" cy="57" rx="9" ry="6" fill="#60a5fa"/>
                    <ellipse cx="40" cy="56" rx="3" ry="2.5" fill="#1e40af"/>
                    <ellipse cx="50" cy="56" rx="3" ry="2.5" fill="#1e40af"/>
                    <path id="mouth" d="M36 63 Q45 69 54 63" stroke="#1e40af" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                    <ellipse cx="18" cy="52" rx="9" ry="6" fill="#bfdbfe" opacity="0.6"/>
                    <ellipse cx="72" cy="52" rx="9" ry="6" fill="#bfdbfe" opacity="0.6"/>
                </svg>
                </div>
            </div>

            <style>
                @keyframes floatPhone {
                0%,100%{ transform:rotate(-15deg) translateY(0px) }
                50%    { transform:rotate(-15deg) translateY(-10px) }
                }
                @keyframes blinkEye {
                0%,100%{ transform:scaleY(1) }
                50%    { transform:scaleY(0.05) }
                }
                @keyframes spinBoom {
                0%  { transform:rotate(-15deg) scale(1.55); opacity:1; }
                40% { transform:rotate(200deg) scale(2.2);  opacity:0.6; }
                100%{ transform:rotate(400deg) scale(0);    opacity:0; }
                }
                @keyframes floatChar1 { 0%,100%{transform:translate(calc(-50% + -155px),calc(-50% + -140px)) rotate(-35deg) translateY(0px)} 50%{transform:translate(calc(-50% + -155px),calc(-50% + -140px)) rotate(-35deg) translateY(-8px)} }
                @keyframes floatChar2 { 0%,100%{transform:translate(calc(-50% + 145px),calc(-50% + -130px)) rotate(28deg) translateY(0px)} 50%{transform:translate(calc(-50% + 145px),calc(-50% + -130px)) rotate(28deg) translateY(-6px)} }
                @keyframes floatChar3 { 0%,100%{transform:translate(calc(-50% + -165px),calc(-50% + 95px)) rotate(18deg) translateY(0px)} 50%{transform:translate(calc(-50% + -165px),calc(-50% + 95px)) rotate(18deg) translateY(-7px)} }
                @keyframes floatChar4 { 0%,100%{transform:translate(calc(-50% + 135px),calc(-50% + 115px)) rotate(-22deg) translateY(0px)} 50%{transform:translate(calc(-50% + 135px),calc(-50% + 115px)) rotate(-22deg) translateY(-9px)} }
                @keyframes floatChar5 { 0%,100%{transform:translate(calc(-50% + -55px),calc(-50% + -165px)) rotate(40deg) translateY(0px)} 50%{transform:translate(calc(-50% + -55px),calc(-50% + -165px)) rotate(40deg) translateY(-5px)} }
                @keyframes floatChar6 { 0%,100%{transform:translate(calc(-50% + 65px),calc(-50% + 155px)) rotate(-18deg) translateY(0px)} 50%{transform:translate(calc(-50% + 65px),calc(-50% + 155px)) rotate(-18deg) translateY(-8px)} }
            </style>

            <script>
                (function(){
                const phone = document.getElementById('phone');
                const mouth = document.getElementById('mouth');
                const pl    = document.getElementById('pl');
                const pr    = document.getElementById('pr');

                const chars = [
                    { id:'c1', x:-155, y:-140, r:'-35deg', anim:'floatChar1', delay:'0s'    },
                    { id:'c2', x:145,  y:-130, r:'28deg',  anim:'floatChar2', delay:'0.3s'  },
                    { id:'c3', x:-165, y:95,   r:'18deg',  anim:'floatChar3', delay:'0.15s' },
                    { id:'c4', x:135,  y:115,  r:'-22deg', anim:'floatChar4', delay:'0.45s' },
                    { id:'c5', x:-55,  y:-165, r:'40deg',  anim:'floatChar5', delay:'0.2s'  },
                    { id:'c6', x:65,   y:155,  r:'-18deg', anim:'floatChar6', delay:'0.35s' },
                ];

                // Float điện thoại
                phone.style.animation = 'floatPhone 3s ease-in-out infinite';

                // Chớp mắt lần 1
                setTimeout(() => {
                    pl.style.cssText += ';transform-origin:35px 44px;animation:blinkEye 0.35s ease-in-out';
                    pr.style.cssText += ';transform-origin:59px 44px;animation:blinkEye 0.35s ease-in-out';
                }, 1000);

                // Chớp mắt lần 2
                setTimeout(() => {
                    pl.style.animation = 'none'; pr.style.animation = 'none';
                    void pl.offsetWidth;
                    pl.style.animation = 'blinkEye 0.35s ease-in-out';
                    pr.style.animation = 'blinkEye 0.35s ease-in-out';
                }, 1800);

                // Há miệng to
                setTimeout(() => {
                    mouth.style.transition = 'd 0.3s ease';
                    mouth.setAttribute('d','M32 62 Q45 84 58 62');
                }, 2500);

                // Phình to
                setTimeout(() => {
                    phone.style.animation = 'none';
                    phone.style.transition = 'transform 0.5s cubic-bezier(0.34,1.56,0.64,1)';
                    phone.style.transform = 'rotate(-15deg) scale(1.55)';
                }, 3000);

                // Nổ tung
                setTimeout(() => {
                    phone.style.transition = 'none';
                    phone.style.animation = 'spinBoom 0.8s cubic-bezier(0.55,0,1,0.45) forwards';

                    // Văng nhân vật ra từng cái, rồi lơ lửng mãi
                    chars.forEach((c, i) => {
                    setTimeout(() => {
                        const el = document.getElementById(c.id);
                        el.style.transition = 'all 0.8s cubic-bezier(0.2,0.8,0.3,1.3)';
                        el.style.opacity = '1';
                        el.style.transform = `translate(calc(-50% + ${c.x}px), calc(-50% + ${c.y}px)) rotate(${c.r}) scale(1)`;

                        // Sau khi đến vị trí thì float nhẹ mãi mãi
                        setTimeout(() => {
                        el.style.transition = 'none';
                        el.style.animation = `${c.anim} ${2.5 + i * 0.2}s ease-in-out infinite`;
                        el.style.animationDelay = c.delay;
                        }, 900);
                    }, 100 + i * 100);
                    });

                }, 3550);

                })();
            </script>

            </div>
        {{-- RIGHT — Text + Buttons --}}
        <div class="flex-1 flex flex-col items-center gap-5 text-center">
            <h1 class="text-3xl font-extrabold text-gray-800 leading-snug">
            Ôn thi thông minh, vui nhộn và hiệu quả!
            </h1>
            <div class="flex flex-col w-full max-w-sm gap-3 mt-2">
            <a href="#"
                class="w-full py-4 bg-blue-500 hover:bg-blue-400 active:bg-blue-600 text-white font-extrabold text-sm uppercase tracking-widest rounded-2xl text-center transition-all shadow-[0_4px_0_#1d4ed8] hover:shadow-[0_2px_0_#1d4ed8] active:shadow-none active:translate-y-1">
                Bắt đầu
            </a>
            <a href="#"
                class="w-full py-4 border-2 border-gray-200 hover:border-gray-300 text-blue-500 font-extrabold text-sm uppercase tracking-widest rounded-2xl text-center transition-all">
                Tôi đã có tài khoản
            </a>
            </div>
        </div>

        </div>
    </div>

    {{-- Bottom subject strip --}}
    <div class="border-t border-gray-200 py-4 px-6">
        <div class="flex items-center justify-center gap-1 overflow-x-auto">
        <button class="text-gray-400 hover:text-gray-600 px-2 text-lg">‹</button>
        <button class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-100 transition whitespace-nowrap uppercase">🇺🇸 Tiếng Anh</button>
        <button class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-100 transition whitespace-nowrap uppercase">🇪🇸 Tiếng Tây Ban Nha</button>
        <button class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-100 transition whitespace-nowrap uppercase">🇫🇷 Tiếng Pháp</button>
        <button class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-100 transition whitespace-nowrap uppercase">🇩🇪 Tiếng Đức</button>
        <button class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-100 transition whitespace-nowrap uppercase">🇮🇹 Tiếng Ý</button>
        <button class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-100 transition whitespace-nowrap uppercase">🇧🇷 Tiếng Bồ Đào Nha</button>
        <button class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-100 transition whitespace-nowrap uppercase">🇯🇵 Tiếng Nhật</button>
        <button class="text-gray-400 hover:text-gray-600 px-2 text-lg">›</button>
        </div>
    </div>

    </div>

@endsection