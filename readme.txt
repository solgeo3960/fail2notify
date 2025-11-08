=== Fail2Notify - WP Mail Failure Alerts ===
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

1. Upload the `fail2notify` directory to `/wp-content/plugins/` or install via the Plugins screen.
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
