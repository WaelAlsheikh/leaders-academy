@extends('layouts.app')

@section('content')
<section class="section">
  <div class="container">

    {{-- العنوان العام إذا أردت --}}
    <div style="text-align:center; margin-bottom:30px;">
      <h1 style="color:var(--primary);">{{ $item->title }}</h1>
    </div>

    {{-- ====== القسم الأول ====== --}}
    <div class="platform-section" style="display:flex; gap:24px; align-items:center; flex-wrap:wrap; margin-bottom:40px;">
      {{-- صورة القسم الأول - على اليسار (في RTL نستخدم float أو order) --}}
      @if($item->image1)
        <div style="flex: 1 1 40%; text-align:left;">
          {{-- نقبل مسار ملف داخل storage أو رابط كامل --}}
          @php
            $img1 = $item->image1;
            $img1Url = (filter_var($img1, FILTER_VALIDATE_URL)) ? $img1 : asset('storage/' . ltrim($img1, '/'));
          @endphp
          <img src="{{ $img1Url }}" alt="{{ $item->title1 ?? 'image1' }}" style="width:100%; max-width:560px; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,0.06);">
        </div>
      @endif

      {{-- نص القسم الأول --}}
      <div style="flex: 1 1 55%; text-align:right;">
        @if($item->title1)
          <h2 style="color:var(--primary); margin-bottom:12px;">{{ $item->title1 }}</h2>
        @endif

        @if($item->content1)
          <div style="color:#333; line-height:1.8; margin-bottom:18px;">
            {!! nl2br(e($item->content1)) !!}
          </div>
        @endif

        {{-- زر التسجيل مثلاً --}}
        <div>
          <a href="{{ route('applications.create', ['type' => 'student_platform', 'slug' => $item->slug, 'section' => 1]) }}" class="btn-primary">
            {{ __('messages.Register / Apply') }}
          </a>
        </div>
      </div>
    </div>

    {{-- ====== القسم الثاني ====== --}}
    <div class="platform-section" style="display:flex; gap:24px; align-items:center; flex-wrap:wrap; margin-bottom:40px;">
      {{-- لتغيير الانتشار: نجعل الصورة في اليمين أو اليسار حسب التصميم
           هنا نضع الصورة على اليسار مثل الصورة المرسلة (أنت طلبت ترتيبين أسفل بعض) --}}
      @if($item->image2)
        <div style="flex: 1 1 40%; text-align:left;">
          @php
            $img2 = $item->image2;
            $img2Url = (filter_var($img2, FILTER_VALIDATE_URL)) ? $img2 : asset('storage/' . ltrim($img2, '/'));
          @endphp
          <img src="{{ $img2Url }}" alt="{{ $item->title2 ?? 'image2' }}" style="width:100%; max-width:560px; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,0.06);">
        </div>
      @endif

      <div style="flex: 1 1 55%; text-align:right;">
        @if($item->title2)
          <h2 style="color:var(--primary); margin-bottom:12px;">{{ $item->title2 }}</h2>
        @endif

        @if($item->content2)
          <div style="color:#333; line-height:1.8; margin-bottom:18px;">
            {!! nl2br(e($item->content2)) !!}
          </div>
        @endif

        <div>
          <a href="{{ route('applications.create', ['type' => 'student_platform', 'slug' => $item->slug, 'section' => 2]) }}" class="btn-primary">
            {{ __('messages.Register / Apply') }}
          </a>
        </div>
      </div>
    </div>

  </div>
</section>
@endsection
