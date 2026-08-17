<div id="system-processing" class="system-processing is-visible" data-system-processing aria-hidden="false"
    aria-label="@lang('core::app.processing.label')">
    <div class="system-processing__decor" aria-hidden="true">
        @for ($cap = 1; $cap <= 6; $cap++)
            <i class="system-processing__cap system-processing__cap--{{ $cap }}"></i>
        @endfor
        <span class="system-processing__wave system-processing__wave--one"></span>
        <span class="system-processing__wave system-processing__wave--two"></span>
        <span class="system-processing__book"><i></i></span>
        <span class="system-processing__pencil"></span>
    </div>

    <div class="system-processing__center" role="status" aria-live="polite">
        <div class="system-processing__indicator">
            <svg class="system-processing__ring" viewBox="0 0 120 120" aria-hidden="true">
                <circle cx="60" cy="60" r="52" class="system-processing__ring-track"/>
                <circle cx="60" cy="60" r="52" class="system-processing__ring-value"/>
            </svg>
            <svg class="system-processing__logo" viewBox="0 0 200 220" aria-hidden="true">
                <circle cx="105" cy="145" r="88" fill="#22c55e"/>
                <path d="M95 59Q87 24 105 10q15 17 7 49M109 57q-5-31 10-44 14 20 2 46" fill="#86efac" stroke="#14532d" stroke-width="5" stroke-linejoin="round"/>
                <circle cx="80" cy="136" r="22" fill="#fff"/><circle cx="130" cy="136" r="22" fill="#fff"/>
                <circle cx="85" cy="140" r="12" fill="#14532d"/><circle cx="135" cy="140" r="12" fill="#14532d"/>
                <circle cx="90" cy="135" r="4" fill="#fff"/><circle cx="140" cy="135" r="4" fill="#fff"/>
                <path d="m91 162 14-8 15 8-15 17-14-17Z" fill="#fbbf24"/>
            </svg>
        </div>
        <p data-system-processing-message>@lang('core::app.processing.loading')</p>
        <span>@lang('core::app.processing.please_wait')</span>
    </div>
</div>
<noscript>
    <style>.system-processing { display: none !important; }</style>
</noscript>
