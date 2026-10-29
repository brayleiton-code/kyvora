<?php
/**
 * Plugin Name: Kyvora
 * Description: Harden WordPress with XML-RPC controls, login rate limiting, user-enumeration blocking, and security headers. Fully usable with no account.
 * Version: 1.0.2
 * Author: Bray Leiton
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kyvora
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package WPPYSHardening
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPPYS_VERSION', '1.0.2' );
define( 'WPPYS_FILE', __FILE__ );
define( 'WPPYS_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPPYS_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register(
	function ( $class ) {
		$prefix = 'WPProtectYourSite\\';
		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$parts    = explode( '\\', $relative );
		$name     = array_pop( $parts );
		$name     = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $name ) );
		$dir      = $parts ? implode( '/', $parts ) . '/' : '';
		$base     = WPPYS_DIR . 'includes/' . $dir;

		foreach ( array( 'class-', 'interface-' ) as $kind ) {
			$file = $base . $kind . $name . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
				return;
			}
		}
	}
);

/**
 * Boot the plugin.
 */
function wppys_init() {
	WPProtectYourSite\Plugin::get_instance()->run();
}
add_action( 'plugins_loaded', 'wppys_init' );

/**
 * Activation hook. No remote calls and no output.
 */
function wppys_activate() {
	if ( false === get_option( 'wppys_settings' ) ) {
		add_option( 'wppys_settings', array(), '', false );
	}
}
register_activation_hook( __FILE__, 'wppys_activate' );
