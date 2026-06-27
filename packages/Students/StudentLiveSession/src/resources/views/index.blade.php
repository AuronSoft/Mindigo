@extends('Mindigo-dashboard::layouts')

@section('title', __('student-dashboard::app.nav_live') . ' · Mindigo LMS')
@section('meta_description', __('student-dashboard::app.nav_live'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
    @include('student-dashboard::partials.placeholder', ['title' => __('student-dashboard::app.nav_live')])
@endsection
