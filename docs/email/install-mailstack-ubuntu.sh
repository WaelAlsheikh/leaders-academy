#!/usr/bin/env bash
# Bootstrap mail packages + MySQL map stubs for Leaders Academy.
# Review and set MAIL_DB_PASSWORD before running on production.
# Usage: sudo bash docs/email/install-mailstack-ubuntu.sh

set -euo pipefail

MAIL_DB_NAME="${MAIL_DB_NAME:-mailserver}"
MAIL_DB_USER="${MAIL_DB_USER:-mailuser}"
MAIL_DB_PASSWORD="${MAIL_DB_PASSWORD:-CHANGE_ME}"
MAIL_HOSTNAME="${MAIL_HOSTNAME:-mail.leaders-academy.net}"
APP_ROOT="${APP_ROOT:-/var/www/leaders}"

if [[ "$(id -u)" -ne 0 ]]; then
  echo "Run as root (sudo)."
  exit 1
fi

if [[ "$MAIL_DB_PASSWORD" == "CHANGE_ME" ]]; then
  echo "Set MAIL_DB_PASSWORD env var to a strong password first."
  exit 1
fi

export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y postfix postfix-mysql dovecot-core dovecot-imapd dovecot-lmtpd dovecot-mysql \
  redis-server rspamd fail2ban

groupadd -g 5000 vmail 2>/dev/null || true
useradd -g vmail -u 5000 vmail -d /var/vmail -m 2>/dev/null || true
mkdir -p /var/vmail
chown -R vmail:vmail /var/vmail

if [[ -f "$APP_ROOT/docs/email/mailserver-schema.sql" ]]; then
  mysql < "$APP_ROOT/docs/email/mailserver-schema.sql" || true
fi

mysql -e "CREATE USER IF NOT EXISTS '${MAIL_DB_USER}'@'localhost' IDENTIFIED BY '${MAIL_DB_PASSWORD}';"
mysql -e "GRANT SELECT, INSERT, UPDATE, DELETE ON ${MAIL_DB_NAME}.* TO '${MAIL_DB_USER}'@'localhost'; FLUSH PRIVILEGES;"

write_cf() {
  local file="$1"
  local query="$2"
  cat >"$file" <<EOF
user = localhost
user = ${MAIL_DB_NAME}
user = ${MAIL_DB_USER}
password = ${MAIL_DB_PASSWORD}
query = ${query}
EOF
  chmod 640 "$file"
  chown root:postfix "$file" 2>/dev/null || chown root:root "$file"
}

write_cf /etc/postfix/mysql-virtual-mailbox-domains.cf \
  "SELECT 1 FROM virtual_domains WHERE name='%s'"
write_cf /etc/postfix/mysql-virtual-mailbox-maps.cf \
  "SELECT 1 FROM virtual_users WHERE email='%s' AND active=1"
write_cf /etc/postfix/mysql-virtual-alias-maps.cf \
  "SELECT destination FROM virtual_aliases WHERE source='%s' AND active=1"

cat >/etc/dovecot/dovecot-sql.conf.ext <<EOF
driver = mysql
connect = host=127.0.0.1 dbname=${MAIL_DB_NAME} user=${MAIL_DB_USER} password=${MAIL_DB_PASSWORD}
default_pass_scheme = BLF-CRYPT
password_query = SELECT email as user, password FROM virtual_users WHERE email='%u' AND active=1
user_query = SELECT email as user, 'vmail' as uid, 'vmail' as gid, CONCAT('/var/vmail/', SUBSTRING_INDEX(email,'@',-1), '/', SUBSTRING_INDEX(email,'@',1)) as home FROM virtual_users WHERE email='%u' AND active=1
EOF
chmod 640 /etc/dovecot/dovecot-sql.conf.ext

echo
echo "Packages and SQL map stubs installed for hostname hint: ${MAIL_HOSTNAME}"
echo "Next: complete Postfix/Dovecot main configs, Certbot, SnappyMail, and Laravel .env"
echo "Full guide (Arabic): ${APP_ROOT}/docs/email/ACTIVATION_AR.md"
