#!/usr/bin/env bash
# Update the installed deployer without pulling/resetting the production worktree.
set -Eeuo pipefail
umask 077
readonly REPO_ROOT='/var/www/daisho'
readonly EXPECTED_ORIGIN='git@github.com:Taiju-h/global-daisho.git'
readonly SSH_KEY='/home/heartf/.ssh/global_daisho_github_ed25519'
readonly HELPER='/usr/local/sbin/global-daisho-deploy'
readonly WEB='/var/www/global-daisho-deployer/index.php'
readonly SUDOERS='/etc/sudoers.d/global-daisho-deployer'

die() { printf 'ERROR: %s\n' "$*" >&2; exit 1; }
git_run() {
    GIT_TERMINAL_PROMPT=0 GIT_SSH_COMMAND="ssh -i $SSH_KEY -o IdentitiesOnly=yes -o BatchMode=yes" git -C "$REPO_ROOT" "$@"
}
[ "$(id -u)" -eq 0 ] || die 'Run as root.'
[ "$#" -eq 0 ] || die 'This updater accepts no arguments.'
for tool in git php bash visudo install flock mktemp cp; do
    command -v "$tool" >/dev/null || die "Required command not found: $tool"
done
[ "$(git_run remote get-url origin)" = "$EXPECTED_ORIGIN" ] || die 'Unexpected origin.'
[ -r "$SSH_KEY" ] || die 'SSH deploy key is unavailable.'
for file in "$HELPER" "$WEB" "$SUDOERS"; do
    [ -f "$file" ] && [ ! -L "$file" ] || die "Existing installation required: $file"
done
exec 9>/run/lock/global-daisho-deployer.lock
flock -n 9 || die 'Another deployment operation is already running.'
git_run fetch --prune origin main
revision="$(git_run rev-parse --verify 'origin/main^{commit}')"
stage="$(mktemp -d)"
trap 'rm -rf -- "$stage"' EXIT
git_run show "$revision:.deploy/global-daisho/bin/global-daisho-deploy" > "$stage/helper"
git_run show "$revision:.deploy/global-daisho/web/index.php" > "$stage/index.php"
git_run show "$revision:.deploy/global-daisho/sudoers.d/global-daisho-deployer" > "$stage/sudoers"
bash -n "$stage/helper"
php -l "$stage/index.php"
visudo -cf "$stage/sudoers"
install -d -o root -g root -m 0700 /var/backups/global-daisho-deployer/runtime
backup="$(mktemp -d /var/backups/global-daisho-deployer/runtime/update-XXXXXXXX)"
cp -p "$HELPER" "$backup/helper"
cp -p "$WEB" "$backup/index.php"
cp -p "$SUDOERS" "$backup/sudoers"
restore_runtime() {
    local result=$?
    trap - ERR
    cp -pf "$backup/helper" "$HELPER"
    cp -pf "$backup/index.php" "$WEB"
    cp -pf "$backup/sudoers" "$SUDOERS"
    printf 'ERROR: Runtime update failed; restored runtime from %s\n' "$backup" >&2
    exit "$result"
}
trap restore_runtime ERR
install -o root -g root -m 0755 "$stage/helper" "$HELPER"
install -o root -g root -m 0440 "$stage/sudoers" "$SUDOERS"
visudo -cf "$SUDOERS"
install -o root -g www-data -m 0640 "$stage/index.php" "$WEB"
trap - ERR
printf 'OK: Deployer updated from %s\nBackup: %s\n' "$revision" "$backup"
printf '%s\n' 'Production files, credentials, IP allowlist and history were not changed.'
