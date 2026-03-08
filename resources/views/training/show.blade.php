@extends('layouts.app')

@section('content')
<section class="section">
  <div class="container">
    @php
      $programImagePath = null;
      if (!empty($program->image)) {
          $programImagePath = str_replace('\\', '/', $program->image);
          if (str_starts_with(trim($program->image), '[')) {
              $decoded = json_decode($program->image, true);
              $programImagePath = $decoded[0]['download_link'] ?? null;
              $programImagePath = $programImagePath ? str_replace('\\', '/', $programImagePath) : null;
          }
      }
      $programImagePath = $programImagePath ? ltrim($programImagePath, '/') : null;
      $programImageUrl = $programImagePath
          ? (str_starts_with($programImagePath, 'storage/') ? asset($programImagePath) : asset('storage/' . $programImagePath))
          : asset('assets/images/default-program.jpg');
    @endphp
    <div class="program-detail" style="max-width:900px; margin:auto;">
      <div style="display:flex; gap:20px; align-items:flex-start; flex-wrap:wrap;">
        <div style="flex:1; min-width:260px;">
          <img src="{{ $programImageUrl }}" alt="{{ $program->title }}" style="width:100%; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.08);">
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
        </div>
      </div>
      <div style="margin-top:28px;">
        <h3 style="margin-top:0; color:var(--secondary);">التخصصات المتاحة</h3>

        @if(($branches ?? collect())->isNotEmpty())
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-top:12px;">
            @foreach($branches as $branch)
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
              <a href="{{ route('training.branches.show', [$program->slug, $branch->slug]) }}"
                 style="display:block;background:#fff;border-radius:12px;padding:12px;text-decoration:none;color:inherit;box-shadow:0 4px 10px rgba(0,0,0,0.05);transition:.2s;">
                <div style="height:140px;overflow:hidden;border-radius:8px;background:#f3f4f6;">
                  <img src="{{ $branchImageUrl }}" alt="{{ $branch->title }}" style="width:100%;height:100%;object-fit:cover;">
                </div>
                <h4 style="margin:10px 0 6px;color:var(--primary);">{{ $branch->title }}</h4>
                @if($branch->short_description)
                  <p style="margin:0;color:#555;line-height:1.6;">{{ \Illuminate\Support\Str::limit($branch->short_description, 110) }}</p>
                @endif
              </a>
            @endforeach
          </div>
        @else
          <div style="margin-top:12px;background:#fff7ed;border:1px solid #fed7aa;padding:12px 14px;border-radius:10px;color:#9a3412;">
            لا توجد تخصصات متاحة حالياً ضمن هذا البرنامج.
          </div>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection
