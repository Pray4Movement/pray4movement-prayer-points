<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Pray4Movement_Prayer_Points_Endpoints
{
    public $permissions = [ 'publish_posts', 'edit_posts', 'delete_posts' ];

    public function add_api_routes() {
        self::register_delete_prayer_library_endpoint();
        self::register_delete_prayer_point_endpoint();
        self::register_get_prayer_points_endpoint();
        self::register_get_replaced_prayer_points_endpoint();
        self::register_get_prayer_libraries_endpoint();
        self::register_get_prayer_points_by_tag_endpoint();
        self::register_set_location_and_people_group_endpoint();
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
                $wpdb->prepare( "SELECT id FROM `{$wpdb->prefix}dt_prayer_points` WHERE library_id = %d;", $library_id )
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

    private function delete_meta_by_key( $meta_key ) {
        global $wpdb;
        return $wpdb->query(
            $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = %s;", $meta_key )
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
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function endpoint_for_get_prayer_points( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['library_id'] ) ) {
            $library_ids = self::validate_library_ids_string( $params['library_id'] );
            $library_ids = explode( ',', $library_ids );
            return self::get_full_prayer_points_from_library_id( $library_ids );
        }
    }

    private function get_full_prayer_points_from_library_id( $library_id ) {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    pp.id AS `id`,
                    pp.title AS `title`,
                    pp.content AS `content`,
                    (SELECT IFNULL( GROUP_CONCAT(meta_value), '' ) FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'tags' AND prayer_id = pp.id) AS 'tags',
                    IFNULL( pp.reference, '' ) AS 'reference',
                    IFNULL( pp.book, '' ) AS 'book',
                    IFNULL( pp.verse, '' ) AS 'verse',
                    pp.status AS 'status',
                    pl.id AS 'library_id',
                    pl.name AS 'library_name'
                FROM `{$wpdb->prefix}dt_prayer_points` pp
                INNER JOIN `{$wpdb->prefix}dt_prayer_points_lib` pl
                ON pl.id = pp.library_id
                WHERE pp.library_id IN ( " . implode( ',', array_fill( 0, count( $library_id ), '%d' ) ) . " )
                ORDER BY pp.library_id ASC;", $library_id
            ), ARRAY_A
        );
    }

    private function register_get_replaced_prayer_points_endpoint() {
        register_rest_route(
            $this->get_namespace(), '/get_prayer_points/(?P<library_id>\d*[,\d+]*)/(?P<location>.+)/(?P<people_group>.+)', [
                'methods' => 'POST',
                'callback' => [ $this , 'endpoint_for_get_replaced_prayer_points' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function endpoint_for_get_replaced_prayer_points( WP_REST_Request $request ) {
        $params = $request->get_params();
        $location = sanitize_text_field( wp_unslash( $params['location'] ) );
        $people_group = sanitize_text_field( wp_unslash( $params['people_group'] ) );
        if ( !isset( $params['location'] ) || empty( $params['location'] ) || is_null( $params['location'] ) || $params['location'] === 'null' ) {
            $location = 'the world';
        }
        if ( !isset( $params['people_group'] ) || empty( $params['people_group'] ) || is_null( $params['people_group'] ) || $params['people_group'] === 'null' ) {
            $people_group = 'people';
        }
        if ( isset( $params['library_id'] ) ) {
            $library_ids = self::validate_library_ids_string( $params['library_id'] );
            $library_ids = explode( ',', $library_ids );
            return self::get_full_replaced_prayer_points_from_library_id( $library_ids, $location, $people_group );
        }
    }

    private function validate_library_ids_string( $library_ids ) {
        return sanitize_text_field( wp_unslash( $library_ids ) );
    }

    private function get_full_replaced_prayer_points_from_library_id( $library_id, $location, $people_group ) {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    pp.id AS `id`,
                    REPLACE( REPLACE( pp.title, 'XXX', %s ), 'YYY', %s ) AS `title`,
	                REPLACE( REPLACE( pp.content, 'XXX', %s ), 'YYY', %s ) AS `content`,
                    (SELECT IFNULL( GROUP_CONCAT(meta_value), '' ) FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'tags' AND prayer_id = pp.id) AS 'tags',
                    IFNULL( pp.reference, '' ) AS 'reference',
                    IFNULL( pp.book, '' ) AS 'book',
                    IFNULL( pp.verse, '' ) AS 'verse',
                    pp.status AS 'status',
                    pl.id AS 'library_id',
                    pl.name AS 'library_name'
                FROM `{$wpdb->prefix}dt_prayer_points` pp
                INNER JOIN `{$wpdb->prefix}dt_prayer_points_lib` pl
                ON pl.id = pp.library_id
                WHERE pp.library_id IN ( %d )
                ORDER BY pp.library_id ASC;", $location, $people_group, $location, $people_group, $library_id[0]
            ), ARRAY_A
        );
    }

    private function register_get_prayer_libraries_endpoint() {
        register_rest_route(
            $this->get_namespace(), '/get_prayer_libraries', [
                'methods' => 'POST',
                'callback' => [ $this , 'endpoint_for_get_prayer_libraries' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function endpoint_for_get_prayer_libraries( WP_REST_Request $request ) {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}dt_prayer_points_lib`;", ARRAY_A );
    }

    private function register_get_prayer_points_by_tag_endpoint() {
        register_rest_route(
            $this->get_namespace(), '/get_prayer_points_by_tag/(?P<tag>.+)', [
                'methods' => 'POST',
                'callback' => [ $this , 'endpoint_for_get_prayer_points_by_tag' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function endpoint_for_get_prayer_points_by_tag( WP_REST_Request $request ) {
        $params = $request->get_params();
        return self::get_prayer_points_by_tag( $params['tag'] );
    }

    private function get_prayer_points_by_tag( $tag ) {
        $tag = urldecode( $tag );
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare( "SELECT pp.*,
                                (SELECT GROUP_CONCAT( meta_value ) FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'tags' AND prayer_id = pp.id) AS 'tags'
                                FROM `{$wpdb->prefix}dt_prayer_points` pp
                                INNER JOIN `{$wpdb->prefix}dt_prayer_points_meta` pm
                                ON pp.id = pm.prayer_id
                                WHERE pm.meta_key = 'tags' AND meta_value = %s;", $tag ), ARRAY_A );
    }
    private function register_set_location_and_people_group_endpoint() {
        register_rest_route(
            $this->get_namespace(), '/set_location_and_people_group/(?P<location>.+)/(?P<people_group>.+)', [
                'methods'  => 'POST',
                'callback' => [ $this, 'endpoint_for_set_location_and_people_group' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return $this->has_permission();
                },
            ]
        );
    }

    public function endpoint_for_set_location_and_people_group( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['location'] ) && isset( $params['people_group'] ) ) {
            self::delete_meta_by_key( 'location' );
            self::delete_meta_by_key( 'people_group' );
            self::set_meta_key_and_value( 'location', $params['location'] );
            self::set_meta_key_and_value( 'people_group', $params['people_group'] );
        }
    }

    private function set_meta_key_and_value( $meta_key, $meta_value ) {
        $meta_value = urldecode( $meta_value );
        global $wpdb;
        return $wpdb->query(
            $wpdb->prepare( "INSERT INTO `{$wpdb->prefix}dt_prayer_points_meta` ( meta_key, meta_value ) VALUES ( %s, %s )", $meta_key, $meta_value )
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
