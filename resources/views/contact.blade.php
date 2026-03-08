@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container contact-page">
        <div class="contact-page-card">
            <h1 class="contact-page-title">تواصل معنا</h1>
            <p class="contact-page-subtitle">
                يسعدنا استقبال استفساراتكم، ويمكنكم التواصل مباشرة عبر واتساب.
            </p>

            <div class="contact-page-grid">
                <div class="contact-page-block">
                    <h3>الفروع</h3>
                    <p>الفرع الرئيسي: دبي، الإمارات العربية المتحدة</p>
                    <p>فرع دمشق - الكسوة</p>
                </div>

                <div class="contact-page-block">
                    <h3>أرقام التواصل</h3>
                    <p dir="ltr">+963 965 121 776</p>
                    <p dir="ltr">+971 56 834 6146</p>
                </div>
            </div>
        </div>

        @php
          $whNumber = '963965121776';
          $presetMessage = urlencode("مرحباً، أود التواصل بخصوص استفسار عبر موقع معهد ليدرز.");
          $waLink = "https://wa.me/{$whNumber}?text={$presetMessage}";
        @endphp

        <div class="contact-page-action">
            <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="btn-primary">
                تواصل عبر واتساب
            </a>
        </div>
    </div>
</section>
@endsection
