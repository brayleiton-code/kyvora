<?php
/**
 * Module interface.
 *
 * @package WPProtectYourSite
 */

namespace WPProtectYourSite\Hardening;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

interface Module {

    /**
     * Unique key for this module.
     *
     * @return string
     */
    public function key();

    /**
     * Human-readable title.
     *
     * @return string
     */
    public function title();

    /**
     * Description.
     *
     * @return string
     */
    public function description();

    /**
     * Required feature key.
     *
     * @return string
     */
    public function required_feature();

    /**
     * Boot the module (add hooks, etc.).
     */
    public function boot();

    /**
     * Check if module is enabled in settings.
     *
     * @return bool
     */
    public function is_enabled();

    /**
     * Get module status for dashboard.
     *
     * @return array
     */
    public function get_status();
}