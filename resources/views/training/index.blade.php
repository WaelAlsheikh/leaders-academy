@extends('layouts.app')

@section('content')
<section class="section">
  <div class="container">
    <h2>{{ __('messages.Training Programs') }}</h2>

    <div class="grid">
      @forelse($programs as $p)
        <div class="card" onclick="window.location='{{ route('training.show', $p->slug) }}'">
          @if($p->image)
            <img src="{{ asset('storage/' . $p->image) }}" alt="{{ $p->title }}">
          @else
            <img src="{{ asset('assets/images/default-program.jpg') }}" alt="{{ $p->title }}">
          @endif
          <h3>{{ $p->title }}</h3>
          <p>{{ $p->short_description }}</p>
          <a href="{{ route('training.show', $p->slug) }}" class="btn-secondary">{{ __('messages.Read More') }}</a>
        </div>
      @empty
        <p>{{ __('messages.No programs available') }}</p>
      @endforelse
    </div>
  </div>
</section>
@endsection
