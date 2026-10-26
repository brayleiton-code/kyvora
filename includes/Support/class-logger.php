<?php
/**
 * Logger - escribe logs en wp-content/uploads/wppys-logs/.
 *
 * @package WPProtectYourSite
 */

namespace WPProtectYourSite\Support;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Logger {

    /**
     * Log directory.
     *
     * @var string
     */
    private $log_dir;

    /**
     * Constructor.
     */
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->log_dir = $upload_dir['basedir'] . '/wppys-logs';

        if ( ! is_dir( $this->log_dir ) ) {
            wp_mkdir_p( $this->log_dir );
            $this->protect_log_dir();
        }
    }

    /**
     * Protect log directory with .htaccess and index.php.
     */
    private function protect_log_dir() {
        // .htaccess
        $htaccess = $this->log_dir . '/.htaccess';
        if ( ! file_exists( $htaccess ) ) {
            $content = "Order deny,allow\nDeny from all\n";
            @file_put_contents( $htaccess, $content );
        }

        // index.php
        $index = $this->log_dir . '/index.php';
        if ( ! file_exists( $index ) ) {
            $content = "<?php\n// Silence is golden.\n";
            @file_put_contents( $index, $content );
        }
    }

    /**
     * Get log file path for today.
     *
     * @return string
     */
    public function get_log_file() {
        $date = gmdate( 'Y-m-d' );
        return $this->log_dir . "/wppys-{$date}.log";
    }

    /**
     * Log message.
     *
     * @param string $message
     * @param string $level
     */
    public function log( $message, $level = 'info' ) {
        $file = $this->get_log_file();
        $timestamp = gmdate( 'c' );
        $line = "[{$timestamp}] [{$level}] {$message}\n";

        @file_put_contents( $file, $line, FILE_APPEND | LOCK_EX );
    }

    /**
     * Get recent logs.
     *
     * @param int $days
     * @return array
     */
    public function get_recent( $days = 7 ) {
        $logs = array();
        for ( $i = 0; $i < $days; $i++ ) {
            $date = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
            $file = $this->log_dir . "/wppys-{$date}.log";
            if ( file_exists( $file ) ) {
                $logs[ $date ] = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
            }
        }
        return $logs;
    }
}