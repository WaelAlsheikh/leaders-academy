@extends('layouts.app')

@section('content')
  <h2 class="text-2xl mb-4">جميع البرامج</h2>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    @foreach($programs as $p)
      <div class="border rounded p-4">
        <img src="{{ asset($p->image) }}" alt="{{ $p->title }}" class="h-40 w-full object-cover mb-3">
        <h3 class="font-semibold">{{ $p->title }}</h3>
        <p class="text-sm">{{ $p->short_description }}</p>
        <a href="{{ route('programs.show',$p->slug) }}" class="inline-block mt-3">تفاصيل</a>
      </div>
    @endforeach
  </div>

  <div class="mt-6">
    {{ $programs->links() }}
  </div>
@endsection
