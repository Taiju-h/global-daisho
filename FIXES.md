# 修正履歴

## 2026-09-03 GLOBAL DAISHO IP管理で任意IP追加に対応

- タスク番号: なし（直接依頼）
- 症状: Basic認証後のIP管理では現在の接続元IPしか追加できず、別端末や固定IPを事前登録できなかった。
- 修正: IP入力欄へ現在IPを初期表示し、任意のIPv4・IPv6へ書き換えて追加可能にした。入力値を再検証し、既存の重複排除・安全なファイル保存・削除・CSRF・Basic認証・デプロイIP制限は維持。
- 対象: `.deploy/global-daisho/web/index.php`、`.deploy/GLOBAL-DAISHO-DEPLOYER.md`、`AGENTS.md`、`FIXES.md`
- Gitコミット: 本コミット
- デプロイ: 未実施
- 確認手順: Basic認証後、現在IPと任意IPv4・IPv6を追加・削除し、許可外IPでは本番反映とロールバックが停止することを確認する。
- 未確認: VPS上の実ブラウザ表示、Apache Basic認証、実デプロイ操作。
