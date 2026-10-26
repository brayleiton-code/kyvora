<?php
/**
 * Login rate limit module.
 *
 * @package WPProtectYourSite
 */

namespace WPProtectYourSite\Hardening;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LoginRateLimit implements Module {

    /**
     * Plugin instance.
     *
     * @var Plugin
     */
    private $plugin;

    /**
     * Transient prefix.
     */
    const TRANSIENT_PREFIX = 'wppys_rl_';

    /**
     * Max attempts.
     */
    const MAX_ATTEMPTS = 5;

    /**
     * Lockout duration (seconds).
     */
    const LOCKOUT_DURATION = 15 * MINUTE_IN_SECONDS;

    /**
     * Window duration (seconds).
     */
    const WINDOW_DURATION = 15 * MINUTE_IN_SECONDS;

    public function __construct() {
        $this->plugin = \WPProtectYourSite\Plugin::get_instance();
    }

    public function key() {
        return 'login_rate_limit';
    }

    public function title() {
        return __( 'Login Rate Limit', 'kyvora' );
    }

    public function description() {
        return __( 'Block IPs after 5 failed attempts in 15 minutes. 15 minute lockout. Also protects XML-RPC login.', 'kyvora' );
    }

    public function required_feature() {
        return 'hardening.rate_limit';
    }

    public function boot() {
        // Filter authenticate (prioridad 30 = antes que wp_authenticate_username_password)
        add_filter( 'authenticate', array( $this, 'check_rate_limit' ), 30, 3 );

        // Track failed logins
        add_action( 'wp_login_failed', array( $this, 'login_failed' ) );

        // XML-RPC login protection
        add_filter( 'xmlrpc_login_error', array( $this, 'xmlrpc_login_error' ) );
    }

    /**
     * Check rate limit on login.
     *
     * @param \WP_User|\WP_Error|null $user
     * @param string                  $username
     * @param string                  $password
     * @return \WP_User|\WP_Error|null
     */
    public function check_rate_limit( $user, $username, $password ) {
        // Si ya hay error, no procesar
        if ( is_wp_error( $user ) ) {
            return $user;
        }

        $ip = $this->get_client_ip();
        $key = $this->transient_key( $ip );

        $data = get_transient( $key );

        if ( false !== $data ) {
            $attempts = $data['attempts'] ?? 0;
            $locked_until = $data['locked_until'] ?? 0;

            // Verificar si está en lockout
            if ( $locked_until > time() ) {
                $remaining = $locked_until - time();
                $minutes = ceil( $remaining / 60 );
                return new \WP_Error(
                    'wppys_rate_limited',
                    sprintf(
                        /* translators: %d: number of minutes. */
                        __( 'Too many failed attempts. Try again in %d minutes.', 'kyvora' ),
                        $minutes
                    )
                );
            }

            // Verificar si excedió intentos (primer lockout)
            if ( $attempts >= self::MAX_ATTEMPTS && empty( $data['locked_until'] ) ) {
                $data['locked_until'] = time() + self::LOCKOUT_DURATION;
                set_transient( $key, $data, self::LOCKOUT_DURATION );

                return new \WP_Error(
                    'wppys_rate_limited',
                    sprintf(
                        /* translators: %d: number of minutes. */
                        __( 'Too many failed attempts. Try again in %d minutes.', 'kyvora' ),
                        15
                    )
                );
            }
        }

        return $user;
    }

    /**
     * Handle failed login attempt.
     */
    public function login_failed( $username ) {
        $ip = $this->get_client_ip();
        $key = $this->transient_key( $ip );

        $data = get_transient( $key );
        if ( false === $data ) {
            $data = array( 'attempts' => 0, 'locked_until' => 0 );
        }

        $data['attempts'] = ( $data['attempts'] ?? 0 ) + 1;
        set_transient( $key, $data, self::WINDOW_DURATION );
    }

    /**
     * XML-RPC login error handler.
     *
     * @param \WP_Error $error
     * @return \WP_Error
     */
    public function xmlrpc_login_error( $error ) {
        // El rate limit ya se maneja en authenticate filter
        return $error;
    }

    /**
     * Get client IP (supports Cloudflare).
     *
     * @return string
     */
    private function transient_key( $ip ) {
        return self::TRANSIENT_PREFIX . md5( $ip );
    }

    /**
     * Client IP from REMOTE_ADDR only.
     *
     * Forwarded headers are attacker-controlled and must not create transients.
     *
     * @return string
     */
    private function get_client_ip() {
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
        if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return 'invalid';
        }
        return $ip;
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