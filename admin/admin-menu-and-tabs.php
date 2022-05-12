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
        return 'PHN2ZyBpZD0ic3ZnIiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHdpZHRoPSI0MDAiIGhlaWdodD0iNDAwIiB2aWV3Qm94PSIwLCAwLCA0MDAsNDAwIj48ZyBpZD0ic3ZnZyI+PHBhdGggaWQ9InBhdGgwIiBkPSJNMjE5LjcxNiAxOS4yNDggQyAxMzkuNDUzIDMzLjcwNyw5Ni42MDggMTI3LjA1OSwxMzcuODU3IDE5Ny42MDAgTCAxNDEuNjAwIDIwNC4wMDAgMTU3LjExOSAyMDMuNTQzIEwgMTcyLjYzOCAyMDMuMDg2IDE2NC4yNDIgMTkwLjc0MyBDIDEyMS40OTUgMTI3LjkwMSwxNjUuMDQwIDQ0LjU0OSwyNDAuODAwIDQ0LjIwMCBDIDMyMS4xODkgNDMuODI4LDM2NC43NzIgMTM1LjA0OCwzMTQuMjM4IDE5Ny45MDggTCAzMTAuMTMyIDIwMy4wMTYgMzE2LjEwNyAyMDEuMzY2IEMgMzE5LjM5NCAyMDAuNDU5LDMyNS4wMzQgMjAwLjA5NywzMjguNjQyIDIwMC41NjIgQyAzMzIuMjQ5IDIwMS4wMjcsMzM2Ljk0OCAyMDEuNjMxLDMzOS4wODQgMjAxLjkwNCBDIDM0Ny45NDYgMjAzLjAzNiwzNjEuNjAwIDE2NC44MTcsMzYxLjYwMCAxMzguODc3IEMgMzYxLjYwMCA2My4zMjQsMjkzLjU0OSA1Ljk0NywyMTkuNzE2IDE5LjI0OCBNMjI4LjgwMCA4NS42MDAgTCAyMjguODAwIDk3LjYwMCAyMTEuMjAwIDk3LjYwMCBMIDE5My42MDAgOTcuNjAwIDE5My42MDAgMTExLjIwMCBMIDE5My42MDAgMTI0LjgwMCAyMTEuMjAwIDEyNC44MDAgTCAyMjguODAwIDEyNC44MDAgMjI4LjgwMCAxNjEuNjAwIEwgMjI4LjgwMCAxOTguNDAwIDI0Mi40MDAgMTk4LjQwMCBMIDI1Ni4wMDAgMTk4LjQwMCAyNTYuMDAwIDE2MS42MDAgTCAyNTYuMDAwIDEyNC44MDAgMjcyLjgwMCAxMjQuODAwIEwgMjg5LjYwMCAxMjQuODAwIDI4OS42MDAgMTExLjIwMCBMIDI4OS42MDAgOTcuNjAwIDI3Mi44MDAgOTcuNjAwIEwgMjU2LjAwMCA5Ny42MDAgMjU2LjAwMCA4NS42MDAgTCAyNTYuMDAwIDczLjYwMCAyNDIuNDAwIDczLjYwMCBMIDIyOC44MDAgNzMuNjAwIDIyOC44MDAgODUuNjAwIE0yOTcuNjIzIDIyNy43MTkgQyAyNjkuMjU3IDIzNi43MTgsMjcwLjU4MSAyMzUuNzkxLDI2OC4wMTggMjQ4LjQ0MCBDIDI2NC41ODUgMjY1LjM4NiwyNDkuMDY4IDI3MS45NjQsMTk5LjIwMCAyNzcuNjEzIEMgMTY0LjE5NiAyODEuNTc4LDE1OC40MDAgMjgwLjUyMiwxNTguNDAwIDI3MC4xODAgQyAxNTguNDAwIDI2MS45OTIsMTYwLjc2MyAyNjEuMDA3LDE4NC44MDAgMjU5LjE3MyBDIDIzNy4zNjAgMjU1LjE2NSwyNTIuNjcyIDI0OS4yNTYsMjQ4Ljk0OCAyMzQuNDIwIEMgMjQ2LjgwNCAyMjUuODc3LDI0My4xNTIgMjI1LjEwNCwxOTcuODE0IDIyMy42MDcgQyAxNTEuNTM3IDIyMi4wNzksMTQ1LjMyNSAyMjIuNDQzLDEzNS45NDYgMjI3LjIyOCBDIDEyOC43MjcgMjMwLjkxMCw2My45NDIgMjc4LjM2Myw2NC4xMDQgMjc5Ljg0OSBDIDY0LjE2MSAyODAuMzcyLDc5LjY3OCAyOTIuMTg3LDk4LjU4NyAzMDYuMTA1IEwgMTMyLjk2NiAzMzEuNDEwIDE3MC4wODMgMzM0LjczMyBDIDIxMS41ODMgMzM4LjQ1MCwyMTkuMzkzIDMzNy44NjMsMjMxLjA4MyAzMzAuMTUxIEMgMjQyLjQ2MCAzMjIuNjQ2LDMzNS44MzQgMjQ3Ljk3OCwzMzguMzY5IDI0NC4zNTggQyAzNDMuNjgxIDIzNi43NzUsMzM5LjY1NiAyMjMuOTg4LDMzMS4wMTAgMjIwLjk3MyBDIDMyNC42MDUgMjE4Ljc0MSwzMjguMjMyIDIxOC4wMDgsMjk3LjYyMyAyMjcuNzE5IE0zNy4yMDAgMzA4LjM0MCBDIDI0LjgwMSAzMjQuNjk5LDIzLjUyNiAzMjguNzcxLDI5LjIwMCAzMzMuODg5IEMgNDIuOTY5IDM0Ni4zMDgsODYuODgzIDM3Ni4yMjYsOTAuMTQyIDM3NS40MDggQyA5My4yNzggMzc0LjYyMSwxMTIuMjYwIDM1MC45NTMsMTE0LjU1OCAzNDQuOTY2IEMgMTE1LjA0MiAzNDMuNzA1LDEwNC4zMDMgMzM0LjYzNCw4Ni44NzQgMzIxLjU4MiBDIDcxLjIxMyAzMDkuODU1LDU2LjI0MCAyOTguNjM0LDUzLjYwMCAyOTYuNjQ4IEwgNDguODAwIDI5My4wMzYgMzcuMjAwIDMwOC4zNDAgIiBzdHJva2U9Im5vbmUiIGZpbGw9IiMwMDAwMDAiIGZpbGwtcnVsZT0iZXZlbm9kZCI+PC9wYXRoPjwvZz48L3N2Zz4=';
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

        if ( isset( $_GET["tab"] ) ) {
            $tab = sanitize_key( wp_unslash( $_GET["tab"] ) );
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
                case "second":
                    $object = new Pray4Movement_Prayer_Points_Tab_Second();
                    $object->content();
                    break;
                default:
                    break;
            }
            ?>

        </div><!-- End wrap -->

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
        <script>
            function check_new_library() {
                let libraries_dropdown = jQuery('#prayer_library_dropdown');
                jQuery('.new_library_td').attr('style', 'display:none;');
                if ( libraries_dropdown.val() === 'add_new' ) {
                    jQuery('.new_library_td').attr('style', 'display:visible;');
                }
            }
        </script>
        <?php
    }

    public function main_column() {
        ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th colspan="4"><?php esc_html_e( 'Prayer Libraries', 'pray4movement_prayer_points' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4">
                        <?php esc_html_e( 'Please select a Prayer Library to continue', 'pray4movement_prayer_points' ); ?>
                    </td>
                </tr>
                <?php
                $prayer_libraries = self::get_prayer_libraries();
                self::display_prayer_libraries( $prayer_libraries );
                ?>
            </tbody>
        </table>
        <br>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th colspan="3"><?php esc_html_e( 'Add Prayer Points', 'pray4movement_prayer_points' ); ?></th>
                </tr>
            </thead>
            <tbody>
                    <?php self::display_add_prayer_library(); ?>
            </tbody>
        </table>
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
                $prayer_icon = $library['icon'];
            }
            ?>
        <tr>
            <td><img src="data:image/svg+xml;base64,<?php echo esc_html( $prayer_icon ); ?>" width="50px"></td>
            <td><a href="prayer-points/<?php echo esc_html( $library['key'] ); ?>"><?php echo esc_html( $library['name'] ); ?></a></td>
            <td><?php echo esc_html( $library['description'] ); ?></td>
            <td><a href="#"><?php esc_html_e( 'Export', 'disciple_tools' ); ?></a></td>
        </tr>
        <?php endforeach;
    }

    public function display_add_prayer_library() {
        $prayer_libraries = self::get_prayer_libraries();
        ?>
        <tr>
            <td>
                <?php self::display_prayer_libraries_list( $prayer_libraries ); ?>    
            </td>
            <td>
                <input class="new_library_td" type="text" name="new_library_name" placeholder="<?php esc_html_e( 'Library Name', 'pray4movement_prayer_points' ); ?>" style="display:none;">
                <button class="new_library_td button" type="post" style="display:none;"><?php esc_html_e( 'Add', 'disciple_tools' ); ?></button>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <textarea rows="5" cols="50"></textarea>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <button type="post"><?php esc_html_e( 'Add', 'disciple_tools' ); ?></button>
            </td>
        </tr>
        <?php
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
 * Class Pray4Movement_Prayer_Points_Tab_Second
 */
class Pray4Movement_Prayer_Points_Tab_Second {
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
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Header</th>
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

