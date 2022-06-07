<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Pray4Movement_Prayer_Points_Endpoints
{
    public $permissions = [ 'access_contacts', 'dt_all_access_contacts', 'view_project_metrics' ];

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
        }
    }

    public function delete_prayer_points_in_library( $library_id ) {
        if ( isset( $library_id ) ) {
            $prayer_ids = self::get_prayer_ids_from_library_id( $library_id );
            foreach ( $prayer_ids as $prayer_id ) {
                self::delete_prayer_point( $prayer_id );
            }
        }
    }

    public function get_prayer_ids_from_library_id( $library_id ) {
        if ( isset( $library_id ) ) {
            global $wpdb;
            return $wpdb->get_col(
                $wpdb->prepare( "SELECT id FROM `{$wpdb->prefix}dt_prayer_points` WHERE lib_id = %d;", $library_id )
            );
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

    public function endpoint_for_delete_prayer_point( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['prayer_id'] ) ) {
            self::delete_prayer_point( $params['prayer_id'] );
        }
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
        if ( isset( $params['library_id'] ) ) {
            $library_ids = self::validate_library_ids_string( $params['library_id' ] );
            $library_ids = explode( ',', $library_ids );
            return self::get_full_prayer_points_from_library_id( $library_ids );
        }
    }

    private function validate_library_ids_string( $library_ids ) {
        return sanitize_text_field( wp_unslash( $library_ids ) );
    }

    private function get_full_prayer_points_from_library_id( $library_id ) {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    REPLACE( REPLACE( (SELECT meta_value FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'title' AND prayer_id = pp.id), 'XXX', pl.people_group), 'YYY', pl.location ) AS 'title',
                    REPLACE( REPLACE( pp.content, 'XXX', pl.people_group), 'YYY', pl.location ) AS 'content',
                    (SELECT meta_value FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'reference' AND prayer_id = pp.id) AS 'reference',
                    (SELECT meta_value FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'book' AND prayer_id = pp.id) AS 'book',
                    (SELECT meta_value FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'verse' AND prayer_id = pp.id) AS 'verse',
                    (SELECT GROUP_CONCAT(meta_value) FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'tags' AND prayer_id = pp.id) AS 'tags',
                    pp.status,
                    pl.location,
                    pl.people_group,
                    pl.id AS 'library_id',
                    pl.name AS 'library_name'
                FROM `{$wpdb->prefix}dt_prayer_points` pp
                INNER JOIN `{$wpdb->prefix}dt_prayer_points_lib` pl
                ON pl.id = pp.lib_id
                WHERE pp.lib_id IN ( " . implode( ',', array_fill( 0, count( $library_id ), '%d' ) ) . " )
                ORDER BY pp.lib_id ASC;", $library_id
            ) , ARRAY_A
        );
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
