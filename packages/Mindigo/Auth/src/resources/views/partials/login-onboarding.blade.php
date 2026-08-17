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
                    <span class="onboarding-spark onboarding-spark--one"></span>
                    <span class="onboarding-spark onboarding-spark--two"></span>
                    <span class="onboarding-ribbon onboarding-ribbon--one"></span>
                    <span class="onboarding-ribbon onboarding-ribbon--two"></span>
                    <span class="onboarding-orbit onboarding-orbit--one"></span>
                    <span class="onboarding-orbit onboarding-orbit--two"></span>

                    @if ($slide === 'classroom')
                        <div class="onboarding-two-scene">
                            <div class="onboarding-two-card onboarding-two-card--activity">
                                <div class="onboarding-two-card__header">
                                    <div><small>@lang('Mindigo-auth::app.onboarding.classroom.activity_label')</small><strong><span>●</span> @lang('Mindigo-auth::app.onboarding.classroom.activity_total')</strong></div>
                                    <button type="button" tabindex="-1" aria-hidden="true">+</button>
                                </div>
                                <div class="onboarding-two-course">
                                    <span class="onboarding-two-course__thumb onboarding-two-course__thumb--one"><i></i><b></b></span>
                                    <div><strong>@lang('Mindigo-auth::app.onboarding.classroom.course_one')</strong><small>@lang('Mindigo-auth::app.onboarding.classroom.course_one_meta')</small></div>
                                    <b>@lang('Mindigo-auth::app.onboarding.duration', ['minutes' => 45])</b>
                                </div>
                                <div class="onboarding-two-course">
                                    <span class="onboarding-two-course__thumb onboarding-two-course__thumb--two"><i></i><b></b></span>
                                    <div><strong>@lang('Mindigo-auth::app.onboarding.classroom.course_two')</strong><small>@lang('Mindigo-auth::app.onboarding.classroom.course_two_meta')</small></div>
                                    <b>@lang('Mindigo-auth::app.onboarding.duration', ['minutes' => 30])</b>
                                </div>
                                <div class="onboarding-two-progress"><span></span></div>
                            </div>

                            <div class="onboarding-two-card onboarding-two-card--stat">
                                <small>@lang('Mindigo-auth::app.onboarding.classroom.stat_label')</small>
                                <strong>103</strong>
                                <span>@lang('Mindigo-auth::app.onboarding.classroom.stat_unit')</span>
                                <div class="onboarding-two-chart">
                                    <i></i><i></i><i></i><i></i><i></i><i></i><i></i>
                                </div>
                                <b>@lang('Mindigo-auth::app.onboarding.classroom.stat_change')</b>
                            </div>

                            <div class="onboarding-two-card onboarding-two-card--notice">
                                <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10v18H7zM10 7h4m-4 4h4m-4 4h3"/></svg></span>
                                <div><strong>@lang('Mindigo-auth::app.onboarding.classroom.notice_title')</strong><small>@lang('Mindigo-auth::app.onboarding.classroom.notice_meta')</small></div>
                                <i></i>
                                <img class="onboarding-two-character" src="{{ asset('images/auth/mindigo-onboarding-learner.png') }}" alt="" loading="eager">
                            </div>
                        </div>
                    @endif

                    <div class="onboarding-card onboarding-card--main">
                        <span class="onboarding-card__sheet onboarding-card__sheet--one"></span>
                        <span class="onboarding-card__sheet onboarding-card__sheet--two"></span>
                        <div class="onboarding-card__top">
                            <span class="onboarding-card__avatar">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3.5h10a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-13a2 2 0 0 1 2-2Z"/><path d="M8 8h8M8 12h5M8 16h7"/></svg>
                            </span>
                            <div><strong>@lang("Mindigo-auth::app.onboarding.$slide.card_title")</strong><small>@lang("Mindigo-auth::app.onboarding.$slide.card_meta")</small></div>
                            <span class="onboarding-card__status"></span>
                        </div>
                        <div class="onboarding-card__hero">
                            <div class="onboarding-card__illustration">
                                <svg viewBox="0 0 240 92" aria-hidden="true">
                                    <path class="ill-bg" d="M0 0h240v92H0z"/>
                                    <path class="ill-board" d="M116 13h91a7 7 0 0 1 7 7v48a7 7 0 0 1-7 7h-91z"/>
                                    <path class="ill-line" d="M133 29h57M133 40h44M133 51h52"/>
                                    <path class="ill-chart" d="m134 65 17-11 15 5 25-18"/>
                                    <path class="ill-desk" d="M23 70h93M35 70v16m68-16v16"/>
                                    <circle class="ill-skin" cx="67" cy="25" r="11"/>
                                    <path class="ill-hair" d="M56 25c0-11 6-16 14-16 8 0 13 5 13 13-7-5-15-6-27 3Z"/>
                                    <path class="ill-shirt" d="M48 67c2-23 9-31 21-31 13 0 21 10 24 31H48Z"/>
                                    <path class="ill-arm" d="M85 45c13 3 22 9 31 17l-6 7c-14-7-24-12-31-18"/>
                                    <circle class="ill-skin" cx="113" cy="65" r="5"/>
                                    <path class="ill-laptop" d="M16 48h38l8 22H24z"/>
                                    <circle cx="38" cy="59" r="3" fill="#fff" opacity=".9"/>
                                    <circle class="ill-dot" cx="221" cy="18" r="4"/>
                                    <circle class="ill-dot ill-dot--two" cx="226" cy="73" r="3"/>
                                </svg>
                            </div>
                            <div class="onboarding-card__summary">
                                <span class="onboarding-card__date">{{ $index === 0 ? '18' : ($index === 1 ? '24' : '92%') }}</span>
                                <div class="onboarding-card__lines"><i></i><i></i><i></i></div>
                                <span class="onboarding-card__trend">{{ $index === 2 ? '+8%' : __('Mindigo-auth::app.onboarding.live') }}</span>
                            </div>
                        </div>
                        <div class="onboarding-card__row"><span>1</span><i></i><b>08:00</b></div>
                        <div class="onboarding-card__row"><span>2</span><i></i><b>10:30</b></div>
                        <div class="onboarding-card__row"><span>3</span><i></i><b>14:00</b></div>
                        <div class="onboarding-card__footer">
                            <span><i></i><i></i><i></i></span>
                            <b>{{ $index === 0 ? '08:00' : ($index === 1 ? '24/30' : '+8%') }}</b>
                        </div>
                    </div>

                    <div class="onboarding-card onboarding-card--mini onboarding-card--mini-one">
                        <span class="onboarding-mini-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6.5 12.5 3.3 3.3 7.7-8"/></svg></span>
                        <div><strong>@lang("Mindigo-auth::app.onboarding.$slide.note_one")</strong><small>@lang("Mindigo-auth::app.onboarding.$slide.note_one_meta")</small></div>
                        <i class="onboarding-mini-state"></i>
                    </div>
                    <div class="onboarding-card onboarding-card--mini onboarding-card--mini-two">
                        <span class="onboarding-mini-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 18V9m7 9V5m7 13v-6"/></svg></span>
                        <div><strong>@lang("Mindigo-auth::app.onboarding.$slide.note_two")</strong><small>@lang("Mindigo-auth::app.onboarding.$slide.note_two_meta")</small></div>
                        <i class="onboarding-mini-state"></i>
                        <div class="onboarding-mini-list">
                            <span><i></i><b></b></span>
                            <span><i></i><b></b></span>
                            <span><i></i><b></b></span>
                        </div>
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
