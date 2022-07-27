<?php
add_shortcode( 'p4m_prayer_libraries', 'p4m_prayer_libraries' );

function p4m_prayer_libraries() {
    ?>
    <script src="<?php echo esc_attr( trailingslashit( plugin_dir_url( __FILE__ ) ) . '../assets/jquery-3.6.0.min.js' ); ?>"></script>
    <link rel="stylesheet" href="<?php echo esc_attr( trailingslashit( plugin_dir_url( __FILE__ ) ) . '../assets/p4m-prayer-points-styles.css' ); ?>">
    <?php
    // function p4m_prayer_points_enqueue_scripts() {
    //     wp_enqueue_script( 'jquery', trailingslashit( plugin_dir_url( __FILE__ ) ) . '../assets/jquery-3.6.0.min.js', [], filemtime( plugin_dir_path( __FILE__ ) . '../assets/jquery-3.6.0.min.js' ) );
    //     wp_enqueue_style( 'p4m-prayer-points-style', trailingslashit( plugin_dir_url( __FILE__ ) ) . '../assets/p4m-prayer-points-styles.css', [], filemtime( plugin_dir_path( __FILE__ ) . '../assets/p4m-prayer-points-styles.css' ) );
    // }
    // add_action( 'wp_enqueue_scripts', 'p4m_prayer_points_enqueue_scripts' );
    if ( isset( $_GET['view_library_id'] ) ) {
        show_prayer_points();
        return;
    }

    if ( isset( $_GET['download_library_id'] ) ) {
        show_download_library();
        return;
    }

    if ( isset( $_GET['prayer_tag'] ) ) {
        show_prayer_points_by_tag();
        return;
    }

    if ( isset( $_GET['download_tag'] ) ) {
        show_download_tag();
        return;
    }

    show_prayer_libraries();
    return;
}

function show_prayer_points() {
    if ( !isset( $_GET['view_library_id'] ) ) {
        return;
    }

    $params = get_library_js_parameters();

    wp_enqueue_script( 'p4m-prayer-points-scripts', trailingslashit( plugin_dir_url( __FILE__ ) ) . '../assets/p4m-prayer-points-functions.js', [], filemtime( plugin_dir_path( __FILE__ ) . '../assets/p4m-prayer-points-functions.js' ) );
    wp_localize_script( 'p4m-prayer-points-scripts', 'p4mPrayerPoints', $params );
    add_action( 'wp_footer', 'show_prayer_points_inline' );
    return;
}

function get_library_id_from_url() {
    if ( isset( $_GET['view_library_id'] ) ) {
        return sanitize_text_field( wp_unslash( $_GET['view_library_id'] ) );
    }
    if ( isset( $_GET['download_library_id'] ) ) {
        return sanitize_text_field( wp_unslash( $_GET['download_library_id'] ) );
    }
    return false;
}
function get_library_js_parameters() {
    $library_id = get_library_id_from_url();
    $library = get_prayer_library( $library_id );
    if ( $library ) {
        $params = [
            'libraryId' => $library['id'],
            'libraryKey' => $library['key'],
            'libraryName' => $library['name'],
            'nonce' => wp_create_nonce( 'wp_rest' ),
        ];
        return $params;
    }
}

function get_tag_from_url() {
    if ( isset( $_GET['download_tag'] ) ) {
        $tag = sanitize_text_field( wp_unslash( $_GET['download_tag'] ) );
        return $tag;
    }
}

function show_download_library() {
    if ( !isset( $_GET['download_library_id'] ) ) {
        return;
    }

    $params = get_library_js_parameters();
    $rules = get_prayer_library_rules_by_id_and_examples();
    $params['rules'] = $rules;
    wp_enqueue_script( 'p4m-prayer-points-scripts', trailingslashit( plugin_dir_url( __FILE__ ) ) . '../assets/p4m-prayer-points-functions.js', [], filemtime( plugin_dir_path( __FILE__ ) . '../assets/p4m-prayer-points-functions.js' ) );
    wp_localize_script( 'p4m-prayer-points-scripts', 'p4mPrayerPoints', $params );
    add_action( 'wp_footer', 'show_download_library_inline' );
}

function show_download_library_inline() {
    ?>
    <script>
        jQuery(document).ready(function() {
            if ( !jQuery.isEmptyObject(p4mPrayerPoints.tag)) {
                loadTagRules();
                return;
            }
            loadLibraryRules();
        });
    </script>
    <?php
}

function show_download_tag() {
    if ( !isset( $_GET['download_tag'] ) ) {
        return;
    }
    $params = [];
    $tag = get_tag_from_url();
    $tag_rules = get_tag_rules_and_examples( $tag );
    $params['tag'] = $tag;
    $params['rules'] = $tag_rules;
    $params['nonce'] = wp_create_nonce( 'wp_rest' );
    wp_enqueue_script( 'p4m-prayer-points-scripts', trailingslashit( plugin_dir_url( __FILE__ ) ) . '../assets/p4m-prayer-points-functions.js', [], filemtime( plugin_dir_path( __FILE__ ) . '../assets/p4m-prayer-points-functions.js' ) );
    wp_localize_script( 'p4m-prayer-points-scripts', 'p4mPrayerPoints', $params );
    add_action( 'wp_footer', 'show_download_library_inline' );
}

function get_prayer_library_rules_by_id_and_examples() {
    $library_id = get_library_id_from_url();
    $rules = get_prayer_library_rules_by_id( $library_id );
    $rules_with_examples = [];
    if ( empty( $rules ) ) {
        return false;
    }
    foreach ( $rules as $rule ) {
        $rule['example_from'] = get_prayer_library_rule_example( $library_id, $rule['replace_from'] );
        if ( $rule['example_from'] ) {
            $rule['example_to'] = str_replace( $rule['replace_from'], $rule['replace_to'], $rule['example_from'] );
        }
        $rules_with_examples[] = $rule;
    }
    return $rules_with_examples;
}
function get_prayer_library_rules_by_id( $library_id ) {
    global $wpdb;
    $rules = $wpdb->get_var(
        $wpdb->prepare( "SELECT `rules` FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE `id` = %d;", $library_id )
    );
    return maybe_unserialize( $rules );
}

function get_prayer_library_rule_example( $library_id, $replace_from ) {
    global $wpdb;
    return $wpdb->get_var(
        $wpdb->prepare( "SELECT `title` FROM `{$wpdb->prefix}dt_prayer_points` WHERE `library_id` = %d AND `title` LIKE CONCAT( '%', %s, '%' ) ORDER BY CHAR_LENGTH(`title`) ASC LIMIT 1;", $library_id, $replace_from )
    );
}

function get_prayer_library_rule_example_for_tag( $tag, $library_id, $replace_from ) {
    global $wpdb;
    return $wpdb->get_var(
        $wpdb->prepare( "SELECT pp.title FROM `{$wpdb->prefix}dt_prayer_points` pp
                         INNER JOIN `{$wpdb->prefix}dt_prayer_points_meta` pm
                         ON pp.id = pm.prayer_id
                         WHERE pp.library_id = %d
                         AND pp.title LIKE CONCAT( '%', %s, '%' )
                         AND pm.meta_value = %s
                         ORDER BY CHAR_LENGTH(pp.title)
                         ASC LIMIT 1;", $library_id, $replace_from, $tag )
    );
}

function get_tag_rules_and_examples( $tag ) {
    $tag_rules = get_tag_rules( $tag );
    $tag_rules_with_examples = [];
    if ( empty( $tag_rules ) ) {
        return false;
    }
    foreach ( $tag_rules as $tag_rule ) {
        $tag_rule['example_from'] = get_prayer_library_rule_example_for_tag( $tag, $tag_rule['library_id'], $tag_rule['replace_from'] );
        $tag_rule['example_to'] = str_replace( $tag_rule['replace_from'], $tag_rule['replace_to'], $tag_rule['example_from'] );
        $tag_rules_with_examples[] = $tag_rule;
    }
    return $tag_rules_with_examples;
}

function get_tag_rules( $tag ) {
    $tag_library_ids = get_library_ids_with_tag( $tag );
    $prayer_points_with_tag = get_prayer_points_by_tag( $tag );
    $tag_rules = [];
    foreach ( $tag_library_ids as $tag_library_id ) {
        $library_rules = get_prayer_library_rules_by_id( $tag_library_id );
        foreach ( $library_rules as $library_rule ) {
            foreach ( $prayer_points_with_tag as $prayer_point_with_tag ) {
                if ( isset( $library_rule['replace_from'] ) ) {
                    if ( rule_is_in_prayer_point( $prayer_point_with_tag, $library_rule['replace_from'] ) ) {
                        $tag_rules[] = $library_rule;
                        break;
                    }
                }
            }
        }
    }
    return $tag_rules;
}

function get_prayer_points_by_tag( $tag ) {
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

function rule_is_in_prayer_point( $prayer_point, $rule ) {
    if ( strpos( $prayer_point['title'], $rule ) ) {
        return true;
    }
    if ( strpos( $prayer_point['content'], $rule ) ) {
        return true;
    }
    return false;
}

function get_library_ids_with_tag( $tag ) {
    global $wpdb;
    return $wpdb->get_col(
        $wpdb->prepare( "SELECT DISTINCT( pp.library_id )
        FROM `{$wpdb->prefix}dt_prayer_points_meta` pm
        INNER JOIN `{$wpdb->prefix}dt_prayer_points` pp
        ON pm.prayer_id = pp.id
        WHERE meta_value = %s;", $tag ) );
}

function show_prayer_points_inline( $library_id ) {
    ?>
    <script>
        jQuery(document).ready(function() {
            loadPrayerPoints();
        });
    </script>
    <?php
}

function show_prayer_points_by_tag() {
    if ( !isset( $_GET['prayer_tag'] ) ) {
        return;
    }

    $tag = sanitize_text_field( wp_unslash( $_GET['prayer_tag'] ) );
    $params = [
        'tag' => $tag,
        'nonce' => wp_create_nonce( 'wp_rest' ),
    ];
    wp_enqueue_script( 'p4m-prayer-points-scripts', trailingslashit( plugin_dir_url( __FILE__ ) ) . '../assets/p4m-prayer-points-functions.js', [], filemtime( plugin_dir_path( __FILE__ ) . '../assets/p4m-prayer-points-functions.js' ) );
    wp_localize_script( 'p4m-prayer-points-scripts', 'p4mPrayerPoints', $params );
    add_action( 'wp_footer', 'show_prayer_points_by_tag_inline' );
}

function show_prayer_points_by_tag_inline() {
    ?>
    <script>
        jQuery(document).ready(function() {
            loadPrayerPointsByTag();
        });
    </script>
    <?php
}

function show_prayer_libraries() {
    $params = [ 'nonce' => wp_create_nonce( 'wp_rest' ) ];
    wp_enqueue_script( 'p4m-prayer-points-scripts', trailingslashit( plugin_dir_url( __FILE__ ) ) . '../assets/p4m-prayer-points-functions.js', [], filemtime( plugin_dir_path( __FILE__ ) . '../assets/p4m-prayer-points-functions.js' ) );
    wp_localize_script( 'p4m-prayer-points-scripts', 'p4mPrayerPoints', $params );
    add_action( 'wp_footer', 'show_prayer_libraries_inline' );
}

function show_prayer_libraries_inline() {
    ?>
    <script>
        jQuery(document).ready(function() {
            loadLibraries();
        });
    </script>
    <?php
}

function get_prayer_library( $library_id ) {
    global $wpdb;
    return $wpdb->get_row(
        $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE `id` = %d;", $library_id ), ARRAY_A
    );
}