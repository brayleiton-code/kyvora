<?php
/**
 * Main plugin class.
 *
 * @package WPPYSHardening
 */

namespace WPProtectYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {

	/**
	 * Instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Settings.
	 *
	 * @var array
	 */
	private $settings = array();

	/**
	 * Module registry.
	 *
	 * @var Hardening\ModuleRegistry
	 */
	private $module_registry;

	/**
	 * Safe mode handler.
	 *
	 * @var SafeMode\SafeMode
	 */
	private $safe_mode;

	/**
	 * Admin.
	 *
	 * @var Admin\Admin
	 */
	private $admin;

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->settings = $this->get_settings();
	}

	/**
	 * Run the plugin.
	 */
	public function run() {
		if ( is_admin() ) {
			$this->init_admin();
		}

		$this->init_hardening();
		$this->init_safe_mode();
	}

	/**
	 * Initialize admin.
	 */
	private function init_admin() {
		$this->admin = new Admin\Admin( $this );
		$this->admin->init();
	}

	/**
	 * Initialize hardening modules.
	 */
	private function init_hardening() {
		$this->module_registry = new Hardening\ModuleRegistry( $this->settings );
		$this->module_registry->boot_all();
	}

	/**
	 * Initialize safe mode.
	 */
	private function init_safe_mode() {
		$this->safe_mode = new SafeMode\SafeMode( $this );
		$this->safe_mode->init();
	}

	/**
	 * Get settings.
	 *
	 * @return array
	 */
	public function get_settings() {
		$defaults = array(
			'onboarding_done'  => false,
			'level'            => 'recommended',
			'modules'          => array(
				'xmlrpc'            => true,
				'rest_api'          => true,
				'user_enumeration'  => true,
				'headers'           => true,
				'file_editor'       => true,
				'login_rate_limit'  => true,
			),
			'disabled_modules' => array(),
			'last_error'       => '',
		);

		$saved = get_option( 'wppys_settings', array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return wp_parse_args( $saved, $defaults );
	}

	/**
	 * Update settings.
	 *
	 * @param array $new_settings Settings.
	 * @return bool
	 */
	public function update_settings( $new_settings ) {
		$this->settings = $new_settings;
		return update_option( 'wppys_settings', $new_settings );
	}

	/**
	 * Get module registry.
	 *
	 * @return Hardening\ModuleRegistry
	 */
	public function get_module_registry() {
		return $this->module_registry;
	}

	/**
	 * Log message.
	 *
	 * @param string $message Message.
	 */
	public function log( $message ) {
		$logger = new Support\Logger();
		$logger->log( $message );
	}
}
