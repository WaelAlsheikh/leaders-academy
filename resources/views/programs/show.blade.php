@extends('layouts.app')

@section('content')
<section class="section" style="margin-top: 80px;">
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
    {{-- ====== القسم العلوي (الصورة + المقدمة) ====== --}}
    <div class="program-intro" style="display:flex; flex-wrap:wrap; gap:30px; align-items:center;">
      {{-- صورة البرنامج --}}
      <div class="program-intro-image" style="flex:1 1 45%; text-align:center;">
        <img src="{{ $programImageUrl }}"
             alt="{{ $program->title }}"
             class="program-main-image">
      </div>

      {{-- معلومات البرنامج --}}
      <div class="program-intro-meta" style="flex:1 1 50%;">
        <h2 class="program-title">{{ $program->title }}</h2>

        @if($program->short_description)
          <p class="program-short">{{ $program->short_description }}</p>
        @endif
      </div>
    </div>

    <div style="margin-top:24px;">
      <h3 class="section-heading" style="margin-bottom:14px;">التخصصات المتاحة</h3>
      @if(($branches ?? collect())->isNotEmpty())
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;">
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
            <a href="{{ route('programs.branches.show', [$program->slug, $branch->slug]) }}"
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
</section>

{{-- ====== القسم الثالث (مدة الدراسة) - معكوس أفقياً: الصورة على اليمين والنص على اليسار ====== --}}
@if($program->duration)
<section class="section">
  <div class="container">
    <h3 class="section-heading">{{ __('messages.Study Duration') }}</h3>

    <div class="duration-row">
      {{-- النص على اليسار --}}
      <div class="duration-text">
        <ul class="duration-list">
          <li>
            <span class="duration-icon">⌚</span>
            <div class="duration-item-content">
              <strong>المدة الزمنية:</strong>
              <div class="duration-item-text">{{ $program->duration }}</div>
            </div>
          </li>

          <li>
            <span class="duration-icon">🌐</span>
            <div class="duration-item-content">
              <strong>مرونة الدراسة:</strong>
              <div class="duration-item-text">مرونة كاملة في متابعة الدراسة عبر الإنترنت.</div>
            </div>
          </li>

          <li>
            <span class="duration-icon">⚡</span>
            <div class="duration-item-content">
              <strong>إمكانية التسريع:</strong>
              <div class="duration-item-text">إمكانية إنهاء البرنامج بوتيرة أسرع وفق نظام الدراسة المكثفة.</div>
            </div>
          </li>
        </ul>
      </div>

      {{-- الصورة على اليمين --}}
      <div class="duration-image">
        <img src="{{ asset('assets/images/duration-right.png') }}" alt="Duration Illustration" class="duration-illustration">
      </div>
    </div>
  </div>
</section>
@endif

{{-- ====== قسم الشهادات والاعتمادات (أسفل فترة الدراسة) ====== --}}
<section class="section gray-section certificates-section">
  <div class="container">
    <h3 class="section-heading" style="margin-bottom:30px;">{{ __('messages.Certificates & Accreditations') }}</h3>

    <div class="certificates-row">
      {{-- صورة على اليسار --}}
      <div class="cert-image">
        <img src="{{ asset('assets/images/certificate-left.jpg') }}" alt="Certificate" class="cert-illustration">
      </div>

      {{-- نص الأيقونات على اليمين --}}
      <div class="cert-text">
        <ul class="cert-list">
          <li>
            <span class="cert-icon">🎓</span>
            <div class="cert-content">
              <strong>شهادة معتمدة من جامعتنا.</strong>
              <div class="cert-desc">{{ $program->certificate }}</div>
            </div>
          </li>
          <li>
            <span class="cert-icon">✔️</span>
            <div class="cert-content">
              <strong>إمكانية التصديق الدولي (أبوستيل).</strong>
              <div class="cert-desc">نقدّم تسهيلات لإجراءات التصديق والاعتماد الدولي عند الحاجة.</div>
            </div>
          </li>
          <li>
            <span class="cert-icon">🤝</span>
            <div class="cert-content">
              <strong>اعتمادات وشراكات أكاديمية.</strong>
              <div class="cert-desc">شراكات مع مؤسسات تعليمية لدعم الاعتراف بالشهادة وانتقال الخريجين.</div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</section>

@endsection
