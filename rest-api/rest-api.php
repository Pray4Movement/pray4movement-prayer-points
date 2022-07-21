<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Pray4Movement_Prayer_Points_Endpoints
{
    public $permissions = [ 'publish_posts', 'edit_posts', 'delete_posts' ];

    public function add_api_routes() {
        self::register_delete_prayer_library_endpoint();
        self::register_delete_prayer_point_endpoint();
        self::register_delete_localization_rule_endpoint();
        self::register_get_prayer_points_endpoint();
        self::register_get_prayer_points_localized_endpoint();
        self::register_get_replaced_prayer_points_endpoint();
        self::register_get_prayer_libraries_endpoint();
        self::register_get_prayer_libraries_by_language_endpoint();
        self::register_get_prayer_points_by_tag_endpoint();
        self::register_set_location_and_people_group_endpoint();
        self::register_save_child_prayer_point_endpoint();
        self::register_save_child_prayer_point_tags_endpoint();
        self::register_add_localization_rule_endpoint();
        self::register_update_localization_rule_endpoint();
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

    private function register_delete_localization_rule_endpoint() {
        register_rest_route(
            $this->get_namespace(), '/delete_localization_rule/(?P<rule_id>\d+)', [
                'methods'  => 'POST',
                'callback' => [ $this, 'endpoint_for_delete_localization_rule' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return $this->has_permission();
                },
            ]
        );
    }

    public function endpoint_for_delete_localization_rule( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['rule_id'] ) ) {
            $rule_id = sanitize_text_field( wp_unslash( $params['rule_id'] ) );
            self::delete_localization_rule( $rule_id );
        }
    }

    private function delete_localization_rule( $rule_id ) {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}dt_prayer_points_localization` WHERE `rule_id` = %d;", $rule_id )
        );
        return;
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

    private function register_get_prayer_points_localized_endpoint() {
        register_rest_route(
            $this->get_namespace(), '/get_prayer_points_localized/(?P<library_id>\d*[,\d+]*)', [
                'methods' => 'POST',
                'callback' => [ $this , 'endpoint_for_get_prayer_points_localized' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function endpoint_for_get_prayer_points_localized( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['library_id'] ) ) {
            $library_ids = self::validate_library_ids_string( $params['library_id'] );
            $library_ids = explode( ',', $library_ids );
            $prayer_points = self::get_full_prayer_points_localized( $library_ids );
        }
        $prayer_points_localized = [];
        foreach ( $prayer_points as $prayer_point ) {
            $rules = self::get_localization_rules( $prayer_point['library_id'] );
            foreach ( $rules as $rule ) {
                $prayer_point['title'] = str_replace( $rule['replace_from'], $rule['replace_to'], $prayer_point['title'] );
                $prayer_point['content'] = str_replace( $rule['replace_from'], $rule['replace_to'], $prayer_point['content'] );
            }
            $prayer_points_localized[] = $prayer_point;
        }
        return $prayer_points_localized;
    }

    private function get_localization_rules( $library_id ) {
        if ( !get_current_user_id() ) {
            return;
        }
        global $wpdb;
        return $wpdb->get_results(
        $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}dt_prayer_points_localization` WHERE `library_id` = %d AND `user_id` = %d ORDER BY `library_id` ASC;", $library_id, get_current_user_id() ), ARRAY_A );
    }

    private function apply_rules_to_prayer_points( $prayer_points, $rules ) {
        $prayer_points_localized = [];
        foreach ( $prayer_points as $prayer_point ) {
            $prayer_point_localized = $prayer_point;
            foreach ( $rules as $rule ) {
                $prayer_point_localized = str_replace( $rule['from'], $rule['to'], $prayer_point_localized );
            }
            $prayer_points_localized[] = $prayer_point_localized;
        }
        return $prayer_points_localized;
    }

    public function get_full_prayer_points_localized( $library_id ) {
        $prayer_points = self::get_full_prayer_points( $library_id );
        $rules = self::get_localization_rules( $library_id );
        $prayer_points_localized = self::apply_rules_to_prayer_points( $prayer_points, $rules );
        return $prayer_points_localized;
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
                'callback' => [ $this, 'endpoint_for_get_prayer_libraries' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function endpoint_for_get_prayer_libraries( WP_REST_Request $request ) {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}dt_prayer_points_lib` ORDER BY `id`, `parent_id`;", ARRAY_A );
    }

    private function register_get_prayer_libraries_by_language_endpoint() {
        register_rest_route(
            $this->get_namespace(), '/get_prayer_libraries_by_language/(?P<language>.+)', [
                'methods' => 'POST',
                'callback' => [ $this, 'endpoint_for_get_prayer_libraries_by_language' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function endpoint_for_get_prayer_libraries_by_language( WP_REST_Request $request ) {
        $params = $request->get_params();
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE `language` = %s;", $params['language'] ), ARRAY_A
        );
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
        $wpdb->insert(
            $wpdb->prefix . 'dt_prayer_points_meta',
            [
                'meta_key' => $meta_key,
                'meta_value' => $meta_value,
            ],
            [ '%s', '%s' ]
        );
    }

    private function register_save_child_prayer_point_endpoint() {
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
        if ( !isset( $params['parent_prayer_point_id'] ) || !isset( $params['library_id'] ) || !isset( $params['title'] ) || !isset( $params['content'] ) ) {
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
                'title' => urldecode( wp_unslash( $wp_rest_params['title'] ) ),
                'content' => urldecode( wp_unslash( $wp_rest_params['content'] ) ),
                'book' => $translated_book,
                'verse' => $parent_prayer_point['verse'],
                'reference' => $translated_reference,
                'hash' => md5( urldecode( wp_unslash( $wp_rest_params['content'] ) ) ),
                'language' => $child_library_language,
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
            null                => [ 'en'  => null,                'es'   => null,               'fr'    => null,                             'pt'  => null,],
            'Genesis'           => [ 'en'  => 'Genesis',           'es'   => 'Génesis',          'fr'    => 'Genèse',                         'pt'  => 'Gênesis',],
            'Exodus'            => [ 'en'  => 'Exodus',            'es'   => 'Éxodo',            'fr'    => 'Exode',                          'pt'  => 'Êxodo', ],
            'Leviticus'         => [ 'en'  => 'Leviticus',         'es'   => 'Levítico',         'fr'    => 'Lévitique',                      'pt'  => 'Levítico', ],
            'Numbers'           => [ 'en'  => 'Numbers',           'es'   => 'Números',          'fr'    => 'Nombres',                        'pt'  => 'Números', ],
            'Deuteronomy'       => [ 'en'  => 'Deuteronomy',       'es'   => 'Deuteronomio',     'fr'    => 'Deutéronome',                    'pt'  => 'Deuteronômio', ],
            'Joshua'            => [ 'en'  => 'Joshua',            'es'   => 'Josué',            'fr'    => 'Josué',                          'pt'  => 'Josué', ],
            'Judges'            => [ 'en'  => 'Judges',            'es'   => 'Jueces',           'fr'    => 'Juges',                          'pt'  => 'Juízes', ],
            'Ruth'              => [ 'en'  => 'Ruth',              'es'   => 'Rut',              'fr'    => 'Ruth',                           'pt'  => 'Rute', ],
            '2 Samuel'          => [ 'en'  => '2 Samuel',          'es'   => '2 Samuel',         'fr'    => '2 Samuel',                       'pt'  => '2 Samuel', ],
            '1 Samuel'          => [ 'en'  => '1 Samuel',          'es'   => '1 Samuel',         'fr'    => '1 Samuel',                       'pt'  => '1 Samuel', ],
            '1 Kings'           => [ 'en'  => '1 Kings',           'es'   => '1 Reyes',          'fr'    => '1 Rois',                         'pt'  => '1 Reis', ],
            '2 Kings'           => [ 'en'  => '2 Kings',           'es'   => '2 Reyes',          'fr'    => '2 Rois',                         'pt'  => '2 Reis', ],
            '1 Chronicles'      => [ 'en'  => '1 Chronicles',      'es'   => '1 Crónicas',       'fr'    => '1 Chroniques',                   'pt'  => '1 Crônicas', ],
            '2 Chronicles'      => [ 'en'  => '2 Chronicles',      'es'   => '2 Crónicas',       'fr'    => '2 Chroniques',                   'pt'  => '2 Crônicas', ],
            'Ezra'              => [ 'en'  => 'Ezra',              'es'   => 'Esdras',           'fr'    => 'Esdras',                         'pt'  => 'Esdras', ],
            'Nehemiah'          => [ 'en'  => 'Nehemiah',          'es'   => 'Nehemías',         'fr'    => 'Néhémie',                        'pt'  => 'Neemias', ],
            'Esther'            => [ 'en'  => 'Esther',            'es'   => 'Ester',            'fr'    => 'Esther',                         'pt'  => 'Ester', ],
            'Job'               => [ 'en'  => 'Job',               'es'   => 'Job',              'fr'    => 'Job',                            'pt'  => 'Jó', ],
            'Psalms'            => [ 'en'  => 'Psalms',            'es'   => 'Salmos',           'fr'    => 'Psaumes',                        'pt'  => 'Salmos', ],
            'Proverbs'          => [ 'en'  => 'Proverbs',          'es'   => 'Proverbios',       'fr'    => 'Proverbes',                      'pt'  => 'Provérbios', ],
            'Ecclesiastes'      => [ 'en'  => 'Ecclesiastes',      'es'   => 'Eclesiastés',      'fr'    => 'Ecclésiaste',                    'pt'  => 'Eclesiastes', ],
            'Song of Solomon'   => [ 'en'  => 'Song of Solomon',   'es'   => 'Cantares',         'fr'    => 'Cantique des Cantiques',         'pt'  => 'Cantares', ],
            'Isaiah'            => [ 'en'  => 'Isaiah',            'es'   => 'Isaías',           'fr'    => 'Ésaïe',                          'pt'  => 'Isaías', ],
            'Jeremiah'          => [ 'en'  => 'Jeremiah',          'es'   => 'Jeremías',         'fr'    => 'Jérémie',                        'pt'  => 'Jeremias', ],
            'Lamentations'      => [ 'en'  => 'Lamentations',      'es'   => 'Lamentaciones',    'fr'    => 'Lamentations',                   'pt'  => 'Lamentações', ],
            'Ezekiel'           => [ 'en'  => 'Ezekiel',           'es'   => 'Ezequiel',         'fr'    => 'Ézéchiel',                       'pt'  => 'Ezequiel', ],
            'Daniel'            => [ 'en'  => 'Daniel',            'es'   => 'Daniel',           'fr'    => 'Daniel',                         'pt'  => 'Daniel', ],
            'Hosea'             => [ 'en'  => 'Hosea',             'es'   => 'Oseas',            'fr'    => 'Osée',                           'pt'  => 'Oseias', ],
            'Joel'              => [ 'en'  => 'Joel',              'es'   => 'Joel',             'fr'    => 'Joël',                           'pt'  => 'Joel', ],
            'Amos'              => [ 'en'  => 'Amos',              'es'   => 'Amós',             'fr'    => 'Amos',                           'pt'  => 'Amós', ],
            'Obadiah'           => [ 'en'  => 'Obadiah',           'es'   => 'Abdías',           'fr'    => 'Abdias',                         'pt'  => 'Obadias', ],
            'Jonah'             => [ 'en'  => 'Jonah',             'es'   => 'Jonás',            'fr'    => 'Jonas',                          'pt'  => 'Jonas', ],
            'Micah'             => [ 'en'  => 'Micah',             'es'   => 'Miqueas',          'fr'    => 'Michée',                         'pt'  => 'Miqueias', ],
            'Nahum'             => [ 'en'  => 'Nahum',             'es'   => 'Nahúm',            'fr'    => 'Nahum',                          'pt'  => 'Naum', ],
            'Habakkuk'          => [ 'en'  => 'Habakkuk',          'es'   => 'Habacuc',          'fr'    => 'Habacuc',                        'pt'  => 'Habacuque', ],
            'Zephaniah'         => [ 'en'  => 'Zephaniah',         'es'   => 'Sofonías',         'fr'    => 'Sophonie',                       'pt'  => 'Sofonias', ],
            'Haggai'            => [ 'en'  => 'Haggai',            'es'   => 'Hageo',            'fr'    => 'Aggée',                          'pt'  => 'Ageu', ],
            'Zechariah'         => [ 'en'  => 'Zechariah',         'es'   => 'Zacarías',         'fr'    => 'Zacharie',                       'pt'  => 'Zacarias', ],
            'Malachi'           => [ 'en'  => 'Malachi',           'es'   => 'Malaquías',        'fr'    => 'Malachie',                       'pt'  => 'Malaquias', ],
            'Matthew'           => [ 'en'  => 'Matthew',           'es'   => 'Mateo',            'fr'    => 'Matthieu',                       'pt'  => 'Mateus', ],
            'Mark'              => [ 'en'  => 'Mark',              'es'   => 'Marcos',           'fr'    => 'Marc',                           'pt'  => 'Marcos', ],
            'Luke'              => [ 'en'  => 'Luke',              'es'   => 'Lucas',            'fr'    => 'Luc',                            'pt'  => 'Lucas', ],
            'John'              => [ 'en'  => 'John',              'es'   => 'Juan',             'fr'    => 'Jean',                           'pt'  => 'João', ],
            'Acts'              => [ 'en'  => 'Acts',              'es'   => 'Hechos',           'fr'    => 'Actes',                          'pt'  => 'Atos', ],
            'Romans'            => [ 'en'  => 'Romans',            'es'   => 'Romanos',          'fr'    => 'Romains',                        'pt'  => 'Romanos', ],
            '1 Corinthians'     => [ 'en'  => '1 Corinthians',     'es'   => '1 Corintios',      'fr'    => '1 Corinthiens',                  'pt'  => '1 Coríntios', ],
            '2 Corinthians'     => [ 'en'  => '2 Corinthians',     'es'   => '2 Corintios',      'fr'    => '2 Corinthiens',                  'pt'  => '2 Coríntios', ],
            'Galatians'         => [ 'en'  => 'Galatians',         'es'   => 'Gálatas',          'fr'    => 'Galates',                        'pt'  => 'Gálatas', ],
            'Ephesians'         => [ 'en'  => 'Ephesians',         'es'   => 'Efesios',          'fr'    => 'Éphésiens',                      'pt'  => 'Efésios', ],
            'Philippians'       => [ 'en'  => 'Philippians',       'es'   => 'Filipenses',       'fr'    => 'Philippiens',                    'pt'  => 'Filipenses', ],
            'Colossians'        => [ 'en'  => 'Colossians',        'es'   => 'Colosenses',       'fr'    => 'Colossiens',                     'pt'  => 'Colossenses', ],
            '1 Thessalonians'   => [ 'en'  => '1 Thessalonians',   'es'   => '1 Tesalonicenses', 'fr'    => '1 Thessaloniciens',              'pt'  => '1 Tessalonicenses', ],
            '2 Thessalonians'   => [ 'en'  => '2 Thessalonians',   'es'   => '2 Tesalonicenses', 'fr'    => '2 Thessaloniciens',              'pt'  => '2 Tessalonicenses', ],
            '1 Timothy'         => [ 'en'  => '1 Timothy',         'es'   => '1 Timoteo',        'fr'    => '1 Timothée',                     'pt'  => '1 Timóteo', ],
            '2 Timothy'         => [ 'en'  => '2 Timothy',         'es'   => '2 Timoteo',        'fr'    => '2 Timothée',                     'pt'  => '2 Timóteo', ],
            'Titus'             => [ 'en'  => 'Titus',             'es'   => 'Tito',             'fr'    => 'Tite',                           'pt'  => 'Tito', ],
            'Philemon'          => [ 'en'  => 'Philemon',          'es'   => 'Filemón',          'fr'    => 'Philémon',                       'pt'  => 'Filemom', ],
            'Hebrews'           => [ 'en'  => 'Hebrews',           'es'   => 'Hebreos',          'fr'    => 'Hébreux',                        'pt'  => 'Hebreus', ],
            'James'             => [ 'en'  => 'James',             'es'   => 'Santiago',         'fr'    => 'Jacques',                        'pt'  => 'Tiago', ],
            '1 Peter'           => [ 'en'  => '1 Peter',           'es'   => '1 Pedro',          'fr'    => '1 Pierre',                       'pt'  => '1 Pedro', ],
            '2 Peter'           => [ 'en'  => '2 Peter',           'es'   => '2 Pedro',          'fr'    => '2 Pierre',                       'pt'  => '2 Pedro', ],
            '1 John'            => [ 'en'  => '1 John',            'es'   => '1 Juan',           'fr'    => '1 Jean',                         'pt'  => '1 João', ],
            '2 John'            => [ 'en'  => '2 John',            'es'   => '2 Juan',           'fr'    => '2 Jean',                         'pt'  => '2 João', ],
            '3 John'            => [ 'en'  => '3 John',            'es'   => '3 Juan',           'fr'    => '3 Jean',                         'pt'  => '3 João', ],
            'Jude'              => [ 'en'  => 'Jude',              'es'   => 'Judas',            'fr'    => 'Jude',                           'pt'  => 'Judas', ],
            'Revelation'        => [ 'en'  => 'Revelation',        'es'   => 'Apocalipsis',      'fr'    => 'Apocalypse',                     'pt'  => 'Apocalipse', ],
        ];
        return $books[$string][$language];
    }

    public function insert_child_prayer_point( $wp_rest_params ) {
        $parent_prayer_point = self::get_prayer_point( $wp_rest_params['parent_prayer_point_id'] );

        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix.'dt_prayer_points',
            [
                'library_id' => $wp_rest_params['library_id'],
                'parent_id' => $parent_prayer_point['id'],
                'title' => urldecode( wp_unslash( $wp_rest_params['title'] ) ),
                'content' => urldecode( wp_unslash( $wp_rest_params['content'] ) ),
                'hash' => md5( urldecode( wp_unslash( $wp_rest_params['content'] ) ) ),
                'language' => self::get_library_language( $wp_rest_params['library_id'] ),
                'status' => 'unpublished',
            ],
            [ '%d', '%d', '%s', '%s', '%s', '%s', '%s' ]
        );
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

    public function get_prayer_point_by_parent_id_and_library_id( $parent_id, $library_id ) {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare( "SELECT `id` FROM `{$wpdb->prefix}dt_prayer_points` WHERE `parent_id` = %d AND `library_id` = %d;", $parent_id, $library_id )
        );
    }

    private function register_save_child_prayer_point_tags_endpoint() {
        register_rest_route(
            $this->get_namespace(), 'save_child_prayer_point_tags/(?P<parent_prayer_id>\d+)/(?P<language>.+)/(?P<tags>.*)', [
                'methods' => 'POST',
                'callback' => [ $this, 'endpoint_for_save_child_prayer_point_tags' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return $this->has_permission();
                }
            ]
        );
    }

    public function endpoint_for_save_child_prayer_point_tags( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( !isset( $params['parent_prayer_id'] ) || !isset( $params['language'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
        $prayer_id = self::get_translated_prayer_point_id( $params['parent_prayer_id'], $params['language'] );
        self::delete_all_tags( $prayer_id );
        if ( urldecode( $params['tags'] ) !== '{null_tags}' ) {
            $tags = self::sanitize_tags( $params['tags'] );
            self::insert_all_tags( $prayer_id, $tags );
        }
        return;
    }

    public function get_translated_prayer_point_id( $parent_id, $language ) {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare( "SELECT `id` FROM `{$wpdb->prefix}dt_prayer_points` WHERE `parent_id` = %d AND `language` = %s;", $parent_id, $language )
        );
    }

    public function sanitize_tags( $raw_tags ) {
        $tags = sanitize_text_field( wp_unslash( urldecode( strtolower( $raw_tags ) ) ) );
        $tags = explode( ',', $tags );
        $tags = array_map( 'trim', $tags );
        return array_filter( $tags );
    }
    public function delete_all_tags( $prayer_id ) {
        global $wpdb;
        return $wpdb->query(
            $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE `meta_key` = 'tags' AND `prayer_id` = %d;", $prayer_id )
        );
    }

    public function insert_all_tags( $prayer_id, $tags ) {
        global $wpdb;
        if ( is_string( $tags ) ) {
            $tags = [ $tags ];
        }
        foreach ( $tags as $tag ) {
            if ( $tag !== '' ) {
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
        }
        return;
    }

    public function register_add_localization_rule_endpoint() {
        register_rest_route(
            $this->get_namespace(), '/add_localization_rule/(?P<library_id>\d+)/(?P<replace_from>.+)/(?P<replace_to>.+)/(?P<user_id>\d+)', [
                'methods'  => 'POST',
                'callback' => [ $this, 'endpoint_for_add_localization_rule' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return $this->has_permission();
                },
            ]
        );
    }

    public function endpoint_for_add_localization_rule( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( !isset( $params['library_id'] ) || !isset( $params['replace_from'] ) || !isset( $params['replace_to'] ) || !isset( $params['user_id'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
        // Todo: add user_id validation
        // if ( $params['user_id'] !== get_current_user_id() ) {
        //     return new WP_Error( __METHOD__, 'Nice try, slick. You can\'t add rules for other users.' );
        // }
        self::add_localization_rule( $params['library_id'], $params['replace_from'], $params['replace_to'], $params['user_id'] );
        return;
    }

    public function add_localization_rule( $library_id, $replace_from, $replace_to, $user_id ) {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'dt_prayer_points_localization',
            [
                'library_id' => $library_id,
                'replace_from' => $replace_from,
                'replace_to' => $replace_to,
                'user_id' => $user_id,
            ],
            [ '%d', '%s', '%s', '%d' ]
        );
        return true;
    }

    public function register_update_localization_rule_endpoint() {
        register_rest_route(
            $this->get_namespace(), '/update_localization_rule/(?P<rule_id>\d+)/(?P<replace_from>.+)/(?P<replace_to>.+)', [
                'methods'  => 'POST',
                'callback' => [ $this, 'endpoint_for_update_localization_rule' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return $this->has_permission();
                },
            ]
        );
    }

    public function endpoint_for_update_localization_rule( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( !isset( $params['rule_id'] ) || !isset( $params['replace_from'] ) || !isset( $params['replace_to'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
        // Todo: add user_id validation
        // if ( $params['user_id'] !== get_current_user_id() ) {
        //     return new WP_Error( __METHOD__, 'Nice try, slick. You can\'t add rules for other users.' );
        // }
        self::update_localization_rule( $params['rule_id'], $params['replace_from'], $params['replace_to'] );
        return;
    }

    public function update_localization_rule( $rule_id, $replace_from, $replace_to ) {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'dt_prayer_points_localization',
            [
                'replace_from' => $replace_from,
                'replace_to' => $replace_to,
            ],
            [
                'rule_id' => $rule_id,
            ]
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
