=== Fail2Notify — Mail Failure Alerts ===
Contributors: solgeo3960
Donate link: https://solgeo.co.jp/
Tags: wp_mail, slack, notification, email, logging
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fail2Notify watches `wp_mail()` failures and pushes masked Slack alerts so you can react before users notice.

== Description ==

Fail2Notify hooks into the core `wp_mail_failed` action to capture transport errors, masks personally identifiable information, optionally stores the latest 50 failure logs, and posts a concise message to a Slack incoming webhook. A manual "Send Test Notification" button is included to verify connectivity without forcing an actual failure.

= Features =
* Instant detection of `wp_mail()` failures.
* Slack Incoming Webhook notifications with site label, environment, and body excerpt.
* Automatic masking for email addresses in any field, including the message body.
* Up to 50 recent logs visible inside the settings page.
* Manual "Send Test Notification" button for quick verification.

== Installation ==

1. Upload the `fail2notify-mail-failure-alerts` directory to `/wp-content/plugins/` or install via the Plugins screen.
2. Activate the plugin through "Plugins" > "Installed Plugins".
3. Open "Settings" > "Fail2Notify".
4. Enable monitoring, optionally set the site/environment labels, and save your Slack Webhook URL.
5. Use the test button to confirm the webhook succeeds.

== Frequently Asked Questions ==

= Can I send alerts anywhere other than Slack? =
Not yet. The notifier layer is intentionally simple, so additional transports (Teams, Discord, etc.) can be contributed later.

= Will it work with Contact Form 7 or other form plugins? =
Yes. They rely on `wp_mail()`, so this plugin captures the same failure hooks without extra configuration.

= Can I keep logs for longer? =
The bundled logger keeps the latest 50 entries. Future releases may add filters; meanwhile you can extend or swap the logger class to persist elsewhere.

== Screenshots ==

1. Settings page with Slack Webhook field, environment label, and test button.

== Changelog ==

= 1.0.0 =
* Initial release with Slack notifications, masking, in-dashboard logs, and manual test trigger.

== Upgrade Notice ==

= 1.0.0 =
First public release. Configure your Slack Webhook after upgrading.

== 説明 ==

Fail2Notifyは、WordPressコアの`wp_mail_failed`アクションにフックして、メール送信エラーを検出します。個人情報を自動的にマスクし、最大50件の最新の失敗ログを保存し、SlackのIncoming Webhookに簡潔なメッセージを送信します。実際のエラーを発生させずに接続を確認できる「テスト通知を送信」ボタンも含まれています。

= 機能 =
* `wp_mail()`の失敗を即座に検出します。
* サイト名、本番、ステージングなどの環境、本文抜粋を含むSlack Incoming Webhook通知を送信します。
* メッセージ本文を含むすべてのフィールドでメールアドレスを自動的にマスクします。
* 設定ページ内で最新50件の送信失敗＆失敗の通知先のログが表示されます。。
* 接続を素早く確認できる「テスト通知を送信」ボタンがあるのでその場で設定に問題がないか確認できます。

== インストール ==

1. `fail2notify-mail-failure-alerts`ディレクトリを`/wp-content/plugins/`にアップロードするか、プラグイン画面からインストールします。
2. 「プラグイン」>「インストール済みプラグイン」からプラグインを有効化します。
3. 「設定」>「Fail2Notify」を開きます。
4. 監視を有効にし、必要に応じてサイト/環境ラベルを設定し、Slack Webhook URLを保存します。
5. テストボタンを使用してWebhookが正常に動作することを確認します。

== よくある質問 ==

= Slack以外にもアラートを送信できますか？ =
現時点ではできません。通知レイヤーは意図的にシンプルに設計されているため、追加のトランスポート（Teams、Discordなど）は今後追加される可能性があります。

= Contact Form 7やその他のフォームプラグインで動作しますか？ =
はい。それらは`wp_mail()`に依存しているため、このプラグインは追加設定なしで同じ失敗フックをキャプチャします。

= ログをより長期間保持できますか？ =
バンドルされているロガーは最新50エントリを保持します。今後のリリースではフィルターが追加される可能性があります。それまでは、ロガークラスを拡張または交換して、別の場所に永続化できます。

== 変更履歴 ==

= 1.0.0 =
Slack通知、マスキング、ダッシュボード内ログ、手動テストトリガーを含む初回リリース。

== アップグレード通知 ==

= 1.0.0 =
初回公開リリース。アップグレード後、Slack Webhookを設定してください。