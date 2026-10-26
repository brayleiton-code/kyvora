<?php
/**
 * Module registry - gestiona registro y boot de módulos de hardening.
 *
 * @package WPProtectYourSite
 */

namespace WPProtectYourSite\Hardening;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ModuleRegistry {

    /**
     * Plugin instance.
     *
     * @var Plugin
     */
    private $plugin;

    /**
     * Settings.
     *
     * @var array
     */
    private $settings;

    /**
     * Registered modules.
     *
     * @var Module[]
     */
    private $modules = array();

    /**
     * Module statuses for dashboard.
     *
     * @var array
     */
    private $statuses = array();

    /**
     * Constructor.
     *
     * @param Plan  $plan
     * @param array $settings
     */
    public function __construct( $settings ) {
        $this->settings = $settings;
        $this->register_modules();
    }

    /**
     * Register all available modules.
     */
    private function register_modules() {
        // Free modules
        $this->register( new XmlRpc() );
        $this->register( new RestApi() );
        $this->register( new UserEnumeration() );
        $this->register( new Headers() );
        $this->register( new FileEditor() );
        $this->register( new LoginRateLimit() );
    }

    /**
     * Register a module.
     *
     * @param Module $module
     */
    public function register( Module $module ) {
        $this->modules[ $module->key() ] = $module;
    }

    /**
     * Boot all enabled modules.
     */
    public function boot_all() {
        foreach ( $this->modules as $key => $module ) {
            if ( $this->should_boot( $module ) ) {
                try {
                    $module->boot();
                    $this->statuses[ $key ] = array(
                        'active'   => true,
                        'error'    => '',
                        'status'   => 'ok',
                    );
                } catch ( \Throwable $e ) {
                    $this->statuses[ $key ] = array(
                        'active' => false,
                        'error'  => $e->getMessage(),
                        'status' => 'error',
                    );
                    // Safe mode se encarga de desactivar si es fatal
                }
            } else {
                $this->statuses[ $key ] = array(
                    'active' => false,
                    'error'  => '',
                    'status' => 'disabled',
                );
            }
        }
    }

    /**
     * Check if module should boot.
     *
     * @param Module $module
     * @return bool
     */
    private function should_boot( Module $module ) {
        $key = $module->key();

        // Usuario lo apagó
        $modules = $this->settings['modules'] ?? array();
        if ( empty( $modules[ $key ] ) ) {
            return false;
        }

        // En disabled_modules (safe mode)
        $disabled = $this->settings['disabled_modules'] ?? array();
        if ( in_array( $key, $disabled, true ) ) {
            return false;
        }

        return true;
    }

    /**
     * Get all module statuses.
     *
     * @return array
     */
    public function get_statuses() {
        return $this->statuses;
    }

    /**
     * Get module by key.
     *
     * @param string $key
     * @return Module|null
     */
    public function get_module( $key ) {
        return isset( $this->modules[ $key ] ) ? $this->modules[ $key ] : null;
    }

    /**
     * Calculate security score (0-100).
     *
     * @return int
     */
    public function calculate_score() {
        $total   = 0;
        $enabled = 0;

        foreach ( $this->modules as $module ) {
            $total++;
            if ( $this->should_boot( $module ) ) {
                $enabled++;
            }
        }

        if ( 0 === $total ) {
            return 0;
        }

        return (int) round( ( $enabled / $total ) * 100 );
    }

    /**
     * Get protection level label.
     *
     * @return string
     */
    public function get_level_label() {
        $score = $this->calculate_score();

        if ( $score >= 80 ) {
            return __( 'Protected', 'kyvora' );
        } elseif ( $score >= 50 ) {
            return __( 'Partial', 'kyvora' );
        }
        return __( 'At risk', 'kyvora' );
    }

    /**
     * Get protection level color.
     *
     * @return string
     */
    public function get_level_color() {
        $score = $this->calculate_score();

        if ( $score >= 80 ) {
            return 'green';
        } elseif ( $score >= 50 ) {
            return 'orange';
        }
        return 'red';
    }

    /**
     * Get required feature for a module key.
     *
     * @param string $key
     * @return string
     */
    public static function get_module_feature( $key ) {
        $map = array(
            'xmlrpc'             => 'hardening.base',
            'rest_api'           => 'hardening.base',
            'user_enumeration'   => 'hardening.base',
            'headers'            => 'hardening.base',
            'file_editor'        => 'hardening.base',
            'login_rate_limit'   => 'hardening.rate_limit',
            'ip_whitelist'       => 'hardening.ip_whitelist',
        );
        return isset( $map[ $key ] ) ? $map[ $key ] : 'hardening.base';
    }
}