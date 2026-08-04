# SnappyMail + future SSO

## Current state
- WebMail URL configured via `MAIL_MODULE_WEBMAIL_URL`
- Laravel exposes:
  - `POST /admin/email/accounts/{account}/webmail-sso` → short-lived token + redirect URL
  - `POST /api/v1/email/webmail/redeem` → redeem token (internal)

## Planned SSO flow
1. Admin/user clicks "Open WebMail" in Leaders Academy.
2. Laravel creates cache token (2 minutes).
3. Browser redirects to SnappyMail with `?sso=TOKEN`.
4. SnappyMail plugin calls Laravel redeem endpoint (server-to-server, IP allowlist).
5. Mail stack authenticates via Dovecot masteruser / proxyauth (no password in Laravel DB).

## Install SnappyMail
Follow `docs/email/VPS_SETUP.md` vhost `mail.leaders-academy.net`.
