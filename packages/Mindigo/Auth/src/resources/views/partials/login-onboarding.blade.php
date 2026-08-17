<aside class="login-onboarding" aria-label="@lang('Mindigo-auth::app.onboarding.label')" data-login-onboarding>
    <div class="login-onboarding__shape login-onboarding__shape--one"></div>
    <div class="login-onboarding__shape login-onboarding__shape--two"></div>
    <div class="login-onboarding__shape login-onboarding__shape--three"></div>
    <div class="login-onboarding__backdrop login-onboarding__backdrop--top"></div>
    <div class="login-onboarding__backdrop login-onboarding__backdrop--left"></div>
    <div class="login-onboarding__backdrop login-onboarding__backdrop--right"></div>
    <div class="login-onboarding__backdrop login-onboarding__backdrop--bottom"></div>
    <div class="login-onboarding__backdrop login-onboarding__backdrop--upper-right"></div>
    <div class="login-onboarding__backdrop login-onboarding__backdrop--middle-left"></div>
    <div class="login-onboarding__backdrop login-onboarding__backdrop--lower-left"></div>
    <div class="login-onboarding__backdrop login-onboarding__backdrop--lower-right"></div>
    <div class="login-onboarding__backdrop login-onboarding__backdrop--center-top"></div>
    <div class="login-onboarding__backdrop login-onboarding__backdrop--center-bottom"></div>
    <div class="login-onboarding__tool login-onboarding__tool--book"><i></i></div>
    <div class="login-onboarding__tool login-onboarding__tool--pencil"></div>
    <div class="login-onboarding__tool login-onboarding__tool--ruler"></div>
    <div class="login-onboarding__tool login-onboarding__tool--note"><i></i><i></i><i></i></div>
    <div class="login-onboarding__tool login-onboarding__tool--center-book"><i></i></div>

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
                                <div class="onboarding-two-character" aria-hidden="true">
                                    <span class="onboarding-two-character__hair"></span>
                                    <span class="onboarding-two-character__head">
                                        <i class="onboarding-two-character__eye onboarding-two-character__eye--left"></i>
                                        <i class="onboarding-two-character__eye onboarding-two-character__eye--right"></i>
                                        <i class="onboarding-two-character__nose"></i>
                                        <i class="onboarding-two-character__smile"></i>
                                    </span>
                                    <span class="onboarding-two-character__body"></span>
                                    <span class="onboarding-two-character__shirt"></span>
                                    <span class="onboarding-two-character__arm"></span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($slide === 'progress')
                        <div class="onboarding-three-scene">
                            <div class="onboarding-three-card onboarding-three-card--journey">
                                <div class="onboarding-three-card__header">
                                    <div><small>@lang('Mindigo-auth::app.onboarding.progress.journey_label')</small><strong>@lang('Mindigo-auth::app.onboarding.progress.journey_title')</strong></div>
                                    <span>↗</span>
                                </div>
                                <div class="onboarding-three-map">
                                    <i class="onboarding-three-map__path"></i>
                                    <span class="onboarding-three-pin onboarding-three-pin--one"><b>1</b></span>
                                    <span class="onboarding-three-pin onboarding-three-pin--two"><b>2</b></span>
                                    <span class="onboarding-three-pin onboarding-three-pin--three"><b>3</b></span>
                                    <span class="onboarding-three-map__pulse"></span>
                                </div>
                                <div class="onboarding-three-task"><span>01</span><div><strong>@lang('Mindigo-auth::app.onboarding.progress.task_one')</strong><small>@lang('Mindigo-auth::app.onboarding.progress.task_one_meta')</small></div><b>✓</b></div>
                                <div class="onboarding-three-task"><span>02</span><div><strong>@lang('Mindigo-auth::app.onboarding.progress.task_two')</strong><small>@lang('Mindigo-auth::app.onboarding.progress.task_two_meta')</small></div><b>→</b></div>
                            </div>

                            <div class="onboarding-three-card onboarding-three-card--course">
                                <div class="onboarding-three-course-art">
                                    <svg viewBox="0 0 180 92" aria-hidden="true">
                                        <rect width="180" height="92" rx="14" fill="#fef3c7"/>
                                        <circle cx="50" cy="43" r="20" fill="#f3ad7b"/>
                                        <path d="M29 43c0-22 12-32 27-29 13 3 19 13 17 28-8-10-18-15-30-16-1 9-6 15-14 17Z" fill="#172033"/>
                                        <path d="M20 92c3-25 14-38 31-38 18 0 31 14 34 38H20Z" fill="#22c55e"/>
                                        <path d="M94 17h58a9 9 0 0 1 9 9v19a9 9 0 0 1-9 9h-31l-13 11 3-11H94a9 9 0 0 1-9-9V26a9 9 0 0 1 9-9Z" fill="#fff"/>
                                        <path d="M101 30h43M101 40h29" stroke="#86efac" stroke-width="5" stroke-linecap="round"/>
                                        <circle cx="137" cy="72" r="14" fill="#8b5cf6"/>
                                        <path d="m133 65 10 7-10 7V65Z" fill="#fff"/>
                                    </svg>
                                </div>
                                <small>@lang('Mindigo-auth::app.onboarding.progress.recommendation')</small>
                                <strong>@lang('Mindigo-auth::app.onboarding.progress.course_title')</strong>
                                <div class="onboarding-three-rating"><span>★★★★★</span><b>4.9</b></div>
                            </div>

                            <div class="onboarding-three-card onboarding-three-card--reward">
                                <span>★</span>
                                <div><strong>@lang('Mindigo-auth::app.onboarding.progress.reward_title')</strong><small>@lang('Mindigo-auth::app.onboarding.progress.reward_meta')</small></div>
                                <i></i>
                            </div>
                            <div class="onboarding-three-badge">M</div>
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
