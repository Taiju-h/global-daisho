#!/usr/bin/env bash
set -Eeuo pipefail

readonly DB_DIR='/var/lib/global-daisho-crm'
readonly DB_FILE="$DB_DIR/crm.sqlite"
readonly APACHE_USER='www-data'

[ "$(id -u)" -eq 0 ] || { echo 'Run as root.' >&2; exit 1; }
command -v php >/dev/null 2>&1 || { echo 'PHP is required.' >&2; exit 1; }
php -m | grep -qiE '^pdo_sqlite$' || { echo 'pdo_sqlite is required. Install php-sqlite3 first.' >&2; exit 1; }

install -d -o "$APACHE_USER" -g "$APACHE_USER" -m 0770 "$DB_DIR"
if [ -f "$DB_FILE" ]; then
  chown "$APACHE_USER:$APACHE_USER" "$DB_FILE"
  chmod 0660 "$DB_FILE"
fi

echo 'CRM runtime directory is ready.'
echo 'URL: https://global.daishokagaku.com/salesdata/crm/'
echo 'First setup code: 0921 (the first admin must immediately register a new password).'
