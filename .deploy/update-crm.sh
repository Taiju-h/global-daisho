#!/usr/bin/env bash
set -euo pipefail

# Server operator entry point. Daily CRM entry/import does not require this script.
script_dir="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
repo_dir="$(cd -- "$script_dir/.." && pwd)"
mode="${1:---update}"
if [[ $# -gt 1 ]] || [[ "$mode" != --update && "$mode" != --db-only && "$mode" != --status ]]; then
  echo 'Usage: bash .deploy/update-crm.sh [--update|--db-only|--status]' >&2
  exit 2
fi
command -v php >/dev/null || { echo 'PHP CLI is required.' >&2; exit 1; }
php -r 'if (PHP_VERSION_ID < 80100 || !extension_loaded("pdo_mysql")) { fwrite(STDERR,"PHP >= 8.1 with pdo_mysql is required.\n"); exit(1); }'
if [[ "$mode" == --status ]]; then
  exec php "$script_dir/crm-migrate.php" --status
fi
if [[ "$mode" == --update ]]; then
  command -v git >/dev/null || { echo 'Git is required.' >&2; exit 1; }
  [[ "$(git -C "$repo_dir" branch --show-current)" == main ]] || { echo 'Use the main branch.' >&2; exit 1; }
  [[ -z "$(git -C "$repo_dir" status --porcelain)" ]] || { echo 'The Git checkout has local changes. Update stopped.' >&2; exit 1; }
  php "$script_dir/crm-migrate.php" --status
  git -C "$repo_dir" pull --ff-only
  # Re-read the pulled script rather than continuing with its previous in-memory version.
  exec bash "$script_dir/update-crm.sh" --db-only
fi

bash -n "$script_dir/update-crm.sh"
php -l "$script_dir/crm-migrate.php" >/dev/null
while IFS= read -r -d '' php_file; do php -l "$php_file" >/dev/null; done < <(find "$repo_dir/salesdata/crm" -type f -name '*.php' -print0)
echo 'PHP syntax OK. Applying pending CRM SQL files...'
php "$script_dir/crm-migrate.php" --apply
echo 'CRM code and SQL checks completed.'
