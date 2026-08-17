<aside class="login-onboarding" aria-label="@lang('Mindigo-auth::app.onboarding.label')" data-login-onboarding>
    <div class="login-onboarding__shape login-onboarding__shape--one"></div>
    <div class="login-onboarding__shape login-onboarding__shape--two"></div>
    <div class="login-onboarding__shape login-onboarding__shape--three"></div>

    <div class="login-onboarding__track">
        @foreach (['schedule', 'classroom', 'progress'] as $index => $slide)
            <section class="login-onboarding__slide {{ $index === 0 ? 'is-active' : '' }}"
                data-onboarding-slide="{{ $index }}" aria-hidden="{{ $index === 0 ? 'false' : 'true' }}">
                <div class="onboarding-visual onboarding-visual--{{ $slide }}" aria-hidden="true">
                    <span class="onboarding-confetti onboarding-confetti--one"></span>
                    <span class="onboarding-confetti onboarding-confetti--two"></span>
                    <span class="onboarding-confetti onboarding-confetti--three"></span>
                    <span class="onboarding-confetti onboarding-confetti--four"></span>
                    <span class="onboarding-confetti onboarding-confetti--five"></span>
                    <span class="onboarding-confetti onboarding-confetti--six"></span>
                    <span class="onboarding-confetti onboarding-confetti--seven"></span>
                    <span class="onboarding-orbit onboarding-orbit--one"></span>
                    <span class="onboarding-orbit onboarding-orbit--two"></span>

                    <div class="onboarding-card onboarding-card--main">
                        <div class="onboarding-card__top">
                            <span class="onboarding-card__avatar">M</span>
                            <div><strong>@lang("Mindigo-auth::app.onboarding.$slide.card_title")</strong><small>@lang("Mindigo-auth::app.onboarding.$slide.card_meta")</small></div>
                            <span class="onboarding-card__status"></span>
                        </div>
                        <div class="onboarding-card__hero">
                            <span class="onboarding-card__date">{{ $index === 0 ? '18' : ($index === 1 ? '24' : '92%') }}</span>
                            <div class="onboarding-card__lines"><i></i><i></i><i></i></div>
                        </div>
                        <div class="onboarding-card__row"><span></span><i></i></div>
                        <div class="onboarding-card__row"><span></span><i></i></div>
                        <div class="onboarding-card__row"><span></span><i></i></div>
                        <div class="onboarding-card__footer">
                            <span><i></i><i></i><i></i></span>
                            <b>{{ $index === 0 ? '08:00' : ($index === 1 ? '24/30' : '+8%') }}</b>
                        </div>
                    </div>

                    <div class="onboarding-card onboarding-card--mini onboarding-card--mini-one">
                        <span class="onboarding-mini-icon">✓</span>
                        <div><strong>@lang("Mindigo-auth::app.onboarding.$slide.note_one")</strong><small>@lang("Mindigo-auth::app.onboarding.$slide.note_one_meta")</small></div>
                    </div>
                    <div class="onboarding-card onboarding-card--mini onboarding-card--mini-two">
                        <span class="onboarding-mini-icon">{{ $index === 2 ? '↗' : '•' }}</span>
                        <div><strong>@lang("Mindigo-auth::app.onboarding.$slide.note_two")</strong><small>@lang("Mindigo-auth::app.onboarding.$slide.note_two_meta")</small></div>
                    </div>
                    <div class="onboarding-brand-mark">
                        <span>m</span>
                    </div>
                    <div class="onboarding-pointer">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3.5 19 13l-6.2 1.2L9.5 20 5 3.5Z"/></svg>
                        <span></span>
                    </div>
                </div>

                <div class="login-onboarding__copy">
                    <p class="login-onboarding__eyebrow">@lang("Mindigo-auth::app.onboarding.$slide.eyebrow")</p>
                    <h2>@lang("Mindigo-auth::app.onboarding.$slide.title")</h2>
                    <p>@lang("Mindigo-auth::app.onboarding.$slide.description")</p>
                </div>
            </section>
        @endforeach
    </div>

    <div class="login-onboarding__dots" role="tablist" aria-label="@lang('Mindigo-auth::app.onboarding.navigation')">
        @foreach (['schedule', 'classroom', 'progress'] as $index => $slide)
            <button type="button" class="login-onboarding__dot {{ $index === 0 ? 'is-active' : '' }}"
                data-onboarding-dot="{{ $index }}" role="tab" aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                aria-label="@lang('Mindigo-auth::app.onboarding.go_to', ['slide' => $index + 1])"></button>
        @endforeach
    </div>
</aside>
