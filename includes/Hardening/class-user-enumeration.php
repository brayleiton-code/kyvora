<?php
/**
 * User enumeration protection module.
 *
 * @package WPProtectYourSite
 */

namespace WPProtectYourSite\Hardening;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class UserEnumeration implements Module {

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
        return 'user_enumeration';
    }

    public function title() {
        return __( 'User Enumeration Block', 'kyvora' );
    }

    public function description() {
        return __( 'Block ?author=N enumeration and use generic login errors.', 'kyvora' );
    }

    public function required_feature() {
        return 'hardening.base';
    }

    public function boot() {
        // Bloquear ?author=N para usuarios anónimos
        add_action( 'template_redirect', array( $this, 'block_author_archives' ) );

        // Errores de login genéricos
        add_filter( 'login_errors', array( $this, 'generic_login_errors' ) );
    }

    /**
     * Block author archives for non-logged-in users.
     */
    public function block_author_archives() {
        if ( is_author() && ! is_user_logged_in() ) {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            nocache_headers();
        }
    }

    /**
     * Generic login errors.
     *
     * @param string $error
     * @return string
     */
    public function generic_login_errors( $error ) {
        return __( 'Invalid username or password.', 'kyvora' );
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