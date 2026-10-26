<?php
/**
 * Admin menus and settings.
 *
 * @package WPPYSHardening
 */

namespace WPProtectYourSite\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {

	/**
	 * Plugin instance.
	 *
	 * @var \WPProtectYourSite\Plugin
	 */
	private $plugin;

	/**
	 * Page slug.
	 */
	const PAGE_SLUG = 'kyvora';

	/**
	 * Constructor.
	 *
	 * @param \WPProtectYourSite\Plugin $plugin Plugin.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Initialize admin.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_wppys_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_wppys_reset_onboarding', array( $this, 'ajax_reset_onboarding' ) );
	}

	/**
	 * Add admin menu.
	 */
	public function add_menu() {
		add_menu_page(
			__( 'Kyvora', 'kyvora' ),
			__( 'Kyvora', 'kyvora' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_dashboard' ),
			'dashicons-shield',
			80
		);
	}

	/**
	 * Enqueue assets only on this plugin screen.
	 *
	 * @param string $hook Current admin hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wppys-admin', WPPYS_URL . 'assets/css/admin.css', array(), WPPYS_VERSION );
		wp_enqueue_script( 'wppys-admin', WPPYS_URL . 'assets/js/admin.js', array( 'jquery' ), WPPYS_VERSION, true );

		wp_localize_script(
			'wppys-admin',
			'wppysAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wppys_admin' ),
				'strings' => array(
					'saving' => __( 'Saving...', 'kyvora' ),
					'saved'  => __( 'Saved!', 'kyvora' ),
					'error'  => __( 'Error', 'kyvora' ),
				),
			)
		);
	}

	/**
	 * Render dashboard or the setup wizard.
	 */
	public function render_dashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$onboarding = new Onboarding( $this->plugin );
		if ( $onboarding->is_needed() ) {
			$onboarding->render();
			return;
		}

		include WPPYS_DIR . 'includes/Admin/views/dashboard.php';
	}

	/**
	 * AJAX: Save settings.
	 */
	public function ajax_save_settings() {
		check_ajax_referer( 'wppys_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Permission denied.', 'kyvora' ) );
		}

		$settings = $this->plugin->get_settings();
		$modules  = array(
			'xmlrpc',
			'rest_api',
			'user_enumeration',
			'headers',
			'file_editor',
			'login_rate_limit',
		);

		foreach ( $modules as $module ) {
			$settings['modules'][ $module ] = isset( $_POST[ 'module_' . $module ] );
		}

		if ( isset( $_POST['level'] ) && in_array( $_POST['level'], array( 'essential', 'recommended', 'paranoid' ), true ) ) {
			$settings['level'] = sanitize_text_field( wp_unslash( $_POST['level'] ) );
		}

		$this->plugin->update_settings( $settings );
		wp_send_json_success( array( 'message' => esc_html__( 'Settings saved.', 'kyvora' ) ) );
	}

	/**
	 * AJAX: Reset onboarding.
	 */
	public function ajax_reset_onboarding() {
		check_ajax_referer( 'wppys_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Permission denied.', 'kyvora' ) );
		}

		$settings                      = $this->plugin->get_settings();
		$settings['onboarding_done']   = false;
		$this->plugin->update_settings( $settings );

		wp_send_json_success( array( 'message' => esc_html__( 'Onboarding reset.', 'kyvora' ) ) );
	}
}
