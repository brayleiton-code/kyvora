<?php
/**
 * Two-step setup wizard. No license step.
 *
 * @package WPPYSHardening
 */

namespace WPProtectYourSite\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Onboarding {

	/**
	 * Plugin instance.
	 *
	 * @var \WPProtectYourSite\Plugin
	 */
	private $plugin;

	/**
	 * Constructor.
	 *
	 * @param \WPProtectYourSite\Plugin $plugin Plugin.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Whether the wizard still needs to run.
	 *
	 * @return bool
	 */
	public function is_needed() {
		$settings = $this->plugin->get_settings();
		return empty( $settings['onboarding_done'] );
	}

	/**
	 * Render the wizard.
	 */
	public function render() {
		if ( isset( $_POST['wppys_onboarding_step'] ) ) {
			$this->handle_step( intval( $_POST['wppys_onboarding_step'] ) );
		}

		$settings = $this->plugin->get_settings();
		$level    = isset( $settings['level'] ) ? $settings['level'] : 'recommended';
		$step     = isset( $_GET['wppys_onboarding'] ) ? intval( $_GET['wppys_onboarding'] ) : 1;
		$step     = max( 1, min( 2, $step ) );

		include WPPYS_DIR . 'includes/Admin/views/onboarding.php';
	}

	/**
	 * Handle a wizard step.
	 *
	 * @param int $step Step number.
	 */
	private function handle_step( $step ) {
		check_admin_referer( 'wppys_onboarding_step_' . $step );

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = $this->plugin->get_settings();

		if ( 1 === $step ) {
			$level = isset( $_POST['level'] ) ? sanitize_text_field( wp_unslash( $_POST['level'] ) ) : 'recommended';
			if ( in_array( $level, array( 'essential', 'recommended', 'paranoid' ), true ) ) {
				$settings['level']   = $level;
				$settings['modules'] = $this->get_default_modules( $level );
			}
		} elseif ( 2 === $step ) {
			$settings['onboarding_done'] = true;
		}

		$this->plugin->update_settings( $settings );

		if ( $step < 2 ) {
			wp_safe_redirect( admin_url( 'admin.php?page=kyvora&wppys_onboarding=' . ( $step + 1 ) ) );
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=kyvora' ) );
		}
		exit;
	}

	/**
	 * Default modules for a protection level.
	 *
	 * @param string $level Level slug.
	 * @return array
	 */
	private function get_default_modules( $level ) {
		$modules = array(
			'xmlrpc'           => true,
			'rest_api'         => false,
			'user_enumeration' => false,
			'headers'          => true,
			'file_editor'      => false,
			'login_rate_limit' => true,
		);

		if ( in_array( $level, array( 'recommended', 'paranoid' ), true ) ) {
			$modules['rest_api']         = true;
			$modules['user_enumeration'] = true;
			$modules['file_editor']      = true;
		}

		return $modules;
	}
}
