<?php
/**
 * Safe Mode - detecta fatals en módulos y los desactiva automáticamente.
 *
 * @package WPProtectYourSite
 */

namespace WPProtectYourSite\SafeMode;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SafeMode {

    /**
     * Plugin instance.
     *
     * @var Plugin
     */
    private $plugin;

    /**
     * Current module being executed.
     *
     * @var string
     */
    private static $current_module = '';

    public function __construct( $plugin ) {
        $this->plugin = $plugin;
    }

    /**
     * Initialize safe mode.
     */
    public function init() {
        // Prioridad 0 = primero en plugins_loaded
        add_action( 'plugins_loaded', array( $this, 'register_shutdown' ), 0 );

        // También en shutdown para capturar fatals en cualquier momento
        register_shutdown_function( array( $this, 'handle_shutdown' ) );
    }

    /**
     * Register shutdown function.
     */
    public function register_shutdown() {
        register_shutdown_function( array( $this, 'handle_shutdown' ) );
    }

    /**
     * Handle shutdown - check for fatal errors.
     */
    public function handle_shutdown() {
        $error = error_get_last();

        if ( ! $error ) {
            return;
        }

        // Solo errores fatales
        $fatal_types = array( E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR );
        if ( ! in_array( $error['type'], $fatal_types, true ) ) {
            return;
        }

        // Verificar si el error es en nuestro plugin
        if ( ! $this->is_our_file( $error['file'] ) ) {
            return;
        }

        // Determinar qué módulo causó el error
        $module = $this->get_module_from_trace( $error );
        if ( ! $module ) {
            $module = self::$current_module;
        }

        if ( ! $module ) {
            return;
        }

        // Desactivar módulo
        $this->disable_module( $module, $error );
    }

    /**
     * Check if file belongs to our plugin.
     *
     * @param string $file
     * @return bool
     */
    private function is_our_file( $file ) {
        return strpos( $file, WPPYS_DIR ) === 0;
    }

    /**
     * Get module from error trace.
     *
     * @param array $error
     * @return string|null
     */
    private function get_module_from_trace( $error ) {
        if ( empty( $error['file'] ) ) {
            return null;
        }

        $modules = array(
            'xmlrpc'             => 'class-xmlrpc.php',
            'rest_api'           => 'class-rest-api.php',
            'user_enumeration'   => 'class-user-enumeration.php',
            'headers'            => 'class-headers.php',
            'file_editor'        => 'class-file-editor.php',
            'login_rate_limit'   => 'class-login-rate-limit.php',
        );

        foreach ( $modules as $key => $filename ) {
            if ( strpos( $error['file'], $filename ) !== false ) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Disable module.
     *
     * @param string $module
     * @param array  $error
     */
    private function disable_module( $module, $error ) {
        $settings = $this->plugin->get_settings();

        $disabled = $settings['disabled_modules'] ?? array();
        if ( ! in_array( $module, $disabled, true ) ) {
            $disabled[] = $module;
            $settings['disabled_modules'] = $disabled;
            update_option( 'wppys_settings', $settings );
        }

        // Log
        $message = sprintf(
            /* translators: 1: module name, 2: error message, 3: file path, 4: line number. */
            __( 'Safe Mode: Module "%1$s" disabled due to fatal error: %2$s in %3$s on line %4$d', 'kyvora' ),
            $module,
            $error['message'],
            $error['file'],
            $error['line']
        );

        $logger = new \WPProtectYourSite\Support\Logger();
        $logger->log( $message );

        // Admin notice para próximo request
        set_transient( 'wppys_safe_mode_notice', array(
            'module' => $module,
            'error'  => $error['message'],
            'file'   => $error['file'],
            'line'   => $error['line'],
        ), 300 );
    }

    /**
     * Set current module (called from Module::boot()).
     *
     * @param string $module
     */
    public static function set_current_module( $module ) {
        self::$current_module = $module;
    }

    /**
     * Get and clear safe mode notice.
     *
     * @return array|null
     */
    public static function get_notice() {
        $notice = get_transient( 'wppys_safe_mode_notice' );
        if ( $notice ) {
            delete_transient( 'wppys_safe_mode_notice' );
        }
        return $notice;
    }
}