@extends('layouts.app')

@section('content')
<section class="section">
  <div class="container">
    <h1>{{ __('messages.Student Platform') }}</h1>

    <div class="grid">
      @foreach($items as $item)
        <div class="card">
          <h3>{{ $item->title }}</h3>
          <p>{{ Str::limit($item->summary, 150) }}</p>
          <a href="{{ route('student-platform.show', $item->slug) }}" class="btn-primary">{{ __('messages.Read More') }}</a>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endsection
