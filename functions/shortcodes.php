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
    $params = [
        'libraryId' => $library['id'],
        'libraryKey' => $library['key'],
        'libraryName' => $library['name'],
        'nonce' => wp_create_nonce( 'wp_rest' ),
    ];
    return $params;
}

function show_download_library() {
    if ( !isset( $_GET['download_library_id'] ) ) {
        return;
    }

    $params = get_library_js_parameters();
    $rules = get_prayer_library_rules_and_examples();
    $params['rules'] = $rules;
    wp_enqueue_script( 'p4m-prayer-points-scripts', trailingslashit( plugin_dir_url( __FILE__ ) ) . '../assets/p4m-prayer-points-functions.js', [], filemtime( plugin_dir_path( __FILE__ ) . '../assets/p4m-prayer-points-functions.js' ) );
    wp_localize_script( 'p4m-prayer-points-scripts', 'p4mPrayerPoints', $params );
    add_action( 'wp_footer', 'show_download_library_inline' );
}

function show_download_library_inline() {
    ?>
    <script>
        jQuery(document).ready(function() {
            loadLibraryRules();
        });
    </script>
    <?php
}

function get_prayer_library_rules_and_examples() {
    $rules = get_prayer_library_rules();
    $rules_with_examples = [];
    foreach ( $rules as $rule ) {
        $rule['example_from'] = get_prayer_library_rule_example( $rule['replace_from'] );
        if ( $rule['example_from'] ) {
            $rule['example_to'] = str_replace( $rule['replace_from'], $rule['replace_to'], $rule['example_from'] );
        }
        $rules_with_examples[] = $rule;
    }
    return $rules_with_examples;
}
function get_prayer_library_rules() {
    $library_id = get_library_id_from_url();
    global $wpdb;
    $rules = $wpdb->get_var(
        $wpdb->prepare( "SELECT `rules` FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE `id` = %d;", $library_id )
    );
    return maybe_unserialize( $rules );
}

function get_prayer_library_rule_example( $replace_from ) {
    $library_id = get_library_id_from_url();
    global $wpdb;
    return $wpdb->get_var(
        $wpdb->prepare( "SELECT `title` FROM `{$wpdb->prefix}dt_prayer_points` WHERE `library_id` = %d AND `title` LIKE CONCAT( '%', %s, '%' ) LIMIT 1;", $library_id, $replace_from )
    );
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