<?php
/**
 * XML-RPC hardening module.
 *
 * @package WPProtectYourSite
 */

namespace WPProtectYourSite\Hardening;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class XmlRpc implements Module {

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
        return 'xmlrpc';
    }

    public function title() {
        return __( 'XML-RPC Protection', 'kyvora' );
    }

    public function description() {
        return __( 'Disable XML-RPC pingbacks and block xmlrpc.php access in paranoid mode.', 'kyvora' );
    }

    public function required_feature() {
        return 'hardening.base';
    }

    public function boot() {
        $level = $this->plugin->get_settings()[ 'level' ] ?? 'recommended';

        // Esencial / Recomendado: quitar pingback methods
        add_filter( 'xmlrpc_methods', array( $this, 'remove_pingback_methods' ) );

        // Paranoico: desactivar xmlrpc completamente
        if ( 'paranoid' === $level ) {
            add_filter( 'xmlrpc_enabled', '__return_false' );
            add_action( 'xmlrpc.php', array( $this, 'block_xmlrpc_access' ), 0 );
        }
    }

    /**
     * Remove pingback methods from XML-RPC.
     *
     * @param array $methods
     * @return array
     */
    public function remove_pingback_methods( $methods ) {
        unset( $methods['pingback.ping'] );
        unset( $methods['pingback.extensions.getPingbacks'] );
        return $methods;
    }

    /**
     * Block access to xmlrpc.php.
     */
    public function block_xmlrpc_access() {
        if ( 'xmlrpc.php' === basename( $_SERVER['SCRIPT_FILENAME'] ?? '' ) ) {
            status_header( 403 );
            wp_die( esc_html__( 'XML-RPC is disabled.', 'kyvora' ), '', array( 'response' => 403 ) );
        }
    }

    public function is_enabled() {
        $modules = $this->plugin->get_settings()[ 'modules' ] ?? array();
        return ! empty( $modules[ $this->key() ] );
    }

    public function get_status() {
        $level = $this->plugin->get_settings()[ 'level' ] ?? 'recommended';
        return array(
            'active'    => $this->is_enabled(),
            'level'     => $level,
            'paranoid'  => 'paranoid' === $level,
        );
    }
}