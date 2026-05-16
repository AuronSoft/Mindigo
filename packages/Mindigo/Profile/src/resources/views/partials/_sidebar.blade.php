<aside class="w-64 shrink-0">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col items-center text-center gap-3">
        {{-- Avatar --}}
        <div class="relative">
            <div class="w-20 h-20 rounded-full bg-green-500 flex items-center justify-center text-white text-xl font-black overflow-hidden" id="av-preview">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="avatar" class="w-full h-full object-cover"/>
                @else
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                @endif
            </div>
            <label for="avatar-input" class="absolute bottom-0 right-0 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center cursor-pointer hover:bg-green-400 transition shadow-md">
                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </label>
            <input type="file" id="avatar-input" accept="image/*" class="hidden" form="profile-form" name="avatar"/>
        </div>

        <div>
            <div class="font-black text-gray-900 text-base">{{ Auth::user()->name }}</div>
            <div class="text-xs text-gray-400 font-bold mt-0.5">{{ Auth::user()->role ?? 'Học viên' }}</div>
            <div class="text-xs text-gray-400">{{ Auth::user()->employee_code ?? '' }}</div>
        </div>

        <div class="flex items-center gap-1.5 bg-green-50 text-green-600 text-xs font-black px-3 py-1 rounded-full">
            <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
            @lang('Mindigo-profile::app.active')
        </div>

        {{-- Meta --}}
        <div class="w-full mt-2 flex flex-col gap-3 text-left">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.58 3.32 2 2 0 0 1 3.55 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.54a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <div>
                    <div class="text-xs text-gray-400 font-bold">@lang('Mindigo-profile::app.phone')</div>
                    <div class="text-xs text-gray-700 font-bold">{{ Auth::user()->phone ?? __('Mindigo-profile::app.none') }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <div>
                    <div class="text-xs text-gray-400 font-bold">@lang('Mindigo-profile::app.hire_date')</div>
                    <div class="text-xs text-gray-700 font-bold">
                        {{ Auth::user()->hire_date ? Auth::user()->hire_date->format('d/m/Y') : __('Mindigo-profile::app.none') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>