# دليل تفعيل البريد المؤسسي على السيرفر — Leaders Academy

هذا الدليل يشرح **خطوة بخطوة** كيف تبني نظام البريد (Postfix + Dovecot + MariaDB + Rspamd + SnappyMail) على **نفس الـ VPS** الذي يعمل عليه Laravel، ثم تربطه بوحدة البريد في المشروع.

> **مهم:** من جهاز التطوير (XAMPP) لا يمكن تشغيل خادم بريد حقيقي يستقبل/يرسل من الإنترنت. التفعيل الفعلي يتم على السيرفر بعد الرفع. محلياً يبقى `MAIL_MODULE_DRIVER=log`.

---

## نظرة عامة على المعمارية

| طبقة | الدور |
|------|--------|
| **Laravel** | يولّد العناوين، يخزّن `mail_accounts`، يدفع Jobs للـ provisioning |
| **قاعدة `mailserver`** | جداول Postfix/Dovecot الفعلية (`virtual_users`…) — يكتب إليها `PostfixVirtualDriver` |
| **Postfix** | استقبال/إرسال SMTP |
| **Dovecot** | IMAP + تسليم LMTP + مصادقة |
| **Rspamd** | مكافحة سبام + DKIM |
| **SnappyMail** | واجهة WebMail على `mail.leaders-academy.net` |
| **Supervisor** | تشغيل `queue:work` لإنشاء الصناديق تلقائياً |

تدفق إنشاء صندوق عند تسجيل طالب/دكتور/…:

1. يُنشأ السجل في Laravel  
2. يُطلق `ProvisionMailboxJob`  
3. الـ Driver يكتب في `mailserver.virtual_users`  
4. يصل الإيميل إلى Postfix/Dovecot  
5. المستخدم يفتح صفحته **الإيميل** في البوابة ثم **فتح صندوق الوارد**

---

## المرحلة 0 — متطلبات قبل البدء

1. VPS بنظام **Ubuntu 22.04/24.04** (مُفضّل) مع صلاحية `root` أو `sudo`.
2. نطاق `leaders-academy.net` يمكنك إدارة DNS له.
3. Laravel مرفوع ويعمل (مثلاً تحت `/var/www/leaders`).
4. منافذ مفتوحة في جدار النار:
   - `25` (استقبال SMTP — بعض المزودين يحجبونه؛ تحقق)
   - `587` (إرسال موثّق)
   - `465` (اختياري)
   - `993` (IMAPS)
   - `80/443` (ويب + شهادات)
5. عنوان IP عام ثابت للـ VPS.

---

## المرحلة 1 — سجلات DNS (حاسمة للتسليم)

من لوحة النطاق (Hostinger / Cloudflare / …) أضف:

| النوع | الاسم | القيمة | ملاحظات |
|------|--------|--------|---------|
| **A** | `mail` | `IP_الـ_VPS` | → `mail.leaders-academy.net` |
| **MX** | `@` | `mail.leaders-academy.net` أولوية **10** | استقبال البريد للنطاق |
| **TXT (SPF)** | `@` | `v=spf1 mx a:mail.leaders-academy.net ~all` | ابدأ بـ `~all` ثم غيّر لـ `-all` بعد الاستقرار |
| **TXT (DMARC)** | `_dmarc` | `v=DMARC1; p=quarantine; rua=mailto:dmarc.system@leaders-academy.net` | |
| **TXT (DKIM)** | حسب المفتاح | من Rspamd بعد التثبيت | لاحقاً في المرحلة 5 |

**PTR (عكس DNS):** من مزوّد الـ VPS اطلب:

`IP_الـ_VPS` → `mail.leaders-academy.net`

بدون PTR كثير من خوادم Gmail/Outlook ترفض أو تضع الرسائل في السبام.

تحقق بعد الانتشار (قد يستغرق دقائق إلى ساعات):

```bash
dig +short mail.leaders-academy.net A
dig +short leaders-academy.net MX
dig +short -x IP_الـ_VPS
```

---

## المرحلة 2 — رفع المشروع وتهيئة Laravel

```bash
cd /var/www/leaders
git pull   # أو ارفع الملفات
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
```

عدّل `.env` على السيرفر:

```env
# وحدة البريد المؤسسي
MAIL_MODULE_DOMAIN=leaders-academy.net
MAIL_MODULE_DRIVER=postfix_virtual
MAIL_MODULE_PROVISION_ON_CREATE=true
MAIL_MODULE_WEBMAIL_URL=https://mail.leaders-academy.net

MAILSERVER_DB_HOST=127.0.0.1
MAILSERVER_DB_PORT=3306
MAILSERVER_DB_DATABASE=mailserver
MAILSERVER_DB_USERNAME=mailuser
MAILSERVER_DB_PASSWORD=ضع_كلمة_مرور_قوية

# طابور حقيقي (إلزامي للـ provisioning في الخلفية)
QUEUE_CONNECTION=database
```

> أثناء الهجرة من Hostinger SMTP: أبقِ `MAIL_*` الحالي لإرسال OTP/الإشعارات حتى يستقر Postfix، ثم انقل الإرسال الرسمي تدريجياً.

---

## المرحلة 3 — قاعدة بيانات Mailserver

```bash
sudo mysql -u root -p < /var/www/leaders/docs/email/mailserver-schema.sql

sudo mysql -u root -p <<'SQL'
CREATE USER IF NOT EXISTS 'mailuser'@'localhost' IDENTIFIED BY 'ضع_كلمة_مرور_قوية';
GRANT SELECT, INSERT, UPDATE, DELETE ON mailserver.* TO 'mailuser'@'localhost';
FLUSH PRIVILEGES;
SQL
```

اختبار الاتصال من Laravel:

```bash
cd /var/www/leaders
php artisan tinker --execute="dump(DB::connection('mailserver')->select('select 1 as ok'));"
```

يجب أن ترى `ok = 1`. إن فشل: راجع كلمة المرور و`config/database.php` اتصال `mailserver`.

---

## المرحلة 4 — تثبيت حزم البريد (Ubuntu)

```bash
sudo apt update
sudo apt install -y postfix postfix-mysql dovecot-core dovecot-imapd dovecot-lmtpd dovecot-mysql \
  redis-server rspamd certbot python3-certbot-nginx fail2ban
```

أثناء تثبيت Postfix اختر **Internet Site** واسم النظام: `mail.leaders-academy.net`.

أنشئ مستخدم التخزين:

```bash
sudo groupadd -g 5000 vmail
sudo useradd -g vmail -u 5000 vmail -d /var/vmail -m
sudo mkdir -p /var/vmail
sudo chown -R vmail:vmail /var/vmail
```

### 4.1 ملف اتصال MySQL مشترك

```bash
sudo tee /etc/postfix/mysql-virtual-mailbox-domains.cf >/dev/null <<'EOF'
user = localhost
user = mailserver
user = mailuser
password = ضع_كلمة_مرور_قوية
query = SELECT 1 FROM virtual_domains WHERE name='%s'
EOF

sudo tee /etc/postfix/mysql-virtual-mailbox-maps.cf >/dev/null <<'EOF'
user = localhost
user = mailserver
user = mailuser
password = ضع_كلمة_مرور_قوية
query = SELECT 1 FROM virtual_users WHERE email='%s' AND active=1
EOF

sudo tee /etc/postfix/mysql-virtual-alias-maps.cf >/dev/null <<'EOF'
user = localhost
user = mailserver
user = mailuser
password = ضع_كلمة_مرور_قوية
query = SELECT destination FROM virtual_aliases WHERE source='%s' AND active=1
EOF

sudo chmod 640 /etc/postfix/mysql-virtual-*.cf
sudo chown root:postfix /etc/postfix/mysql-virtual-*.cf
```

### 4.2 إعدادات Postfix الأساسية

أضف/عدّل في `/etc/postfix/main.cf` (بعد النسخ الاحتياطي):

```bash
sudo cp /etc/postfix/main.cf /etc/postfix/main.cf.bak
```

مفاتيح مهمة (دمجها مع الموجود):

```
myhostname = mail.leaders-academy.net
mydomain = leaders-academy.net
myorigin = $mydomain
inet_interfaces = all
mydestination = localhost
virtual_mailbox_domains = mysql:/etc/postfix/mysql-virtual-mailbox-domains.cf
virtual_mailbox_maps = mysql:/etc/postfix/mysql-virtual-mailbox-maps.cf
virtual_alias_maps = mysql:/etc/postfix/mysql-virtual-alias-maps.cf
virtual_transport = lmtp:unix:private/dovecot-lmtp
smtpd_recipient_restrictions = permit_mynetworks, permit_sasl_authenticated, reject_unauth_destination
smtpd_sasl_type = dovecot
smtpd_sasl_path = private/auth
smtpd_sasl_auth_enable = yes
smtpd_tls_cert_file=/etc/letsencrypt/live/mail.leaders-academy.net/fullchain.pem
smtpd_tls_key_file=/etc/letsencrypt/live/mail.leaders-academy.net/privkey.pem
smtpd_tls_security_level = may
smtp_tls_security_level = may
```

فعّل submission في `/etc/postfix/master.cf` (إلغاء التعليق عن قسم `submission` وربطه بـ SASL).

```bash
sudo systemctl restart postfix
```

### 4.3 Dovecot (IMAP + Auth + LMTP)

في `/etc/dovecot/conf.d/10-mail.conf`:

```
mail_location = maildir:/var/vmail/%d/%n
mail_uid = vmail
mail_gid = vmail
first_valid_uid = 5000
```

في `/etc/dovecot/conf.d/10-auth.conf`:

```
disable_plaintext_auth = yes
auth_mechanisms = plain login
!include auth-sql.conf.ext
```

أنشئ `/etc/dovecot/dovecot-sql.conf.ext`:

```
driver = mysql
connect = host=127.0.0.1 dbname=mailserver user=mailuser password=ضع_كلمة_مرور_قوية
default_pass_scheme = BLF-CRYPT
password_query = SELECT email as user, password FROM virtual_users WHERE email='%u' AND active=1
user_query = SELECT email as user, 'vmail' as uid, 'vmail' as gid, CONCAT('/var/vmail/', SUBSTRING_INDEX(email,'@',-1), '/', SUBSTRING_INDEX(email,'@',1)) as home FROM virtual_users WHERE email='%u' AND active=1
```

فعّل LMTP وsocket المصادقة لـ Postfix في `10-master.conf` (socket تحت `/var/spool/postfix/private/`).

```bash
sudo systemctl restart dovecot
```

---

## المرحلة 5 — شهادة TLS + Rspamd + Fail2Ban

```bash
# تأكد أن A record لـ mail يشير للسيرفر وأن nginx/apache يخدم mail.*
sudo certbot certonly --nginx -d mail.leaders-academy.net
# أو: certbot certonly --apache -d mail.leaders-academy.net

sudo systemctl enable --now redis-server rspamd
sudo systemctl restart postfix dovecot
```

أعد تشغيل Postfix بعد وجود ملفات الشهادة.

**DKIM:** من إعدادات Rspamd ولّد مفتاحاً للنطاق، ثم انشر سجل TXT عند المسجّل. أدوات فحص مفيدة لاحقاً: [mail-tester.com](https://www.mail-tester.com).

**Fail2Ban:** فعّل سجوناً لـ `postfix` و`dovecot` ضد محاولات الدخول الفاشلة.

جدار النار:

```bash
sudo ufw allow 25,80,443,465,587,993/tcp
sudo ufw reload
```

---

## المرحلة 6 — SnappyMail (WebMail)

1. نزّل SnappyMail من الموقع الرسمي إلى مجلد مثل `/var/www/snappymail`.
2. أنشئ Virtual Host لـ `mail.leaders-academy.net` يشير لهذا المجلد (منفصل عن DocumentRoot الخاص بـ Laravel).
3. في إعدادات SnappyMail:
   - IMAP: `127.0.0.1` أو `localhost` منفذ `993` / أو `143` داخلياً مع TLS حسب إعدادك
   - SMTP: `127.0.0.1` منفذ `587`
4. اختبر تسجيل الدخول يدوياً بحساب صندوق بعد إنشائه من Laravel.

تفاصيل SSO لاحقاً: `docs/email/WEBMAIL_SSO.md`.

---

## المرحلة 7 — Supervisor لطابور Laravel

انسخ النموذج:

```bash
sudo cp /var/www/leaders/docs/email/supervisor-leaders-queue.conf /etc/supervisor/conf.d/leaders-queue.conf
# عدّل المسار والمستخدم داخل الملف إن لزم
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start leaders-queue:*
```

تحقق:

```bash
sudo supervisorctl status
php artisan queue:failed
```

---

## المرحلة 8 — إنشاء عناوين الموجودين واختبار التدفق

```bash
cd /var/www/leaders
php artisan config:cache

# معاينة أولاً
php artisan leaders:generate-emails --dry-run

# إنشاء فعلي (مزامنة فورية للاختبار)
php artisan leaders:generate-emails --sync --limit=5

# أو دفع للطابور (يفضّل بعد Supervisor)
php artisan leaders:generate-emails
```

اختبارات سريعة:

1. من لوحة الأدمن: **إدارة البريد** → تأكد أن الحساب `active` / `synced`.
2. من بوابة الطالب: زر **الإيميل** → يظهر العنوان `*.student@leaders-academy.net`.
3. سجّل دخولاً في SnappyMail بنفس العنوان وكلمة المرور (التي وصلت للإيميل الشخصي عند الـ provision، أو التي أعاد الأدمن تعيينها).
4. أرسل رسالة اختبار من Gmail إلى العنوان المؤسسي، وتأكد من وصولها في IMAP.
5. أرسل من الصندوق إلى بريد خارجي وتحقق من عدم الرفض (SPF/DKIM/PTR).

قوائم التوزيع:

```bash
php artisan leaders:sync-distribution-lists --sync --all
```

(مجدولة تلقائياً كل ساعة عبر `app/Console/Kernel.php` — تأكد أن `cron` يشغّل `schedule:run`.)

```cron
* * * * * cd /var/www/leaders && php artisan schedule:run >> /dev/null 2>&1
```

---

## المرحلة 9 — ماذا يفعل كل دور داخل المنصة؟

| الدور | زر القائمة | الصفحة | ملاحظات |
|-------|------------|--------|---------|
| طالب | الإيميل | `/student/my-email` | عرض عنوانه + فتح WebMail |
| دكتور | الإيميل | `/doctor/my-email` | نفس الشيء |
| موظف | الإيميل | `/employee/my-email` | بريده الشخصي |
| موظف | إدارة البريد | `/employee/email/accounts` | إدارة كل الصناديق |
| أدمن | الإيميل | `/admin/my-email` | بريده الشخصي |
| أدمن | البريد المؤسسي | `/admin/email/accounts` | إدارة كاملة |

---

## المرحلة 10 — الهجرة من Hostinger SMTP

1. أبقِ Hostinger لإرسال OTP/الترحيب حتى يعمل Postfix بثبات أسبوعاً.
2. راقب السمعة عبر mail-tester ورفضات السجلات `/var/log/mail.log`.
3. غيّر SPF من `~all` إلى `-all`.
4. انقل `MAIL_HOST` تدريجياً إلى `mail.leaders-academy.net` أو أبقِ Hostinger للقنوات التسويقية وPostfix للإيميل المؤسسي.

---

## استكشاف الأخطاء الشائعة

| العرض | السبب المحتمل | الحل |
|-------|----------------|------|
| `health` فاشل في لوحة البريد | اتصال `mailserver` | راجع `.env` وصلاحيات MySQL |
| الحساب `provisioning_status=failed` | خطأ Driver/SQL | انظر `last_error` في الحساب و`storage/logs/laravel.log` |
| البريد لا يصل من الخارج | MX/DNS/منفذ 25 | `dig MX` + فتح المنفذ عند المزود |
| يصل للسبام | PTR/SPF/DKIM | أكمل السجلات + warm-up |
| لا يفتح WebMail | snappymail/vhost | تحقق من شهادة TLS وإعداد IMAP |
| Jobs لا تُنفَّذ | Queue sync أو Supervisor متوقف | `QUEUE_CONNECTION=database` + supervisor |

سجلات مفيدة:

```bash
sudo tail -f /var/log/mail.log
sudo journalctl -u dovecot -f
sudo journalctl -u postfix -f
tail -f /var/www/leaders/storage/logs/laravel.log
```

---

## ملخص ترتيب التنفيذ (Checklist)

1. [ ] DNS: A + MX + SPF + DMARC (+ PTR من المزوّد)
2. [ ] رفع Laravel + `migrate` + `.env` بـ `postfix_virtual`
3. [ ] إنشاء DB `mailserver` + مستخدم
4. [ ] تثبيت Postfix/Dovecot/Rspamd/Redis/Fail2Ban
5. [ ] Certbot لـ `mail.`
6. [ ] SnappyMail على subdomain منفصل
7. [ ] Supervisor للـ queue
8. [ ] `leaders:generate-emails`
9. [ ] اختبار استقبال/إرسال + صفحة **الإيميل** لكل بوابة
10. [ ] تشديد SPF وDKIM بعد الاستقرار

للسكربت المساعد (اختياري): `docs/email/install-mailstack-ubuntu.sh` — يثبّت الحزم ويجهّز ملفات MySQL maps كنقطة بداية؛ راجع الإعدادات يدوياً قبل الإنتاج.
