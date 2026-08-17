<div class="login-processing" data-login-processing aria-hidden="true"
    aria-label="@lang('Mindigo-auth::app.processing.label')"
    data-processing-message="@lang('Mindigo-auth::app.processing.processing')"
    data-success-message="@lang('Mindigo-auth::app.processing.success')"
    data-failed-message="@lang('Mindigo-auth::app.processing.failed')"
    data-returning-message="@lang('Mindigo-auth::app.processing.returning')">
    <div class="login-processing__backdrop login-processing__backdrop--one"></div>
    <div class="login-processing__backdrop login-processing__backdrop--two"></div>
    <div class="login-processing__backdrop login-processing__backdrop--three"></div>
    <div class="login-processing__academic" aria-hidden="true">
        <i class="login-processing__cap login-processing__cap--one"></i>
        <i class="login-processing__cap login-processing__cap--two"></i>
        <i class="login-processing__cap login-processing__cap--three"></i>
        <i class="login-processing__cap login-processing__cap--four"></i>
        <i class="login-processing__cap login-processing__cap--five"></i>
        <i class="login-processing__cap login-processing__cap--six"></i>
        <span class="login-processing__wave login-processing__wave--one"></span>
        <span class="login-processing__wave login-processing__wave--two"></span>
        <span class="login-processing__book"><b></b></span>
        <span class="login-processing__pencil"></span>
        <span class="login-processing__orbit login-processing__orbit--one"></span>
        <span class="login-processing__orbit login-processing__orbit--two"></span>
    </div>

    <div class="login-processing__center" role="status" aria-live="polite">
        <div class="login-processing__indicator">
            <svg class="login-processing__ring" viewBox="0 0 120 120" aria-hidden="true">
                <circle class="login-processing__ring-track" cx="60" cy="60" r="52"/>
                <circle class="login-processing__ring-value" cx="60" cy="60" r="52"/>
            </svg>
            <div class="login-processing__logo">
                <svg viewBox="0 0 200 220" aria-hidden="true">
                    <circle cx="105" cy="145" r="88" fill="#22c55e"/>
                    <path d="M95 59Q87 24 105 10q15 17 7 49M109 57q-5-31 10-44 14 20 2 46" fill="#86efac" stroke="#14532d" stroke-width="5" stroke-linejoin="round"/>
                    <circle cx="80" cy="136" r="22" fill="#fff"/><circle cx="130" cy="136" r="22" fill="#fff"/>
                    <circle cx="85" cy="140" r="12" fill="#14532d"/><circle cx="135" cy="140" r="12" fill="#14532d"/>
                    <circle cx="90" cy="135" r="4" fill="#fff"/><circle cx="140" cy="135" r="4" fill="#fff"/>
                    <path d="m91 162 14-8 15 8-15 17-14-17Z" fill="#fbbf24"/>
                </svg>
            </div>
            <svg class="login-processing__result login-processing__result--success" viewBox="0 0 64 64" aria-hidden="true"><path d="m17 33 10 10 21-23"/></svg>
            <svg class="login-processing__result login-processing__result--error" viewBox="0 0 64 64" aria-hidden="true"><path d="m20 20 24 24M44 20 20 44"/></svg>
        </div>
        <p class="login-processing__message" data-login-processing-message>@lang('Mindigo-auth::app.processing.processing')</p>
        <p class="login-processing__detail" data-login-processing-detail></p>
    </div>

    <div class="login-processing__confetti" aria-hidden="true">
        @for ($particle = 1; $particle <= 14; $particle++)
            <i style="--particle: {{ $particle }}"></i>
        @endfor
    </div>
</div>
