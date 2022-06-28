<?php
add_shortcode( 'p4m_prayer_libraries', 'show_prayer_libraries' );

function show_prayer_libraries() {
    add_action( 'wp_enqueue_scripts', 'p4m_prayer_points_enqueue_scripts' );

    ?>
    <?php
    if ( isset( $_GET['library_id'] ) ) {
        show_prayer_points( sanitize_text_field( wp_unslash( $_GET['library_id'] ) ) );
        return;
    }

    if ( isset( $_GET['tag'] ) ) {
        show_prayer_points_by_tag( sanitize_text_field( wp_unslash( $_GET['tag'] ) ) );
        return;
    }
}

function p4m_prayer_points_enqueue_scripts() {
    wp_enqueue_style( 'p4m-prayer-points-style', trailingslashit( plugin_dir_url( __FILE__ ) ) . '../assets/p4m-prayer-points-styles.css', [], filemtime( plugin_dir_path( __FILE__ ) . '../assets/p4m-prayer-points-styles.css' ) );
    wp_enqueue_script( 'jquery', 'https://code.jquery.com/jquery-3.6.0.min.js', [], '3.6.0' );
    wp_enqueue_script( 'p4m-prayer-points-scripts', trailingslashit( plugin_dir_url( __FILE__ ) ) . '../assets/p4m-prayer-points-functions.js', [], filemtime( plugin_dir_path( __FILE__ ) . '../assets/p4m-prayer-points-functions.js' ) );
}

function show_prayer_points( $library_id ) {
    ?>
    <script>
        jQuery(document).ready(function() {
            loadPrayerPoints('<?php echo esc_html( $library_id ); ?>');
        });
    </script>
    <?php
}

function show_prayer_points_by_tag( $tag ) {
    ?>
    <script>
        jQuery(document).ready(function() {
            loadPrayerPointsByTag('<?php echo esc_html( $tag ); ?>');
        });
    </script>
    <?php
}

