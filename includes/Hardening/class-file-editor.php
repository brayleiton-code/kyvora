<?php
/**
 * File editor disable module.
 *
 * @package WPProtectYourSite
 */

namespace WPProtectYourSite\Hardening;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FileEditor implements Module {

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
        return 'file_editor';
    }

    public function title() {
        return __( 'File Editor Disable', 'kyvora' );
    }

    public function description() {
        return __( 'Deny edit_themes/edit_plugins caps and remove Editor menu if DISALLOW_FILE_EDIT not in wp-config.', 'kyvora' );
    }

    public function required_feature() {
        return 'hardening.base';
    }

    public function boot() {
        // Solo actuar si DISALLOW_FILE_EDIT no está definido
        if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) {
            return;
        }

        // Denegar capacidades
        add_filter( 'map_meta_cap', array( $this, 'deny_file_edit_caps' ), 10, 4 );

        // Quitar menú Editor
        add_action( 'admin_menu', array( $this, 'remove_editor_menu' ), 999 );
    }

    /**
     * Deny edit_themes and edit_plugins capabilities.
     *
     * @param array  $caps
     * @param string $cap
     * @param int    $user_id
     * @param array  $args
     * @return array
     */
    public function deny_file_edit_caps( $caps, $cap, $user_id, $args ) {
        if ( in_array( $cap, array( 'edit_themes', 'edit_plugins' ), true ) ) {
            $caps[] = 'do_not_allow';
        }
        return $caps;
    }

    /**
     * Remove Editor menu.
     */
    public function remove_editor_menu() {
        remove_submenu_page( 'themes.php', 'theme-editor.php' );
        remove_submenu_page( 'plugins.php', 'plugin-editor.php' );
    }

    public function is_enabled() {
        $modules = $this->plugin->get_settings()[ 'modules' ] ?? array();
        return ! empty( $modules[ $this->key() ] );
    }

    public function get_status() {
        $defined = defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT;
        return array(
            'active'       => $this->is_enabled(),
            'wp_config'    => $defined,
            'note'         => $defined ? __( 'Already defined in wp-config.php', 'kyvora' ) : '',
        );
    }
}