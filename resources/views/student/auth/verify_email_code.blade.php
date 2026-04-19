@extends('layouts.app')

@section('content')
<div class="site-content">
    <div class="container">
        <div style="max-width:520px;margin:60px auto;">
            <div class="card" style="padding:32px 26px;">
                <h2 style="margin-bottom:14px;color:var(--secondary);font-size:1.45rem;text-align:center;">
                    تحقق من البريد الإلكتروني
                </h2>

                <p style="margin:0 0 20px;text-align:center;color:#5d7180;line-height:1.9;">
                    أرسلنا رمز تحقق إلى البريد:
                    <strong style="direction:ltr;display:inline-block;">{{ $maskedEmail }}</strong>
                </p>

                @if ($errors->any())
                    <div style="
                        background:#ffecec;
                        border:1px solid #f5c2c2;
                        color:#b00020;
                        padding:10px 12px;
                        border-radius:8px;
                        margin-bottom:15px;
                        font-size:0.9rem;
                        text-align:right;">
                        <ul style="margin:0;padding-right:18px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div style="
                        background:#edf7ff;
                        border:1px solid #b7d8ef;
                        color:#0d5c86;
                        padding:10px 12px;
                        border-radius:8px;
                        margin-bottom:15px;
                        font-size:0.92rem;
                        text-align:right;">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('student.register.verify.submit') }}">
                    @csrf

                    <div style="margin-bottom:15px;text-align:right;">
                        <label style="display:block;margin-bottom:6px;font-weight:600;">
                            رمز التحقق
                        </label>
                        <input type="text"
                               name="verification_code"
                               value="{{ old('verification_code') }}"
                               inputmode="numeric"
                               autocomplete="one-time-code"
                               maxlength="6"
                               required
                               placeholder="000000"
                               style="width:100%;padding:14px;border-radius:10px;border:1px solid #ccc;direction:ltr;text-align:center;font-size:1.2rem;letter-spacing:8px;">
                    </div>

                    <div style="margin-bottom:18px;padding:14px 16px;border-radius:12px;background:#f7fbfd;border:1px solid rgba(13,92,134,.10);color:#516776;line-height:1.8;font-size:0.94rem;">
                        <div>
                            صلاحية الرمز تنتهي خلال
                            <strong id="expires-countdown" data-expires-at="{{ $expiresAt }}"></strong>
                        </div>
                        <div>
                            المحاولات المتبقية:
                            <strong>{{ $remainingAttempts }}</strong> من أصل <strong>{{ $maxAttempts }}</strong>
                        </div>
                    </div>

                    <button type="submit"
                        style="
                            width:100%;
                            padding:14px;
                            background-color:var(--primary);
                            color:#fff;
                            border:none;
                            border-radius:10px;
                            font-size:1rem;
                            font-weight:600;
                            cursor:pointer;
                            transition:0.3s;
                        ">
                        تأكيد الرمز وإكمال التسجيل
                    </button>
                </form>

                <div style="margin-top:18px;display:flex;gap:10px;flex-wrap:wrap;">
                    <form method="POST" action="{{ route('student.register.resend_code') }}" style="flex:1;min-width:180px;">
                        @csrf
                        <button id="resend-button"
                                type="submit"
                                data-resend-at="{{ $resendAvailableAt }}"
                                style="
                                    width:100%;
                                    padding:12px 14px;
                                    background:#fff7d8;
                                    color:#8a5a00;
                                    border:1px solid rgba(244,194,29,.45);
                                    border-radius:10px;
                                    font-size:0.96rem;
                                    font-weight:600;
                                    cursor:pointer;">
                            إعادة إرسال الرمز
                        </button>
                    </form>

                    <form method="POST" action="{{ route('student.register.cancel') }}" style="flex:1;min-width:180px;">
                        @csrf
                        <button type="submit"
                                style="
                                    width:100%;
                                    padding:12px 14px;
                                    background:#fff;
                                    color:#0d5c86;
                                    border:1px solid rgba(13,92,134,.22);
                                    border-radius:10px;
                                    font-size:0.96rem;
                                    font-weight:600;
                                    cursor:pointer;">
                            تعديل البريد والبيانات
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const expiresNode = document.getElementById('expires-countdown');
        const resendButton = document.getElementById('resend-button');

        if (!expiresNode || !resendButton) {
            return;
        }

        const expiresAt = Number(expiresNode.dataset.expiresAt);
        const resendAt = Number(resendButton.dataset.resendAt);

        const formatSeconds = function (seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return mins + ':' + String(secs).padStart(2, '0');
        };

        const updateCountdowns = function () {
            const now = Math.floor(Date.now() / 1000);
            const expiresIn = Math.max(0, expiresAt - now);
            const resendIn = Math.max(0, resendAt - now);

            expiresNode.textContent = expiresIn > 0 ? formatSeconds(expiresIn) : '00:00';

            if (resendIn > 0) {
                resendButton.disabled = true;
                resendButton.style.opacity = '0.7';
                resendButton.style.cursor = 'not-allowed';
                resendButton.textContent = 'إعادة الإرسال خلال ' + formatSeconds(resendIn);
            } else {
                resendButton.disabled = false;
                resendButton.style.opacity = '1';
                resendButton.style.cursor = 'pointer';
                resendButton.textContent = 'إعادة إرسال الرمز';
            }
        };

        updateCountdowns();
        window.setInterval(updateCountdowns, 1000);
    })();
</script>
@endsection
