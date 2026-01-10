@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container contact-container">
        <h1>تواصل معنا</h1>
        <div class="contact-info">
            <h3>📍 الفروع:</h3>
            <p>الفرع الرئيسي: دبي، الإمارات العربية المتحدة</p>
            <p>فرع دمشق - الكسوة</p>

            <h3>📞 أرقام التواصل:</h3>
            <p>+963965121776</p>
            <p>+971568346146</p>
        </div>

        <!-- زر التواصل عبر واتساب بدل الفورم -->
        @php
          // المستخدم أعطى الرقم: 00963965121776
          // wa.me يطلب الرقم بصيغة دولية بدون 00 أو + -> 963965121776
          $whNumber = '963965121776';
          $presetMessage = urlencode("مرحباً، أود التواصل بخصوص استفسار عبر موقع معهد ليدرز.");
          $waLink = "https://wa.me/{$whNumber}?text={$presetMessage}";
        @endphp

        <div style="margin-top:18px; text-align:center;">
            <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="btn-primary" style="display:inline-block;">
                انقر هنا للتواصل معنا
            </a>
        </div>
    </div>
</section>
@endsection
