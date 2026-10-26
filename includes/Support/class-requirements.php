<?php
/**
 * Requirements checker.
 *
 * @package WPProtectYourSite
 */

namespace WPProtectYourSite\Support;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Requirements {

    /**
     * Check if requirements are met.
     *
     * @return array
     */
    public static function check() {
        $results = array(
            'php'     => self::check_php(),
            'wp'      => self::check_wp(),
            'curl'    => self::check_curl(),
            'json'    => self::check_json(),
            'openssl' => self::check_openssl(),
        );

        $results['ok'] = true;
        foreach ( $results as $check ) {
            if ( is_array( $check ) && ! $check['ok'] ) {
                $results['ok'] = false;
                break;
            }
        }

        return $results;
    }

    /**
     * Check PHP version.
     *
     * @return array
     */
    private static function check_php() {
        $version = PHP_VERSION;
        $ok = version_compare( $version, '7.4', '>=' );

        return array(
            'ok'        => $ok,
            'version'   => $version,
            'required'  => '7.4',
            'message'   => $ok ? '' : sprintf( /* translators: %s: current PHP version. */ __( 'PHP 7.4+ required. Current: %s', 'kyvora' ), $version ),
        );
    }

    /**
     * Check WordPress version.
     *
     * @return array
     */
    private static function check_wp() {
        global $wp_version;
        $ok = version_compare( $wp_version, '5.8', '>=' );

        return array(
            'ok'        => $ok,
            'version'   => $wp_version,
            'required'  => '5.8',
            'message'   => $ok ? '' : sprintf( /* translators: %s: current WordPress version. */ __( 'WordPress 5.8+ required. Current: %s', 'kyvora' ), $wp_version ),
        );
    }

    /**
     * Check cURL extension.
     *
     * @return array
     */
    private static function check_curl() {
        $ok = extension_loaded( 'curl' );

        return array(
            'ok'      => $ok,
            'message' => $ok ? '' : __( 'cURL extension is required.', 'kyvora' ),
        );
    }

    /**
     * Check JSON extension.
     *
     * @return array
     */
    private static function check_json() {
        $ok = extension_loaded( 'json' );

        return array(
            'ok'      => $ok,
            'message' => $ok ? '' : __( 'JSON extension is required.', 'kyvora' ),
        );
    }

    /**
     * Check OpenSSL extension.
     *
     * @return array
     */
    private static function check_openssl() {
        $ok = extension_loaded( 'openssl' );

        return array(
            'ok'      => $ok,
            'message' => $ok ? '' : __( 'OpenSSL extension is required.', 'kyvora' ),
        );
    }
}