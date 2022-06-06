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
        self::register_delete_prayer_library_endpoint();
        self::register_delete_prayer_point_endpoint();
        self::register_get_prayer_points_endpoint();    
    }

    private function get_namespace() {
        return 'pray4movement-prayer-points/v1';
    }

    private function register_delete_prayer_library_endpoint() {
        register_rest_route(
            $this->get_namespace(), '/delete_prayer_library/(?P<library_id>\d+)', [
                'methods'  => 'POST',
                'callback' => [ $this, 'endpoint_for_delete_prayer_library' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return $this->has_permission();
                },
            ]
        );
    }

    public function endpoint_for_delete_prayer_library( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['library_id'] ) ) {
            self::delete_prayer_points_in_library( $params['library_id'] );
            self::delete_prayer_library( $params['library_id'] );
            return true;
        }
    }

    public function delete_prayer_points_in_library( $library_id ) {
        if ( isset( $library_id ) ) {
            $prayer_ids = self::get_prayer_ids_from_library_id( $library_id );
            foreach ( $prayer_ids as $prayer_id ) {
                self::delete_prayer_point( $prayer_id );
            }
            return true;
        }
    }

    private function delete_prayer_point( $prayer_id ) {
        self::delete_prayer_point_content( $prayer_id );
        self::delete_prayer_point_meta( $prayer_id );
    }

    private function delete_prayer_point_content( $prayer_id ) {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}dt_prayer_points` WHERE id = %d;", $prayer_id )
        );
    }

    public function delete_prayer_point_meta( $prayer_id ) {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE prayer_id = %d;", $prayer_id )
        );
    }

    public function delete_prayer_library( $library_id ) {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE id = %d;", $library_id
            )
        );
    }

    private function register_delete_prayer_point_endpoint() {
        register_rest_route(
            $this->get_namespace(), '/delete_prayer_point/(?P<prayer_id>\d+)', [
                'methods'  => 'POST',
                'callback' => [ $this, 'endpoint_for_delete_prayer_point' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return $this->has_permission();
                },
            ]
        );
    }

    private function register_get_prayer_points_endpoint() {
        register_rest_route(
            $this->get_namespace(), '/get_prayer_points/(?P<library_id>\d*[,\d+]*)', [
                'methods' => 'POST',
                'callback' => [ $this , 'endpoint_for_get_prayer_points' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return $this->has_permission();
                },
            ]
        );
    }

    

    public function endpoint_for_get_prayer_points ( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( !isset( $library_id ) ) {
            new WP_Error ( __METHOD__, 'Missing a valid prayer library id', [ 'status' => 400 ] );
        }
        $library_id = sanitize_text_field( wp_unslash( $request['library_id'] ) );
        $library_id = explode( ',', $library_id );
        global $wpdb;
        
        // One query to rule them all...
        $prayer_points = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    (SELECT meta_value FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'title' AND prayer_id = pp.id) AS 'title',
                    pp.content,
                    (SELECT meta_value FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'reference' AND prayer_id = pp.id) AS 'reference',
                    (SELECT meta_value FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'book' AND prayer_id = pp.id) AS 'book',
                    (SELECT meta_value FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'verse' AND prayer_id = pp.id) AS 'verse',
                    (SELECT GROUP_CONCAT(meta_value) FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'tags' AND prayer_id = pp.id) AS 'tags',
                    pp.status
                FROM `{$wpdb->prefix}dt_prayer_points` pp
                WHERE pp.library_id IN ( " . implode( ',', array_fill( 0, count( $library_id ), '%d' ) ) . " )
                ORDER BY pp.library_id ASC;", $library_id )
            , ARRAY_A );

            
        $library = self::get_prayer_library( $library_id );
        $library_people_group = $library['people_group'];
        $library_location = $library['location'];
        $replaced_prayer_points = [];

        foreach($prayer_points as $prayer_point) {
            $new_string = null;
            $new_string = str_replace( 'XXX', $library_people_group, $prayer_point);
            $new_string = str_replace( 'YYY', $library_location, $new_string);
            $replaced_prayer_points[] = $new_string;
        }

        return $replaced_prayer_points;
    }

    public function endpoint_for_delete_prayer_point( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['prayer_id'] ) ) {
            self::delete_prayer_point( $params['prayer_id'] );
        }
    }

    public function get_prayer_ids_from_library_id( $library_id ) {
        if ( !isset( $library_id ) ) {
            return new WP_Error( __METHOD__, 'Missing valid action parameters', [ 'status' => 400 ] );
        }
        global $wpdb;
        $prayer_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT id FROM `{$wpdb->prefix}dt_prayer_points` WHERE lib_id = %d;", $library_id 
            )
        );
        return $prayer_ids;
    }

    public function get_prayer_library( $library_id ) {
        if ( !isset( $library_id ) ) {
            return new WP_Error( __METHOD__, 'Missing valid action parameters', [ 'status' => 400 ] );
        }
        global $wpdb;
        $library = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE id = %d;", $library_id 
            ), ARRAY_A
        );
        return $library;
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
