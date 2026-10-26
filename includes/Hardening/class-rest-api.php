<?php
/**
 * REST API hardening module.
 *
 * @package WPProtectYourSite
 */

namespace WPProtectYourSite\Hardening;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RestApi implements Module {

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
        return 'rest_api';
    }

    public function title() {
        return __( 'REST API Protection', 'kyvora' );
    }

    public function description() {
        return __( 'Restrict /wp/v2/users to authenticated users. Paranoid mode requires auth for all REST.', 'kyvora' );
    }

    public function required_feature() {
        return 'hardening.base';
    }

    public function boot() {
        $level = $this->plugin->get_settings()[ 'level' ] ?? 'recommended';

        // Recomendado: solo /wp/v2/users requiere auth
        if ( in_array( $level, array( 'recommended', 'paranoid' ), true ) ) {
            add_filter( 'rest_endpoints', array( $this, 'restrict_users_endpoint' ) );
        }

        // Paranoid: todo REST requiere auth (con excepciones)
        if ( 'paranoid' === $level ) {
            add_filter( 'rest_authentication_errors', array( $this, 'require_auth_for_all' ) );
        }
    }

    /**
     * Restrict /wp/v2/users endpoint to authenticated users.
     *
     * @param array $endpoints
     * @return array
     */
    public function restrict_users_endpoint( $endpoints ) {
        foreach ( $endpoints as $route => $handlers ) {
            if ( preg_match( '#/wp/v2/users#', $route ) ) {
                foreach ( $handlers as $handler ) {
                    if ( isset( $handler['permission_callback'] ) ) {
                        $original = $handler['permission_callback'];
                        $handler['permission_callback'] = function( $request ) use ( $original ) {
                            if ( is_user_logged_in() ) {
                                return true;
                            }
                            if ( is_callable( $original ) ) {
                                return $original( $request );
                            }
                            return new \WP_Error( 'rest_forbidden', __( 'User data is private.', 'kyvora' ), array( 'status' => 403 ) );
                        };
                    }
                }
            }
        }
        return $endpoints;
    }

    /**
     * Require authentication for all REST endpoints (paranoid).
     *
     * @param \WP_Error|null $error
     * @return \WP_Error|null
     */
    public function require_auth_for_all( $error ) {
        if ( ! empty( $error ) ) {
            return $error;
        }

        // Permitir endpoints públicos conocidos
        $public_endpoints = array(
            'oembed',
            'contact-form-7',
            'wp/v2/types',
            'wp/v2/statuses',
            'wp/v2/taxonomies',
            'wp/v2/pages',
            'wp/v2/posts',
            'wp/v2/media',
            'wp/v2/comments',
            'wp/v2/search',
        );

        $request = isset( $GLOBALS['wp_rest_request'] ) ? $GLOBALS['wp_rest_request'] : null;
        if ( $request ) {
            $route = $request->get_route();
            foreach ( $public_endpoints as $public ) {
                if ( strpos( $route, $public ) !== false ) {
                    return null;
                }
            }

            // Filtro para permitir excepciones
            $allowed = apply_filters( 'wppys_rest_allow', false, $request );
            if ( $allowed ) {
                return null;
            }
        }

        if ( ! is_user_logged_in() ) {
            return new \WP_Error( 'rest_forbidden', __( 'REST API requires authentication.', 'kyvora' ), array( 'status' => 403 ) );
        }

        return null;
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