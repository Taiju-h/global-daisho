# CRMのコード・MySQL更新

サーバー管理者が実行する更新用EXECです。通常の名刺・日報の取り込みでは不要です。
接続情報は既存の `/etc/global-daisho/crm-db.php` の `dsn`、`user`、`password` を使用します。Gitやコマンド引数へパスワードを書きません。

## 初回

このEXEC自体を初めてサーバーへ取得する際だけ、先にGit更新が必要です。

```bash
git -C /var/www/daisho pull --ff-only
bash /var/www/daisho/.deploy/update-crm.sh --db-only
```

## 以降の更新：一つのコマンド

```bash
bash /var/www/daisho/.deploy/update-crm.sh
```

PHP・DB接続と履歴を確認 → mainをfast-forward更新 → PHP構文確認 → 未適用SQLを番号順に実行します。チェックアウトに未コミットの変更がある場合や、DB更新に失敗した場合は停止します。失敗後に「完了」と表示しません。Git更新後の検証やSQLが失敗しても、コードは自動で巻き戻しません。

Gitを更新せずDBだけ反映：

```bash
bash /var/www/daisho/.deploy/update-crm.sh --db-only
```

未適用SQL・適用状況の確認（DBへ書き込みません）：

```bash
bash /var/www/daisho/.deploy/update-crm.sh --status
```

## SQLを追加する

`.deploy/crm-sql/` に次の番号のファイルを追加します。適用済みファイルは編集・削除せず、訂正も新しい番号で追加してください。内容のSHA-256が変わった場合は停止します。

- テーブル・カラム変更：`0005_example.ddl.sql`
- データ更新：`0006_example.dml.sql`
- **1ファイルにつき1文**。番号は重複させず、末尾のセミコロンは任意です。
- 通常のコメント、文字列内のセミコロン、二重化した引用符に対応します。バックスラッシュによるエスケープ、DELIMITER、ストアドルーチン、実行可能コメントは使用しません。
- DMLはINSERT INTO / UPDATE / DELETE FROMに対応し、InnoDBテーブルだけを対象にしてください。データと適用記録を同じトランザクションで確定します。DDLはMySQLの暗黙コミットがあるため一括ロールバックできません。

今回の0001〜0004は、ChatGPT取り込み・修正履歴・日報とタスクの関連・初期データの適用管理テーブルを作成します。既存テーブルは `IF NOT EXISTS` で保持します。CRMの従来の基礎テーブル作成・旧データ取り込みは既存PHPに残しています。このSQL群だけで新しい空DBを完全初期化するものではありません。

## エラーと再実行

`crm_schema_migrations` にファイル名、ハッシュ、状態、試行回数、実行日時、SQLSTATEを記録します。`applied` はスキップし、`failed` / `running` は自動再実行しません。並列実行もMySQLのロックで防止します。

失敗したSQL名を確認し、DBの実際の状態を調べてから再実行してください。先に成功したファイルは適用済みのままです。DDLが成功して履歴保存だけ失敗した可能性もあるため、「失敗＝未変更」とは限りません。

安全に再実行できることを確認したファイルだけ、次で再試行できます。

```bash
php /var/www/daisho/.deploy/crm-migrate.php --retry=0001_chatgpt_imports.ddl.sql
```

これはHTTPからSQLやシェルを実行する画面ではありません。PHP 8.1以上・pdo_mysqlと、既存DB設定を読めるサーバー権限が必要です。

## テスト

```bash
php .deploy/tests/crm-migrate-test.php
bash -n .deploy/update-crm.sh
```
