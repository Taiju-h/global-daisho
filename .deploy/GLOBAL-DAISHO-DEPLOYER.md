# GLOBAL DAISHO Deployer

## Runtime

- URL: `https://global.daishokagaku.com/__deploy/`
- Repository: `Taiju-h/global-daisho`
- Branch: `main`
- Production repository: `/var/www/daisho`
- Web runtime: `/var/www/global-daisho-deployer`
- State: `/var/lib/global-daisho-deployer`
- Root helper: `/usr/local/sbin/global-daisho-deploy`

The deployer runtime is outside the production Git worktree. Rolling the site
back therefore does not remove the deployer. The tracked source lives in
`.deploy/global-daisho`.

## Initial installation

Run as root on the VPS:

```bash
cd /var/www/daisho
git pull --ff-only origin main
chmod 700 .deploy/install-global-daisho-deployer.sh
.deploy/install-global-daisho-deployer.sh taiju
```

The installer asks for the Basic authentication password. It also installs a
strict Apache configuration inside only the two `global.daishokagaku.com`
VirtualHosts, installs a command-specific sudoers rule, and reloads Apache
after `apache2ctl configtest` succeeds. VirtualHost backups are written below
`/var/backups/global-daisho-deployer` before the first edit.

## Protecting Salesdata SDS

The complete `/salesdata/sds/` directory, including direct links to individual
SDS documents, uses the same Apache Basic-auth user file as `/__deploy/`.
No user ID or password is stored in the repository.

After deploying the corresponding Git commit, run as root on the VPS:

```bash
cd /var/www/daisho
chmod 700 .deploy/install-global-daisho-sds-auth.sh
.deploy/install-global-daisho-sds-auth.sh
```

The installer reuses
`/etc/apache2/global-daisho-deployer.htpasswd` without modifying it, backs up
the current Apache snippet, runs `apache2ctl configtest`, restores the previous
configuration if validation fails, and reloads Apache only after validation
succeeds.

## Safety rules

- Opening the page only reads repository status. It does not fetch, merge, or reset.
- Apache Basic authentication is mandatory.
- State-changing requests use POST and a session CSRF token.
- The web process may invoke only five exact root-helper commands through sudo.
- The helper validates the production path, `origin`, and `main` branch.
- Deploy uses `fetch` followed by a fast-forward-only merge from `origin/main`.
- Normal deploy and rollback stop when tracked local changes exist. Diff remains available with local changes.
- Rollback moves production back exactly one commit and can be repeated.
- A process lock prevents concurrent Git operations.
- The deployer runtime remains available after rollback.
- Deploy and rollback attempts record time, authenticated user, client IP,
  result, and before/after commit IDs.
- Once an IP is added to the allowlist, deploy and rollback are limited to
  registered IPs. Diff and allowlist maintenance remain available to an
  authenticated user.
- The IP management card accepts the current address or any valid IPv4/IPv6
  address. A Basic-authenticated administrator can add a new address even
  when the current address is not yet allowed.

## Reinstall or password change

Pull the latest `main` and run the installer again. Existing history and IP
allowlist files are preserved; the named Basic-auth user password is updated.

## Update the existing deployer when production has local changes

Updating just the PHP page is insufficient: the installed root helper and
command-specific sudoers entry also need updating. The updater below reads a
fixed revision of origin/main into a temporary directory, validates PHP, Bash
and sudoers, and installs only those three runtime files. It does not pull,
reset the production worktree, modify Apache, change credentials, or change
the IP allowlist. A failed installation restores the previous runtime files.

Run once as root on the VPS (not in the PHP editor):

```bash
set -e
export GIT_SSH_COMMAND='ssh -i /home/heartf/.ssh/global_daisho_github_ed25519 -o IdentitiesOnly=yes -o BatchMode=yes'
git -C /var/www/daisho fetch origin main
updater=$(mktemp /tmp/global-daisho-update.XXXXXX)
git -C /var/www/daisho show origin/main:.deploy/update-global-daisho-deployer.sh > "$updater"
bash "$updater"
rm -f "$updater"
```

Refresh the deployer page, inspect the diff and use **バックアップして強制反映**
or **Back up and force deploy**. The button retains Basic authentication,
CSRF protection and the existing deployment IP restriction. An older installed
helper does not advertise this capability and the button remains disabled.

## Force deployment and recovery

Force deployment fetches main, pins its commit, validates the target PHP page,
then saves a root-only backup under `/var/backups/global-daisho-deployer/force/`:

- `worktree.tar`: all production worktree files including ignored/untracked
  files, excluding `.git` (symlinks are archived, not followed).
- `repository.bundle`: Git refs/history, including local-only commits.
- `index`, `staged.patch`, `unstaged.patch`, `status.z`, `before`, `target`.
- A `refs/deployer-backups/<backup-name>` ref retains the old commit.

Only after all backup commands succeed does `git reset --hard <target>` run.
It replaces tracked files and may replace untracked/ignored files that obstruct
target paths; these are included in the archive. It does not run `git clean`.
Backups are retained until an administrator removes them. Allow enough disk
space for the complete worktree and Git history. Quiesce application uploads
and writes during a backup; this is not a database or external-storage backup.
An unresolved merge/rebase is rejected. Read the exact error if force deploy
also fails; it cannot solve permissions, SSH authentication or disk failures.

The normal rollback button moves back one Git commit. It does **not** restore
the pre-force dirty worktree. For recovery, first stop application writes and
save any changes made since deployment. As root, choose the exact `BACKUP=`
directory printed by the failed/successful force operation, then run:

```bash
backup=/var/backups/global-daisho-deployer/force/REPLACE_WITH_BACKUP_NAME
(
  set -e
  exec 9>/run/lock/global-daisho-deployer.lock
  flock -n 9
  before=$(cat "$backup/before")
  git -C /var/www/daisho cat-file -e "$before^{commit}"
  tar -tf "$backup/worktree.tar" >/dev/null
  git -C /var/www/daisho reset --hard "$before"
  tar -xpf "$backup/worktree.tar" -C /var/www/daisho
  cp -p "$backup/index" /var/www/daisho/.git/index
)
```

This restores the saved commit, worktree and index. Untracked files created
after backup are not purged. Keep the newer deployer runtime for recovery;
the restoration does not change its credentials, IP settings or runtime files.
