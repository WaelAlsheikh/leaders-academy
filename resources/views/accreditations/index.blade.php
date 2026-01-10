@extends('layouts.app')

@section('content')
<div class="container" style="padding: 60px 0;">

  {{-- عنوان الصفحة --}}
  <div style="text-align:center; margin-bottom:40px;">
    <h1 style="color:var(--primary); font-size:1.8rem; margin:0 0 8px;">{{ __('messages.Accreditations') }}</h1>
    <p style="color:#666; max-width:900px; margin:8px auto 0;">
      {{ __('messages.Our official accreditations and partnerships that guarantee quality and recognition.') }}
    </p>
  </div>

  {{-- ثلاث بطاقات اعتماد (كما كانت) --}}
  <div class="accredits-grid">
    <article class="accredit-card">
      <div class="accredit-icon">
        <i class="fa-solid fa-school"></i>
      </div>
      <div class="accredit-body">
        <h3>وكيل حصري لجامعة أكسفورد البريطانية في سوريا</h3>
        <p>
          شراكة رسمية تمنح الأكاديمية تمثيلاً وتعاوناً أكاديمياً مع جامعة أكسفورد البريطانية في سوريا، ما يعزز مستوى البرامج وفرص الاعتراف الدولي.
        </p>
      </div>
    </article>

    <article class="accredit-card">
      <div class="accredit-icon">
        <i class="fa-solid fa-pencil"></i>
      </div>
      <div class="accredit-body">
        <h3>وكيل حصري لتدريب وامتحان الـ IELTS</h3>
        <p>
          نقدم برامج تحضير معتمدة لاختبار الأيلتس، بالإضافة إلى إمكانية عقد الامتحانات والتقييمات عبر شراكات مع جهات معتمدة.
        </p>
      </div>
    </article>

    <article class="accredit-card">
      <div class="accredit-icon">
        <i class="fa-solid fa-certificate"></i>
      </div>
      <div class="accredit-body">
        <h3>معتمد من مركز أبوظبي للتعليم والتدريب التقني والمهني</h3>
        <p>
          اعتماد رسمي من مركز أبوظبي للتعليم والتدريب التقني والمهني يدعم موثوقية شهاداتنا ويعزز اعتراف المؤسسات بها.
        </p>
      </div>
    </article>
  </div>

  {{-- -------------------------
       قسم اللوغوهات (4 شعارات في صف واحد)
       ------------------------- --}}
  <div style="margin-top:30px; text-align:center;">
    {{-- <h2 class="section-heading" style="margin-bottom:18px;">{{ __('messages.Our Partners & Accreditations') }}</h2> s) --}

    {{-- استخدمنا .logos و .logo-item (موجودين في style.css) --}}
    <div class="logos" aria-label="Accreditation logos" role="list">
      @php
        // أسماء الملفات بدون امتداد — سنحاول .png أولاً ثم نجرب .jpg عبر onerror
        $logos = ['acc1','acc2','acc3','acc4'];
        $base = 'assets/images/accreditations';
      @endphp

      @foreach($logos as $logo)
        <div class="logo-item" role="listitem" style="flex:0 0 20%;max-width:160px;">
          <img
            src="{{ $base }}/{{ $logo }}.png"
            alt="{{ __('messages.Accreditation logo') }} - {{ $logo }}"
            loading="lazy"
            style="max-width:160px;height:auto;display:inline-block"
            onerror="this.onerror=null;this.src='{{ $base }}/{{ $logo }}.jpg'">
        </div>
      @endforeach
    </div>
  </div>

</div>
@endsection
