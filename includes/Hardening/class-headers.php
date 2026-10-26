<?php
/**
 * Security headers module.
 *
 * @package WPProtectYourSite
 */

namespace WPProtectYourSite\Hardening;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Headers implements Module {

    /**
     * Plugin instance.
     *
     * @var Plugin
     */
    private $plugin;

    public function __construct() {
        $this->plugin = \WPProtectYourSite\Plugin::get_instance();
    }

    public function key() {
        return 'headers';
    }

    public function title() {
        return __( 'Security Headers', 'kyvora' );
    }

    public function description() {
        return __( 'Remove generator, wlwmanifest, RSD, shortlink. Add X-Content-Type-Options: nosniff.', 'kyvora' );
    }

    public function required_feature() {
        return 'hardening.base';
    }

    public function boot() {
        // Eliminar generator meta tag
        remove_action( 'wp_head', 'wp_generator' );

        // Eliminar wlwmanifest
        remove_action( 'wp_head', 'wlwmanifest_link' );

        // Eliminar RSD
        remove_action( 'wp_head', 'rsd_link' );

        // Eliminar shortlink
        remove_action( 'wp_head', 'wp_shortlink_wp_head' );

        // Añadir X-Content-Type-Options: nosniff si no existe
        add_action( 'send_headers', array( $this, 'add_nosniff_header' ) );
    }

    /**
     * Add X-Content-Type-Options header.
     */
    public function add_nosniff_header() {
        if ( ! headers_sent() && ! isset( $_SERVER['HTTP_X_CONTENT_TYPE_OPTIONS'] ) ) {
            header( 'X-Content-Type-Options: nosniff' );
        }
    }

    public function is_enabled() {
        $modules = $this->plugin->get_settings()[ 'modules' ] ?? array();
        return ! empty( $modules[ $this->key() ] );
    }

    public function get_status() {
        return array(
            'active' => $this->is_enabled(),
        );
    }
}