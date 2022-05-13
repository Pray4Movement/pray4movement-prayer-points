<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Pray4Movement_Prayer_Points_Endpoints
{
    /**
     * @todo Set the permissions your endpoint needs
     * @link https://github.com/DiscipleTools/Documentation/blob/master/theme-core/capabilities.md
     * @var string[]
     */
    public $permissions = [ 'access_contacts', 'dt_all_access_contacts', 'view_project_metrics' ];


    /**
     * @todo define the name of the $namespace
     * @todo define the name of the rest route
     * @todo defne method (CREATABLE, READABLE)
     * @todo apply permission strategy. '__return_true' essentially skips the permission check.
     */
    //See https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
    public function add_api_routes() {
        $namespace = 'pray4movement-prayer-points/v1';

        register_rest_route(
            $namespace, '/delete_prayer_library/(?P<lib_id>\d+)', [
                'methods'  => "POST",
                'callback' => [ $this, 'endpoint_delete_prayer_lib' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return $this->has_permission();
                },
            ]
        );
    }


    public function endpoint_delete_prayer_lib( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( !isset( $params['lib_id'] ) ) {
            return new WP_Error( __METHOD__, 'Missing a valid prayer library id', [ 'status' => 400 ] );
        }
        $lib_id = esc_sql( $params['lib_id'] );
        $current_user_id = get_current_user_id();
        // todo define can_delete
        // if ( !Pray4Movement_Prayer_Points::can_delete( 'libraries', $current_user_id = get_current_user_id() ) ) {
        //     return new WP_Error( __METHOD__, 'You do not have permission for this', [ 'status' => 403 ] );
        // }
        self::delete_prayer_lib( $lib_id );
        self::delete_prayer_points_by_lib( $lib_id );
        return true;
    }

    private function delete_prayer_lib( $lib_id ) {
        if ( !isset( $lib_id ) ) {
            return new WP_Error( __METHOD__, 'Missing valid action parameters', [ 'status' => 400 ] );
        }
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE id = %s;", $lib_id
            )
        );
        return true;
    }

    private function delete_prayer_points_by_lib( $lib_id ) {
        if ( !isset( $lib_id ) ) {
            return new WP_Error( __METHOD__, 'Missing valid action parameters', [ 'status' => 400 ] );
        }
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM `{$wpdb->prefix}dt_prayer_points` WHERE lib_id = %d;", $lib_id
            )
        );
        return true;
    }

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }
    public function has_permission(){
        $pass = false;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }
}
Pray4Movement_Prayer_Points_Endpoints::instance();
