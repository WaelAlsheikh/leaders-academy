@extends('layouts.app')

@section('content')
@php
    $collegeImage = null;

    if (!empty($college->image)) {
        $collegeImage = \Illuminate\Support\Str::startsWith($college->image, ['http://', 'https://', '/'])
            ? $college->image
            : asset('storage/' . ltrim($college->image, '/'));
    }
@endphp

<section class="section">
  <div class="container">

    <div class="college-hero">
      <div class="college-hero-image-shell">
        <div class="college-hero-image">
          @if($collegeImage)
            <img src="{{ $collegeImage }}" alt="{{ $college->title }}">
          @else
            <img src="{{ asset('assets/images/placeholder.png') }}" alt="{{ $college->title }}">
          @endif
        </div>
      </div>

      <div class="college-hero-text">
        <h1 class="college-title">{{ $college->title }}</h1>
        @if($college->short_description)
          <p class="college-short">{{ $college->short_description }}</p>
        @endif
        <a href="{{ route('student.login') }}" class="btn-primary college-contact-btn">
          تسجيل
        </a>
      </div>
    </div>

    <div class="college-content">
      <div class="college-desc">
        <h3>{{ __('messages.About the College') }}</h3>
        <div class="college-long">
          {!! $college->long_description !!}
        </div>
      </div>

      <div class="college-features">
        <h3>{{ __('messages.Study System & Features') }}</h3>
        <ul class="college-features-list">
          <li><span class="dot">•</span> {{ __('messages.Online learning platform and recorded lectures') }}</li>
          <li><span class="dot">•</span> {{ __('messages.Qualified faculty and support') }}</li>
          <li><span class="dot">•</span> {{ __('messages.Recognized certificate upon completion') }}</li>
        </ul>
      </div>
    </div>

  </div>
</section>
@endsection
