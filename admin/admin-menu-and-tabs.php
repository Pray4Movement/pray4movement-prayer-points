<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Pray4Movement_Prayer_Points_Menu
 */
class Pray4Movement_Prayer_Points_Menu {

    public $token = 'pray4movement_prayer_points';
    public $page_title = 'Pray4Movement Prayer Points';

    private static $_instance = null;

    /**
     * Pray4Movement_Prayer_Points_Menu Instance
     *
     * Ensures only one instance of Pray4Movement_Prayer_Points_Menu is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return Pray4Movement_Prayer_Points_Menu instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()


    /**
     * Constructor function.
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {

        add_action( "admin_menu", array( $this, "register_menu" ) );

        $this->page_title = __( "Pray4Movement Prayer Points", 'pray4movement-prayer-points' );
    } // End __construct()


    /**
     * Loads the subnav page
     * @since 0.1
     */
    public function register_menu() {
        $this->page_title = __( "Pray4Movement Prayer Points", 'pray4movement-prayer-points' );
        $menu_icon = self::get_prayer_points_icon();
        add_menu_page( 'Prayer Points', 'Prayer Points', 'manage_dt', $this->token, [ $this, 'content' ], $menu_icon );
    }

    public static function get_prayer_points_icon() {
        return 'data:image/svg+xml;base64,PHN2ZyBpZD0ic3ZnIiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHdpZHRoPSI0MDAiIGhlaWdodD0iNDAwIiB2aWV3Qm94PSIwLCAwLCA0MDAsNDAwIj48ZyBpZD0ic3ZnZyI+PHBhdGggaWQ9InBhdGgwIiBkPSJNMjE5LjcxNiAxOS4yNDggQyAxMzkuNDUzIDMzLjcwNyw5Ni42MDggMTI3LjA1OSwxMzcuODU3IDE5Ny42MDAgTCAxNDEuNjAwIDIwNC4wMDAgMTU3LjExOSAyMDMuNTQzIEwgMTcyLjYzOCAyMDMuMDg2IDE2NC4yNDIgMTkwLjc0MyBDIDEyMS40OTUgMTI3LjkwMSwxNjUuMDQwIDQ0LjU0OSwyNDAuODAwIDQ0LjIwMCBDIDMyMS4xODkgNDMuODI4LDM2NC43NzIgMTM1LjA0OCwzMTQuMjM4IDE5Ny45MDggTCAzMTAuMTMyIDIwMy4wMTYgMzE2LjEwNyAyMDEuMzY2IEMgMzE5LjM5NCAyMDAuNDU5LDMyNS4wMzQgMjAwLjA5NywzMjguNjQyIDIwMC41NjIgQyAzMzIuMjQ5IDIwMS4wMjcsMzM2Ljk0OCAyMDEuNjMxLDMzOS4wODQgMjAxLjkwNCBDIDM0Ny45NDYgMjAzLjAzNiwzNjEuNjAwIDE2NC44MTcsMzYxLjYwMCAxMzguODc3IEMgMzYxLjYwMCA2My4zMjQsMjkzLjU0OSA1Ljk0NywyMTkuNzE2IDE5LjI0OCBNMjI4LjgwMCA4NS42MDAgTCAyMjguODAwIDk3LjYwMCAyMTEuMjAwIDk3LjYwMCBMIDE5My42MDAgOTcuNjAwIDE5My42MDAgMTExLjIwMCBMIDE5My42MDAgMTI0LjgwMCAyMTEuMjAwIDEyNC44MDAgTCAyMjguODAwIDEyNC44MDAgMjI4LjgwMCAxNjEuNjAwIEwgMjI4LjgwMCAxOTguNDAwIDI0Mi40MDAgMTk4LjQwMCBMIDI1Ni4wMDAgMTk4LjQwMCAyNTYuMDAwIDE2MS42MDAgTCAyNTYuMDAwIDEyNC44MDAgMjcyLjgwMCAxMjQuODAwIEwgMjg5LjYwMCAxMjQuODAwIDI4OS42MDAgMTExLjIwMCBMIDI4OS42MDAgOTcuNjAwIDI3Mi44MDAgOTcuNjAwIEwgMjU2LjAwMCA5Ny42MDAgMjU2LjAwMCA4NS42MDAgTCAyNTYuMDAwIDczLjYwMCAyNDIuNDAwIDczLjYwMCBMIDIyOC44MDAgNzMuNjAwIDIyOC44MDAgODUuNjAwIE0yOTcuNjIzIDIyNy43MTkgQyAyNjkuMjU3IDIzNi43MTgsMjcwLjU4MSAyMzUuNzkxLDI2OC4wMTggMjQ4LjQ0MCBDIDI2NC41ODUgMjY1LjM4NiwyNDkuMDY4IDI3MS45NjQsMTk5LjIwMCAyNzcuNjEzIEMgMTY0LjE5NiAyODEuNTc4LDE1OC40MDAgMjgwLjUyMiwxNTguNDAwIDI3MC4xODAgQyAxNTguNDAwIDI2MS45OTIsMTYwLjc2MyAyNjEuMDA3LDE4NC44MDAgMjU5LjE3MyBDIDIzNy4zNjAgMjU1LjE2NSwyNTIuNjcyIDI0OS4yNTYsMjQ4Ljk0OCAyMzQuNDIwIEMgMjQ2LjgwNCAyMjUuODc3LDI0My4xNTIgMjI1LjEwNCwxOTcuODE0IDIyMy42MDcgQyAxNTEuNTM3IDIyMi4wNzksMTQ1LjMyNSAyMjIuNDQzLDEzNS45NDYgMjI3LjIyOCBDIDEyOC43MjcgMjMwLjkxMCw2My45NDIgMjc4LjM2Myw2NC4xMDQgMjc5Ljg0OSBDIDY0LjE2MSAyODAuMzcyLDc5LjY3OCAyOTIuMTg3LDk4LjU4NyAzMDYuMTA1IEwgMTMyLjk2NiAzMzEuNDEwIDE3MC4wODMgMzM0LjczMyBDIDIxMS41ODMgMzM4LjQ1MCwyMTkuMzkzIDMzNy44NjMsMjMxLjA4MyAzMzAuMTUxIEMgMjQyLjQ2MCAzMjIuNjQ2LDMzNS44MzQgMjQ3Ljk3OCwzMzguMzY5IDI0NC4zNTggQyAzNDMuNjgxIDIzNi43NzUsMzM5LjY1NiAyMjMuOTg4LDMzMS4wMTAgMjIwLjk3MyBDIDMyNC42MDUgMjE4Ljc0MSwzMjguMjMyIDIxOC4wMDgsMjk3LjYyMyAyMjcuNzE5IE0zNy4yMDAgMzA4LjM0MCBDIDI0LjgwMSAzMjQuNjk5LDIzLjUyNiAzMjguNzcxLDI5LjIwMCAzMzMuODg5IEMgNDIuOTY5IDM0Ni4zMDgsODYuODgzIDM3Ni4yMjYsOTAuMTQyIDM3NS40MDggQyA5My4yNzggMzc0LjYyMSwxMTIuMjYwIDM1MC45NTMsMTE0LjU1OCAzNDQuOTY2IEMgMTE1LjA0MiAzNDMuNzA1LDEwNC4zMDMgMzM0LjYzNCw4Ni44NzQgMzIxLjU4MiBDIDcxLjIxMyAzMDkuODU1LDU2LjI0MCAyOTguNjM0LDUzLjYwMCAyOTYuNjQ4IEwgNDguODAwIDI5My4wMzYgMzcuMjAwIDMwOC4zNDAgIiBzdHJva2U9Im5vbmUiIGZpbGw9IiMwMDAwMDAiIGZpbGwtcnVsZT0iZXZlbm9kZCI+PC9wYXRoPjwvZz48L3N2Zz4=';
    }

    /**
     * Menu stub. Replaced when Disciple.Tools Theme fully loads.
     */
    public function extensions_menu() {}

    /**
     * Builds page contents
     * @since 0.1
     */
    public function content() {

        if ( !current_user_can( 'manage_dt' ) ) { // manage dt is a permission that is specific to Disciple.Tools and allows admins, strategists and dispatchers into the wp-admin
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        if ( isset( $_GET['view_lib'] ) ) {
            $object = new Pray4Movement_Prayer_Points_View_Lib();
            $object->content();
            return;
        }

        if ( isset( $_GET['tab'] ) && !isset( $_GET['view_lib'] ) ) {
            $tab = sanitize_key( wp_unslash( $_GET['tab'] ) );
        } else {
            $tab = 'general';
        }

        $link = 'admin.php?page='.$this->token.'&tab=';
        ?>
        <div class="wrap">
            <h2><?php echo esc_html( $this->page_title ) ?></h2>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_attr( $link ) . 'general' ?>"
                   class="nav-tab <?php echo esc_html( ( $tab == 'general' || !isset( $tab ) ) ? 'nav-tab-active' : '' ); ?>">General</a>
                <a href="<?php echo esc_attr( $link ) . 'second' ?>" class="nav-tab <?php echo esc_html( ( $tab == 'second' ) ? 'nav-tab-active' : '' ); ?>">Second</a>
            </h2>

            <?php
            switch ( $tab ) {
                case "general":
                    $object = new Pray4Movement_Prayer_Points_Tab_General();
                    $object->content();
                    break;
                    // todo: if no other cases exist, remove switch case
                default:
                    break;
            }
            ?>

        </div><!-- End wrap -->

        <?php
    }

    /**
     * Display admin notice
     * @param $notice string
     * @param $type string error|success|warning
     */
    public static function admin_notice( string $notice, string $type ) {
        ?>
        <div class="notice notice-<?php echo esc_attr( $type ) ?> is-dismissible">
            <p><?php echo esc_html( $notice ) ?></p>
        </div>
        <?php
    }
}
Pray4Movement_Prayer_Points_Menu::instance();

/**
 * Class Pray4Movement_Prayer_Points_Tab_General
 */
class Pray4Movement_Prayer_Points_Tab_General {
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->main_column() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php $this->right_column() ?>

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {
        if ( isset( $_POST['add_library_nonce'] ) ) {
            if ( !isset( $_POST['add_library_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['add_library_nonce'] ), 'add_library' ) ) {
                return;
            }
            self::process_add_library();
        }
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
                $prayer_libraries = self::get_prayer_libraries();
                self::display_prayer_libraries( $prayer_libraries );
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
                    <input type="text" name="new_library_name">
                </td>
            </tr>
            <tr>
                <td>
                    <?php esc_html_e( 'Description', 'pray4movement_prayer_points' ); ?>
                </td>
                <td>
                    <input type="text" name="new_library_desc" size="50">
                </td>
            </tr>
            <tr>
                <td>
                    <?php esc_html_e( 'Icon', 'pray4movement_prayer_points' ); ?>
                </td>
                <td>
                    <textarea name="new_library_icon" cols="50" placeholder="data:image/svg+xml;base64,PHN2ZyBpZD0..."></textarea>
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
            jQuery( '.delete_library' ).on( 'click', function () {
                var lib_name = jQuery( this ).data('name');
                if(confirm(`Delete the '${lib_name}' Prayer Library?`)) {
                    var lib_id = jQuery( this ).data('id');
                    jQuery.ajax( {
                        type: 'POST',
                        contentType: 'application/json; charset=utf-8',
                        dataType: 'json',
                        url: window.location.origin + '/wp-json/pray4movement-prayer-points/v1/delete_prayer_library/' + lib_id,
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                        },
                        success: delete_lib_success(lib_id, lib_name),
                    } );
                }
            } );

            function delete_lib_success( lib_id, lib_name ) {
                jQuery( '#delete-library-' + lib_id ).remove();
                    let admin_notice = `
                        <div class="notice notice-success is-dismissible">
                            <p>'${lib_name}' Prayer Library deleted successfully</p>
                        </div>
                    `;
                    jQuery('.nav-tab-wrapper').before(admin_notice);
            }
        </script>
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Information</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    Content
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }

    public function process_add_library() {
        if ( !isset( $_POST['add_library_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['add_library_nonce'] ), 'add_library' ) ) {
            return;
        }

        if ( !isset( $_POST['new_library_name'] ) || !isset( $_POST['new_library_desc'] ) || !isset( $_POST['new_library_icon'] ) ) {
            return;
        }

        if ( !empty( $_POST['new_library_name'] ) ) {
            $new_library_name = sanitize_text_field( wp_unslash( $_POST['new_library_name'] ) );
        }

        if ( !empty( $_POST['new_library_desc'] ) ) {
            $new_library_desc = sanitize_text_field( wp_unslash( $_POST['new_library_desc'] ) );
        }

        $new_library_icon = null;
        if ( !empty( $_POST['new_library_icon'] ) ) {
            $new_library_icon = sanitize_text_field( wp_unslash( $_POST['new_library_icon'] ) );
        }

        $new_library_key = sanitize_key( strtolower( str_replace( ' ', '_', $new_library_name ) ) );

        // Todo: Check that key doesn't already exist in DB

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $test = $wpdb->query( $wpdb->prepare(
            "INSERT INTO `{$wpdb->prefix}dt_prayer_points_lib`
            ( `key`, `name`, `description`, `icon` )
            VALUES
            ( %s, %s, %s, %s );", $new_library_key, $new_library_name, $new_library_desc, $new_library_icon
        ) );
        if ( !$test ) {
            Pray4Movement_Prayer_Points_Menu::admin_notice( __( 'Could not add new prayer library to table', 'pray4movement_prayer_points' ), 'error' );
            return;
        }
        Pray4Movement_Prayer_Points_Menu::admin_notice( __( 'Prayer Library created successfully', 'pray4movement_prayer_points' ), 'success' );
    }

    public function get_prayer_libraries() {
        global $wpdb;
        $prayer_libraries = $wpdb->get_results(
            "SELECT * FROM `{$wpdb->prefix}dt_prayer_points_lib`;", ARRAY_A
        );
        return $prayer_libraries;
    }

    public function display_prayer_libraries( $prayer_libraries ) {
        foreach ( $prayer_libraries as $library ) :
            $prayer_icon = Pray4Movement_Prayer_Points_Menu::get_prayer_points_icon();
            if ( isset( $library['icon'] ) ) {
                if ( $library['icon'] !== '' ) {
                    $prayer_icon = $library['icon'];
                }
            }
            ?>
        <tr id="delete-library-<?php echo esc_html( $library['id'] ); ?>">
            <td><img src="<?php echo esc_html( $prayer_icon ); ?>" width="50px"></td>
            <td><a href="/wp-admin/admin.php?page=pray4movement_prayer_points&view_lib=<?php echo esc_html( $library['id'] ); ?>"><?php echo esc_html( $library['name'] ); ?></a></td>
            <td><?php echo esc_html( $library['description'] ); ?></td>
            <td><?php echo esc_html( self::count_prayer_points( $library['id'] ) ); ?></td>
            <td>
                <a href="#"><?php esc_html_e( 'Export', 'pray4movement_prayer_points' ); ?></a> | 
                <a href="#" style="color:#b32d2e;" class="delete_library" data-id="<?php echo esc_html( $library['id'] ); ?>" data-name="<?php echo esc_html( $library['name'] ); ?>"><?php esc_html_e( 'Delete', 'pray4movement_prayer_points' ); ?></a>
            </td>
        </tr>
        <?php endforeach;
    }

    public function count_prayer_points( $lib_id ) {
        if ( !isset( $lib_id ) ) {
            return;
        }
        global $wpdb;
        $count_prayer_points = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM `{$wpdb->prefix}dt_prayer_points` WHERE lib_id = %d;", $lib_id
            )
        );
        return $count_prayer_points;
    }

    public function display_prayer_libraries_list( $prayer_libraries ) {
        ?>
        <select id="prayer_library_dropdown" onchange="javascript:check_new_library();">
                <option hidden><?php esc_html_e( 'Select a Prayer Library', 'pray4movement_prayer_points' ); ?></option>
                <?php if ( empty( $prayer_libraries ) ) : ?>
                    <option disabled>No prayer libraries found</option>
                <?php else : ?>
                    <?php foreach ( $prayer_libraries as $library ) : ?>
                    <option><?php echo esc_html( $library['name'] ); ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
                <option disabled>--------------</option>
                <option value="add_new"><?php esc_html_e( 'Add a new Prayer Library...', 'pray4movement_prayer_points' ); ?></option>
        </select>
        <?php
    }
}


/**
 * Class Pray4Movement_Prayer_Points_Tab_Details
 */
class Pray4Movement_Prayer_Points_View_Lib {
    public function get_prayer_library( $lib_id ) {
        $lib_id = esc_sql( $lib_id );
        global $wpdb;
        $prayer_library = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE id = %d;", $lib_id
            ), ARRAY_A
        );
        return $prayer_library;
    }

    public function get_prayer_points( $lib_id ) {
        $lib_id = esc_sql( $lib_id );
        global $wpdb;
        $prayer_points = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `{$wpdb->prefix}dt_prayer_points` WHERE lib_id = %d;", $lib_id
            ), ARRAY_A
        );
        return $prayer_points;
    }

    public function get_prayer_meta( $prayer_id, $meta_key ) {
        $prayer_id = esc_sql( sanitize_text_field( $prayer_id ) );
        $meta_key = esc_sql( sanitize_text_field( $meta_key ) );
        global $wpdb;
        $prayer_meta = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT meta_value FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE meta_key = %s AND prayer_id = %d;",
                $meta_key, $prayer_id
            )
        );
        return $prayer_meta;
    }


    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->main_column(); ?>
                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php $this->right_column() ?>

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {
        // todo: fix validate view_lib param is present and is_numeric
        if ( !isset( $_GET['view_lib'] ) || is_null( $_GET['view_lib'] ) ) {
            return new WP_Error( __METHOD__, 'Invalid Prayer Library ID' );
        }
        $lib_id = sanitize_key( wp_unslash( $_GET['view_lib'] ) );
        $prayer_library = self::get_prayer_library( $lib_id );


        if ( isset( $_POST['add_prayer_point_nonce'] ) ) {
            if ( !isset( $_POST['add_prayer_point_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['add_prayer_point_nonce'] ), 'add_prayer_point' ) ) {
                return;
            }
            self::process_add_prayer_point();
        }
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <?php if ( !empty( $prayer_library ) ) : ?>
                        <th colspan="6"><?php echo esc_html( $prayer_library['name'] ); ?></th>
                    <?php else : ?>
                        <td colspan="6"><?php esc_html_e( 'Error: Prayer Library not found', 'pray4movement-prayer-points' ); ?></td>
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
                <?php self::display_prayer_points( $lib_id ); ?>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
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
                            <input type="text" name="new_prayer_title" size="50" required>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php esc_html_e( 'Reference', 'pray4movement_prayer_points' ); ?>
                        </td>
                        <td>
                            <select name="new_prayer_reference_book">
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
                            <input type="text" name="new_prayer_reference_verse" size="30">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php esc_html_e( 'Content', 'pray4movement_prayer_points' ); ?> (*)
                        </td>
                        <td>
                            <textarea name="new_prayer_content" rows="10" cols="50" required></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php esc_html_e( 'Tags', 'pray4movement_prayer_points' ); ?>
                        </td>
                        <td>
                            <input type="text" name="new_prayer_tags" size="50">
                        </td>
                    </tr>
                    <tr style="display:none;">
                        <td></td>
                        <td><input type="hidden" name="new_prayer_libid" value="<?php echo esc_html( $lib_id ); ?>"></td>
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

        if ( !isset( $_POST['new_prayer_libid'] ) || !isset( $_POST['new_prayer_title'] ) || !isset( $_POST['new_prayer_content'] ) ) {
            return;
        }

        if ( !empty( $_POST['new_prayer_title'] ) ) {
            $new_prayer_title = sanitize_text_field( wp_unslash( $_POST['new_prayer_title'] ) );
        }

        if ( !empty( $_POST['new_prayer_content'] ) ) {
            $new_prayer_content = sanitize_text_field( wp_unslash( $_POST['new_prayer_content'] ) );
            $new_prayer_content_hash = md5( $new_prayer_content );
        }

        $meta_args = [];
        $meta_args['title'] = $new_prayer_title;
        $meta_args['reference'] = null;
        if ( !empty( $_POST['new_prayer_reference_book'] ) && !empty( $_POST['new_prayer_reference_verse'] ) ) {
            $reference = sanitize_text_field( wp_unslash( $_POST['new_prayer_reference_book'] ) );
            $reference .= ' ';
            $reference .= sanitize_text_field( wp_unslash( $_POST['new_prayer_reference_verse'] ) );
            $meta_args['reference'] = $reference;
        }

        $new_prayer_libid = sanitize_key( wp_unslash( $_POST['new_prayer_libid'] ) );
        $new_prayer_status = 'unpublished'; //todo delete this test line

        global $wpdb;

        $test = $wpdb->insert(
            $wpdb->prefix.'dt_prayer_points',
            [
                'lib_id' => $new_prayer_libid,
                'content' => $new_prayer_content,
                'hash' => $new_prayer_content_hash,
                'status' => $new_prayer_status,
            ],
            [
                '%d', // lib_id
                '%s', // content
                '%s', // hash
                '%s', // status
            ]
        );

        if ( !$test ) {
            Pray4Movement_Prayer_Points_Menu::admin_notice( __( 'Could not add new prayer point to library', 'pray4movement_prayer_points' ), 'error' );
            return;
        }

        // Insert Prayer Point Metas
        $prayer_id = $wpdb->insert_id;

        foreach ( $meta_args as $arg_key => $arg_value ) {
            $wpdb->insert(
                $wpdb->prefix.'dt_prayer_points_meta',
                [
                    'prayer_id' => $prayer_id,
                    'meta_key' => $arg_key,
                    'meta_value' => $arg_value
                ],
                [
                    '%s', // prayer_id
                    '%s', // meta_key
                    '%s', // meta_value
                ]
            );

        }

        if ( !empty( $_POST['new_prayer_tags'] ) ) {
            $tags_text = sanitize_text_field( wp_unslash( $_POST['new_prayer_tags'] ) );
            $tags = explode( ',', $tags_text );

            foreach ( $tags as $tag ) {
                $tag = trim( $tag );
                $wpdb->insert(
                    $wpdb->prefix.'dt_prayer_points_meta',
                    [
                        'prayer_id' => $prayer_id,
                        'meta_key' => 'tags',
                        'meta_value' => $tag,
                    ],
                    [
                        '%s', // meta_value
                    ]
                );
            }
        }

        Pray4Movement_Prayer_Points_Menu::admin_notice( __( 'Prayer Point added successfully', 'pray4movement_prayer_points' ), 'success' );

    }

    private function display_prayer_points( $lib_id ) {
        $prayer_points = self::get_prayer_points( $lib_id );
        if ( !$prayer_points ) : ?>
            <tr>
                <td colspan="6">
                    <i><?php esc_html_e( 'This Prayer Library is currently empty.', 'pray4movement_prayer_points' ); ?></i>
                </td>
            </tr>
            <?php
            return;
            endif;

        foreach ( $prayer_points as $prayer_point ) :
            $prayer_title = self::get_prayer_meta( $prayer_point['id'], 'title' )[0];
            $prayer_reference = self::get_prayer_meta( $prayer_point['id'], 'reference' )[0];
            $prayer_tags = self::get_prayer_meta( $prayer_point['id'], 'tags' );

            $tags = [];
            if ( $prayer_tags ) {
                foreach ( $prayer_tags as $prayer_tag ) {
                    $tags[] = $prayer_tag;
                }
            }
            ?>
                <tr id="delete-prayer-<?php echo esc_html( $prayer_point['id'] ); ?>">
                    <td>
                        <?php echo esc_html( $prayer_point['id'] ); ?>
                    </td>
                    <td>
                        <?php echo esc_html( $prayer_title ); ?>
                    </td>
                    <td>
                        <?php echo esc_html( $prayer_reference ); ?>
                    </td>
                    <td>
                        <?php echo esc_html( $prayer_point['content'] ); ?>
                    </td>
                    <td>
                        <?php echo esc_html( implode( ', ', $tags ) ); ?>
                    </td>
                    <td>
                        <a href="#" style="color:#b32d2e;" class="delete_prayer"  data-id="<?php echo esc_html( $prayer_point['id'] ); ?>" data-title="<?php echo esc_html( $prayer_title ); ?>">Delete</a>
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
                            <p>'${prayer_title}' Prayer Point deleted successfully</p>
                        </div>
                    `;
                    jQuery('#post-body-content').prepend(admin_notice);
            }
        </script>
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Information</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    Content
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }
}

