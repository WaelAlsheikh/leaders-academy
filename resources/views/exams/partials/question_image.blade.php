@php
    $imageUrl = $imageUrl ?? null;
    $imageAlt = $imageAlt ?? 'صورة السؤال';
@endphp
@if($imageUrl)
<figure class="exam-question-image">
    <button type="button" class="exam-question-image-trigger" data-exam-image="{{ $imageUrl }}" aria-label="توسيع صورة السؤال">
        <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" loading="lazy">
        <span class="exam-question-image-hint"><i class="fa-solid fa-expand"></i> اضغط للتكبير</span>
    </button>
</figure>
@once
<style>
.exam-question-image{margin:14px 0 18px;max-width:100%;}
.exam-question-image-trigger{
  display:block;width:100%;max-width:640px;padding:0;border:1px solid #e5edf2;border-radius:14px;
  background:#f7fafc;overflow:hidden;cursor:zoom-in;text-align:center;position:relative;
}
.exam-question-image-trigger img{
  display:block;width:100%;max-height:360px;height:auto;object-fit:contain;background:#fff;aspect-ratio:auto;
}
.exam-question-image-hint{
  display:flex;align-items:center;justify-content:center;gap:8px;padding:8px 12px;
  font-size:.85rem;color:#5a7080;background:rgba(255,255,255,.92);border-top:1px solid #e5edf2;
}
.exam-image-lightbox{
  position:fixed;inset:0;z-index:99999;display:none;align-items:center;justify-content:center;
  padding:24px;background:rgba(8,30,45,.88);
}
.exam-image-lightbox.is-open{display:flex;}
.exam-image-lightbox img{max-width:min(96vw,1200px);max-height:90vh;width:auto;height:auto;object-fit:contain;border-radius:10px;box-shadow:0 18px 48px rgba(0,0,0,.35);}
.exam-image-lightbox-close{
  position:absolute;top:16px;left:16px;width:42px;height:42px;border:0;border-radius:999px;
  background:#fff;color:#083b59;font-size:1.6rem;line-height:1;cursor:pointer;
}
body.exam-image-lightbox-open{overflow:hidden;}
.exam-question-image-thumb{width:72px;height:54px;object-fit:cover;border-radius:8px;border:1px solid #e5edf2;cursor:zoom-in;background:#fff;}
.exam-question-image-upload{margin-top:12px;padding:14px 16px;border:1px dashed #c9d8e2;border-radius:12px;background:#fbfdff;}
.exam-question-image-upload label{display:block;margin-bottom:8px;font-weight:600;color:#083b59;}
.exam-question-image-upload .form-control{max-width:420px;}
.exam-question-image-current{display:flex;flex-wrap:wrap;gap:12px;align-items:flex-start;margin-bottom:12px;}
</style>
<script src="{{ asset('assets/js/exam-question-image.js') }}?v={{ @filemtime(public_path('assets/js/exam-question-image.js')) }}"></script>
@endonce
@endif
