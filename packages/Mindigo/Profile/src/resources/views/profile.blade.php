@extends('Mindigo-dashboard::layouts')

@section('title', 'Tài khoản của tôi — Mindigo')

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Profile/src/resources/css/app.css',
        'packages/Mindigo/Profile/src/resources/js/app.js',
    ])
@endsection

@section('content')

    @include('Mindigo-profile::partials._topbar')

    <div class="max-w-6xl mx-auto px-6 py-8 flex gap-6">

        @include('Mindigo-profile::partials._sidebar')

        <div class="flex-1 flex flex-col gap-5">
            @include('Mindigo-profile::partials._tab_profile')
            @include('Mindigo-profile::partials._tab_email')
            @include('Mindigo-profile::partials._tab_security')
        </div>

    </div>

@endsection

@section('scripts')
    <script>
        window.__profileSuccess = @json(session('success'));
        window.__profileErrors = @json($errors->all());
    </script>
@endsection