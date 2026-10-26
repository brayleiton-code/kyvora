<?php
/**
 * Options helper.
 *
 * @package WPProtectYourSite
 */

namespace WPProtectYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Options {

    /**
     * Get option with default.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public static function get( $key, $default = null ) {
        $settings = get_option( 'wppys_settings', array() );
        return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
    }

    /**
     * Set option.
     *
     * @param string $key
     * @param mixed  $value
     * @return bool
     */
    public static function set( $key, $value ) {
        $settings = get_option( 'wppys_settings', array() );
        $settings[ $key ] = $value;
        return update_option( 'wppys_settings', $settings );
    }

    /**
     * Update multiple options.
     *
     * @param array $data
     * @return bool
     */
    public static function update( $data ) {
        $settings = get_option( 'wppys_settings', array() );
        $settings = array_merge( $settings, $data );
        return update_option( 'wppys_settings', $settings );
    }

    /**
     * Get all settings.
     *
     * @return array
     */
    public static function get_all() {
        return get_option( 'wppys_settings', array() );
    }
}