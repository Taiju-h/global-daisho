#!/usr/bin/env bash
set -Eeuo pipefail

readonly REPO_ROOT='/var/www/daisho'
readonly SOURCE_CONF="$REPO_ROOT/.deploy/global-daisho/apache/global-daisho-deployer.conf"
readonly TARGET_CONF='/etc/apache2/global-daisho-deployer-vhost.conf'
readonly PASSWORD_FILE='/etc/apache2/global-daisho-deployer.htpasswd'
readonly BACKUP_ROOT='/var/backups/global-daisho-deployer'

die() {
    printf 'ERROR: %s\n' "$*" >&2
    exit 1
}

[ "$(id -u)" -eq 0 ] || die 'Run this installer as root.'

for command in apache2ctl cp date grep install rm systemctl; do
    command -v "$command" >/dev/null 2>&1 || die "Required command was not found: $command"
done

[ -f "$SOURCE_CONF" ] || die "Apache source configuration was not found: $SOURCE_CONF"
[ -s "$PASSWORD_FILE" ] || die "Existing Basic-auth password file was not found: $PASSWORD_FILE"
grep -RFlq "$TARGET_CONF" /etc/apache2/sites-enabled || \
    die "The GLOBAL DAISHO deployer configuration is not included by an enabled VirtualHost."

backup_dir="$BACKUP_ROOT/$(date +%Y%m%d-%H%M%S)-sds-auth"
install -d -o root -g root -m 0700 "$backup_dir"
target_existed=0
if [ -f "$TARGET_CONF" ]; then
    cp -a "$TARGET_CONF" "$backup_dir/global-daisho-deployer-vhost.conf"
    target_existed=1
fi

install -o root -g root -m 0644 "$SOURCE_CONF" "$TARGET_CONF"

if ! apache2ctl configtest; then
    if [ "$target_existed" -eq 1 ]; then
        cp -a "$backup_dir/global-daisho-deployer-vhost.conf" "$TARGET_CONF"
    else
        rm -f "$TARGET_CONF"
    fi
    apache2ctl configtest || true
    die "Apache configuration failed. The previous configuration was restored from $backup_dir"
fi

systemctl reload apache2

printf '%s\n' 'OK: Basic authentication enabled for /salesdata/sds/.'
printf '%s\n' 'Existing /__deploy/ user IDs and passwords were preserved.'
printf '%s\n' 'URL: https://global.daishokagaku.com/salesdata/sds/'
