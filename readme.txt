=== Fail2Notify – WP Mail Failure Alerts ===
Contributors: Solgeo Corp.
Donate link:
Tags: wp_mail, slack, notification, email, logging,Contact Form 7
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fail2Notify は WordPress の `wp_mail()` 送信失敗を即座に検知し、Slack などの外部チャネルへ通知する軽量プラグインです。フォームプラグインや `wp_mail()` を利用するあらゆる機能で失敗が発生した際、最小限の設定で気付きやすいアラートを受け取れます。

== Description ==

Fail2Notify hooks into the core `wp_mail_failed` action to capture transport errors, masks personally identifiable information, optionally stores the latest 50 failure logs, and posts a concise message to a Slack incoming webhook. A manual test button is included to verify connectivity without forcing an actual failure.

= 主な機能 =
* `wp_mail_failed` をフックしてメール送信失敗を即検知
* Slack Incoming Webhook へサイト名・環境・メッセージ抜粋を通知
* アドレスや本文を自動マスキングし、プライバシーを保護
* 直近 50 件の失敗ログを管理画面で閲覧可能
* テスト通知ボタンで設定直後の確認も簡単

== Installation ==

1. Upload the `fail2notify` directory to `/wp-content/plugins/` or install via the Plugins screen.
2. Activate the plugin through “Plugins” > “Installed Plugins”.
3. Open “Settings” > “Fail2Notify”.
4. 有効化チェックを入れ、任意でサイト名・環境ラベルを入力し、Slack Webhook URL を保存します。
5. 「テスト通知を送る」ボタンで Slack へ疎通確認を行います。

== Frequently Asked Questions ==

= Slack 以外にも送れますか？ =
現時点では Slack Webhook のみサポートしています。Webhook 実装を追加することで拡張できるよう設計しているため、PR やフィードバックを歓迎します。

= 既存のフォームプラグイン（Contact Form 7 など）にも対応していますか？ =
はい。これらは内部で `wp_mail()` を利用しているため、Fail2Notify が同じアクションを監視することで一括検知できます。

= ログをもっと長く保持したい =
`F2N_Logger::LIMIT` をフィルター化する予定ですが、現段階では 50 件固定です。

== Screenshots ==

1. 設定画面。Slack Webhook と環境ラベルを入力し、テスト通知を送信できます。

== Changelog ==

= 0.1.0 =
* Initial release with Slack notifications, masking, in-dashboard logs, and manual test trigger.

== Upgrade Notice ==

= 0.1.0 =
初回公開版です。Slack Webhook を設定してからアップグレードしてください。
