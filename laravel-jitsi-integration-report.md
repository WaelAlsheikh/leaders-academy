# تقرير دمج Jitsi — meet.leaders-academy.net

**التاريخ:** 30 مايو 2026  
**المشروع:** `C:\xampp\htdocs\leaders`  
**خادم Jitsi:** `https://meet.leaders-academy.net`  
**الحالة:** مكتمل — Embedded Mode مُفعَّل

---

## 1. ملخص التنفيذ

تم الانتقال من `meet.jit.si` (الافتراضي) إلى **`meet.leaders-academy.net`** عبر متغيرات البيئة والإعدادات المركزية.  
**Embedded Mode** مُفعَّل: Jitsi يُعرض داخل المنصة عبر `JitsiMeetExternalAPI` (iframe) وليس في نافذة منفصلة.

**نسخة احتياطية قبل التعديل:**  
`storage/backups/pre-jitsi-leaders-academy_20260530_123617/`

> **ملاحظة Git:** المستودع موجود لكن `git status` فشل بسبب `dubious ownership` — لم يُنشأ branch. تم الاعتماد على النسخة الاحتياطية أعلاه.

---

## 2. الملفات المعدّلة

| الملف | نوع التعديل |
|-------|-------------|
| `.env` | إضافة متغيرات Jitsi/Meetings |
| `.env.example` | تحديث القيم والتوثيق |
| `config/meetings.php` | domain جديد، `jitsi_embed_enabled`، defaults محدّثة |
| `app/Services/Meetings/JitsiPublicMeetingProvider.php` | domain دائماً من config عبر `jitsiDomain()` |
| `app/Services/Meetings/MeetingStandaloneWindowHelper.php` | منطق embed vs standalone محدّث |
| `app/Services/Meetings/JitsiJwtTokenBuilder.php` | تعليق توضيحي (الحماية من meet.jit.si بقيت) |
| `public/assets/js/live-session.js` | رسائل ديناميكية حسب domain |
| `resources/views/doctor/live_sessions/show.blade.php` | نصوص operator محدّثة |

**لم يُعدَّل:** قاعدة البيانات، migrations، routes، controllers (عدا ما يقرأ من config تلقائياً).

---

## 3. التعديلات المنفذة بالتفصيل

### 3.1 متغيرات البيئة (`.env`)

```env
MEETING_PROVIDER_DEFAULT=jitsi_public
JITSI_PUBLIC_DOMAIN=meet.leaders-academy.net
JITSI_STANDALONE_WINDOW_DOMAINS=
JITSI_EMBED_ENABLED=true
JITSI_PREFER_STANDALONE_WINDOW=false
LIVE_SESSION_COMMENT_POLL_SECONDS=5
LIVE_SESSION_HEARTBEAT_INTERVAL_SECONDS=12
LIVE_SESSION_HEARTBEAT_TIMEOUT_SECONDS=45
MEETING_SHOW_OPERATOR_TIPS=false
MEET_LAUNCH_URL_TTL_MINUTES=45
```

### 3.2 `config/meetings.php`

- **`jitsi_public_domain`:** `env('JITSI_PUBLIC_DOMAIN', env('JITSI_DOMAIN', 'meet.leaders-academy.net'))`
- **`jitsi_embed_enabled`:** جديد — `JITSI_EMBED_ENABLED` (افتراضي `true`)
- **`jitsi_standalone_window_domains`:** افتراضي فارغ `''`
- **`jitsi_prefer_standalone_window`:** افتراضي `false`

### 3.3 Embedded Mode

`MeetingStandaloneWindowHelper::shouldUse()`:

| الشرط | النتيجة |
|-------|---------|
| النطاق **ليس** في `JITSI_STANDALONE_WINDOW_DOMAINS` | **iframe embed** ✅ |
| `JITSI_EMBED_ENABLED=true` + قائمة standalone فارغة | **iframe embed** ✅ |
| نطاق في القائمة + embed enabled | standalone (fallback) |

**التحقق الآلي:**
```
domain=meet.leaders-academy.net
embed=true
standalone_domains=[]
shouldUseStandalone=false
```

### 3.4 `JitsiPublicMeetingProvider.php`

- أُضيفت `jitsiDomain()` — تقرأ دائماً من `config('meetings.jitsi_public_domain')`
- **`provisionSession()`** و **`buildEmbedPayload()`** يستخدمانها
- الجلسات القديمة التي خزّنت `domain: meet.jit.si` في `provider_payload` **تُتجاهَل** — يُستخدم النطاق من config

**مثال URL ناتج:**
```
https://meet.leaders-academy.net/leaders-test-room#config.prejoinConfig.enabled=false&userInfo.displayName=...
```

### 3.5 `live-session.js`

- تحميل API: `https://${domain}/external_api.js` (domain من `embedPayload`)
- `JitsiMeetExternalAPI(config.embedPayload.domain, options)`
- رسالة أذونات الكاميرا/الميكروفون تستخدم `config.embedPayload.domain` ديناميكياً

### 3.6 أوامر Laravel

```bash
php artisan config:clear   ✅
php artisan cache:clear    ✅
php artisan view:clear     ✅
```

---

## 4. نتائج الاختبارات

### 4.1 اختبارات آلية (منفّذة)

| الاختبار | النتيجة | التفاصيل |
|----------|---------|----------|
| قراءة config | ✅ ناجح | `domain=meet.leaders-academy.net`, `embed=true` |
| Embedded vs Standalone | ✅ ناجح | `shouldUseStandalone=false` |
| توليد meeting URL | ✅ ناجح | يستخدم `https://meet.leaders-academy.net/...` |
| تجاهل domain قديم في DB payload | ✅ ناجح | حتى مع `meet.jit.si` مخزّن → URL على الخادم الجديد |
| `external_api.js` | ✅ HTTP 200 | `https://meet.leaders-academy.net/external_api.js` |
| Jitsi root | ✅ HTTP 200 | الخادم يستجيب |
| X-Frame-Options | ✅ مناسب للتضمين | غير مُعرَّف — iframe embedding مسموح |
| Mixed Content | ⚠️ ملاحظة | LMS على `http://localhost` وJitsi على `https` — iframe HTTPS داخل HTTP مسموح عادةً؛ getUserMedia يعمل داخل iframe الآمن |
| CORS (script load) | ✅ متوقع OK | `<script src="https://meet.../external_api.js">` — cross-origin script عادي |

### 4.2 اختبارات يدوية (WebRTC — تتطلب متصفح)

| الاختبار | الحالة | ملاحظات |
|----------|--------|---------|
| إنشاء جلسة جديدة | ⏳ يدوي | Doctor → بدء محاضرة من لوحة التحكم |
| دخول المدرس | ⏳ يدوي | iframe داخل `/doctor/live-sessions/{id}` |
| دخول الطالب | ⏳ يدوي | iframe بعد `doctorReady` + `canEmbed` |
| الصوت | ⏳ يدوي | اسمح بأذونات الميكروفون للنطاق |
| الفيديو | ⏳ يدوي | اسمح بأذونات الكاميرا |
| مشاركة الشاشة | ⏳ يدوي | زر desktop في toolbar (doctor) |
| مغادرة الجلسة | ⏳ يدوي | hangup → placeholder Leaders |
| إعادة الدخول | ⏳ يدوي | refresh + re-join |
| أخطاء JavaScript | ⏳ يدوي | DevTools → Console |
| أخطاء CORS في runtime | ⏳ يدوي | DevTools → Network |

**سبب:** اختبارات WebRTC (صوت/فيديو/شاشة) لا يمكن أتمتتها بشكل موثوق من CLI — تحتاج جلسة حية ومتصفح.

### 4.3 سيناريو الاختبار اليدوي المُوصى به

1. سجّل دخول كـ **Doctor** → ابدأ محاضرة اليوم
2. افتح `/doctor/live-sessions/{id}` — تحقق أن iframe يحمّل `meet.leaders-academy.net`
3. فعّل «أنا المضيف» / انضم بالفيديو
4. «السماح للطلاب بالدخول»
5. سجّل دخول **Student** → `/student/live-sessions/{id}`
6. تحقق: صوت، فيديو، مشاركة شاشة، مغادرة، إعادة دخول
7. DevTools: لا أخطاء حمراء في Console/Network

---

## 5. الأسطر المهمة التي تغيّرت

```php
// config/meetings.php
'jitsi_public_domain' => env('JITSI_PUBLIC_DOMAIN', env('JITSI_DOMAIN', 'meet.leaders-academy.net')),
'jitsi_embed_enabled' => filter_var(env('JITSI_EMBED_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
```

```php
// JitsiPublicMeetingProvider.php
private function jitsiDomain(): string
{
    return strtolower(trim((string) config('meetings.jitsi_public_domain', 'meet.leaders-academy.net')));
}
```

```javascript
// live-session.js
script.src = `https://${domain}/external_api.js`;
state.api = new window.JitsiMeetExternalAPI(config.embedPayload.domain, options);
```

---

## 6. مشاكل مكتشفة

| # | المشكلة | الخطورة | الحل |
|---|---------|---------|------|
| 1 | Git branch لم يُنشأ (ownership) | منخفضة | نسخة احتياطية في `storage/backups/` |
| 2 | JWT غير مُفعَّل | متوسطة | أي شخص يعرف رابط الغرفة قد يدخل — راجع §7 |
| 3 | LMS على HTTP محلي | منخفضة | للإنتاج استخدم HTTPS على `APP_URL` |

**لم تُكتشف** أخطاء CORS أو Mixed Content blocking في الاختبارات الآلية.

---

## 7. توصيات مستقبلية

### 7.1 تفعيل JWT (موصى به بشدة)

```env
JITSI_JWT_APP_ID=your_app_id
JITSI_JWT_APP_SECRET=your_secret
JITSI_JWT_SUB=meet.leaders-academy.net
JITSI_JWT_AUDIENCE=jitsi
```

يتطلب إعداد token authentication على Prosody في docker-jitsi-meet.

### 7.2 HTTPS للمنصة

`APP_URL=https://your-domain.com` — يحسّن الأمان ويتجنب تحذيرات Mixed Content.

### 7.3 TURN/STUN

للشبكات المقيدة — تأكد من إعداد `coturn` على خادم Jitsi.

### 7.4 مراقبة

راقب `storage/logs/laravel.log` عند أول محاضرة حقيقية.

### 7.5 إذا احتجت standalone مؤقتاً

```env
JITSI_EMBED_ENABLED=false
JITSI_PREFER_STANDALONE_WINDOW=true
JITSI_STANDALONE_WINDOW_DOMAINS=meet.leaders-academy.net
```

---

## 8. قرارات معمارية

**لم يُطلب توقف** — الخيار المعتمد:

| الخيار | القرار |
|--------|--------|
| A: JitsiMeetExternalAPI (embed) | ✅ **مُطبَّق** |
| B: Redirect فقط | ❌ مرفوض — يفقد تكامل LMS |
| C: iframe src مباشر | ❌ مرفوض — لا events |

---

## 9. التحقق السريع بعد النشر

```bash
php artisan config:clear
php storage/backups/jitsi-config-test.php
```

المخرجات المتوقعة:
- `domain=meet.leaders-academy.net`
- `shouldUseStandalone=false`
- `uses_leaders_domain=true`

---

*تم إعداد هذا التقرير بعد تنفيذ التعديلات والاختبارات الآلية.*
