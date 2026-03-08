@extends('layouts.app')

@section('content')
<section class="section">
  <div class="container">
    @php
      $branchImagePath = null;
      if (!empty($branch->image)) {
          $branchImagePath = str_replace('\\', '/', $branch->image);
          if (str_starts_with(trim($branch->image), '[')) {
              $decoded = json_decode($branch->image, true);
              $branchImagePath = $decoded[0]['download_link'] ?? null;
              $branchImagePath = $branchImagePath ? str_replace('\\', '/', $branchImagePath) : null;
          }
      }
      $branchImagePath = $branchImagePath ? ltrim($branchImagePath, '/') : null;
      $branchImageUrl = $branchImagePath
          ? (str_starts_with($branchImagePath, 'storage/') ? asset($branchImagePath) : asset('storage/' . $branchImagePath))
          : asset('assets/images/default-program.jpg');
    @endphp

    <div class="program-detail" style="max-width:900px; margin:auto;">
      <a href="{{ route('programs.show', $program->slug) }}" class="btn-secondary" style="display:inline-block;margin-bottom:14px;">
        العودة إلى {{ $program->title }}
      </a>

      <div style="display:flex; gap:20px; align-items:flex-start; flex-wrap:wrap;">
        <div style="flex:1; min-width:260px;">
          <img src="{{ $branchImageUrl }}" alt="{{ $branch->title }}" style="width:100%; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.08);">
        </div>

        <div style="flex:2; min-width:300px;">
          <h1 style="color:var(--primary); margin-bottom:8px;">{{ $branch->title }}</h1>

          @if($branch->short_description)
            <p style="color:#444; line-height:1.8; margin-bottom:10px;">{{ $branch->short_description }}</p>
          @endif

          @if($branch->long_description)
            <div style="color:#444; line-height:1.8;">{!! $branch->long_description !!}</div>
          @endif

          <a href="{{ route('student.login') }}" class="btn-primary" style="display:inline-block;margin-top:16px;">
            تسجيل
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

