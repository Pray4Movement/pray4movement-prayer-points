<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
Pray4Movement_Prayer_Points_Menu::instance();

class Pray4Movement_Prayer_Points_Menu {
    public $token = 'pray4movement_prayer_points';
    public $page_title = 'Pray4Movement Prayer Points';
    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_action( "admin_menu", array( $this, "register_menu" ) );
        $this->page_title = __( "Pray4Movement Prayer Points", 'pray4movement-prayer-points' );
    } // End __construct()

    public function register_menu() {
        $this->page_title = __( "Pray4Movement Prayer Points", 'pray4movement-prayer-points' );
        $menu_icon = Pray4Movement_Prayer_Points_Utilities::get_default_prayer_library_icon();
        add_menu_page( 'Prayer Points', 'Prayer Points', 'manage_dt', $this->token, [ $this, 'content' ], $menu_icon, 7 );
    }

    // Menu stub. Replaced when Disciple.Tools Theme fully loads.
    public function extensions_menu() {}

    public function content() {
        Pray4Movement_Prayer_Points_Utilities::check_permissions();
        $this->check_view_library_tab();
        $this->check_edit_library_tab();
        $this->check_edit_prayer_tab();
        $tab = $this->get_sanitized_tab();
        $this->display_html_for_tab( $tab );
    }

    private function check_view_library_tab() {
        if ( isset( $_GET['view_library'] ) ) {
            $object = new Pray4Movement_Prayer_Points_View_Library();
            $object->content();
            die();
        }
    }

    private function check_edit_library_tab() {
        if ( isset( $_GET['edit_library'] ) ) {
            $object = new Pray4Movement_Prayer_Points_Edit_Library();
            $object->content();
            die();
        }
    }

    private function check_edit_prayer_tab() {
        if ( isset( $_GET['edit_prayer'] ) ) {
            $object = new Pray4Movement_Prayer_Points_Edit_Prayer();
            $object->content();
            die();
        }
    }

    private function get_sanitized_tab() {
        $tab = 'explore';
        if ( isset( $_GET['tab'] ) ) {
            $tab = sanitize_key( wp_unslash( $_GET['tab'] ) );
        }
        return $tab;
    }

    private function display_html_for_tab( $tab ) {
        $link = self::get_url_path_with_tab();
        ?>
        <div class="wrap">
            <h2><?php echo esc_html( $this->page_title ); ?></h2>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_attr( $link ) . 'explore' ?>"
                class="nav-tab <?php echo esc_html( ( $tab == 'explore' || !isset( $tab ) ) ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html( 'Explore', 'pray4movement_prayer_points' ); ?></a>
                <a href="<?php echo esc_attr( $link ) . 'import' ?>" class="nav-tab <?php echo esc_html( ( $tab == 'import' ) ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html( 'Import', 'pray4movement_prayer_points' ); ?></a>
                <a href="<?php echo esc_attr( $link ) . 'export' ?>" class="nav-tab <?php echo esc_html( ( $tab == 'export' ) ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html( 'Export', 'pray4movement_prayer_points' ); ?></a>
            </h2>
            <?php $this->show_content_for_tab( $tab ); ?>
        </div>
        <?php
    }

    private function get_url_path_with_tab() {
        return 'admin.php?page='.$this->token.'&tab=';
    }

    private function show_content_for_tab( $tab ) {
        switch ( $tab ) {
            case 'explore':
                $object = new Pray4Movement_Prayer_Points_Tab_Explore();
                $object->content();
                break;
            case 'import':
                $object = new Pray4Movement_Prayer_Points_Tab_Import();
                $object->content();
                break;
            case 'export':
                $object = new Pray4Movement_Prayer_Points_Tab_Export();
                $object->content();
                break;
            default:
                break;
        }
    }
}

Pray4Movement_Prayer_Points_Menu::instance();

class Pray4Movement_Prayer_Points_Utilities {
    public static function check_permissions() {
        if ( !current_user_can( 'manage_dt' ) ) {
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }
    }

    public static function get_default_prayer_library_icon() {
        return 'data:image/svg+xml;base64,PHN2ZyBpZD0ic3ZnIiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHdpZHRoPSI0MDAiIGhlaWdodD0iNDAwIiB2aWV3Qm94PSIwLCAwLCA0MDAsNDAwIj48ZyBpZD0ic3ZnZyI+PHBhdGggaWQ9InBhdGgwIiBkPSJNMjE5LjcxNiAxOS4yNDggQyAxMzkuNDUzIDMzLjcwNyw5Ni42MDggMTI3LjA1OSwxMzcuODU3IDE5Ny42MDAgTCAxNDEuNjAwIDIwNC4wMDAgMTU3LjExOSAyMDMuNTQzIEwgMTcyLjYzOCAyMDMuMDg2IDE2NC4yNDIgMTkwLjc0MyBDIDEyMS40OTUgMTI3LjkwMSwxNjUuMDQwIDQ0LjU0OSwyNDAuODAwIDQ0LjIwMCBDIDMyMS4xODkgNDMuODI4LDM2NC43NzIgMTM1LjA0OCwzMTQuMjM4IDE5Ny45MDggTCAzMTAuMTMyIDIwMy4wMTYgMzE2LjEwNyAyMDEuMzY2IEMgMzE5LjM5NCAyMDAuNDU5LDMyNS4wMzQgMjAwLjA5NywzMjguNjQyIDIwMC41NjIgQyAzMzIuMjQ5IDIwMS4wMjcsMzM2Ljk0OCAyMDEuNjMxLDMzOS4wODQgMjAxLjkwNCBDIDM0Ny45NDYgMjAzLjAzNiwzNjEuNjAwIDE2NC44MTcsMzYxLjYwMCAxMzguODc3IEMgMzYxLjYwMCA2My4zMjQsMjkzLjU0OSA1Ljk0NywyMTkuNzE2IDE5LjI0OCBNMjI4LjgwMCA4NS42MDAgTCAyMjguODAwIDk3LjYwMCAyMTEuMjAwIDk3LjYwMCBMIDE5My42MDAgOTcuNjAwIDE5My42MDAgMTExLjIwMCBMIDE5My42MDAgMTI0LjgwMCAyMTEuMjAwIDEyNC44MDAgTCAyMjguODAwIDEyNC44MDAgMjI4LjgwMCAxNjEuNjAwIEwgMjI4LjgwMCAxOTguNDAwIDI0Mi40MDAgMTk4LjQwMCBMIDI1Ni4wMDAgMTk4LjQwMCAyNTYuMDAwIDE2MS42MDAgTCAyNTYuMDAwIDEyNC44MDAgMjcyLjgwMCAxMjQuODAwIEwgMjg5LjYwMCAxMjQuODAwIDI4OS42MDAgMTExLjIwMCBMIDI4OS42MDAgOTcuNjAwIDI3Mi44MDAgOTcuNjAwIEwgMjU2LjAwMCA5Ny42MDAgMjU2LjAwMCA4NS42MDAgTCAyNTYuMDAwIDczLjYwMCAyNDIuNDAwIDczLjYwMCBMIDIyOC44MDAgNzMuNjAwIDIyOC44MDAgODUuNjAwIE0yOTcuNjIzIDIyNy43MTkgQyAyNjkuMjU3IDIzNi43MTgsMjcwLjU4MSAyMzUuNzkxLDI2OC4wMTggMjQ4LjQ0MCBDIDI2NC41ODUgMjY1LjM4NiwyNDkuMDY4IDI3MS45NjQsMTk5LjIwMCAyNzcuNjEzIEMgMTY0LjE5NiAyODEuNTc4LDE1OC40MDAgMjgwLjUyMiwxNTguNDAwIDI3MC4xODAgQyAxNTguNDAwIDI2MS45OTIsMTYwLjc2MyAyNjEuMDA3LDE4NC44MDAgMjU5LjE3MyBDIDIzNy4zNjAgMjU1LjE2NSwyNTIuNjcyIDI0OS4yNTYsMjQ4Ljk0OCAyMzQuNDIwIEMgMjQ2LjgwNCAyMjUuODc3LDI0My4xNTIgMjI1LjEwNCwxOTcuODE0IDIyMy42MDcgQyAxNTEuNTM3IDIyMi4wNzksMTQ1LjMyNSAyMjIuNDQzLDEzNS45NDYgMjI3LjIyOCBDIDEyOC43MjcgMjMwLjkxMCw2My45NDIgMjc4LjM2Myw2NC4xMDQgMjc5Ljg0OSBDIDY0LjE2MSAyODAuMzcyLDc5LjY3OCAyOTIuMTg3LDk4LjU4NyAzMDYuMTA1IEwgMTMyLjk2NiAzMzEuNDEwIDE3MC4wODMgMzM0LjczMyBDIDIxMS41ODMgMzM4LjQ1MCwyMTkuMzkzIDMzNy44NjMsMjMxLjA4MyAzMzAuMTUxIEMgMjQyLjQ2MCAzMjIuNjQ2LDMzNS44MzQgMjQ3Ljk3OCwzMzguMzY5IDI0NC4zNTggQyAzNDMuNjgxIDIzNi43NzUsMzM5LjY1NiAyMjMuOTg4LDMzMS4wMTAgMjIwLjk3MyBDIDMyNC42MDUgMjE4Ljc0MSwzMjguMjMyIDIxOC4wMDgsMjk3LjYyMyAyMjcuNzE5IE0zNy4yMDAgMzA4LjM0MCBDIDI0LjgwMSAzMjQuNjk5LDIzLjUyNiAzMjguNzcxLDI5LjIwMCAzMzMuODg5IEMgNDIuOTY5IDM0Ni4zMDgsODYuODgzIDM3Ni4yMjYsOTAuMTQyIDM3NS40MDggQyA5My4yNzggMzc0LjYyMSwxMTIuMjYwIDM1MC45NTMsMTE0LjU1OCAzNDQuOTY2IEMgMTE1LjA0MiAzNDMuNzA1LDEwNC4zMDMgMzM0LjYzNCw4Ni44NzQgMzIxLjU4MiBDIDcxLjIxMyAzMDkuODU1LDU2LjI0MCAyOTguNjM0LDUzLjYwMCAyOTYuNjQ4IEwgNDguODAwIDI5My4wMzYgMzcuMjAwIDMwOC4zNDAgIiBzdHJva2U9Im5vbmUiIGZpbGw9IiMwMDAwMDAiIGZpbGwtcnVsZT0iZXZlbm9kZCI+PC9wYXRoPjwvZz48L3N2Zz4=';
    }

    public static function admin_notice( string $notice, string $type ) {
        ?>
        <div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
            <p><?php echo esc_html( $notice ); ?></p>
        </div>
        <?php
    }

    public static function get_prayer_library( $library_id ) {
        global $wpdb;
        $library = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE id = %d;", $library_id
            ), ARRAY_A
        );
        return $library;
    }

    public static function get_lib_prayer_points( $library_id ) {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    id,
                    library_id,
                    REPLACE(
                        REPLACE(
                            `title`,
                            'XXX',
                            IFNULL((SELECT meta_value FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'people_group'), 'XXX')
                        ),
                        'YYY',
                        IFNULL((SELECT meta_value FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'location'), 'YYY')
                    ) AS `title`,
                    REPLACE(
                        REPLACE(
                            `content`,
                            'XXX',
                            IFNULL((SELECT meta_value FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'people_group'), 'XXX')
                        ),
                        'YYY',
                        IFNULL((SELECT meta_value FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'location'), 'YYY')
                    ) AS `content`,
                    reference,
                    book,
                    verse,
                    hash,
                    status
                FROM `{$wpdb->prefix}dt_prayer_points`
                WHERE library_id = %d;", $library_id
            ), ARRAY_A
        );
    }

    public static function get_prayer_point( $prayer_id ) {
        $prayer = self::get_prayer_content( $prayer_id );
        $prayer['tags'] = self::get_prayer_tags( $prayer_id );
        return $prayer;
    }

    public static function get_prayer_point_from_url_param() {
        if ( !isset( $_GET['edit_prayer'] ) || empty( $_GET['edit_prayer'] ) || !is_numeric( $_GET['edit_prayer'] ) ) {
            self::admin_notice( 'Invalid Prayer Point ID', 'error' );
            return;
        }
        $prayer_id = sanitize_text_field( wp_unslash( $_GET['edit_prayer'] ) );
        return self::get_prayer_point( $prayer_id );
    }

    public static function get_prayer_content( $prayer_id ) {
        if ( !isset( $prayer_id ) ) {
            return;
        }
        global $wpdb;
        $prayer_point = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM `{$wpdb->prefix}dt_prayer_points` WHERE id = %d;", $prayer_id
            ), ARRAY_A
        );
        return $prayer_point;
    }

    public static function get_prayer_tags( $prayer_id ) {
        global $wpdb;
        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT meta_value FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'tags' AND prayer_id = %d;", $prayer_id
            )
        );
    }

    public static function get_library_id( $prayer_id ) {
        if ( !isset( $prayer_id ) ) {
            return;
        }
        global $wpdb;
        $prayer_point = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT library_id FROM `{$wpdb->prefix}dt_prayer_points` WHERE id = %d;", $prayer_id
            )
        );
        return $prayer_point;
    }

    public static function get_last_prayer_point_id() {
        global $wpdb;
        return $wpdb->get_var(
            "SELECT id FROM `{$wpdb->prefix}dt_prayer_points` ORDER BY id DESC LIMIT 1;"
        );
    }

    public static function check_prayer_id_exists( $prayer_id ) {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}dt_prayer_points WHERE id = %d", $prayer_id )
        );
    }

    public static function count_prayer_points_in_library( $library_id ) {
        global $wpdb;
        $count_prayer_points_in_library = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM `{$wpdb->prefix}dt_prayer_points` WHERE library_id = %d;", $library_id
            )
        );
        return $count_prayer_points_in_library;
    }

    public static function sanitize_tags( $raw_tags ) {
        $tags = sanitize_text_field( wp_unslash( strtolower( $raw_tags ) ) );
        $tags = explode( ',', $tags );
        $tags = array_map( 'trim', $tags );
        return array_filter( $tags );
    }

    public static function get_prayer_libraries() {
        global $wpdb;
        $prayer_libraries = $wpdb->get_results(
            "SELECT * FROM `{$wpdb->prefix}dt_prayer_points_lib`;", ARRAY_A
        );
        return $prayer_libraries;
    }

    public static function get_meta_value_by_key( $meta_key ) {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}dt_prayer_points_meta WHERE meta_key = %s;", $meta_key )
        );
    }

    public static function insert_prayer_library( $library ) {
        global $wpdb;
        $test = $wpdb->insert(
            $wpdb->prefix.'dt_prayer_points_lib',
            [
                'key' => $library['key'],
                'name' => $library['name'],
                'description' => $library['desc'],
                'icon' => $library['icon'],
            ],
            [ '%s', '%s', '%s', '%s' ]
        );
        if ( !$test ) {
            self::admin_notice( __( 'Could not add new Prayer Library to table', 'pray4movement_prayer_points' ), 'error' );
            return;
        }
        self::admin_notice( __( 'Prayer Library created successfully!', 'pray4movement_prayer_points' ), 'success' );
    }

    public static function insert_prayer_point( $prayer ) {
        global $wpdb;
        $test = $wpdb->insert(
            $wpdb->prefix.'dt_prayer_points',
            [
                'library_id' => $prayer['library_id'],
                'title' => $prayer['title'],
                'content' => $prayer['content'],
                'hash' => $prayer['hash'],
                'book' => $prayer['book'],
                'verse' => $prayer['verse'],
                'reference' => $prayer['reference'],
                'status' => $prayer['status'],
            ],
            [ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );
        if ( !$test ) {
            self::admin_notice( __( 'Could not add new Prayer Point to library', 'pray4movement_prayer_points' ), 'error' );
        }
        return;
    }

    public static function insert_all_tags( $prayer_id, $tags ) {
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

    public static function update_prayer_point( $prayer ) {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix.'dt_prayer_points',
            [
                'title' => $prayer['title'],
                'content' => $prayer['content'],
                'reference' => $prayer['reference'],
                'book' => $prayer['book'],
                'verse' => $prayer['verse'],
                'status' => $prayer['status'],
                'hash' => $prayer['hash'],
            ],
            [ 'id' => $prayer['id'] ],
            [ '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );
        return;
    }

    public static function update_prayer_tags( $prayer_id, $tags ) {
        self::delete_prayer_tags( $prayer_id );
        self::insert_all_tags( $prayer_id, $tags );
        return;
    }

    public static function get_prayer_reference( $book, $verse ) {
        if ( !empty( $book ) ) {
            $reference = $book;
            if ( !empty( $verse ) ) {
                $reference .= " $verse";
            }
            return $reference;
        }
    }

    public static function delete_prayer_tags( $prayer_id ) {
        global $wpdb;
        return $wpdb->query(
            $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = 'tags' AND prayer_id = %d;", $prayer_id )
        );
    }

    public static function sanitize_library_post_variables() {
        if ( !isset( $_POST['add_library_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['add_library_nonce'] ), 'add_library' ) ) {
            return;
        }
        if ( !isset( $_POST['library_name'] ) || isset( $_POST['library_desc'] ) || isset( $_POST['library_icon'] ) ) {
            return;
        }
        $library = [
            'name' => sanitize_text_field( wp_unslash( $_POST['library_name'] ) ),
            'desc' => sanitize_text_field( wp_unslash( $_POST['library_desc'] ) ),
            'icon' => sanitize_text_field( wp_unslash( $_POST['library_icon'] ) ),
        ];
        if ( isset( $_POST['library_id'] ) ) {
            $library['id'] = sanitize_text_field( wp_unslash( $_POST['library_id'] ) );
        }
        return $library;
    }

    public static function generate_key_from_string( $string ) {
        return strtolower( str_replace( ' ', '_', $string ) );
    }

    public static function check_library_id_exists( $library_id ) {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare( "SELECT id FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE id = %d;", $library_id )
        );
    }

    public static function localize_prayers_column() {
        $location = self::get_meta_value_by_key( 'location' );
        $people_group = self::get_meta_value_by_key( 'people_group' );
        ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th colspan="2">
                        <?php esc_html_e( 'Localize your prayers', 'pray4movement_prayer_points' ); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <?php esc_html_e( 'Location:', 'pray4movement_prayer_points' ); ?>
                </td>
                <td>
                    <input type="text" id="location" class="localization" value="<?php echo esc_html( $location ); ?>">
                </td>
            </tr>
            <tr>
                <td>
                    <?php esc_html_e( 'People Group:', 'pray4movement_prayer_points' ); ?>
                </td>
                <td>
                    <input type="text" id="people_group" class="localization" value="<?php echo esc_html( $people_group ); ?>">
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align:right;">
                    <button id="update_localization" class="button" type="post" disabled>Update</button>
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <script>
            jQuery('.localization').on('input', function() {
                jQuery('#update_localization').prop('disabled',false);
            });

            jQuery('#update_localization').on('click', function() {
                var location = jQuery('#location')[0].value;
                var people_group = jQuery('#people_group')[0].value;
                jQuery.ajax( {
                        type: 'POST',
                        contentType: 'application/json; charset=utf-8',
                        dataType: 'json',
                        url: window.location.origin + `/wp-json/pray4movement-prayer-points/v1/set_location_and_people_group/${location}/${people_group}`,
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                        },
                        success: update_localization_success(),
                    } );
            });

            function update_localization_success() {
                var update_button = jQuery('#update_localization');
                update_button.html('Updated!');
                update_button.prop('disabled',true);
            }
        </script>
        <?php
    }
}

class Pray4Movement_Prayer_Points_Tab_Explore {
    public function content() {
        Pray4Movement_Prayer_Points_Utilities::check_permissions();
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <?php $this->main_explore_column(); ?>
                    </div>
                    <div id="postbox-container-1" class="postbox-container">
                        <?php Pray4Movement_Prayer_Points_Utilities::localize_prayers_column(); ?>
                    </div>
                    <div id="postbox-container-2" class="postbox-container">
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function main_explore_column() {
        $this->process_add_library();
        ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th colspan="5"><?php esc_html_e( 'Prayer Libraries', 'pray4movement_prayer_points' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Prayer Points</th>
                    <th>Actions</th>
                </tr>
                <?php
                $prayer_libraries = Pray4Movement_Prayer_Points_Utilities::get_prayer_libraries();
                $this->display_prayer_libraries( $prayer_libraries );
                ?>
            </tbody>
        </table>
        <br>
        <form method="POST">
            <?php wp_nonce_field( 'add_library', 'add_library_nonce' ); ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th colspan="3"><?php esc_html_e( 'Add Prayer Library', 'pray4movement_prayer_points' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <tr>
                <td>
                    <?php esc_html_e( 'Name', 'pray4movement_prayer_points' ); ?>
                </td>
                <td>
                    <input type="text" name="library_name">
                </td>
            </tr>
            <tr>
                <td>
                    <?php esc_html_e( 'Description', 'pray4movement_prayer_points' ); ?>
                </td>
                <td>
                    <input type="text" name="library_desc" size="50">
                </td>
            </tr>
            <tr>
                <td>
                    <?php esc_html_e( 'Icon', 'pray4movement_prayer_points' ); ?>
                </td>
                <td>
                    <textarea name="library_icon" cols="50" placeholder="data:image/svg+xml;base64,PHN2ZyBpZD0..."></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <button class="button" type="post"><?php esc_html_e( 'Add', 'pray4movement_prayer_points' ); ?></button>
                </td>
            </tr>
                </tbody>
            </table>
        </form>
        <script>
            jQuery('.delete_library').on('click', function () {
                var lib_name = jQuery(this).data('name');
                if(confirm(`Delete the '${lib_name}' Prayer Library?`)) {
                    var library_id = jQuery(this).data('id');
                    jQuery.ajax({
                        type: 'POST',
                        contentType: 'application/json; charset=utf-8',
                        dataType: 'json',
                        url: window.location.origin + '/wp-json/pray4movement-prayer-points/v1/delete_prayer_library/' + library_id,
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                        },
                        success: delete_lib_success(library_id, lib_name),
                    });
                }
            } );

            function delete_lib_success( library_id, lib_name ) {
                jQuery( '#delete-library-' + library_id ).remove();
                    let admin_notice = `
                        <div class="notice notice-success is-dismissible">
                            <p>'${lib_name}' Prayer Library deleted successfully!</p>
                        </div>
                    `;
                jQuery('.nav-tab-wrapper').before(admin_notice);
            }
        </script>
        <?php
    }

    private function process_add_library() {
        if ( !isset( $_POST['add_library_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['add_library_nonce'] ), 'add_library' ) ) {
            return;
        }
        if ( !isset( $_POST['library_name'] ) || !isset( $_POST['library_desc'] ) || !isset( $_POST['library_icon'] ) ) {
            return;
        }
        $library = [
            'name' => sanitize_text_field( wp_unslash( $_POST['library_name'] ) ),
            'desc' => sanitize_text_field( wp_unslash( $_POST['library_desc'] ) ),
            'icon' => sanitize_text_field( wp_unslash( $_POST['library_icon'] ) ),
        ];
        $library['key'] = Pray4Movement_Prayer_Points_Utilities::generate_key_from_string( $library['name'] );
        Pray4Movement_Prayer_Points_Utilities::insert_prayer_library( $library );
    }

    public function display_prayer_libraries( $prayer_libraries ) {
        foreach ( $prayer_libraries as $library ) :
            $prayer_icon = Pray4Movement_Prayer_Points_Utilities::get_default_prayer_library_icon();
            if ( isset( $library['icon'] ) ) {
                if ( $library['icon'] !== '' ) {
                    $prayer_icon = $library['icon'];
                }
            }
            ?>
        <tr id="delete-library-<?php echo esc_html( $library['id'] ); ?>">
            <td><img src="<?php echo esc_html( $prayer_icon ); ?>" width="50px"></td>
            <td><a href="/wp-admin/admin.php?page=pray4movement_prayer_points&view_library=<?php echo esc_html( $library['id'] ); ?>"><?php echo esc_html( $library['name'] ); ?></a></td>
            <td><?php echo esc_html( $library['description'] ); ?></td>
            <td><?php echo esc_html( Pray4Movement_Prayer_Points_Utilities::count_prayer_points_in_library( $library['id'] ) ); ?></td>
            <td>
                <a href="/wp-admin/admin.php?page=pray4movement_prayer_points&edit_library=<?php echo esc_attr( $library['id'] ); ?>"><?php esc_html_e( 'Edit', 'pray4movement_prayer_points' ); ?></a> | 
                <a href="#" style="color:#b32d2e;" class="delete_library" data-id="<?php echo esc_attr( $library['id'] ); ?>" data-name="<?php echo esc_html( $library['name'] ); ?>"><?php esc_html_e( 'Delete', 'pray4movement_prayer_points' ); ?></a>
            </td>
        </tr>
        <?php endforeach;
    }
}


class Pray4Movement_Prayer_Points_Edit_Library {
    public function content() {
        Pray4Movement_Prayer_Points_Utilities::check_permissions();
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <?php $this->main_edit_library_column(); ?>
                    </div>
                    <div id="postbox-container-1" class="postbox-container">
                        <?php $this->right_edit_library_column(); ?>
                    </div>
                    <div id="postbox-container-2" class="postbox-container">
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function main_edit_library_column() {
        if ( !isset( $_GET['edit_library'] ) || empty( $_GET['edit_library'] ) || !is_numeric( $_GET['edit_library'] ) ) {
            Pray4Movement_Prayer_Points_Utilities::admin_notice( 'Invalid Prayer Library ID', 'error' );
            return;
        }
        $this->process_edit_library();
        $library_id = sanitize_key( wp_unslash( $_GET['edit_library'] ) );
        $library = Pray4Movement_Prayer_Points_Utilities::get_prayer_library( $library_id );
        ?>
        <p>
            <a href="/wp-admin/admin.php?page=pray4movement_prayer_points"><?php esc_html_e( '<< Back to Prayer Libraries', 'pray4movement_prayer_points' ); ?></a>
        </p>
        <form method="POST">
            <?php wp_nonce_field( 'edit_library', 'edit_library_nonce' ); ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th colspan="3"><?php esc_html_e( 'Edit Prayer Library', 'pray4movement_prayer_points' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <tr>
                <td>
                    <?php esc_html_e( 'Name', 'pray4movement_prayer_points' ); ?>
                </td>
                <td>
                    <input type="text" name="library_name" size="50" value="<?php echo esc_attr( $library['name'] ); ?>">
                </td>
            </tr>
            <tr>
                <td>
                    <?php esc_html_e( 'Description', 'pray4movement_prayer_points' ); ?>
                </td>
                <td>
                    <input type="text" name="library_desc" size="50" value="<?php echo esc_attr( $library['description'] ); ?>">
                </td>
            </tr>
            <tr>
                <td>
                    <?php esc_html_e( 'Icon', 'pray4movement_prayer_points' ); ?>
                </td>
                <td>
                    <textarea name="library_icon" cols="50" placeholder="data:image/svg+xml;base64,PHN2ZyBpZD0..."><?php echo esc_attr( $library['icon'] ); ?></textarea>
                    <input type="hidden" name="library_id" size="50" value="<?php echo esc_attr( $library['id'] ); ?>">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <button class="button" type="post"><?php esc_html_e( 'Update', 'pray4movement_prayer_points' ); ?></button>
                </td>
            </tr>
                </tbody>
            </table>
        </form>
        <?php
    }

    private function check_library_id_is_set() {
        if ( !isset( $_GET['edit_library'] ) || empty( $_GET['edit_library'] ) ) {
            Pray4Movement_Prayer_Points_Utilities::admin_notice( 'Invalid Prayer Library ID', 'error' );
            return false;
        }
    }

    public function right_edit_library_column() {
        ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Information', 'pray4movement_prayer_points' ); ?></th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <?php esc_html_e( 'Edit your Prayer Library.', 'pray4movement_prayer_points' ); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?php esc_html_e( 'If you remove the custom Prayer Library Icon, the default icon will be used instead.', 'pray4movement_prayer_points' ); ?>
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <?php
    }

    private function process_edit_library() {
        if ( !isset( $_POST['edit_library_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['edit_library_nonce'] ), 'edit_library' ) ) {
            return;
        }

        if ( !isset( $_POST['library_id'] ) || !isset( $_POST['library_name'] ) || !isset( $_POST['library_desc'] ) || !isset( $_POST['library_icon'] ) ) {
            return;
        }
        $library = [
            'id' => sanitize_text_field( wp_unslash( $_POST['library_id'] ) ),
            'name' => sanitize_text_field( wp_unslash( $_POST['library_name'] ) ),
            'desc' => sanitize_text_field( wp_unslash( $_POST['library_desc'] ) ),
            'icon' => sanitize_text_field( wp_unslash( $_POST['library_icon'] ) ),
        ];
        $this->update_prayer_library( $library );
    }

    private function update_prayer_library( $library ) {
        global $wpdb;
        $test = $wpdb->update(
            $wpdb->prefix.'dt_prayer_points_lib',
            [
                'name' => $library['name'],
                'description' => $library['desc'],
                'icon' => $library['icon'],
            ],
            [ 'id' => $library['id'] ],
            [ '%s', '%s', '%s' ]
        );
        if ( !$test ) {
            Pray4Movement_Prayer_Points_Utilities::admin_notice( __( 'Could not update Prayer Library', 'pray4movement_prayer_points' ), 'error' );
            return;
        }
        Pray4Movement_Prayer_Points_Utilities::admin_notice( __( 'Prayer Library updated successfully!', 'pray4movement_prayer_points' ), 'success' );
    }
}

class Pray4Movement_Prayer_Points_View_Library {
    public function content() {
        Pray4Movement_Prayer_Points_Utilities::check_permissions();
        ?>
        <div class="wrap">
            <div id="poststuff">
                <p>
                    <a href="/wp-admin/admin.php?page=pray4movement_prayer_points"><?php esc_html_e( '<< Back to Prayer Libraries', 'pray4movement_prayer_points' ); ?></a>
                </p>
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <?php $this->main_view_library_column(); ?>
                    </div>
                    <div id="postbox-container-1" class="postbox-container">
                        <?php Pray4Movement_Prayer_Points_Utilities::localize_prayers_column(); ?>
                    </div>
                    <div id="postbox-container-2" class="postbox-container">
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function main_view_library_column() {
        $this->process_add_prayer_point();
        if ( !isset( $_GET['view_library'] ) || empty( $_GET['view_library'] ) || !is_numeric( $_GET['view_library'] ) ) {
            Pray4Movement_Prayer_Points_Utilities::admin_notice( 'Invalid Prayer Library ID', 'error' );
            return;
        }
        $library_id = sanitize_key( wp_unslash( $_GET['view_library'] ) );
        if ( ! Pray4Movement_Prayer_Points_Utilities::check_library_id_exists( $library_id ) ) {
            Pray4Movement_Prayer_Points_Utilities::admin_notice( 'Prayer Library ID does not exist.', 'error' );
            return;
        }
        $library = Pray4Movement_Prayer_Points_Utilities::get_prayer_library( $library_id );
        ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <?php if ( !empty( $library ) ) : ?>
                        <th colspan="6"><?php echo esc_html( $library['name'] ); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>#</th>    
                    <th>Title</th>
                    <th>Reference</th>
                    <th>Content</th>
                    <th>Tags</th>
                    <th>Actions</th>
                </tr>
                <?php self::display_prayer_points( $library['id'] ); ?>
            </tbody>
        </table>
        <br>
        <form method="POST">
            <?php wp_nonce_field( 'add_prayer_point', 'add_prayer_point_nonce' ); ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th colspan="3"><?php esc_html_e( 'Add Prayer Point', 'pray4movement_prayer_points' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <?php esc_html_e( 'Title', 'pray4movement_prayer_points' ); ?> (*)
                        </td>
                        <td>
                            <input type="text" name="prayer_title" size="50" required>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php esc_html_e( 'Reference', 'pray4movement_prayer_points' ); ?>
                        </td>
                        <td>
                            <select name="prayer_reference_book" id="">
                                <option value="">(No Reference)</option>
                                <option value="Genesis">Genesis</option>
                                <option value="Exodus">Exodus</option>
                                <option value="Leviticus">Leviticus</option>
                                <option value="Numbers">Numbers</option>
                                <option value="Deuteronomy">Deuteronomy</option>
                                <option value="Joshua">Joshua</option>
                                <option value="Judges">Judges</option>
                                <option value="Ruth">Ruth</option>
                                <option value="1 Samuel">1 Samuel</option>
                                <option value="2 Samuel">2 Samuel</option>
                                <option value="1 Kings">1 Kings</option>
                                <option value="2 Kings">2 Kings</option>
                                <option value="1 Chronicles">1 Chronicles</option>
                                <option value="2 Chronicles">2 Chronicles</option>
                                <option value="Ezra">Ezra</option>
                                <option value="Nehemiah">Nehemiah</option>
                                <option value="Esther">Esther</option>
                                <option value="Job">Job</option>
                                <option value="Psalm">Psalm</option>
                                <option value="Proverbs">Proverbs</option>
                                <option value="Ecclesiastes">Ecclesiastes</option>
                                <option value="Song of Solomon">Song of Solomon</option>
                                <option value="Isaiah">Isaiah</option>
                                <option value="Jeremiah">Jeremiah</option>
                                <option value="Lamentations">Lamentations</option>
                                <option value="Ezekiel">Ezekiel</option>
                                <option value="Daniel">Daniel</option>
                                <option value="Hosea">Hosea</option>
                                <option value="Joel">Joel</option>
                                <option value="Amos">Amos</option>
                                <option value="Obadiah">Obadiah</option>
                                <option value="Jonah">Jonah</option>
                                <option value="Micah">Micah</option>
                                <option value="Nahum">Nahum</option>
                                <option value="Habakkuk">Habakkuk</option>
                                <option value="Zephaniah">Zephaniah</option>
                                <option value="Haggai">Haggai</option>
                                <option value="Zechariah">Zechariah</option>
                                <option value="Malachi">Malachi</option>
                                <option value="Matthew">Matthew</option>
                                <option value="Mark">Mark</option>
                                <option value="Luke">Luke</option>
                                <option value="John">John</option>
                                <option value="Acts">Acts</option>
                                <option value="Romans">Romans</option>
                                <option value="1 Corinthians">1 Corinthians</option>
                                <option value="2 Corinthians">2 Corinthians</option>
                                <option value="Galatians">Galatians</option>
                                <option value="Ephesians">Ephesians</option>
                                <option value="Philippians">Philippians</option>
                                <option value="Colossians">Colossians</option>
                                <option value="1 Thessalonians">1 Thessalonians</option>
                                <option value="2 Thessalonians">2 Thessalonians</option>
                                <option value="1 Timothy">1 Timothy</option>
                                <option value="2 Timothy">2 Timothy</option>
                                <option value="Titus">Titus</option>
                                <option value="Philemon">Philemon</option>
                                <option value="Hebrews">Hebrews</option>
                                <option value="James">James</option>
                                <option value="1 Peter">1 Peter</option>
                                <option value="2 Peter">2 Peter</option>
                                <option value="1 John">1 John</option>
                                <option value="2 John">2 John</option>
                                <option value="3 John">3 John</option>
                                <option value="Jude">Jude</option>
                                <option value="Revelation">Revelation</option>
                            </select>
                            <input type="text" name="prayer_reference_verse" size="30">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php esc_html_e( 'Content', 'pray4movement_prayer_points' ); ?> (*)
                        </td>
                        <td>
                            <textarea name="prayer_content" rows="10" cols="50" required></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php esc_html_e( 'Tags', 'pray4movement_prayer_points' ); ?>
                        </td>
                        <td>
                            <input type="text" name="prayer_tags" size="50">
                        </td>
                    </tr>
                    <tr style="display:none;">
                        <td></td>
                        <td><input type="hidden" name="prayer_library_id" value="<?php echo esc_html( $library['id'] ); ?>"></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button class="button" type="post"><?php esc_html_e( 'Add', 'pray4movement_prayer_points' ); ?></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
        <?php
    }

    public function process_add_prayer_point() {
        if ( !isset( $_POST['add_prayer_point_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['add_prayer_point_nonce'] ), 'add_prayer_point' ) ) {
            return;
        }
        if (
            !isset( $_POST['prayer_library_id'] ) ||
            !isset( $_POST['prayer_title'] ) ||
            !isset( $_POST['prayer_content'] ) ||
            !isset( $_POST['prayer_reference_book'] ) ||
            !isset( $_POST['prayer_reference_verse'] ) ||
            !isset( $_POST['prayer_tags'] )
        ) {
            return;
        }
        $prayer = [
            'library_id' => sanitize_text_field( wp_unslash( $_POST['prayer_library_id'] ) ),
            'title' => sanitize_text_field( wp_unslash( $_POST['prayer_title'] ) ),
            'content' => sanitize_text_field( wp_unslash( $_POST['prayer_content'] ) ),
            'hash' => md5( sanitize_text_field( wp_unslash( $_POST['prayer_content'] ) ) ),
            'book' => sanitize_text_field( wp_unslash( $_POST['prayer_reference_book'] ) ),
            'verse' => sanitize_text_field( wp_unslash( $_POST['prayer_reference_verse'] ) ),
            'reference' => sanitize_text_field( wp_unslash( $_POST['prayer_reference_book'] . ' ' . $_POST['prayer_reference_verse'] ) ),
            'tags' => sanitize_text_field( wp_unslash( $_POST['prayer_tags'] ) ),
            'status' => 'unpublished',
        ];

        Pray4Movement_Prayer_Points_Utilities::insert_prayer_point( $prayer );
        $prayer['id'] = Pray4Movement_Prayer_Points_Utilities::get_last_prayer_point_id();
        $tags = Pray4Movement_Prayer_Points_Utilities::sanitize_tags( $prayer['tags'] );
        Pray4Movement_Prayer_Points_Utilities::insert_all_tags( $prayer['id'], $tags );
        Pray4Movement_Prayer_Points_Utilities::admin_notice( __( 'Prayer Point added successfully!', 'pray4movement_prayer_points' ), 'success' );
    }

    public static function process_edit_prayer_point() {
        $prayer = Pray4Movement_Prayer_Points_Utilities::get_prayer_point_from_url_param();
        $prayer = self::get_edited_prayer_post_data();
        Pray4Movement_Prayer_Points_Utilities::update_prayer_point( $prayer );
        Pray4Movement_Prayer_Points_Utilities::update_prayer_tags( $prayer['id'], $prayer['tags'] );
        Pray4Movement_Prayer_Points_Utilities::admin_notice( __( 'Prayer Point updated successfully!', 'pray4movement_prayer_points' ), 'success' );
    }

    private static function get_edited_prayer_post_data() {
        if ( !isset( $_POST['edit_prayer_point_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['edit_prayer_point_nonce'] ), 'edit_prayer_point' ) ) {
            $prayer = Pray4Movement_Prayer_Points_Utilities::get_prayer_point_from_url_param();
            return $prayer;
        }
        if (
            !isset( $_POST['prayer_id'] ) ||
            !isset( $_POST['prayer_title'] ) ||
            !isset( $_POST['prayer_content'] ) ||
            !isset( $_POST['prayer_reference_book'] ) ||
            !isset( $_POST['prayer_reference_verse'] ) ||
            !isset( $_POST['prayer_tags'] )
        ) {
            return;
        }
        $prayer = [
            'id' => sanitize_text_field( wp_unslash( $_POST['prayer_id'] ) ),
            'title' => sanitize_text_field( wp_unslash( $_POST['prayer_title'] ) ),
            'content' => sanitize_text_field( wp_unslash( $_POST['prayer_content'] ) ),
            'book' => sanitize_text_field( wp_unslash( $_POST['prayer_reference_book'] ) ),
            'verse' => sanitize_text_field( wp_unslash( $_POST['prayer_reference_verse'] ) ),
            'status' => 'unpublished',
        ];
        $prayer['reference'] = trim( $prayer['book'] . ' ' . $prayer['verse'] );
        $prayer['library_id'] = Pray4Movement_Prayer_Points_Utilities::get_library_id( $prayer['id'] );
        $prayer['library'] = Pray4Movement_Prayer_Points_Utilities::get_prayer_library( $prayer['library_id'] );
        $prayer['tags'] = Pray4Movement_Prayer_Points_Utilities::sanitize_tags( sanitize_text_field( wp_unslash( $_POST['prayer_tags'] ) ) );
        $prayer['hash'] = md5( $prayer['content'] );
        return $prayer;
    }

    private static function get_row_count_for_prayer_id_meta( $prayer_id, $meta_key ) {
        global $wpdb;
        $row_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT( prayer_id ) FROM {$wpdb->prefix}dt_prayer_points_meta WHERE meta_key = %s AND prayer_id = %d;",
                $meta_key, $prayer_id
            )
        );
        return $row_count;
    }

    private static function delete_prayer_meta( $prayer_id, $meta_key ) {
        global $wpdb;
        $wpdb->delete(
            $wpdb->prefix.'dt_prayer_points_meta',
            [
                'prayer_id' => $prayer_id,
                'meta_key' => $meta_key
            ],
            [ '%d', '%s' ]
        );
        return;
    }

    private function display_prayer_points( $library_id ) {
        $prayer_points = Pray4Movement_Prayer_Points_Utilities::get_lib_prayer_points( $library_id );
        if ( !$prayer_points ) : ?>
            <tr>
                <td colspan="6">
                    <i><?php esc_html_e( 'This Prayer Library is currently empty.', 'pray4movement_prayer_points' ); ?></i>
                </td>
            </tr>
            <?php
            return;
            endif;

        foreach ( $prayer_points as $prayer ) :
            $prayer['tags'] = Pray4Movement_Prayer_Points_Utilities::get_prayer_tags( $prayer['id'] );
            ?>
                <tr id="delete-prayer-<?php echo esc_html( $prayer['id'] ); ?>">
                    <td>
                        <?php echo esc_html( $prayer['id'] ); ?>
                    </td>
                    <td>
                        <?php echo esc_html( $prayer['title'] ); ?>
                    </td>
                    <td>
                        <?php echo esc_html( $prayer['reference'] ); ?>
                    </td>
                    <td>
                        <?php echo esc_html( $prayer['content'] ); ?>
                    </td>
                    <td>
                        <?php echo esc_html( implode( ', ', $prayer['tags'] ) ); ?>
                    </td>
                    <td>
                        <a href="/wp-admin/admin.php?page=pray4movement_prayer_points&edit_prayer=<?php echo esc_html( $prayer['id'] ); ?>"" >Edit</a> | 
                        <a href="#" style="color:#b32d2e;" class="delete_prayer"  data-id="<?php echo esc_html( $prayer['id'] ); ?>" data-title="<?php echo esc_html( $prayer['title'] ); ?>">Delete</a>
                    </td>
                </tr>
        <?php endforeach; ?>
        <script>
            jQuery( '.delete_prayer' ).on( 'click', function () {
                var prayer_title = jQuery( this ).data('title');
                if(confirm(`Delete the '${prayer_title}' Prayer Point?`)) {
                    var prayer_id = jQuery( this ).data('id');
                    jQuery.ajax( {
                        type: 'POST',
                        contentType: 'application/json; charset=utf-8',
                        dataType: 'json',
                        url: window.location.origin + '/wp-json/pray4movement-prayer-points/v1/delete_prayer_point/' + prayer_id,
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                        },
                        success: delete_prayer_success(prayer_id, prayer_title),
                    } );
                }
            } );

            function delete_prayer_success( prayer_id, prayer_title ) {
                jQuery( '#delete-prayer-' + prayer_id ).remove();
                    let admin_notice = `
                        <div class="notice notice-success is-dismissible">
                            <p>'${prayer_title}' Prayer Point deleted successfully!</p>
                        </div>
                    `;
                    jQuery('#post-body-content').prepend(admin_notice);
            }
        </script>
        <?php
    }
}

class Pray4Movement_Prayer_Points_Edit_Prayer {
    public function content() {
        Pray4Movement_Prayer_Points_Utilities::check_permissions();
        if ( !isset( $_GET['edit_prayer'] ) || empty( $_GET['edit_prayer'] ) || !is_numeric( $_GET['edit_prayer'] ) ) {
            Pray4Movement_Prayer_Points_Utilities::admin_notice( 'Invalid Prayer Point ID', 'error' );
            return;
        }
        $prayer_id = sanitize_text_field( wp_unslash( $_GET['edit_prayer'] ) );
        if ( ! Pray4Movement_Prayer_Points_Utilities::check_prayer_id_exists( $prayer_id ) ) {
            Pray4Movement_Prayer_Points_Utilities::admin_notice( 'Prayer Point ID does not exist', 'error' );
            return;
        }
        $prayer = Pray4Movement_Prayer_Points_Utilities::get_prayer_point( $prayer_id );
        $library = Pray4Movement_Prayer_Points_Utilities::get_prayer_library( $prayer['library_id'] );
        ?>
        <div class="wrap">
            <div id="poststuff">
                <p>
                    <a href="/wp-admin/admin.php?page=pray4movement_prayer_points&view_library=<?php echo esc_attr( $library['id'] ); ?>">
                        <?php echo esc_html( sprintf( __( "<< Back to '%s'", 'pray4movement_prayer_points' ), $library['name'] ) ); ?>
                    </a>
                </p>
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <?php $this->main_edit_prayer_column(); ?>
                    </div>
                    <div id="postbox-container-1" class="postbox-container">
                        <?php $this->right_edit_prayer_column(); ?>
                    </div>
                    <div id="postbox-container-2" class="postbox-container">
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function main_edit_prayer_column() {
        Pray4Movement_Prayer_Points_View_Library::process_edit_prayer_point();

        if ( !isset( $_GET['edit_prayer'] ) || empty( $_GET['edit_prayer'] ) ) {
            return;
        }
        $prayer_id = sanitize_text_field( wp_unslash( $_GET['edit_prayer'] ) );
        $prayer = Pray4Movement_Prayer_Points_Utilities::get_prayer_point( $prayer_id );
        ?>
        <form method="POST">
        <?php wp_nonce_field( 'edit_prayer_point', 'edit_prayer_point_nonce' ); ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th colspan="2"><?php esc_html_e( 'Edit Prayer Point', 'pray4movement_prayer_points' ); ?></th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <?php esc_html_e( 'Title', 'pray4movement_prayer_points' ); ?> (*)
                </td>
                <td>
                    <input type="text" name="prayer_title" size="50" value="<?php echo esc_html( $prayer['title'] ); ?>" required>
                </td>
            </tr>
            <tr>
                <td>
                    <?php esc_html_e( 'Reference', 'pray4movement_prayer_points' ); ?>
                </td>
                <td>
                    <select name="prayer_reference_book" id="prayer_reference_book">
                        <option value="">(No Reference)</option>
                        <option value="Genesis">Genesis</option>
                        <option value="Exodus">Exodus</option>
                        <option value="Leviticus">Leviticus</option>
                        <option value="Numbers">Numbers</option>
                        <option value="Deuteronomy">Deuteronomy</option>
                        <option value="Joshua">Joshua</option>
                        <option value="Judges">Judges</option>
                        <option value="Ruth">Ruth</option>
                        <option value="1 Samuel">1 Samuel</option>
                        <option value="2 Samuel">2 Samuel</option>
                        <option value="1 Kings">1 Kings</option>
                        <option value="2 Kings">2 Kings</option>
                        <option value="1 Chronicles">1 Chronicles</option>
                        <option value="2 Chronicles">2 Chronicles</option>
                        <option value="Ezra">Ezra</option>
                        <option value="Nehemiah">Nehemiah</option>
                        <option value="Esther">Esther</option>
                        <option value="Job">Job</option>
                        <option value="Psalm">Psalm</option>
                        <option value="Proverbs">Proverbs</option>
                        <option value="Ecclesiastes">Ecclesiastes</option>
                        <option value="Song of Solomon">Song of Solomon</option>
                        <option value="Isaiah">Isaiah</option>
                        <option value="Jeremiah">Jeremiah</option>
                        <option value="Lamentations">Lamentations</option>
                        <option value="Ezekiel">Ezekiel</option>
                        <option value="Daniel">Daniel</option>
                        <option value="Hosea">Hosea</option>
                        <option value="Joel">Joel</option>
                        <option value="Amos">Amos</option>
                        <option value="Obadiah">Obadiah</option>
                        <option value="Jonah">Jonah</option>
                        <option value="Micah">Micah</option>
                        <option value="Nahum">Nahum</option>
                        <option value="Habakkuk">Habakkuk</option>
                        <option value="Zephaniah">Zephaniah</option>
                        <option value="Haggai">Haggai</option>
                        <option value="Zechariah">Zechariah</option>
                        <option value="Malachi">Malachi</option>
                        <option value="Matthew">Matthew</option>
                        <option value="Mark">Mark</option>
                        <option value="Luke">Luke</option>
                        <option value="John">John</option>
                        <option value="Acts">Acts</option>
                        <option value="Romans">Romans</option>
                        <option value="1 Corinthians">1 Corinthians</option>
                        <option value="2 Corinthians">2 Corinthians</option>
                        <option value="Galatians">Galatians</option>
                        <option value="Ephesians">Ephesians</option>
                        <option value="Philippians">Philippians</option>
                        <option value="Colossians">Colossians</option>
                        <option value="1 Thessalonians">1 Thessalonians</option>
                        <option value="2 Thessalonians">2 Thessalonians</option>
                        <option value="1 Timothy">1 Timothy</option>
                        <option value="2 Timothy">2 Timothy</option>
                        <option value="Titus">Titus</option>
                        <option value="Philemon">Philemon</option>
                        <option value="Hebrews">Hebrews</option>
                        <option value="James">James</option>
                        <option value="1 Peter">1 Peter</option>
                        <option value="2 Peter">2 Peter</option>
                        <option value="1 John">1 John</option>
                        <option value="2 John">2 John</option>
                        <option value="3 John">3 John</option>
                        <option value="Jude">Jude</option>
                        <option value="Revelation">Revelation</option>
                    </select>
                    <input type="text" name="prayer_reference_verse" size="30" value="<?php echo esc_html( $prayer['verse'] ); ?>">
                </td>
            </tr>
            <tr>
                <td>
                    <?php esc_html_e( 'Content', 'pray4movement_prayer_points' ); ?> (*)
                </td>
                <td>
                    <textarea name="prayer_content" rows="10" cols="50" required><?php echo esc_html( $prayer['content'] ); ?></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <?php esc_html_e( 'Tags', 'pray4movement_prayer_points' ); ?>
                </td>
                <td>
                    <input type="text" name="prayer_tags" size="50" value="<?php echo esc_html( implode( ', ', $prayer['tags'] ) ); ?>">
                </td>
            </tr>
            <tr style="display:none;">
                <td></td>
                <td><input type="hidden" name="prayer_id" value="<?php echo esc_html( $prayer['id'] ); ?>"></td>
            </tr>
            <tr>
                <td colspan="2">
                    <button class="button" type="post"><?php esc_html_e( 'Update', 'pray4movement_prayer_points' ); ?></button>
                </td>
            </tr>
            </tbody>
        </table>
        </form>
        <br>
        <script>
            jQuery('#prayer_reference_book option[value="<?php echo esc_html( $prayer['book'] ); ?>"]').attr("selected", "selected");
        </script>
        
        <?php
    }

    public function right_edit_prayer_column() {
        ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Information', 'pray4movement_prayer_points' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php esc_html_e( 'Edit your Prayer Point.', 'pray4movement_prayer_points' ); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <?php
    }
}

class Pray4Movement_Prayer_Points_Tab_Import {
    public function content() {
        Pray4Movement_Prayer_Points_Utilities::check_permissions();
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <?php $this->main_prayer_points_import_column(); ?>
                    </div>
                    <div id="postbox-container-1" class="postbox-container">
                        <?php $this->right_prayer_points_import_column(); ?>
                    </div>
                    <div id="postbox-container-2" class="postbox-container">
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function main_prayer_points_import_column() {
        $this->check_for_import_prayer_nonce();
        ?>
        <form method="POST" enctype="multipart/form-data">
            <?php wp_nonce_field( 'import_prayer_points', 'import_prayer_points_nonce' ); ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <td colspan="2">
                            <?php esc_html_e( 'Import CSV', 'pray4movement_prayer_points' ); ?>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <?php esc_html_e( 'Select CSV file with Prayer Points', 'pray4movement_prayer_points' ); ?>
                        </td>
                        <td>
                            <input type="file" name="import-file">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php esc_html_e( 'Please select the Prayer Library you want to insert the Prayer Points into', 'pray4movement_prayer_points' ); ?>
                        </td>
                        <td>
                        <?php $prayer_libraries = Pray4Movement_Prayer_Points_Utilities::get_prayer_libraries(); ?>
                        <select name="prayer-library-id" required>
                                <option hidden><?php esc_html_e( 'Select a Prayer Library', 'pray4movement_prayer_points' ); ?></option>
                                <?php if ( empty( $prayer_libraries ) ) : ?>
                                    <option disabled><?php esc_html_e( 'No Prayer Libraries found', 'pray4movement_prayer_points' ); ?></option>
                                <?php else : ?>
                                    <?php foreach ( $prayer_libraries as $library ) : ?>
                                    <option value="<?php echo esc_html( $library['id'] ); ?>"><?php echo esc_html( $library['name'] ); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                        </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align:right;">
                            <input type="submit" class="button" value="<?php esc_html_e( 'Import', 'pray4movement_prayer_points' ); ?>">
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
        <?php
    }

    private function check_for_import_prayer_nonce() {
        if ( isset( $_POST['import_prayer_points_nonce'] ) ) {
            if ( !isset( $_POST['import_prayer_points_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['import_prayer_points_nonce'] ), 'import_prayer_points' ) ) {
                return;
            }
            $this->process_import_prayer_points();
        }
    }

    public function process_import_prayer_points() {
        if (
            $this->import_prayer_nonce_verified() &&
            $this->prayer_library_is_selected() &&
            $this->prayer_library_id_exists() &&
            $this->verify_is_csv_extension()
        )
        {
            $file_tmp_name = $this->get_file_tmp_name();
            $csv_data = $this->prepare_prayer_data_from_csv_file( $file_tmp_name );
            $this->add_prayer_points_from_csv_data( $csv_data );
        }
    }

    private function import_prayer_nonce_verified() {
        if ( !isset( $_POST['import_prayer_points_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['import_prayer_points_nonce'] ), 'import_prayer_points' ) ) {
            Pray4Movement_Prayer_Points_Utilities::admin_notice( esc_html( 'Invalid nonce error', 'pray4movement_prayer_points' ), 'error' );
            return false;
        }
        return true;
    }

    private function prayer_library_is_selected() {
        if ( !isset( $_POST['import_prayer_points_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['import_prayer_points_nonce'] ), 'import_prayer_points' ) ) {
            Pray4Movement_Prayer_Points_Utilities::admin_notice( esc_html( 'Destination Prayer Library not set or empty', 'pray4movement_prayer_points' ), 'error' );
            return false;
        }
        if ( !isset( $_POST['prayer-library-id'] ) && empty( $_POST['prayer-library-id'] ) ) {
            return false;
        }
        return true;
    }

    private function prayer_library_id_exists() {
        if ( !isset( $_POST['import_prayer_points_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['import_prayer_points_nonce'] ), 'import_prayer_points' ) ) {
            return false;
        }
        if ( isset( $_POST['prayer-library-id'] ) ) {
            if ( !Pray4Movement_Prayer_Points_Utilities::check_library_id_exists( sanitize_text_field( wp_unslash( $_POST['prayer-library-id'] ) ) ) ) {
                Pray4Movement_Prayer_Points_Utilities::admin_notice( esc_html( 'Selected Prayer Library does not exist', 'pray4movement_prayer_points' ), 'error' );
                return false;
            }
        }
        return true;
    }

    private function verify_is_csv_extension() {
        if ( !isset( $_POST['import_prayer_points_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['import_prayer_points_nonce'] ), 'import_prayer_points' ) || !isset( $_FILES['import-file']['name'] ) ) {
            return false;
        }
        $file_extension = pathinfo( sanitize_text_field( wp_unslash( $_FILES['import-file']['name'] ) ), PATHINFO_EXTENSION );
        if ( $file_extension === 'csv' ) {
            return true;
        }
        return false;
    }

    private function get_file_tmp_name() {
        if ( !isset( $_POST['import_prayer_points_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['import_prayer_points_nonce'] ), 'import_prayer_points' ) || !isset( $_FILES['import-file']['tmp_name'] ) ) {
            return false;
        }
        return sanitize_text_field( wp_unslash( $_FILES['import-file']['tmp_name'] ) );
    }

    private function prepare_prayer_data_from_csv_file( $file_tmp_name ) {
        $csv_input = fopen( $file_tmp_name, 'r' );
        $output = [];
        $linecount = 0;
        while ( $csv_data = fgetcsv( $csv_input ) ) {
            $linecount ++;
            $csv_data = array_map( 'utf8_encode', $csv_data );
            if ( self::csv_line_is_invalid( $csv_data ) ) {
                Pray4Movement_Prayer_Points_Utilities::admin_notice( "Skipped row #$linecount: Wrong amount of columns", 'error' );
                continue;
            }
            $output[] = $csv_data;
        }
        return $output;
    }

    private function csv_line_is_invalid( $csv_data ) {
        $data_col_count = count( $csv_data );
        $required_columns = [ 'title', 'content', 'book', 'verse', 'tags', 'status' ];
        $required_columns_count = count( $required_columns );
        if ( $data_col_count !== $required_columns_count ) {
            return true;
        }
        return false;
    }

    private function add_prayer_points_from_csv_data( $csv_data ) {
        $insert_count = 0;
        $linecount = 0;
        foreach ( $csv_data as $csv_prayer ) {
            $prayer = self::get_prayer_data_from_prepared_csv_data( $csv_prayer );
            if ( !is_null( $prayer['title'] ) || !is_null( $prayer['content'] ) ) {
                Pray4Movement_Prayer_Points_Utilities::insert_prayer_point( $prayer );
                $prayer['id'] = Pray4Movement_Prayer_Points_Utilities::get_last_prayer_point_id();
                Pray4Movement_Prayer_Points_Utilities::insert_all_tags( $prayer['id'], $prayer['tags'] );
                $insert_count ++;
            }
            $linecount ++;
        }
        Pray4Movement_Prayer_Points_Utilities::admin_notice( esc_html( sprintf( __( '%d Prayer Points added successfully!', 'pray4movement_prayer_points' ), $insert_count ) ), 'success' );
    }

    private function get_prayer_data_from_prepared_csv_data( $csv_data ) {
        if ( !isset( $_POST['import_prayer_points_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['import_prayer_points_nonce'] ), 'import_prayer_points' ) || !isset( $_POST['prayer-library-id'] ) ) {
            return false;
        }
        $prayer = [];
        $prayer['library_id'] = sanitize_text_field( wp_unslash( $_POST['prayer-library-id'] ) );
        $prayer['title'] = sanitize_text_field( wp_unslash( $csv_data[0] ) );
        $prayer['content'] = sanitize_text_field( wp_unslash( $csv_data[1] ) );
        $prayer['book'] = sanitize_text_field( wp_unslash( $csv_data[2] ) );
        $prayer['verse'] = sanitize_text_field( wp_unslash( $csv_data[3] ) );
        $prayer['reference'] = $prayer['book'] . ' ' . $prayer['verse'];
        $prayer['tags'] = sanitize_text_field( wp_unslash( $csv_data[4] ) );
        $prayer['status'] = sanitize_text_field( wp_unslash( $csv_data[5] ) );
        $prayer['hash'] = md5( $prayer['content'] );
        return $prayer;
    }

    public static function prepare_prayer_metas( $prayer ) {
        $prayer_metas = [
            'title' => $prayer['title'],
            'book' => $prayer['book'],
            'verse' => $prayer['verse'],
            'reference' => Pray4Movement_Prayer_Points_Utilities::get_prayer_reference( $prayer['book'], $prayer['verse'] ),
        ];
        return array_filter( $prayer_metas );
    }

    public function right_prayer_points_import_column() {
        ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Help', 'pray4movement_prayer_points' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php echo esc_html( 'Upload your Pray4Movement Prayer Library .CSV file and import your Prayer Points into a Prayer Library.', 'pray4movement_prayer_points' ); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo esc_html( 'Make sure the document has the following columns in this exact order:', 'pray4movement_prayer_points' ); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <pre>title,content,book,verse,tags,status</pre>    
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <?php
    }
}

class Pray4Movement_Prayer_Points_Tab_Export {
    public function content() {
        Pray4Movement_Prayer_Points_Utilities::check_permissions();
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <?php $this->main_prayer_points_column(); ?>
                    </div>
                    <div id="postbox-container-1" class="postbox-container">
                        <?php Pray4Movement_Prayer_Points_Utilities::localize_prayers_column(); ?>
                    </div>
                    <div id="postbox-container-2" class="postbox-container">
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function main_prayer_points_column() {
        $prayer_libraries = Pray4Movement_Prayer_Points_Utilities::get_prayer_libraries();
        if ( empty( $prayer_libraries ) ) {
            ?>
            <p>
                <i><?php esc_html_e( 'No Prayer Libraries created yet', 'pray4movement_prayer_points' ); ?></i>
            </p>
            <?php
            return;
        }
        ?>
        <table class="wp-list-table widefat plugins">
            <thead>
            <tr>
                <td class="manage-column column-cb check-column">
                    <label class="screen-reader-text">Select All</label>
                    <input type="checkbox">
                </td>
                <th id="name" class="manage-column column-name column-primary"><?php esc_html_e( 'Prayer Library', 'pray4movement_prayer_points' ); ?></th>
                <th id="description" class="manage-column column-description"><?php esc_html_e( 'Description', 'pray4movement_prayer_points' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ( $prayer_libraries as $prayer_library ): ?>
            <tr class="inactive">
                <th class="check-column"><label class="screen-reader-text"><?php echo esc_html( $prayer_library['name'] ); ?></label>
                    <input type="checkbox" name="checked[]" value="<?php echo esc_attr( $prayer_library['id'] ); ?>">
                </th>
                <td>
                    <strong>
                        <?php echo esc_html( $prayer_library['name'] ); ?>
                    </strong>
                    <div class="row-actions visible">
                        <span>
                            <a href="javascript:void(0);" class="export_library" onclick="export_csv(<?php echo esc_attr( $prayer_library['id'] ); ?>, '<?php echo esc_attr( $prayer_library['key'] ); ?>')">
                                <?php esc_html_e( 'Export', 'pray4movement_prayer_points' ); ?>
                            </a>
                        </span>
                    </div>
                </td>
                <td>
                    <div>
                        <p><?php echo esc_html( $prayer_library['description'] ); ?></p>
                    </div>
                    <div>
                        <i>
                            <?php esc_html_e( 'Last updated:', 'pray4movement_prayer_points' ); ?> <?php echo esc_html( substr( $prayer_library['last_updated'], 0, 16 ) ); ?>
                        </i>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2">
                    <button class="button" id="export-libraries"><?php esc_html_e( 'Export', 'pray4movement_prayer_points' ); ?></button>
                </td>
            </tr>
            </tbody>
        </table>
        <script>
            function export_csv( libraryId, fileName='pray4movement_prayer_libraries_download' ) {
                jQuery.ajax( {
                        type: 'POST',
                        contentType: 'application/json; charset=utf-8',
                        dataType: 'json',
                        url: window.location.origin + `/wp-json/pray4movement-prayer-points/v1/get_prayer_points/${libraryId}/0`,
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                        },
                        success: function(response) {
                            var columnsAlreadyDisplayed = false;
                            let output = "data:text/csv;charset=utf-8,";
                                var columnNames = Object.keys(response[0])
                                if (columnsAlreadyDisplayed){
                                    columnNames.forEach( function(column) {
                                        output += `"` + column + `",`;
                                    } )
                                    output = output.slice(0,-1);
                                    output += `\r\n`;
                                    columnsAlreadyDisplayed = true;
                                }
                                response.forEach( function(row){
                                    columnNames.forEach( function( columnName ) {
                                        output += `"${row[columnName]}",`;
                                    } )
                                output = output.slice(0,-1);
                                output += `\r\n`;
                            } );
                            var encodedUri = encodeURI(output);
                            var downloadLink = document.createElement('a');
                            downloadLink.href = encodedUri;
                            downloadLink.download = `pray4movement_prayer_library_${fileName}.csv`;
                            document.body.appendChild(downloadLink);
                            downloadLink.click();
                            document.body.removeChild(downloadLink);
                        }
                    } );
            }
        </script>
        <script>
            jQuery('#export-libraries').on('click', function(){
                library_ids = [];
                document.querySelectorAll( 'input[name="checked[]"]:checked' ).forEach( function(checked){
                    library_ids.push(checked.value);
                });
                library_ids = library_ids.join(',');
                export_csv(library_ids);
            });
        </script>
        <?php
    }
}