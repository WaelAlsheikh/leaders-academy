@extends('layouts.app')

@section('content')
<section class="section" style="padding: 40px 0;">
  <div class="container">

    <!-- Coming Soon Card (simple and focused) -->
    <div class="virtual-school-hero" style="display:flex; align-items:center; justify-content:center; min-height:60vh;">
      <div class="coming-card" style="width:100%; max-width:980px; background: linear-gradient(180deg, #ffffff 0%, #fbfbfb 100%); border-radius:16px; box-shadow: 0 18px 50px rgba(18,18,18,0.06); overflow:hidden; display:flex; gap:0; align-items:stretch;">

        <!-- Left: illustration (hidden on small screens) -->
        <div class="coming-illustration" style="flex: 0 0 46%; display:flex; align-items:center; justify-content:center; background: linear-gradient(135deg, rgba(242,183,5,0.06), rgba(74,74,74,0.02)); padding:30px;">
          <!-- استخدمت الملف المرفوع مسبقاً كمصدر للصورة -->
          <img src="{{ asset('assets/images/vertual-university.jpg') }}" alt="الجامعة الافتراضية" style="width:100%; max-width:420px; border-radius:10px; object-fit:cover;">
        </div>

        <!-- Right: content -->
        <div class="coming-body" style="flex:1 1 54%; padding:34px 36px; text-align:right; direction:rtl;">
          <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
            <div>
              <h1 style="margin:0 0 8px; font-size:1.85rem; color:var(--secondary); font-weight:700;">{{ __('messages.Virtual university') }}</h1>
              <p style="margin:0; color:#666; line-height:1.6; font-size:1rem;">
                منصة قادمة قريباً — نعمل على منصة تعليمية تفاعلية ومتكاملة تقدم دورات وبرامج مع شهادات واعتمادات رسمية.
              </p>
            </div>

            <!-- Badge "قريباً" -->
            <div style="text-align:center;">
              <div style="background: linear-gradient(180deg,#f2b705,#d9a100); color:#000; padding:8px 12px; border-radius:10px; font-weight:700; box-shadow:0 6px 18px rgba(242,183,5,0.12);">
                🔜 قريباً
              </div>
            </div>
          </div>

          <div style="height:18px;"></div>

          <p style="color:#555; margin-bottom:18px;">
            نجهّز محتوى تفاعليًا مع أدوات تعليم حديثة ودعم أكاديمي كامل. إن رغبت بالإبلاغ عند الإطلاق أو الانضمام للقائمة الأولية، استخدم زر التواصل عبر واتساب أدناه.
          </p>

          <!-- Single CTA: WhatsApp only -->
          <div style="display:flex; gap:12px; justify-content:flex-end; flex-wrap:wrap;">
            <a href="https://wa.me/963965121776" target="_blank" rel="noopener noreferrer" class="btn-primary" style="display:inline-flex; align-items:center; gap:10px; padding:12px 18px; border-radius:10px; font-weight:700;">
              <i class="fa-brands fa-whatsapp" style="font-size:18px;"></i> تواصل عبر واتساب
            </a>
          </div>
        </div> <!-- end coming-body -->

      </div> <!-- end coming-card -->
    </div> <!-- end hero -->

  </div>
</section>

<!-- Small style tweaks for responsiveness and polish -->
<style>
  /* إخفاء الجزء الرسومي على الشاشات الصغيرة للحفاظ على تركيز المحتوى */
  @media (max-width: 900px) {
    .coming-illustration { display: none !important; }
    .coming-body { padding: 22px !important; text-align: center !important; }
    .coming-card { border-radius: 12px !important; display:block; }
    .coming-body p { text-align: center; }
    .coming-body .btn-primary { margin: 0 auto; }
  }

  /* تحسين مظهر زر واتساب داخل البطاقة */
  .coming-card .btn-primary { background: linear-gradient(180deg,#25D366,#13B54A); color:#fff; border:none; }
  .coming-card .btn-primary i { margin-left:6px; }

  /* خصائص عامة للبطاقة */
  .coming-card a.btn-primary:hover { transform: translateY(-3px); box-shadow: 0 10px 26px rgba(0,0,0,0.12); }
</style>
@endsection
