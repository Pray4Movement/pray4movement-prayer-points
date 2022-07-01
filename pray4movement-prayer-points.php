<?php
/**
 * Plugin Name: Disciple.Tools - Pray4Movement Prayer Points
 * Plugin URI: https://github.com/DiscipleTools/pray4movement-prayer-points
 * Description: Disciple.Tools - Pray4Movement Prayer Points is intended to help developers and integrator jumpstart their extension of the Disciple.Tools system.
 * Text Domain: pray4movement-prayer-points
 * Domain Path: /languages
 * Version:  0.1
 * Author URI: https://github.com/DiscipleTools
 * GitHub Plugin URI: https://github.com/DiscipleTools/pray4movement-prayer-points
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 5.6
 *
 * @package Disciple_Tools
 * @link    https://github.com/DiscipleTools
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Gets the instance of the `Pray4Movement_Prayer_Points` class.
 *
 * @since  0.1
 * @access public
 * @return object|bool
 */
function pray4movement_prayer_points() {
    return Pray4Movement_Prayer_Points::instance();
}

add_action( 'after_setup_theme', 'pray4movement_prayer_points', 20 );

/**
 * Singleton class for setting up the plugin.
 *
 * @since  0.1
 * @access public
 */
class Pray4Movement_Prayer_Points {

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        require_once( 'rest-api/rest-api.php' );

        if ( is_admin() ) {
            require_once( 'admin/admin-menu-and-tabs.php' );
        }

        $this->i18n();

        if ( is_admin() ) {
            add_filter( 'plugin_row_meta', [ $this, 'plugin_description_links' ], 10, 4 );
        }
    }

    public function plugin_description_links( $links_array, $plugin_file_name, $plugin_data, $status ) {
        if ( strpos( $plugin_file_name, basename( __FILE__ ) ) ) {
            // You can still use `array_unshift()` to add links at the beginning.

            $links_array[] = '<a href="https://disciple.tools">Disciple.Tools Community</a>'; // @todo replace with your links.
            // @todo add other links here
        }

        return $links_array;
    }

    public static function activation() {
        self::create_prayer_points_table_if_not_exist();
        self::create_prayer_points_library_table_if_not_exist();
        self::create_prayer_points_meta_table_if_not_exist();
    }

    private static function create_prayer_points_table_if_not_exist() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $test = $wpdb->query(
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dt_prayer_points` (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `library_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
                `title` LONGTEXT COLLATE utf8mb4_unicode_520_ci NOT NULL,
                `content` LONGTEXT COLLATE utf8mb4_unicode_520_ci NOT NULL,
                `reference` VARCHAR(100) COLLATE utf8mb4_unicode_520_ci NULL,
                `book` VARCHAR(50) COLLATE utf8mb4_unicode_520_ci NULL,
                `verse` VARCHAR(50) COLLATE utf8mb4_unicode_520_ci NULL,
                `hash` VARCHAR(65) DEFAULT NULL,
                `status` VARCHAR(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'unpublished',
                PRIMARY KEY (`id`)
            ) $charset_collate;" //@phpcs:ignore
        );
        if ( !$test ) {
            throw new Exception( 'Could not create table dt_prayer_points' );
        }
    }

    private static function create_prayer_points_library_table_if_not_exist() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $test = $wpdb->query(
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dt_prayer_points_lib` (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `key` VARCHAR(255) NOT NULL,
                `name` VARCHAR(191) NOT NULL,
                `description` LONGTEXT DEFAULT NULL,
                `icon` LONGTEXT COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
                `language` VARCHAR(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'en',
                `status` VARCHAR(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'unpublished',
                `last_updated` TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
                PRIMARY KEY (`id`)
            ) $charset_collate;" //@phpcs:ignore
        );
        if ( !$test ) {
            throw new Exception( 'Could not create table dt_prayer_points_lib' );
        }
    }

    private static function create_prayer_points_meta_table_if_not_exist() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $test = $wpdb->query(
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dt_prayer_points_meta` (
                `meta_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `prayer_id` BIGINT(20) UNSIGNED DEFAULT NULL,
                `meta_key` varchar(255) DEFAULT NULL,
                `meta_value` LONGTEXT,
                PRIMARY KEY (`meta_id`)
            ) $charset_collate;" //@phpcs:ignore
        );
        if ( !$test ) {
            throw new Exception( 'Could not create table dt_prayer_points_meta' );
        }
    }

    public static function deactivation() {
        delete_option( 'dismissed-pray4movement-prayer-points' );
    }

    public function i18n() {
        $domain = 'pray4movement-prayer-points';
        load_plugin_textdomain( $domain, false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ). 'languages' );
    }

    public function __toString() {
        return 'pray4movement-prayer-points';
    }

    public function __clone() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }

    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }

    public function __call( $method = '', $args = array() ) {
        _doing_it_wrong( "pray4movement_prayer_points::" . esc_html( $method ), 'Method does not exist.', '0.1' );
        unset( $method, $args );
        return null;
    }
}

register_activation_hook( __FILE__, [ 'Pray4Movement_Prayer_Points', 'activation' ] );
register_deactivation_hook( __FILE__, [ 'Pray4Movement_Prayer_Points', 'deactivation' ] );

/**
 * AJAX handler to store the state of dismissible notices.
 */
if ( !function_exists( "dt_hook_ajax_notice_handler" ) ){
    function dt_hook_ajax_notice_handler(){
        check_ajax_referer( 'wp_rest_dismiss', 'security' );
        if ( isset( $_POST["type"] ) ){
            $type = sanitize_text_field( wp_unslash( $_POST["type"] ) );
            update_option( 'dismissed-' . $type, true );
        }
    }
}
require_once( 'functions/shortcodes.php' );

/**
 * Plugin Releases and updates
 * @todo Uncomment and change the url if you want to support remote plugin updating with new versions of your plugin
 * To remove: delete the section of code below and delete the file called version-control.json in the plugin root
 *
 * This section runs the remote plugin updating service, so you can issue distributed updates to your plugin
 *
 * @note See the instructions for version updating to understand the steps involved.
 * @link https://github.com/DiscipleTools/pray4movement-prayer-points/wiki/Configuring-Remote-Updating-System
 *
 * @todo Enable this section with your own hosted file
 * @todo An example of this file can be found in (version-control.json)
 * @todo Github is a good option for delivering static json.
 */
/**
 * Check for plugin updates even when the active theme is not Disciple.Tools
 *
 * Below is the publicly hosted .json file that carries the version information. This file can be hosted
 * anywhere as long as it is publicly accessible. You can download the version file listed below and use it as
 * a template.
 * Also, see the instructions for version updating to understand the steps involved.
 * @see https://github.com/DiscipleTools/disciple-tools-version-control/wiki/How-to-Update-the-Starter-Plugin
 */
//add_action( 'plugins_loaded', function (){
//    if ( is_admin() && !( is_multisite() && class_exists( "DT_Multisite" ) ) || wp_doing_cron() ){
//        // Check for plugin updates
//        if ( ! class_exists( 'Puc_v4_Factory' ) ) {
//            if ( file_exists( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' )){
//                require( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' );
//            }
//        }
//        if ( class_exists( 'Puc_v4_Factory' ) ){
//            Puc_v4_Factory::buildUpdateChecker(
//                'https://raw.githubusercontent.com/DiscipleTools/pray4movement-prayer-points/master/version-control.json',
//                __FILE__,
//                'pray4movement-prayer-points'
//            );
//
//        }
//    }
//} );
