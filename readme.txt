=== Kyvora ===
Contributors: brayleiton
Tags: security, hardening, login, xmlrpc, rest-api
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Harden WordPress with XML-RPC controls, login rate limiting, and user-enumeration blocking. No account required.

== Description ==

Kyvora applies local WordPress hardening. Every feature in this plugin works after activation. There is no account, license key, trial, or usage limit.

* XML-RPC and pingback controls
* Login rate limiting
* Block user enumeration
* Basic security headers and generator removal
* Disable the theme and plugin file editors
* Restrict the users REST endpoint to logged-in users
* Three presets: Essential, Recommended, and Paranoid
* Safe mode: if a module causes a fatal error, that module is turned off and the site keeps loading

This plugin does not connect to an external service.

== Installation ==

1. Install Kyvora from the Plugins screen, or upload the zip.
2. Activate it.
3. Choose a protection level in the setup wizard.
4. Review the modules on the Kyvora screen.

== Frequently Asked Questions ==

= Does this plugin need an account? =

No.

= Will the Recommended preset break WooCommerce or forms? =

It is meant to keep Gutenberg, WooCommerce, Contact Form 7, and oEmbed working. The Paranoid preset is stricter and can affect plugins that need XML-RPC or open REST routes.

= Does the plugin phone home? =

No. It does not send site data anywhere.

== Changelog ==

= 1.0.1 =
* Directory release with local hardening only.
* Fixed a fatal error during activation.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.1 =
Fixes activation and keeps every included feature available without an account.
