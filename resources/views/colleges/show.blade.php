@extends('layouts.app')

@section('content')
<section class="section">
  <div class="container">

    <!-- Hero small for college -->
    <div class="college-hero">
      @if($college->image)
        <div class="college-hero-image">
          <img src="{{ asset('storage/' . $college->image) }}" alt="{{ $college->title }}">
        </div>
      @endif

      <div class="college-hero-text">
        <h1 class="college-title">{{ $college->title }}</h1>
        @if($college->short_description)
          <p class="college-short" style="margin-top:10px;">{{ $college->short_description }}</p>
        @endif
        <a href="{{ route('contact') }}" class="btn-primary college-contact-btn" style="margin-top:12px; display:inline-block;">
          {{ __('messages.Contact Us') }}
        </a>
      </div>
    </div>

    <!-- Content blocks: description and features -->
    <div class="college-content">
      <div class="college-desc">
        <h3>{{ __('messages.About the College') }}</h3>
        <div class="college-long" style="color:#444; margin-top:12px;">
          {!! $college->long_description !!}
        </div>
      </div>

      <div class="college-features">
        <h3>{{ __('messages.Study System & Features') }}</h3>
        <ul class="college-features-list" style="list-style:none; padding:0; margin-top:12px;">
          <li><span class="dot">•</span> {{ __('messages.Online learning platform and recorded lectures') }}</li>
          <li><span class="dot">•</span> {{ __('messages.Qualified faculty and support') }}</li>
          <li><span class="dot">•</span> {{ __('messages.Recognized certificate upon completion') }}</li>
        </ul>
      </div>
    </div>

  </div>
</section>
@endsection
