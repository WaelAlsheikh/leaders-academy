@extends('layouts.app')

@section('content')
<section class="section">
  <div class="container">
    <h1>{{ __('messages.University Faculties') }}</h1>
    <div class="grid">
      @forelse($colleges as $college)
        <div class="card">
          @if($college->image)
            <img src="{{ asset('storage/' . $college->image) }}" alt="{{ $college->title }}">
          @endif
          <h3>{{ $college->title }}</h3>
          <p>{{ $college->short_description }}</p>
          <a href="{{ route('colleges.show', $college->slug) }}" class="btn-primary">{{ __('messages.Read More') }}</a>
        </div>
      @empty
        <p>{{ __('messages.No colleges available') }}</p>
      @endforelse
    </div>
  </div>
</section>
@endsection
