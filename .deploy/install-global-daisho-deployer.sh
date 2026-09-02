#!/usr/bin/env bash
set -Eeuo pipefail

readonly REPO_ROOT='/var/www/daisho'
readonly SOURCE_ROOT="$REPO_ROOT/.deploy/global-daisho"
readonly RUNTIME_DIR='/var/www/global-daisho-deployer'
readonly STATE_DIR='/var/lib/global-daisho-deployer'
readonly PASSWORD_FILE='/etc/apache2/global-daisho-deployer.htpasswd'
readonly APACHE_CONF='/etc/apache2/global-daisho-deployer-vhost.conf'
readonly SUDOERS_FILE='/etc/sudoers.d/global-daisho-deployer'
readonly HELPER='/usr/local/sbin/global-daisho-deploy'
readonly SSH_KEY='/home/heartf/.ssh/global_daisho_github_ed25519'
readonly EXPECTED_ORIGIN='git@github.com:Taiju-h/global-daisho.git'
readonly DEPLOY_USER="${1:-taiju}"

die() {
    printf 'ERROR: %s\n' "$*" >&2
    exit 1
}

[ "$(id -u)" -eq 0 ] || die 'Run this installer as root.'
[[ "$DEPLOY_USER" =~ ^[A-Za-z0-9._-]{1,64}$ ]] || die 'Invalid Basic authentication username.'

for command in git php htpasswd apache2ctl a2enmod visudo sudo install flock ssh systemctl awk readlink; do
    command -v "$command" >/dev/null 2>&1 || die "Required command was not found: $command"
done

[ -d "$REPO_ROOT/.git" ] || die "Git repository not found: $REPO_ROOT"
[ -f "$SOURCE_ROOT/web/index.php" ] || die 'Web source is missing.'
[ -f "$SOURCE_ROOT/bin/global-daisho-deploy" ] || die 'Deployment helper is missing.'
[ -f "$SOURCE_ROOT/apache/global-daisho-deployer.conf" ] || die 'Apache configuration is missing.'
[ -f "$SOURCE_ROOT/sudoers.d/global-daisho-deployer" ] || die 'sudoers configuration is missing.'
[ -f "$SSH_KEY" ] || die "SSH deploy key was not found: $SSH_KEY"

php -l "$SOURCE_ROOT/web/index.php"
bash -n "$SOURCE_ROOT/bin/global-daisho-deploy"

if ! git config --system --get-all safe.directory | grep -Fxq "$REPO_ROOT"; then
    git config --system --add safe.directory "$REPO_ROOT"
fi

git -C "$REPO_ROOT" remote set-url origin "$EXPECTED_ORIGIN"
git -C "$REPO_ROOT" config core.sshCommand "ssh -i $SSH_KEY -o IdentitiesOnly=yes -o BatchMode=yes"
chmod 600 "$SSH_KEY"

install -o root -g root -m 0755 "$SOURCE_ROOT/bin/global-daisho-deploy" "$HELPER"
install -d -o root -g www-data -m 0750 "$RUNTIME_DIR"
install -o root -g www-data -m 0640 "$SOURCE_ROOT/web/index.php" "$RUNTIME_DIR/index.php"

install -d -o www-data -g www-data -m 0750 "$STATE_DIR"
touch "$STATE_DIR/allowed-ips.txt" "$STATE_DIR/history.jsonl"
chown www-data:www-data "$STATE_DIR/allowed-ips.txt" "$STATE_DIR/history.jsonl"
chmod 0640 "$STATE_DIR/allowed-ips.txt" "$STATE_DIR/history.jsonl"

install -o root -g root -m 0440 "$SOURCE_ROOT/sudoers.d/global-daisho-deployer" "$SUDOERS_FILE"
visudo -cf "$SUDOERS_FILE"

install -o root -g root -m 0644 "$SOURCE_ROOT/apache/global-daisho-deployer.conf" "$APACHE_CONF"

if [ -f "$PASSWORD_FILE" ]; then
    printf 'Updating Basic authentication user: %s\n' "$DEPLOY_USER"
    htpasswd -B "$PASSWORD_FILE" "$DEPLOY_USER"
else
    printf 'Creating Basic authentication user: %s\n' "$DEPLOY_USER"
    htpasswd -cB "$PASSWORD_FILE" "$DEPLOY_USER"
fi
chown root:www-data "$PASSWORD_FILE"
chmod 0640 "$PASSWORD_FILE"

a2enmod alias auth_basic authn_file authz_core >/dev/null

backup_dir="/var/backups/global-daisho-deployer/$(date +%Y%m%d-%H%M%S)"
include_line='    IncludeOptional /etc/apache2/global-daisho-deployer-vhost.conf'
modified_targets=()
backup_files=()

for enabled_vhost in \
    /etc/apache2/sites-enabled/global.daishokagaku.com.conf \
    /etc/apache2/sites-enabled/global.daishokagaku.com-le-ssl.conf; do
    [ -e "$enabled_vhost" ] || continue
    target_vhost="$(readlink -f "$enabled_vhost")"
    [ -f "$target_vhost" ] || die "VirtualHost configuration is not a file: $enabled_vhost"
    if grep -Fq '/etc/apache2/global-daisho-deployer-vhost.conf' "$target_vhost"; then
        continue
    fi

    install -d -o root -g root -m 0700 "$backup_dir"
    backup_file="$backup_dir/$(basename "$target_vhost")"
    cp -a "$target_vhost" "$backup_file"
    temp_vhost="$(mktemp "${target_vhost}.XXXXXX")"
    awk -v include_line="$include_line" '
        /^[[:space:]]*<\/VirtualHost>[[:space:]]*$/ { print include_line }
        { print }
    ' "$target_vhost" > "$temp_vhost"
    grep -Fq '/etc/apache2/global-daisho-deployer-vhost.conf' "$temp_vhost" || die "Could not update VirtualHost: $target_vhost"
    chown --reference="$target_vhost" "$temp_vhost"
    chmod --reference="$target_vhost" "$temp_vhost"
    cp "$temp_vhost" "$target_vhost"
    rm -f "$temp_vhost"
    modified_targets+=("$target_vhost")
    backup_files+=("$backup_file")
done

[ "${#modified_targets[@]}" -gt 0 ] || \
    grep -RFlq '/etc/apache2/global-daisho-deployer-vhost.conf' /etc/apache2/sites-enabled || \
    die 'No global.daishokagaku.com VirtualHost configuration was found.'

if ! apache2ctl configtest; then
    for index in "${!modified_targets[@]}"; do
        cp -a "${backup_files[$index]}" "${modified_targets[$index]}"
    done
    apache2ctl configtest || true
    die "Apache configuration failed. VirtualHost files were restored from $backup_dir"
fi
systemctl reload apache2

sudo -u www-data -- sudo -n "$HELPER" status >/dev/null

printf '%s\n' 'OK: GLOBAL DAISHO Deployer installed.'
printf '%s\n' 'URL: https://global.daishokagaku.com/__deploy/'
