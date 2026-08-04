@php
    $status = $account?->status ?? 'missing';
    $statusLabel = match ($status) {
        'active' => 'نشط',
        'pending' => 'قيد التفعيل',
        'disabled' => 'معطّل',
        'deleted' => 'محذوف',
        default => 'غير مُنشأ بعد',
    };
    $statusClass = match ($status) {
        'active' => 'success',
        'pending' => 'info',
        'disabled', 'deleted' => 'warning',
        default => 'muted',
    };
    $institutional = $account?->institutional_email ?: ($identity->institutional_email ?? null);
    $personal = $identity->email ?? null;
    $quota = $account?->quota_mb;
    $usedMb = $account ? round(($account->used_bytes ?? 0) / 1048576, 1) : null;
    $openRoute = $portal.'.my_email.open';
@endphp

<section class="exam-portal-page my-email-page">
    <div class="exam-portal-header">
        <div>
            <h3>الإيميل المؤسسي</h3>
            <p class="exam-portal-subtitle">عنوان بريدك الرسمي في Leaders Academy وبيانات الدخول إلى WebMail.</p>
        </div>
        @if($account && $account->isActive())
            <div class="exam-portal-actions">
                <a href="{{ route($openRoute) }}" class="btn btn-primary" target="_blank" rel="noopener">
                    فتح صندوق الوارد
                </a>
                <a href="{{ $webmailUrl }}" class="btn btn-secondary" target="_blank" rel="noopener">
                    WebMail مباشرة
                </a>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="exam-portal-panel my-email-hero">
        <div class="my-email-address-block">
            <span class="my-email-label">عنوانك المؤسسي</span>
            @if($institutional)
                <div class="my-email-address" id="institutional-email">{{ $institutional }}</div>
                <button type="button" class="btn btn-secondary btn-sm my-email-copy" data-copy="{{ $institutional }}">نسخ العنوان</button>
            @else
                <div class="my-email-address my-email-address--empty">لم يُنشأ عنوان بعد</div>
            @endif
        </div>
        <div class="my-email-meta">
            <div class="my-email-meta-item">
                <span class="my-email-label">الحالة</span>
                <span class="exam-badge exam-badge--{{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
            @if($quota)
            <div class="my-email-meta-item">
                <span class="my-email-label">الحصة</span>
                <strong>{{ $usedMb ?? 0 }} / {{ $quota }} MB</strong>
            </div>
            @endif
            @if($account?->provisioning_status)
            <div class="my-email-meta-item">
                <span class="my-email-label">المزامنة</span>
                <strong>{{ $account->provisioning_status }}</strong>
            </div>
            @endif
        </div>
    </div>

    @if(! $account)
        <div class="exam-portal-panel">
            <div class="exam-portal-empty">
                لم يُفعَّل صندوق بريد مؤسسي لحسابك بعد. عند اكتمال التفعيل سيظهر العنوان هنا، وستصلك بيانات الدخول على بريدك الشخصي إن وُجد.
            </div>
        </div>
    @elseif($account->status === 'pending' || $account->provisioning_status === 'pending')
        <div class="exam-portal-panel">
            <div class="exam-portal-empty">
                جارٍ تجهيز صندوقك… قد يستغرق ذلك دقائق بعد إنشاء الحساب. حدّث الصفحة لاحقاً.
            </div>
        </div>
    @elseif($account->status === 'disabled')
        <div class="exam-portal-panel">
            <div class="exam-portal-empty">
                صندوق البريد معطّل حالياً. تواصل مع الإدارة إن كنت بحاجة لإعادة التفعيل.
            </div>
        </div>
    @endif

    @if($account?->last_error)
        <div class="alert alert-danger">تعذّرت المزامنة: {{ $account->last_error }}</div>
    @endif

    <div class="my-email-grid">
        <div class="exam-portal-panel">
            <h4 class="exam-portal-section-title">بيانات الاتصال</h4>
            <dl class="my-email-dl">
                <div>
                    <dt>البريد الشخصي (احتياطي)</dt>
                    <dd>{{ $personal ?: '—' }}</dd>
                </div>
                <div>
                    <dt>خادم WebMail</dt>
                    <dd><a href="{{ $webmailUrl }}" target="_blank" rel="noopener">{{ $webmailUrl }}</a></dd>
                </div>
                <div>
                    <dt>IMAP</dt>
                    <dd>mail.leaders-academy.net — المنفذ 993 (SSL)</dd>
                </div>
                <div>
                    <dt>SMTP</dt>
                    <dd>mail.leaders-academy.net — المنفذ 587 (STARTTLS)</dd>
                </div>
            </dl>
            <p class="exam-portal-subtitle" style="margin-top:12px;">
                كلمة مرور الصندوق لا تُعرض هنا لأسباب أمنية. إن نسيتها، اطلب إعادة التعيين من الإدارة.
            </p>
        </div>

        <div class="exam-portal-panel">
            <h4 class="exam-portal-section-title">الأسماء المستعارة (Aliases)</h4>
            @if($account && $account->aliases->isNotEmpty())
                <ul class="my-email-alias-list">
                    @foreach($account->aliases as $alias)
                        <li>
                            <strong>{{ $alias->source_email }}</strong>
                            <span>→ {{ $alias->destination_email }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="exam-portal-empty">لا توجد aliases مرتبطة بصندوقك.</div>
            @endif
        </div>
    </div>
</section>

<style>
.my-email-page .my-email-hero {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    align-items: flex-start;
    justify-content: space-between;
}
.my-email-label {
    display: block;
    font-size: 0.85rem;
    opacity: 0.75;
    margin-bottom: 6px;
}
.my-email-address {
    font-size: 1.35rem;
    font-weight: 700;
    word-break: break-all;
    letter-spacing: 0.01em;
    margin-bottom: 10px;
}
.my-email-address--empty { opacity: 0.55; font-weight: 600; }
.my-email-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 18px 28px;
}
.my-email-meta-item strong { display: block; margin-top: 4px; }
.my-email-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
    margin-top: 16px;
}
.my-email-dl { margin: 0; }
.my-email-dl > div { margin-bottom: 14px; }
.my-email-dl dt { font-size: 0.85rem; opacity: 0.75; margin-bottom: 2px; }
.my-email-dl dd { margin: 0; font-weight: 600; word-break: break-word; }
.my-email-alias-list { list-style: none; padding: 0; margin: 0; }
.my-email-alias-list li {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(0,0,0,.06);
}
.exam-portal-actions { display: flex; flex-wrap: wrap; gap: 8px; }
@media (max-width: 640px) {
    .my-email-address { font-size: 1.1rem; }
}
</style>
<script>
document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var text = btn.getAttribute('data-copy') || '';
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                btn.textContent = 'تم النسخ';
                setTimeout(function () { btn.textContent = 'نسخ العنوان'; }, 1500);
            });
        }
    });
});
</script>
