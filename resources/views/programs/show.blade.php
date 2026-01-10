@extends('layouts.app')

@section('content')
<section class="section" style="margin-top: 80px;">
  <div class="container">
    {{-- ====== القسم العلوي (الصورة + المقدمة) ====== --}}
    <div class="program-intro" style="display:flex; flex-wrap:wrap; gap:30px; align-items:center;">
      {{-- صورة البرنامج --}}
      <div class="program-intro-image" style="flex:1 1 45%; text-align:center;">
        @if($program->image)
          <img src="{{ asset('storage/' . $program->image) }}" 
               alt="{{ $program->title }}" 
               class="program-main-image">
        @else
          <img src="{{ asset('assets/images/default-program.jpg') }}" 
               alt="{{ $program->title }}" 
               class="program-main-image">
        @endif
      </div>

      {{-- معلومات البرنامج --}}
      <div class="program-intro-meta" style="flex:1 1 50%;">
        <h2 class="program-title">{{ $program->title }}</h2>

        @if($program->short_description)
          <p class="program-short">{{ $program->short_description }}</p>
        @endif

        <a href="{{ route('applications.create', ['type' => 'program', 'slug' => $program->slug]) }}" class="btn-primary">
          {{ __('messages.Register / Apply') }}
        </a>
      </div>
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
