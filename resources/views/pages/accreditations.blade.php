@extends('layouts.app')

@section('content')
  <!-- Hero -->
  <section class="mb-8">
    <div class="relative rounded overflow-hidden">
      <img src="{{ asset('assets/images/hero.jpg') }}" alt="قاعات" class="w-full h-64 object-cover">
      <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
        <div class="text-center text-white">
          <h1 class="text-3xl font-bold">معهد ليدرز للتدريب والتطوير</h1>
          <p class="mt-2">نحن نصنع قادة اليوم لتقود الغد</p>
        </div>
      </div>
    </div>
  </section>

  <!-- About short -->
  <section class="mb-8">
    <h2 class="text-xl font-semibold mb-3">الرؤية</h2>
    <div class="prose max-w-none">
      {!! $about->content ?? '<p>نص الرؤية غير مُعطى بعد</p>' !!}
    </div>
  </section>

  <!-- Programs -->
  <section class="mb-8">
    <h2 class="text-xl font-semibold mb-3">البرامج</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      @foreach($programs as $program)
        <div class="border rounded p-4">
          <img src="{{ asset($program->image) }}" alt="{{ $program->title }}" class="h-40 w-full object-cover mb-3">
          <h3 class="font-semibold">{{ $program->title }}</h3>
          <p class="text-sm">{{ $program->short_description }}</p>
          <a href="{{ route('programs.show',$program->slug) }}" class="inline-block mt-3 text-gold">المزيد</a>
        </div>
      @endforeach
    </div>
  </section>

  <!-- Accreditations -->
  <section class="mb-8">
    <h2 class="text-xl font-semibold mb-3">الاعتمادات والشركاء</h2>
    <div class="flex gap-4 items-center">
      @foreach($accreditations as $acc)
        <div class="w-32">
          <img src="{{ asset($acc->logo) }}" alt="{{ $acc->title }}" class="object-contain h-16 w-full">
          <p class="text-sm text-center">{{ $acc->title }}</p>
        </div>
      @endforeach
    </div>
  </section>

  <!-- Gallery -->
  <section>
    <h2 class="text-xl font-semibold mb-3">معرض الصور</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
      @foreach($gallery as $g)
        <img src="{{ asset($g->file) }}" alt="{{ $g->alt }}" class="object-cover h-40 w-full">
      @endforeach
    </div>
  </section>
@endsection
