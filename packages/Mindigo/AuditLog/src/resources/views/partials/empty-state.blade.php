@include('core::partials.empty-state', [
    'preset' => $preset ?? 'default',
    'icon' => $icon ?? null,
    'title' => $title ?? null,
    'message' => $message ?? null,
])
