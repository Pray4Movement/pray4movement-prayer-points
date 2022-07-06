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
        self::register_save_child_prayer_point();
        self::register_save_tags();
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
            return self::get_full_prayer_points( $library_ids );
        }
    }

    private function get_full_prayer_points( $library_id ) {
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

    private function register_save_child_prayer_point() {
        register_rest_route(
            $this->get_namespace(), 'save_child_prayer_point/(?P<parent_prayer_point_id>\d+)/(?P<library_id>\d+)/(?P<title>.+)/(?P<content>.+)', [
                'methods' => 'POST',
                'callback' => [ $this, 'endpoint_for_save_child_prayer_point' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return $this->has_permission();
                }
            ]
        );
    }

    public function endpoint_for_save_child_prayer_point( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( !isset( $params['parent_prayer_point_id'] ) && !isset( $params['library_id'] ) && !isset( $params['title'] ) && !isset( $params['content'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
        if ( self::prayer_point_exists( $params['parent_prayer_point_id'], $params['library_id'] ) ) {
            self::update_child_prayer_point( $params );
            return;
        }
        self::insert_child_prayer_point( $params );
        return;
    }

    public function update_child_prayer_point( $wp_rest_params ) {
        $parent_prayer_point = self::get_prayer_point( $wp_rest_params['parent_prayer_point_id'] );
        $child_library_language = self::get_library_language( $wp_rest_params['library_id'] );
        $translated_book = self::get_book_translation( $parent_prayer_point['book'], $child_library_language );
        $translated_reference = str_replace( $parent_prayer_point['book'], $translated_book, $parent_prayer_point['reference'] );
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix.'dt_prayer_points',
            [
                'title' => urldecode( $wp_rest_params['title'] ),
                'content' => urldecode( $wp_rest_params['content'] ),
                'book' => $translated_book,
                'verse' => $parent_prayer_point['verse'],
                'reference' => $translated_reference,
                'hash' => md5( urldecode( $wp_rest_params['content'] ) ),
                'status' => 'unpublished',
            ],
            [
                'library_id' => $wp_rest_params['library_id'],
                'parent_id' => $wp_rest_params['parent_prayer_point_id'],
            ]
        );
        return;
    }

    private function get_library_language( $library_id ) {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare( "SELECT `language` FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE `id` = %d;", $library_id )
        );
    }

    private function get_book_translation( $string, $language ) {
        $books = [
            'Genesis' => [
                'en' => 'Genesis',
                'es' => 'Génesis',
                'fr' => 'Genesis',
                'pt' => 'Genesis',
            ],
            'Exodus' => [
                'en' => 'Exodus',
                'es' => 'Éxodo',
                'fr' => 'Exodus',
                'pt' => 'Exodus',
            ],
            'Leviticus' => [
                'en' => 'Leviticus',
                'es' => 'Levítico',
                'fr' => 'Leviticus',
                'pt' => 'Leviticus',
            ],
            'Numbers' => [
                'en' => 'Numbers',
                'es' => 'Números',
                'fr' => 'Numbers',
                'pt' => 'Numbers',
            ],
            'Deuteronomy' => [
                'en' => 'Deuteronomy',
                'es' => 'Deuteronomio',
                'fr' => 'Deuteronomy',
                'pt' => 'Deuteronomy',
            ],
            'Joshua' => [
                'en' => 'Joshua',
                'es' => 'Josué',
                'fr' => 'Joshua',
                'pt' => 'Joshua',
            ],
            'Judges' => [
                'en' => 'Judges',
                'es' => 'Jueces',
                'fr' => 'Judges',
                'pt' => 'Judges',
            ],
            'Ruth' => [
                'en' => 'Ruth',
                'es' => 'Rut',
                'fr' => 'Ruth',
                'pt' => 'Ruth',
            ],
            '1 Samuel' => [
                'en' => '1 Samuel',
                'es' => '1 Samuel',
                'fr' => '1 Samuel',
                'pt' => '1 Samuel',
            ],
            '2 Samuel' => [
                'en' => '2 Samuel',
                'es' => '2 Samuel',
                'fr' => '2 Samuel',
                'pt' => '2 Samuel',
            ],
            '1 Kings' => [
                'en' => '1 Kings',
                'es' => '1 Reyes',
                'fr' => '1 Kings',
                'pt' => '1 Kings',
            ],
            '2 Kings' => [
                'en' => '2 Kings',
                'es' => '2 Reyes',
                'fr' => '2 Kings',
                'pt' => '2 Kings',
            ],
            '1 Chronicles' => [
                'en' => '1 Chronicles',
                'es' => '1 Crónicas',
                'fr' => '1 Chronicles',
                'pt' => '1 Chronicles',
            ],
            '2 Chronicles' => [
                'en' => '2 Chronicles',
                'es' => '2 Crónicas',
                'fr' => '2 Chronicles',
                'pt' => '2 Chronicles',
            ],
            'Ezra' => [
                'en' => 'Ezra',
                'es' => 'Esdras',
                'fr' => 'Ezra',
                'pt' => 'Ezra',
            ],
            'Nehemiah' => [
                'en' => 'Nehemiah',
                'es' => 'Nehemías',
                'fr' => 'Nehemiah',
                'pt' => 'Nehemiah',
            ],
            'Esther' => [
                'en' => 'Esther',
                'es' => 'Ester',
                'fr' => 'Esther',
                'pt' => 'Esther',
            ],
            'Job' => [
                'en' => 'Job',
                'es' => 'Job',
                'fr' => 'Job',
                'pt' => 'Job',
            ],
            'Psalm' => [
                'en' => 'Psalm',
                'es' => 'Salmos',
                'fr' => 'Psalm',
                'pt' => 'Psalm',
            ],
            'Proverbs' => [
                'en' => 'Proverbs',
                'es' => 'Proverbios',
                'fr' => 'Proverbs',
                'pt' => 'Proverbs',
            ],
            'Ecclesiastes' => [
                'en' => 'Ecclesiastes',
                'es' => 'Eclesiastés',
                'fr' => 'Ecclesiastes',
                'pt' => 'Ecclesiastes',
            ],
            'Song of Solomon' => [
                'en' => 'Song of Solomon',
                'es' => 'Cantar de los Cantares',
                'fr' => 'Song of Solomon',
                'pt' => 'Song of Solomon',
            ],
            'Isaiah' => [
                'en' => 'Isaiah',
                'es' => 'Isaías',
                'fr' => 'Isaiah',
                'pt' => 'Isaiah',
            ],
            'Jeremiah' => [
                'en' => 'Jeremiah',
                'es' => 'Jeremías',
                'fr' => 'Jeremiah',
                'pt' => 'Jeremiah',
            ],
            'Lamentations' => [
                'en' => 'Lamentations',
                'es' => 'Lamentaciones',
                'fr' => 'Lamentations',
                'pt' => 'Lamentations',
            ],
            'Ezekiel' => [
                'en' => 'Ezekiel',
                'es' => 'Ezequiel',
                'fr' => 'Ezekiel',
                'pt' => 'Ezekiel',
            ],
            'Daniel' => [
                'en' => 'Daniel',
                'es' => 'Daniel',
                'fr' => 'Daniel',
                'pt' => 'Daniel',
            ],
            'Hosea' => [
                'en' => 'Hosea',
                'es' => 'Oseas',
                'fr' => 'Hosea',
                'pt' => 'Hosea',
            ],
            'Joel' => [
                'en' => 'Joel',
                'es' => 'Joel',
                'fr' => 'Joel',
                'pt' => 'Joel',
            ],
            'Amos' => [
                'en' => 'Amos',
                'es' => 'Amós',
                'fr' => 'Amos',
                'pt' => 'Amos',
            ],
            'Obadiah' => [
                'en' => 'Obadiah',
                'es' => 'Abdías',
                'fr' => 'Obadiah',
                'pt' => 'Obadiah',
            ],
            'Jonah' => [
                'en' => 'Jonah',
                'es' => 'Jonás',
                'fr' => 'Jonah',
                'pt' => 'Jonah',
            ],
            'Micah' => [
                'en' => 'Micah',
                'es' => 'Miquéas',
                'fr' => 'Micah',
                'pt' => 'Micah',
            ],
            'Nahum' => [
                'en' => 'Nahum',
                'es' => 'Nahúm',
                'fr' => 'Nahum',
                'pt' => 'Nahum',
            ],
            'Habakkuk' => [
                'en' => 'Habakkuk',
                'es' => 'Habacuc',
                'fr' => 'Habakkuk',
                'pt' => 'Habakkuk',
            ],
            'Zephaniah' => [
                'en' => 'Zephaniah',
                'es' => 'Sofonías',
                'fr' => 'Zephaniah',
                'pt' => 'Zephaniah',
            ],
            'Haggai' => [
                'en' => 'Haggai',
                'es' => 'Hageo',
                'fr' => 'Haggai',
                'pt' => 'Haggai',
            ],
            'Zechariah' => [
                'en' => 'Zechariah',
                'es' => 'Zacarías',
                'fr' => 'Zechariah',
                'pt' => 'Zechariah',
            ],
            'Malachi' => [
                'en' => 'Malachi',
                'es' => 'Malaquías',
                'fr' => 'Malachi',
                'pt' => 'Malachi',
            ],
            'Matthew' => [
                'en' => 'Matthew',
                'es' => 'Mateo',
                'fr' => 'Matthew',
                'pt' => 'Matthew',
            ],
            'Mark' => [
                'en' => 'Mark',
                'es' => 'Marcos',
                'fr' => 'Mark',
                'pt' => 'Mark',
            ],
            'Luke' => [
                'en' => 'Luke',
                'es' => 'Lucas',
                'fr' => 'Luke',
                'pt' => 'Luke',
            ],
            'John' => [
                'en' => 'John',
                'es' => 'Juan',
                'fr' => 'John',
                'pt' => 'John',
            ],
            'Acts' => [
                'en' => 'Acts',
                'es' => 'Hechos',
                'fr' => 'Acts',
                'pt' => 'Acts',
            ],
            'Romans' => [
                'en' => 'Romans',
                'es' => 'Romanos',
                'fr' => 'Romans',
                'pt' => 'Romans',
            ],
            '1 Corinthians' => [
                'en' => '1 Corinthians',
                'es' => '1 Corintios',
                'fr' => '1 Corinthians',
                'pt' => '1 Corinthians',
            ],
            '2 Corinthians' => [
                'en' => '2 Corinthians',
                'es' => '2 Corintios',
                'fr' => '2 Corinthians',
                'pt' => '2 Corinthians',
            ],
            'Galatians' => [
                'en' => 'Galatians',
                'es' => 'Gálatas',
                'fr' => 'Galatians',
                'pt' => 'Galatians',
            ],
            'Ephesians' => [
                'en' => 'Ephesians',
                'es' => 'Efesios',
                'fr' => 'Ephesians',
                'pt' => 'Ephesians',
            ],
            'Philippians' => [
                'en' => 'Philippians',
                'es' => 'Filipenses',
                'fr' => 'Philippians',
                'pt' => 'Philippians',
            ],
            'Colossians' => [
                'en' => 'Colossians',
                'es' => 'Colosenses',
                'fr' => 'Colossians',
                'pt' => 'Colossians',
            ],
            '1 Thessalonians' => [
                'en' => '1 Thessalonians',
                'es' => '1 Tesalonicenses',
                'fr' => '1 Thessalonians',
                'pt' => '1 Thessalonians',
            ],
            '2 Thessalonians' => [
                'en' => '2 Thessalonians',
                'es' => '2 Tesalonicenses',
                'fr' => '2 Thessalonians',
                'pt' => '2 Thessalonians',
            ],
            '1 Timothy' => [
                'en' => '1 Timothy',
                'es' => '1 Timoteo',
                'fr' => '1 Timothy',
                'pt' => '1 Timothy',
            ],
            '2 Timothy' => [
                'en' => '2 Timothy',
                'es' => '2 Timoteo',
                'fr' => '2 Timothy',
                'pt' => '2 Timothy',
            ],
            'Titus' => [
                'en' => 'Titus',
                'es' => 'Tito',
                'fr' => 'Titus',
                'pt' => 'Titus',
            ],
            'Philemon' => [
                'en' => 'Philemon',
                'es' => 'Filemón',
                'fr' => 'Philemon',
                'pt' => 'Philemon',
            ],
            'Hebrews' => [
                'en' => 'Hebrews',
                'es' => 'Hebreos',
                'fr' => 'Hebrews',
                'pt' => 'Hebrews',
            ],
            'James' => [
                'en' => 'James',
                'es' => 'Santiago',
                'fr' => 'James',
                'pt' => 'James',
            ],
            '1 Peter' => [
                'en' => '1 Peter',
                'es' => '1 Pedro',
                'fr' => '1 Peter',
                'pt' => '1 Peter',
            ],
            '2 Peter' => [
                'en' => '2 Peter',
                'es' => '2 Pedro',
                'fr' => '2 Peter',
                'pt' => '2 Peter',
            ],
            '1 John' => [
                'en' => '1 John',
                'es' => '1 Juan',
                'fr' => '1 John',
                'pt' => '1 John',
            ],
            '2 John' => [
                'en' => '2 John',
                'es' => '2 Juan',
                'fr' => '2 John',
                'pt' => '2 John',
            ],
            '3 John' => [
                'en' => '3 John',
                'es' => '3 Juan',
                'fr' => '3 John',
                'pt' => '3 John',
            ],
            'Jude' => [
                'en' => 'Jude',
                'es' => 'Judas',
                'fr' => 'Jude',
                'pt' => 'Jude',
            ],
            'Revelation' => [
                'en' => 'Revelation',
                'es' => 'Apocalipsis',
                'fr' => 'Revelation',
                'pt' => 'Revelation',
            ],
        ];
        return $books[$string][$language];
    }

    public function insert_child_prayer_point( $wp_rest_params ) {
        $parent_prayer_point = self::get_prayer_point( $wp_rest_params['parent_prayer_point_id'] );
        error_log( var_dump( $parent_prayer_point ) );
        return;
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix.'dt_prayer_points',
            [
                'library_id' => $wp_rest_params['library_id'],
                'parent_id' => $wp_rest_params['parent_prayer_point_id'],
                'title' => urldecode( $wp_rest_params['title'] ),
                'content' => urldecode( $wp_rest_params['content'] ),
                'hash' => md5( urldecode( $wp_rest_params['content'] ) ),
                'status' => 'unpublished',
            ],
            [ '%d', '%d', '%s', '%s', '%s', '%s' ]
        );
        return;
    }

    private function prayer_point_exists( $parent_prayer_point_id, $library_id ) {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare( "SELECT id FROM `{$wpdb->prefix}dt_prayer_points` WHERE `parent_id` = %d AND `library_id` = %d;", $parent_prayer_point_id, $library_id )
        );
    }

    public function get_prayer_point( $prayer_id ) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}dt_prayer_points` WHERE id = %d;", $prayer_id ), ARRAY_A
        );
    }

    private function register_save_tags() {
        register_rest_route(
            $this->get_namespace(), 'save_tags/(?P<prayer_id>\d+)/(?P<tags>.+)', [
                'methods' => 'POST',
                'callback' => [ $this, 'endpoint_for_save_tags' ],
                // 'permission_callback' => function( WP_REST_Request $request ) {
                //     return $this->has_permission();
                // }
            ]
        );
    }

    public function endpoint_for_save_tags( WP_REST_Request $request ) {
        $params = $request->get_params();
        $tags = self::sanitize_tags( $params['tags'] );
        self::insert_all_tags( $tags );
        return;
    }

    public function sanitize_tags( $raw_tags ) {
        $tags = sanitize_text_field( wp_unslash( strtolower( $raw_tags ) ) );
        $tags = explode( ',', $tags );
        $tags = array_map( 'trim', $tags );
        return array_filter( $tags );
    }

    public function insert_all_tags( $prayer_id, $tags ) {
        global $wpdb;
        if ( is_string( $tags ) ) {
            $tags = [ $tags ];
        }
        foreach ( $tags as $tag ) {
            $wpdb->insert(
                $wpdb->prefix.'dt_prayer_points_meta',
                [
                    'prayer_id' => $prayer_id,
                    'meta_key' => 'tags',
                    'meta_value' => $tag
                ],
                [ '%d', '%s', '%s' ]
            );
        }
        return;
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
