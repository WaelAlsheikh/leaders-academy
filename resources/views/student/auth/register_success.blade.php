@extends('layouts.app')

@section('content')
<div class="container" style="max-width:500px;margin:80px auto;">
    <div class="card" style="padding:30px;text-align:center">

        <h2 style="color:green">تم إنشاء الحساب وتأكيد البريد بنجاح ✅</h2>

        <p style="margin-top:20px">
            اسم المستخدم الخاص بك:
        </p>

        <div style="
            background:#f5f5f5;
            padding:12px;
            font-size:1.2rem;
            font-weight:bold;
            border-radius:8px;
        " id="student-username-value">
            {{ $student->username }}
        </div>

        <button
            type="button"
            id="copy-student-username"
            class="btn-primary"
            style="margin-top:12px;display:inline-block;border:none;cursor:pointer;"
            data-username="{{ $student->username }}"
        >
            نسخ اسم المستخدم
        </button>

        <p id="copy-student-username-status" style="margin-top:10px;color:#0d5c86;display:none;">
            تم نسخ اسم المستخدم بنجاح.
        </p>

        <p style="margin-top:15px;color:#666">
            تم توثيق بريدك الإلكتروني. يرجى الاحتفاظ باسم المستخدم وكلمة المرور.
        </p>

        @if(!empty($welcomeEmailSent))
            <p style="margin-top:10px;color:#0d5c86">
                أرسلنا أيضًا رسالة ترحيبية إلى بريدك الإلكتروني تتضمن اسم المستخدم الخاص بك.
            </p>
        @else
            <p style="margin-top:10px;color:#8a6d3b">
                تم إنشاء الحساب بنجاح، لكن تعذر إرسال رسالة الترحيب إلى البريد الإلكتروني حاليًا.
            </p>
        @endif

        <a href="{{ route('student.login') }}" class="btn-primary" style="margin-top:20px;display:inline-block">
            تسجيل الدخول
        </a>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var copyButton = document.getElementById('copy-student-username');
    var statusMessage = document.getElementById('copy-student-username-status');

    if (!copyButton || !statusMessage) {
        return;
    }

    var showStatus = function (message, color) {
        statusMessage.textContent = message;
        statusMessage.style.color = color;
        statusMessage.style.display = 'block';
    };

    var fallbackCopy = function (text) {
        var tempInput = document.createElement('input');
        tempInput.value = text;
        document.body.appendChild(tempInput);
        tempInput.select();
        tempInput.setSelectionRange(0, text.length);

        var copied = false;

        try {
            copied = document.execCommand('copy');
        } catch (error) {
            copied = false;
        }

        document.body.removeChild(tempInput);
        return copied;
    };

    copyButton.addEventListener('click', function () {
        var username = copyButton.getAttribute('data-username') || '';

        if (!username) {
            showStatus('تعذر العثور على اسم المستخدم لنسخه.', '#b94a48');
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(username)
                .then(function () {
                    showStatus('تم نسخ اسم المستخدم بنجاح.', '#0d5c86');
                })
                .catch(function () {
                    if (fallbackCopy(username)) {
                        showStatus('تم نسخ اسم المستخدم بنجاح.', '#0d5c86');
                        return;
                    }

                    showStatus('تعذر نسخ اسم المستخدم تلقائيًا. جرّب النسخ يدويًا.', '#b94a48');
                });

            return;
        }

        if (fallbackCopy(username)) {
            showStatus('تم نسخ اسم المستخدم بنجاح.', '#0d5c86');
            return;
        }

        showStatus('تعذر نسخ اسم المستخدم تلقائيًا. جرّب النسخ يدويًا.', '#b94a48');
    });
});
</script>
@endsection
