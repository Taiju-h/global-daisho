# GLOBAL DAISHO 作業ルール

- Repository: `Taiju-h/global-daisho`
- Branch: `main`
- Deployer URL: `https://global.daishokagaku.com/__deploy/`
- Deployer source: `.deploy/global-daisho/web/index.php`
- Installer: `.deploy/install-global-daisho-deployer.sh`
- Runtime IP file: `/var/lib/global-daisho-deployer/allowed-ips.txt`
- Basic authentication file: `/etc/apache2/global-daisho-deployer.htpasswd`

デプロイヤーはApache Basic認証を必須とし、ID・パスワードやhtpasswdをGitへ追加しません。正しいID・パスワードで認証後、現在IPまたは任意のIPv4・IPv6を管理画面から追加・削除します。IPファイルを直接編集しません。

許可IPが登録済みの場合、本番反映とロールバックは許可IPだけが実行できます。差分確認とIP管理はBasic認証済みユーザーが利用できます。

変更後は `php -l .deploy/global-daisho/web/index.php` と `bash -n .deploy/install-global-daisho-deployer.sh` を実行し、GitHubの `main` へ反映します。デプロイは依頼された場合だけ実行します。
