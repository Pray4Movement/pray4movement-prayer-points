<?php
add_shortcode( 'p4m_prayer_libraries', 'p4m_prayer_libraries' );

function p4m_prayer_libraries() {
    add_action( 'wp_enqueue_scripts', 'p4m_prayer_points_enqueue_scripts' );
    if ( isset( $_GET['library_id'] ) ) {
        show_prayer_points();
        return;
    }

    if ( isset( $_GET['prayer_tag'] ) ) {
        show_prayer_points_by_tag();
        return;
    }

    show_prayer_libraries();
    return;
}

function p4m_prayer_points_enqueue_scripts( $params ) {
    wp_enqueue_style( 'p4m-prayer-points-style', trailingslashit( plugin_dir_url( __FILE__ ) ) . '../assets/p4m-prayer-points-styles.css', [], filemtime( plugin_dir_path( __FILE__ ) . '../assets/p4m-prayer-points-styles.css' ) );
    wp_enqueue_script( 'jquery', 'https://code.jquery.com/jquery-3.6.0.min.js', [], '3.6.0' );
}

function show_prayer_points() {
    if ( !isset( $_GET['library_id'] ) ) {
        return;
    }

    $library_id = sanitize_text_field( wp_unslash( $_GET['library_id'] ) );
    $params = [
        'libraryId' => $library_id,
        'nonce' => wp_create_nonce( 'wp_rest' ),
    ];
    wp_enqueue_script( 'p4m-prayer-points-scripts', trailingslashit( plugin_dir_url( __FILE__ ) ) . '../assets/p4m-prayer-points-functions.js', [], filemtime( plugin_dir_path( __FILE__ ) . '../assets/p4m-prayer-points-functions.js' ) );
    wp_localize_script( 'p4m-prayer-points-scripts', 'p4mPrayerPoints', $params );
    add_action( 'wp_footer', 'show_prayer_points_inline' );
    return;
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