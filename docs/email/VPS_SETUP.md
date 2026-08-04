# VPS Mail Stack — Leaders Academy

**دليل التفعيل المفصّل بالعربية:** [ACTIVATION_AR.md](./ACTIVATION_AR.md)  
**سكربت تمهيدي (Ubuntu):** [install-mailstack-ubuntu.sh](./install-mailstack-ubuntu.sh)

Target: same VPS as Laravel. Stack: Postfix + Dovecot + Rspamd + Redis + MariaDB + SnappyMail + Certbot + Fail2Ban.

## 1. DNS (at domain registrar)

| Type | Name | Value |
|------|------|-------|
| A | mail | VPS_PUBLIC_IP |
| MX | @ | mail.leaders-academy.net (prio 10) |
| TXT | @ | `v=spf1 mx a:mail.leaders-academy.net ~all` |
| TXT | _dmarc | `v=DMARC1; p=quarantine; rua=mailto:dmarc.system@leaders-academy.net` |
| TXT | mail._domainkey | (from Rspamd/OpenDKIM after install) |

Ask VPS provider for PTR: `VPS_PUBLIC_IP` → `mail.leaders-academy.net`.

## 2. Create Mail DB

```bash
mysql -u root -p < /var/www/leaders/docs/email/mailserver-schema.sql
```

Create DB user with rights only on `mailserver`.

## 3. Laravel `.env`

```
MAIL_MODULE_DOMAIN=leaders-academy.net
MAIL_MODULE_DRIVER=postfix_virtual
MAIL_MODULE_PROVISION_ON_CREATE=true
MAIL_MODULE_WEBMAIL_URL=https://mail.leaders-academy.net

MAILSERVER_DB_HOST=127.0.0.1
MAILSERVER_DB_PORT=3306
MAILSERVER_DB_DATABASE=mailserver
MAILSERVER_DB_USERNAME=mailuser
MAILSERVER_DB_PASSWORD=secret

QUEUE_CONNECTION=database
```

## 4. High-level Postfix/Dovecot

- Virtual maps from MySQL (`virtual_users`, `virtual_aliases`, `virtual_domains`)
- LMTP delivery to Dovecot
- Submission on 587 with TLS
- IMAPS 993
- Rspamd milter
- Maildir under `/var/vmail/%d/%n`

Exact distro packages vary (Debian/Ubuntu recommended). Keep configs under `/etc/postfix`, `/etc/dovecot`.

## 5. TLS

```bash
certbot certonly --nginx -d mail.leaders-academy.net
```

## 6. SnappyMail

Install under `mail.leaders-academy.net` vhost; connect via IMAP to localhost.

## 7. Supervisor queue worker

```ini
[program:leaders-queue]
command=php /var/www/leaders/artisan queue:work --sleep=1 --tries=3
autostart=true
autorestart=true
user=/var/www/leaders
```

## 8. Bootstrap institutional emails

```bash
php artisan migrate
php artisan leaders:generate-emails --sync
# or dry-run first:
php artisan leaders:generate-emails --dry-run
```

## 9. Fail2Ban

Jail SMTP (postfix) and IMAP (dovecot) auth failures.
