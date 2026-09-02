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
strict Apache configuration, a command-specific sudoers rule, and reloads
Apache after `apache2ctl configtest` succeeds.

## Safety rules

- Opening the page only reads repository status. It does not fetch, merge, or reset.
- Apache Basic authentication is mandatory.
- State-changing requests use POST and a session CSRF token.
- The web process may invoke only four exact root-helper commands through sudo.
- The helper validates the production path, `origin`, and `main` branch.
- Deploy uses `fetch` followed by a fast-forward-only merge from `origin/main`.
- Deploy and rollback stop when tracked local changes exist.
- Rollback moves production back exactly one commit and can be repeated.
- A process lock prevents concurrent Git operations.
- The deployer runtime remains available after rollback.
- Deploy and rollback attempts record time, authenticated user, client IP,
  result, and before/after commit IDs.
- Once an IP is added to the allowlist, deploy and rollback are limited to
  registered IPs. Diff and allowlist maintenance remain available to an
  authenticated user.

## Reinstall or password change

Pull the latest `main` and run the installer again. Existing history and IP
allowlist files are preserved; the named Basic-auth user password is updated.
