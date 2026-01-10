@extends('layouts.app')

@section('content')
<section class="section">
  <div class="container">
    <div class="program-detail" style="max-width:900px; margin:auto;">
      <div style="display:flex; gap:20px; align-items:flex-start; flex-wrap:wrap;">
        <div style="flex:1; min-width:260px;">
          @if($program->image)
            <img src="{{ asset('storage/' . $program->image) }}" alt="{{ $program->title }}" style="width:100%; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.08);">
          @else
            <img src="{{ asset('assets/images/default-program.jpg') }}" alt="{{ $program->title }}" style="width:100%; border-radius:10px;">
          @endif
        </div>

        <div style="flex:2; min-width:300px;">
          <h1 style="color:var(--primary); margin-bottom:8px;">{{ $program->title }}</h1>
          @if($program->category)
            <p style="margin:0 0 10px; color:#666;"><strong>{{ $program->category }}</strong></p>
          @endif

          <p style="color:#444; line-height:1.6;">{!! nl2br(e($program->long_description ?? $program->short_description)) !!}</p>

          <div style="margin-top:18px; display:flex; gap:12px; flex-wrap:wrap;">
            @if($program->duration)
              <div style="padding:10px 14px; background:#fff;border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.06);">
                <strong>المدة:</strong> {{ $program->duration }}
              </div>
            @endif

            @if($program->certificate)
              <div style="padding:10px 14px; background:#fff;border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.06);">
                <strong>الشهادة:</strong> {{ $program->certificate }}
              </div>
            @endif
          </div>

          <a href="{{ route('applications.create', ['type' => 'training', 'slug' => $program->slug]) }}" class="btn-primary">
  			  {{ __('messages.Register / Apply') }}
		  </a>

        </div>
      </div>

      {{-- أسفل الصفحة: محتوى تفصيلي / مناهج (إن وُجد) --}}
      <div style="margin-top:28px; background:#fff; padding:18px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.04);">
        <h3 style="margin-top:0; color:var(--secondary);">تفاصيل البرنامج</h3>
        <div style="color:#444; line-height:1.7;">{!! $program->long_description !!}</div>
      </div>
    </div>
  </div>
</section>
@endsection
