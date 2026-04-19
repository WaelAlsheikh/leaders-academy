<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>رمز التحقق لحساب الطالب</title>
</head>
<body style="margin:0;padding:24px;background:#f5f8fb;font-family:Tahoma, Arial, sans-serif;color:#173042;">
    <div style="max-width:620px;margin:0 auto;background:#ffffff;border-radius:20px;overflow:hidden;border:1px solid rgba(13,92,134,.10);box-shadow:0 14px 34px rgba(8,59,89,.08);">
        <div style="padding:28px 28px 18px;background:linear-gradient(135deg,#083b59 0%,#0d5c86 100%);color:#fff;">
            <h1 style="margin:0;font-size:24px;font-weight:700;">تأكيد البريد الإلكتروني</h1>
            <p style="margin:10px 0 0;line-height:1.9;font-size:15px;color:rgba(255,255,255,.85);">
                استخدم الرمز التالي لإكمال إنشاء حساب الطالب في أكاديمية ليدرز.
            </p>
        </div>

        <div style="padding:28px;">
            <div style="margin-bottom:24px;text-align:center;">
                <div style="display:inline-block;padding:16px 28px;border-radius:18px;background:linear-gradient(135deg,#fff2c8 0%,#ffd84d 100%);border:1px solid rgba(244,194,29,.32);font-size:32px;font-weight:800;letter-spacing:10px;color:#083b59;">
                    {{ $code }}
                </div>
            </div>

            <p style="margin:0 0 12px;line-height:1.9;font-size:15px;">
                صلاحية هذا الرمز هي <strong>{{ $expiresInMinutes }} دقائق</strong>.
            </p>
            <p style="margin:0 0 12px;line-height:1.9;font-size:15px;">
                إذا لم تقم بطلب إنشاء الحساب، يمكنك تجاهل هذه الرسالة بأمان.
            </p>
            <p style="margin:18px 0 0;line-height:1.9;font-size:14px;color:#5d7180;">
                تحياتي،<br>
                فريق أكاديمية ليدرز
            </p>
        </div>
    </div>
</body>
</html>
