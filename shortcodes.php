<?php
add_shortcode( 'show_prayer_libraries', 'p4m_prayer_libraries' );

function p4m_prayer_libraries() {
    ob_start();
    ?> <b>Hello, World!</b>
    <?php
    return ob_get_clean();

}