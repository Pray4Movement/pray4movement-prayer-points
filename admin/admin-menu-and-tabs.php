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
    }

    public function __construct() {
        add_action( "admin_menu", array( $this, "register_menu" ) );
        $this->page_title = __( "Pray4Movement Prayer Points", 'pray4movement-prayer-points' );
    }

    public function register_menu() {
        $this->page_title = __( "Pray4Movement Prayer Points", 'pray4movement-prayer-points' );
        $menu_icon = Pray4Movement_Prayer_Points_Utilities::get_default_prayer_library_icon();
        add_menu_page( 'Prayer Points', 'Prayer Points', 'read', $this->token, [ $this, 'content' ], $menu_icon, 7 );
    }

    // Menu stub. Replaced when Disciple.Tools Theme fully loads.
    public function extensions_menu() {}

    public function content() {
        Pray4Movement_Prayer_Points_Utilities::check_user_can( 'read' );
        $this->check_view_library_tab();
        $this->check_edit_library_tab();
        $this->check_edit_prayer_tab();
        $this->check_localize_prayers_tab();
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

    private function check_localize_prayers_tab() {
        if ( isset( $_GET['localize'] ) ) {
            $object = new Pray4Movement_Prayer_Points_Localize_Prayers();
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
                <a href="<?php echo esc_attr( $link ) . 'localize' ?>" class="nav-tab <?php echo esc_html( ( $tab == 'localize' ) ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html( 'Localize', 'pray4movement_prayer_points' ); ?></a>
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
            case 'localize':
                $object = new Pray4Movement_Prayer_Points_Localize_Prayers();
                $object->content();
                break;
            default:
                break;
        }
    }
}

class Pray4Movement_Prayer_Points_Utilities {
    public static function check_user_can( $permission, $verbose = true ) {
        if ( !current_user_can( $permission ) ) {
            $error_message = '';
            if ( $verbose ) {
                $error_message = 'You do not have sufficient permissions to access this page.';
            }
            wp_die( esc_html( $error_message ) );
            return false;
        }
        return true;
    }

    public static function get_default_prayer_library_icon() {
        return 'data:image/svg+xml;base64,PHN2ZyBpZD0ic3ZnIiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHdpZHRoPSI0MDAiIGhlaWdodD0iNDAwIiB2aWV3Qm94PSIwLCAwLCA0MDAsNDAwIj48ZyBpZD0ic3ZnZyI+PHBhdGggaWQ9InBhdGgwIiBkPSJNMjE5LjcxNiAxOS4yNDggQyAxMzkuNDUzIDMzLjcwNyw5Ni42MDggMTI3LjA1OSwxMzcuODU3IDE5Ny42MDAgTCAxNDEuNjAwIDIwNC4wMDAgMTU3LjExOSAyMDMuNTQzIEwgMTcyLjYzOCAyMDMuMDg2IDE2NC4yNDIgMTkwLjc0MyBDIDEyMS40OTUgMTI3LjkwMSwxNjUuMDQwIDQ0LjU0OSwyNDAuODAwIDQ0LjIwMCBDIDMyMS4xODkgNDMuODI4LDM2NC43NzIgMTM1LjA0OCwzMTQuMjM4IDE5Ny45MDggTCAzMTAuMTMyIDIwMy4wMTYgMzE2LjEwNyAyMDEuMzY2IEMgMzE5LjM5NCAyMDAuNDU5LDMyNS4wMzQgMjAwLjA5NywzMjguNjQyIDIwMC41NjIgQyAzMzIuMjQ5IDIwMS4wMjcsMzM2Ljk0OCAyMDEuNjMxLDMzOS4wODQgMjAxLjkwNCBDIDM0Ny45NDYgMjAzLjAzNiwzNjEuNjAwIDE2NC44MTcsMzYxLjYwMCAxMzguODc3IEMgMzYxLjYwMCA2My4zMjQsMjkzLjU0OSA1Ljk0NywyMTkuNzE2IDE5LjI0OCBNMjI4LjgwMCA4NS42MDAgTCAyMjguODAwIDk3LjYwMCAyMTEuMjAwIDk3LjYwMCBMIDE5My42MDAgOTcuNjAwIDE5My42MDAgMTExLjIwMCBMIDE5My42MDAgMTI0LjgwMCAyMTEuMjAwIDEyNC44MDAgTCAyMjguODAwIDEyNC44MDAgMjI4LjgwMCAxNjEuNjAwIEwgMjI4LjgwMCAxOTguNDAwIDI0Mi40MDAgMTk4LjQwMCBMIDI1Ni4wMDAgMTk4LjQwMCAyNTYuMDAwIDE2MS42MDAgTCAyNTYuMDAwIDEyNC44MDAgMjcyLjgwMCAxMjQuODAwIEwgMjg5LjYwMCAxMjQuODAwIDI4OS42MDAgMTExLjIwMCBMIDI4OS42MDAgOTcuNjAwIDI3Mi44MDAgOTcuNjAwIEwgMjU2LjAwMCA5Ny42MDAgMjU2LjAwMCA4NS42MDAgTCAyNTYuMDAwIDczLjYwMCAyNDIuNDAwIDczLjYwMCBMIDIyOC44MDAgNzMuNjAwIDIyOC44MDAgODUuNjAwIE0yOTcuNjIzIDIyNy43MTkgQyAyNjkuMjU3IDIzNi43MTgsMjcwLjU4MSAyMzUuNzkxLDI2OC4wMTggMjQ4LjQ0MCBDIDI2NC41ODUgMjY1LjM4NiwyNDkuMDY4IDI3MS45NjQsMTk5LjIwMCAyNzcuNjEzIEMgMTY0LjE5NiAyODEuNTc4LDE1OC40MDAgMjgwLjUyMiwxNTguNDAwIDI3MC4xODAgQyAxNTguNDAwIDI2MS45OTIsMTYwLjc2MyAyNjEuMDA3LDE4NC44MDAgMjU5LjE3MyBDIDIzNy4zNjAgMjU1LjE2NSwyNTIuNjcyIDI0OS4yNTYsMjQ4Ljk0OCAyMzQuNDIwIEMgMjQ2LjgwNCAyMjUuODc3LDI0My4xNTIgMjI1LjEwNCwxOTcuODE0IDIyMy42MDcgQyAxNTEuNTM3IDIyMi4wNzksMTQ1LjMyNSAyMjIuNDQzLDEzNS45NDYgMjI3LjIyOCBDIDEyOC43MjcgMjMwLjkxMCw2My45NDIgMjc4LjM2Myw2NC4xMDQgMjc5Ljg0OSBDIDY0LjE2MSAyODAuMzcyLDc5LjY3OCAyOTIuMTg3LDk4LjU4NyAzMDYuMTA1IEwgMTMyLjk2NiAzMzEuNDEwIDE3MC4wODMgMzM0LjczMyBDIDIxMS41ODMgMzM4LjQ1MCwyMTkuMzkzIDMzNy44NjMsMjMxLjA4MyAzMzAuMTUxIEMgMjQyLjQ2MCAzMjIuNjQ2LDMzNS44MzQgMjQ3Ljk3OCwzMzguMzY5IDI0NC4zNTggQyAzNDMuNjgxIDIzNi43NzUsMzM5LjY1NiAyMjMuOTg4LDMzMS4wMTAgMjIwLjk3MyBDIDMyNC42MDUgMjE4Ljc0MSwzMjguMjMyIDIxOC4wMDgsMjk3LjYyMyAyMjcuNzE5IE0zNy4yMDAgMzA4LjM0MCBDIDI0LjgwMSAzMjQuNjk5LDIzLjUyNiAzMjguNzcxLDI5LjIwMCAzMzMuODg5IEMgNDIuOTY5IDM0Ni4zMDgsODYuODgzIDM3Ni4yMjYsOTAuMTQyIDM3NS40MDggQyA5My4yNzggMzc0LjYyMSwxMTIuMjYwIDM1MC45NTMsMTE0LjU1OCAzNDQuOTY2IEMgMTE1LjA0MiAzNDMuNzA1LDEwNC4zMDMgMzM0LjYzNCw4Ni44NzQgMzIxLjU4MiBDIDcxLjIxMyAzMDkuODU1LDU2LjI0MCAyOTguNjM0LDUzLjYwMCAyOTYuNjQ4IEwgNDguODAwIDI5My4wMzYgMzcuMjAwIDMwOC4zNDAgIiBzdHJva2U9Im5vbmUiIGZpbGw9IiMwMDAwMDAiIGZpbGwtcnVsZT0iZXZlbm9kZCI+PC9wYXRoPjwvZz48L3N2Zz4=';
    }

    public static function display_bible_book_dropdown() {
        ?>
        <select name="prayer_reference_book" id="prayer_reference_book">
            <option value="Genesis"><?php echo esc_html( 'Genesis', 'pray4movement_prayer_points' ); ?></option>
            <option value="Exodus"><?php echo esc_html( 'Exodus', 'pray4movement_prayer_points' ); ?></option>
            <option value="Leviticus"><?php echo esc_html( 'Leviticus', 'pray4movement_prayer_points' ); ?></option>
            <option value="Numbers"><?php echo esc_html( 'Numbers', 'pray4movement_prayer_points' ); ?></option>
            <option value="Deuteronomy"><?php echo esc_html( 'Deuteronomy', 'pray4movement_prayer_points' ); ?></option>
            <option value="Joshua"><?php echo esc_html( 'Joshua', 'pray4movement_prayer_points' ); ?></option>
            <option value="Judges"><?php echo esc_html( 'Judges', 'pray4movement_prayer_points' ); ?></option>
            <option value="Ruth"><?php echo esc_html( 'Ruth', 'pray4movement_prayer_points' ); ?></option>
            <option value="1 Samuel"><?php echo esc_html( '1 Samuel', 'pray4movement_prayer_points' ); ?></option>
            <option value="2 Samuel"><?php echo esc_html( '2 Samuel', 'pray4movement_prayer_points' ); ?></option>
            <option value="1 Kings"><?php echo esc_html( '1 Kings', 'pray4movement_prayer_points' ); ?></option>
            <option value="2 Kings"><?php echo esc_html( '2 Kings', 'pray4movement_prayer_points' ); ?></option>
            <option value="1 Chronicles"><?php echo esc_html( '1 Chronicles', 'pray4movement_prayer_points' ); ?></option>
            <option value="2 Chronicles"><?php echo esc_html( '2 Chronicles', 'pray4movement_prayer_points' ); ?></option>
            <option value="Ezra"><?php echo esc_html( 'Ezra', 'pray4movement_prayer_points' ); ?></option>
            <option value="Nehemiah"><?php echo esc_html( 'Nehemiah', 'pray4movement_prayer_points' ); ?></option>
            <option value="Esther"><?php echo esc_html( 'Esther', 'pray4movement_prayer_points' ); ?></option>
            <option value="Job"><?php echo esc_html( 'Job', 'pray4movement_prayer_points' ); ?></option>
            <option value="Psalms"><?php echo esc_html( 'Psalms', 'pray4movement_prayer_points' ); ?></option>
            <option value="Proverbs"><?php echo esc_html( 'Proverbs', 'pray4movement_prayer_points' ); ?></option>
            <option value="Ecclesiastes"><?php echo esc_html( 'Ecclesiastes', 'pray4movement_prayer_points' ); ?></option>
            <option value="Song of Solomon"><?php echo esc_html( 'Song of Solomon', 'pray4movement_prayer_points' ); ?></option>
            <option value="Isaiah"><?php echo esc_html( 'Isaiah', 'pray4movement_prayer_points' ); ?></option>
            <option value="Jeremiah"><?php echo esc_html( 'Jeremiah', 'pray4movement_prayer_points' ); ?></option>
            <option value="Lamentations"><?php echo esc_html( 'Lamentations', 'pray4movement_prayer_points' ); ?></option>
            <option value="Ezekiel"><?php echo esc_html( 'Ezekiel', 'pray4movement_prayer_points' ); ?></option>
            <option value="Daniel"><?php echo esc_html( 'Daniel', 'pray4movement_prayer_points' ); ?></option>
            <option value="Hosea"><?php echo esc_html( 'Hosea', 'pray4movement_prayer_points' ); ?></option>
            <option value="Joel"><?php echo esc_html( 'Joel', 'pray4movement_prayer_points' ); ?></option>
            <option value="Amos"><?php echo esc_html( 'Amos', 'pray4movement_prayer_points' ); ?></option>
            <option value="Obadiah"><?php echo esc_html( 'Obadiah', 'pray4movement_prayer_points' ); ?></option>
            <option value="Jonah"><?php echo esc_html( 'Jonah', 'pray4movement_prayer_points' ); ?></option>
            <option value="Micah"><?php echo esc_html( 'Micah', 'pray4movement_prayer_points' ); ?></option>
            <option value="Nahum"><?php echo esc_html( 'Nahum', 'pray4movement_prayer_points' ); ?></option>
            <option value="Habakkuk"><?php echo esc_html( 'Habakkuk', 'pray4movement_prayer_points' ); ?></option>
            <option value="Zephaniah"><?php echo esc_html( 'Zephaniah', 'pray4movement_prayer_points' ); ?></option>
            <option value="Haggai"><?php echo esc_html( 'Haggai', 'pray4movement_prayer_points' ); ?></option>
            <option value="Zechariah"><?php echo esc_html( 'Zechariah', 'pray4movement_prayer_points' ); ?></option>
            <option value="Malachi"><?php echo esc_html( 'Malachi', 'pray4movement_prayer_points' ); ?></option>
            <option value="Matthew"><?php echo esc_html( 'Matthew', 'pray4movement_prayer_points' ); ?></option>
            <option value="Mark"><?php echo esc_html( 'Mark', 'pray4movement_prayer_points' ); ?></option>
            <option value="Luke"><?php echo esc_html( 'Luke', 'pray4movement_prayer_points' ); ?></option>
            <option value="John"><?php echo esc_html( 'John', 'pray4movement_prayer_points' ); ?></option>
            <option value="Acts"><?php echo esc_html( 'Acts', 'pray4movement_prayer_points' ); ?></option>
            <option value="Romans"><?php echo esc_html( 'Romans', 'pray4movement_prayer_points' ); ?></option>
            <option value="1 Corinthians"><?php echo esc_html( '1 Corinthians', 'pray4movement_prayer_points' ); ?></option>
            <option value="2 Corinthians"><?php echo esc_html( '2 Corinthians', 'pray4movement_prayer_points' ); ?></option>
            <option value="Galatians"><?php echo esc_html( 'Galatians', 'pray4movement_prayer_points' ); ?></option>
            <option value="Ephesians"><?php echo esc_html( 'Ephesians', 'pray4movement_prayer_points' ); ?></option>
            <option value="Philippians"><?php echo esc_html( 'Philippians', 'pray4movement_prayer_points' ); ?></option>
            <option value="Colossians"><?php echo esc_html( 'Colossians', 'pray4movement_prayer_points' ); ?></option>
            <option value="1 Thessalonians"><?php echo esc_html( '1 Thessalonians', 'pray4movement_prayer_points' ); ?></option>
            <option value="2 Thessalonians"><?php echo esc_html( '2 Thessalonians', 'pray4movement_prayer_points' ); ?></option>
            <option value="1 Timothy"><?php echo esc_html( '1 Timothy', 'pray4movement_prayer_points' ); ?></option>
            <option value="2 Timothy"><?php echo esc_html( '2 Timothy', 'pray4movement_prayer_points' ); ?></option>
            <option value="Titus"><?php echo esc_html( 'Titus', 'pray4movement_prayer_points' ); ?></option>
            <option value="Philemon"><?php echo esc_html( 'Philemon', 'pray4movement_prayer_points' ); ?></option>
            <option value="Hebrews"><?php echo esc_html( 'Hebrews', 'pray4movement_prayer_points' ); ?></option>
            <option value="James"><?php echo esc_html( 'James', 'pray4movement_prayer_points' ); ?></option>
            <option value="1 Peter"><?php echo esc_html( '1 Peter', 'pray4movement_prayer_points' ); ?></option>
            <option value="2 Peter"><?php echo esc_html( '2 Peter', 'pray4movement_prayer_points' ); ?></option>
            <option value="1 John"><?php echo esc_html( '1 John', 'pray4movement_prayer_points' ); ?></option>
            <option value="2 John"><?php echo esc_html( '2 John', 'pray4movement_prayer_points' ); ?></option>
            <option value="3 John"><?php echo esc_html( '3 John', 'pray4movement_prayer_points' ); ?></option>
            <option value="Jude"><?php echo esc_html( 'Jude', 'pray4movement_prayer_points' ); ?></option>
            <option value="Revelation"><?php echo esc_html( 'Revelation', 'pray4movement_prayer_points' ); ?></option>
        </select>
        <?php
    }

    public static function get_languages() {
        $languages = [
            'en' => [
                    'code' => 'en',
                    'name' => __( 'English', 'pray4movement-prayer-points' ),
                    'flag' => '🇺🇸'
                ],
            'es' => [
                'code' => 'es',
                'name' => __( 'Spanish', 'pray4movement-prayer-points' ),
                'flag' => '🇪🇸'
                ],
            'fr' => [
                'code' => 'fr',
                'name' => __( 'French', 'pray4movement-prayer-points' ),
                'flag' => '🇫🇷'
                ],
            'pt' => [
                'code' => 'pt',
                'name' => __( 'Portuguese', 'pray4movement-prayer-points' ),
                'flag' => '🇧🇷'
            ],
        ];
        return $languages;
    }

    public static function display_translation_flags( $parent_library_id, $child_library_id ) {
        $languages = self::get_languages();
        $flag_from = $languages[self::get_language_from_library( $parent_library_id )]['flag'];
        $flag_to = $languages[self::get_language_from_library( $child_library_id )]['flag'];
        echo esc_html( "$flag_from → $flag_to" );
    }

    public static function get_language_from_library( $library_id ) {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare( "SELECT `language` FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE `id` = %d;", $library_id )
        );
    }
    public static function display_languages_dropdown() {
        $languages = self::get_languages();
        ?>
        <select name="language-dropdown" id="language-dropdown" required>
            <option value="" hidden>- <?php echo esc_html( 'Select Language', 'pray4movement_prayer_points' ); ?> -</option>
            <?php foreach ( $languages as $key => $value ): ?>
            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $languages[$key]['flag'] ); ?> <?php echo esc_html( $languages[$key]['name'] ); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public static function display_library_icon( $library_id ) {
        $icon = self::get_default_prayer_library_icon( $library_id );
        $library_icon = self::get_library_icon( $library_id );
        if ( $library_icon ) {
            $icon = $library_icon;
        }
        echo esc_html( $icon );
    }

    public static function get_library_icon( $library_id ) {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare( "SELECT `icon` FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE `id` = %d;", $library_id )
        );
    }

    public static function get_language_flag( $lang_code ) {
        $languages = self::get_languages();
        $flag = $languages[$lang_code]['flag'];
        return $flag;
    }

    public static function get_library_translation_links( $library_id ) {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare( "SELECT `id`, `language` FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE parent_id = %d", $library_id ), ARRAY_A
        );
    }

    public static function display_library_translation_links( $library_id ) {
        $child_libraries = self::get_library_translation_links( $library_id );
        foreach ( $child_libraries as $library ) {
            ?><a href="?page=pray4movement_prayer_points&view_library=<?php echo esc_attr( $library['id'] ); ?>"><?php echo esc_html( self::get_language_flag( $library['language'] ) ); ?></a>
            <?php
        }
    }

    public static function get_libraries_by_language( $language ) {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE `language` = %s;", $language ), ARRAY_A
        );
    }

    public static function change_parent_library_dropdown_selected_value( $parent_id ) {
        if ( empty( $parent_id ) ) {
            $parent_id = 'none';
        }
        ?>
        <script>
            
            jQuery('#library_parent_id option[value="<?php echo esc_html( $parent_id ); ?>"]').attr("selected", "selected");
        </script>
        <?php
    }
    public static function change_language_dropdown_selected_value( $language ) {
        ?>
        <script>
            jQuery('#language-dropdown option[value="<?php echo esc_html( $language ); ?>"]').attr("selected", "selected");
        </script>
        <?php
    }

    public static function show_delete_library_from_list_screen_script() {
        ?>
        <script>
        function deleteLibrary( libraryId, libraryName ) {
            if(confirm(`Delete the '${libraryName}' Prayer Library?`)) {
                jQuery.ajax({
                    type: 'POST',
                    contentType: 'application/json; charset=utf-8',
                    dataType: 'json',
                    url: window.location.origin + '/wp-json/pray4movement-prayer-points/v1/delete_prayer_library/' + libraryId,
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                    },
                    success: deleteLibrarySuccess( libraryId, libraryName ),
                });
            }
        }

        function deleteLibrarySuccess( libraryId, libraryName ) {
            jQuery( '#delete-library-' + libraryId ).remove();
                let adminNotice = `
                    <div class="notice notice-success is-dismissible">
                        <p>'${libraryName}' Prayer Library deleted successfully!</p>
                    </div>
                `;
            jQuery('.nav-tab-wrapper').before(adminNotice);
        }
        </script>
        <?php
    }
    public static function show_delete_library_from_library_screen_script() {
        ?>
        <script>
            function deleteLibrary( libraryId, libraryName ) {
                    if(confirm(`Delete the '${libraryName}' Prayer Library?`)) {
                        jQuery.ajax({
                            type: 'POST',
                            contentType: 'application/json; charset=utf-8',
                            dataType: 'json',
                            url: window.location.origin + '/wp-json/pray4movement-prayer-points/v1/delete_prayer_library/' + libraryId,
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                            },
                            success: deleteLibraryFromLibraryScreenSuccess( libraryName ),
                        });
                    }
                }

                function deleteLibraryFromLibraryScreenSuccess( libraryName ) {
                    alert( `${libraryName} deleted successfully!`);
                    window.location['href'] = '/wp-admin/admin.php?page=pray4movement_prayer_points';
                }
        </script>
        <?php
    }

    public static function display_tags( $parent_prayer_id, $language ) {
        $child_prayer_point = self::get_child_prayer_point_from_parent_id( $parent_prayer_id, $language );
        if ( !isset( $child_prayer_point['id'] ) || is_null( $child_prayer_point['id'] ) ) {
            return;
        }
        $prayer_id = $child_prayer_point['id'];

        global $wpdb;
        $result = $wpdb->get_var(
            $wpdb->prepare( "SELECT GROUP_CONCAT( meta_value ) AS `tags` FROM `{$wpdb->prefix}dt_prayer_points_meta` WHERE `prayer_id` = %s;", $prayer_id )
        );
            echo esc_html( str_replace( ',', ', ', $result ) );
    }

    public static function display_parent_libraries_dropdown() {
        $prayer_libraries = self::get_parent_prayer_libraries();
        ?>
        <select name="library_parent_id" id="library_parent_id">
                <option hidden value="">- <?php esc_html_e( 'Parent Library', 'pray4movement_prayer_points' ); ?> -</option>
                <option value="none"><?php esc_html_e( '-- none --', 'pray4movement_prayer_points' ); ?></option>
                <?php if ( empty( $prayer_libraries ) ) : ?>
                    <option disabled><?php esc_html_e( 'No Prayer Libraries found', 'pray4movement_prayer_points' ); ?></option>
                <?php else : ?>
                    <?php foreach ( $prayer_libraries as $library ) : ?>
                    <option value="<?php echo esc_html( $library['id'] ); ?>"><?php echo esc_html( $library['name'] ); ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
        </select>
        <?php
    }

    public static function display_all_libraries_dropdown() {
        $prayer_libraries = self::get_prayer_libraries();
        ?>
        <select name="library-id" id="library-id" required="required">
                <option hidden value="">- <?php esc_html_e( 'Select a Library', 'pray4movement_prayer_points' ); ?> -</option>
                <?php if ( empty( $prayer_libraries ) ) {
                    ?>
                        <option disabled><?php esc_html_e( 'No Prayer Libraries found', 'pray4movement_prayer_points' ); ?></option>
                    </select>
                    <?php
                    return;
                }

                foreach ( $prayer_libraries as $library ) {
                    $library_flag = self::get_language_flag( $library['language'] );
                    $library_label = $library_flag . ' ' . $library['name'];
                    if ( is_null( $library['parent_id'] ) ) {
                        ?>
                        <option value="<?php echo esc_attr( $library['id'] ); ?>"><?php echo esc_html( $library_label );?></option>
                        <?php
                        continue;
                    }
                    ?>
                    <option value="<?php echo esc_attr( $library['id'] ); ?>">  - <?php echo esc_html( $library_label );?></option>
                    <?php
                }
                ?>
                </select>
                <?php
    }

    public static function get_child_prayer_point_from_parent_id( $parent_prayer_id, $language ) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}dt_prayer_points` WHERE `parent_id` = %d AND `language` = %s ORDER BY id DESC LIMIT 1;", $parent_prayer_id, $language ),
        ARRAY_A );
    }

    public static function get_book_translation( $string, $language ) {
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

    public static function admin_notice( string $notice, string $type ) {
        ?>
        <div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
            <p><?php echo esc_html( $notice ); ?></p>
        </div>
        <?php
    }

    public static function get_prayer_library( $library_id ) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE id = %d;", $library_id
            ), ARRAY_A
        );
    }

    public static function get_parent_library_id_from_child_id( $child_library_id ) {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare( "SELECT parent_id FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE id = %d;", $child_library_id )
        );
    }

    public static function library_is_parent( $library_id ) {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare( "SELECT ISNULL(parent_id) FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE id = %d;", $library_id )
        );
    }

    public static function get_prayer_points( $library_id ) {
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

    public static function get_prayer_points_localized( $library_id ) {
        $prayer_points = self::get_prayer_points( $library_id );
        $rules = self::get_localization_rules_by_library_id( $library_id );
        $prayer_points_localized = self::apply_rules_to_prayer_points( $prayer_points, $rules );
        return $prayer_points_localized;
    }

    public static function get_full_prayer_points( $library_id ) {
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
                WHERE pp.library_id = %d
                ORDER BY pp.library_id ASC;", $library_id
            ), ARRAY_A
        );
    }

    public static function get_localization_rules_by_library_id( $library_id ) {
        global $wpdb;
        $rules = $wpdb->get_var(
            $wpdb->prepare( "SELECT `rules` FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE `id` = %d;", $library_id )
        );
        return maybe_unserialize( $rules );
    }

    public static function apply_rules_to_prayer_points( $prayer_points, $rules ) {
        $prayer_points_localized = [];
        foreach ( $prayer_points as $prayer_point ) {
            $prayer_point_localized = $prayer_point;
            if ( $rules ) {
                foreach ( $rules as $rule ) {
                    $prayer_point_localized = str_replace( $rule['replace_from'], $rule['replace_to'], $prayer_point_localized );
                }
            }
            $prayer_points_localized[] = $prayer_point_localized;
        }
        return $prayer_points_localized;
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
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM `{$wpdb->prefix}dt_prayer_points` WHERE id = %d;", $prayer_id
            ), ARRAY_A
        );
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
            $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}dt_prayer_points WHERE id = %d;", $prayer_id )
        );
    }

    public static function check_prayer_point_exists( $hash ) {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare( "SELECT hash FROM {$wpdb->prefix}dt_prayer_points WHERE hash = %s;", $hash )
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
        $tags = sanitize_text_field( wp_unslash( urldecode( strtolower( $raw_tags ) ) ) );
        $tags = explode( ',', $tags );
        $tags = array_map( 'trim', $tags );
        return array_filter( $tags );
    }

    public static function get_prayer_libraries() {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT * FROM `{$wpdb->prefix}dt_prayer_points_lib`;", ARRAY_A
        );
    }

    public static function get_parent_prayer_libraries() {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT * FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE parent_id IS NULL;", ARRAY_A
        );
    }

    public static function get_meta_value_by_key( $meta_key ) {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}dt_prayer_points_meta WHERE meta_key = %s;", $meta_key )
        );
    }

    public static function insert_prayer_library( $library ) {
        global $wpdb;
        if ( self::is_existing_library_key( $library['key'] ) ) {
            self::admin_notice( __( 'A Prayer Library with that name already exists. Please choose a different name.', 'pray4movement_prayer_points' ), 'error' );
            return;
        }

        $language = 'en';
        if ( isset( $library['language'] ) && $library['language'] !== 'none' ) {
            $language = sanitize_text_field( wp_unslash( $library['language'] ) );
        }
        $test = $wpdb->insert(
            $wpdb->prefix.'dt_prayer_points_lib',
            [
                'key' => $library['key'],
                'name' => $library['name'],
                'description' => $library['desc'],
                'language' => $language,
                'parent_id' => $library['parent_id'],
                'icon' => $library['icon'],
            ],
            [ '%s', '%s', '%s', '%s', '%d', '%s' ]
        );
        if ( !$test ) {
            self::admin_notice( __( 'Could not add new Prayer Library to table', 'pray4movement_prayer_points' ), 'error' );
            return;
        }
        self::admin_notice( __( 'Prayer Library created successfully!', 'pray4movement_prayer_points' ), 'success' );
    }

    private static function is_existing_library_key( $library_key ) {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare( "SELECT `key` FROM `{$wpdb->prefix}dt_prayer_points_lib` WHERE `key` = %s;", $library_key )
        );
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
}

class Pray4Movement_Prayer_Points_Tab_Explore {
    public function content() {
        Pray4Movement_Prayer_Points_Utilities::check_user_can( 'read' );
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-3">
                    <div id="post-body-content">
                        <?php $this->main_explore_column(); ?>
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
                    <th><?php Pray4Movement_Prayer_Points_Utilities::display_languages_dropdown(); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th></th>
                    <th><?php esc_html_e( 'Name', 'pray4movement_prayer_points' ); ?></th>
                    <th><?php esc_html_e( 'Description', 'pray4movement_prayer_points' ); ?></th>
                    <th><?php esc_html_e( 'Prayer Points', 'pray4movement_prayer_points' ); ?></th>
                    <th><?php esc_html_e( 'Languages', 'pray4movement_prayer_points' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'pray4movement_prayer_points' ); ?></th>
                </tr>
                <?php
                $prayer_libraries = Pray4Movement_Prayer_Points_Utilities::get_parent_prayer_libraries();
                if ( isset( $_GET['lang'] ) ) {
                    $language = sanitize_text_field( wp_unslash( $_GET['lang'] ) );
                    $prayer_libraries = Pray4Movement_Prayer_Points_Utilities::get_libraries_by_language( $language );
                    Pray4Movement_Prayer_Points_Utilities::change_language_dropdown_selected_value( $language );
                }
                $this->display_prayer_libraries( $prayer_libraries );
                ?>
            </tbody>
        </table>
        <br>
        <?php Pray4Movement_Prayer_Points_Utilities::check_user_can( 'publish_posts', false ); ?>
        <form method="post">
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
                    <input type="text" name="library_name" required>
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
                    <?php esc_html_e( 'Language', 'pray4movement_prayer_points' ); ?>
                </td>
                <td>
                    <?php Pray4Movement_Prayer_Points_Utilities::display_languages_dropdown(); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?php esc_html_e( 'Parent Library', 'pray4movement_prayer_points' ); ?>
                </td>
                <td>
                    <?php Pray4Movement_Prayer_Points_Utilities::display_parent_libraries_dropdown(); ?>
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
        <?php Pray4Movement_Prayer_Points_Utilities::show_delete_library_from_list_screen_script(); ?>
        <script>
            jQuery('#language-dropdown').on('change', function(){
                window.location['href'] = '/wp-admin/admin.php?page=pray4movement_prayer_points&lang=' + this.value;
            });
        </script>
        <?php
    }

    private function process_add_library() {
        if ( !isset( $_POST['add_library_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['add_library_nonce'] ), 'add_library' ) ) {
            return;
        }
        if ( !isset( $_POST['library_name'] ) || !isset( $_POST['library_desc'] ) || !isset( $_POST['language-dropdown'] ) || !isset( $_POST['library_parent_id'] ) || !isset( $_POST['library_icon'] ) ) {
            return;
        }
        $library_parent_id = null;
        if ( $_POST['library_parent_id'] > 0 ) {
            $library_parent_id = sanitize_text_field( wp_unslash( $_POST['library_parent_id'] ) );
        }
        $library = [
            'name' => sanitize_text_field( wp_unslash( $_POST['library_name'] ) ),
            'desc' => sanitize_text_field( wp_unslash( $_POST['library_desc'] ) ),
            'language' => sanitize_text_field( wp_unslash( $_POST['language-dropdown'] ) ),
            'parent_id' => $library_parent_id,
            'icon' => sanitize_text_field( wp_unslash( $_POST['library_icon'] ) ),
        ];
        $library['key'] = Pray4Movement_Prayer_Points_Utilities::generate_key_from_string( $library['name'] );
        Pray4Movement_Prayer_Points_Utilities::insert_prayer_library( $library );
    }

    public function display_prayer_libraries( $prayer_libraries ) {
        foreach ( $prayer_libraries as $library ) :
            $prayer_icon = Pray4Movement_Prayer_Points_Utilities::get_default_prayer_library_icon();
            if ( isset( $library['icon'] ) && $library['icon'] !== '' ) {
                    $prayer_icon = $library['icon'];
            }
            ?>
        <tr id="delete-library-<?php echo esc_html( $library['id'] ); ?>">
            <td><img src="<?php echo esc_html( $prayer_icon ); ?>" width="50px"></td>
            <td><a href="/wp-admin/admin.php?page=pray4movement_prayer_points&view_library=<?php echo esc_html( $library['id'] ); ?>"><?php echo esc_html( $library['name'] ); ?></a></td>
            <td><?php echo esc_html( $library['description'] ); ?></td>
            <td><?php echo esc_html( Pray4Movement_Prayer_Points_Utilities::count_prayer_points_in_library( $library['id'] ) ); ?></td>
            <td><?php Pray4Movement_Prayer_Points_Utilities::display_library_translation_links( $library['id'] ); ?></td>
            <td>
                <a href="/wp-admin/admin.php?page=pray4movement_prayer_points&edit_library=<?php echo esc_attr( $library['id'] ); ?>"><?php esc_html_e( 'Edit', 'pray4movement_prayer_points' ); ?></a> | 
                <a href="javascript:deleteLibrary(<?php echo esc_attr( $library['id'] ); ?>, `<?php echo esc_attr( $library['name'] ); ?>`);" style="color:#b32d2e;"><?php esc_html_e( 'Delete', 'pray4movement_prayer_points' ); ?></a>
            </td>
        </tr>
        <?php endforeach;
    }
}


class Pray4Movement_Prayer_Points_Edit_Library {
    public function content() {
        Pray4Movement_Prayer_Points_Utilities::check_user_can( 'edit_posts' );
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
        <form method="post">
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
                    <input type="text" name="library_name" size="50" value="<?php echo esc_attr( $library['name'] ); ?>" required>
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
                    <?php esc_html_e( 'Language', 'pray4movement_prayer_points' ); ?>
                </td>
                <td>
                    <?php Pray4Movement_Prayer_Points_Utilities::display_languages_dropdown(); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?php esc_html_e( 'Parent Library', 'pray4movement_prayer_points' ); ?>
                </td>
                <td>
                    <?php Pray4Movement_Prayer_Points_Utilities::display_parent_libraries_dropdown(); ?>
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
        Pray4Movement_Prayer_Points_Utilities::change_parent_library_dropdown_selected_value( $library['parent_id'] );
        Pray4Movement_Prayer_Points_Utilities::change_language_dropdown_selected_value( $library['language'] );
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

        if ( !isset( $_POST['library_id'] ) || !isset( $_POST['library_parent_id'] ) || !isset( $_POST['library_name'] ) || !isset( $_POST['library_desc'] ) || !isset( $_POST['language-dropdown'] ) || !isset( $_POST['library_icon'] ) ) {
            return;
        }

        $library_parent_id = null;
        if ( $_POST['library_parent_id'] > 0 ) {
            $library_parent_id = sanitize_text_field( wp_unslash( $_POST['library_parent_id'] ) );
        }
        $library = [
            'id' => sanitize_text_field( wp_unslash( $_POST['library_id'] ) ),
            'parent_id' => $library_parent_id,
            'name' => sanitize_text_field( wp_unslash( $_POST['library_name'] ) ),
            'desc' => sanitize_text_field( wp_unslash( $_POST['library_desc'] ) ),
            'language' => sanitize_text_field( wp_unslash( $_POST['language-dropdown'] ) ),
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
                'language' => $library['language'],
                'parent_id' => $library['parent_id'],
                'icon' => $library['icon'],
            ],
            [ 'id' => $library['id'] ],
            [ '%s', '%s', '%s', '%d', '%s' ]
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
        Pray4Movement_Prayer_Points_Utilities::check_user_can( 'read' );
        ?>
        <div class="wrap">
            <div id="poststuff">
                <p>
                    <a href="/wp-admin/admin.php?page=pray4movement_prayer_points"><?php esc_html_e( '<< Back to Prayer Libraries', 'pray4movement_prayer_points' ); ?></a>
                </p>
                <div id="post-body" class="metabox-holder columns-3">
                    <div id="post-body-content">
                        <?php $this->main_view_library_column(); ?>
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

        if ( Pray4Movement_Prayer_Points_Utilities::library_is_parent( $library_id ) ) {
            self::show_parent_library( $library_id );
            return;
        }
        self::show_child_library( $library_id );
        return;
    }

    public function show_parent_library( $library_id ) {
        $library = Pray4Movement_Prayer_Points_Utilities::get_prayer_library( $library_id );
        self::display_prayer_points_for_parent_library( $library['id'] );
    }

    public function show_child_library( $library_id ) {
        $library = Pray4Movement_Prayer_Points_Utilities::get_prayer_library( $library_id );
        self::display_prayer_points_for_child_library( $library['id'] );
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
            'content' => sanitize_textarea_field( wp_unslash( $_POST['prayer_content'] ) ),
            'hash' => md5( sanitize_text_field( wp_unslash( $_POST['prayer_content'] ) ) ),
            'book' => sanitize_text_field( wp_unslash( $_POST['prayer_reference_book'] ) ),
            'verse' => sanitize_text_field( wp_unslash( $_POST['prayer_reference_verse'] ) ),
            'reference' => sanitize_text_field( wp_unslash( $_POST['prayer_reference_book'] . ' ' . $_POST['prayer_reference_verse'] ) ),
            'tags' => sanitize_text_field( wp_unslash( $_POST['prayer_tags'] ) ),
            'status' => 'unpublished',
        ];

        if ( Pray4Movement_Prayer_Points_Utilities::check_prayer_point_exists( $prayer['hash'] ) ) {
            return;
        }
        Pray4Movement_Prayer_Points_Utilities::insert_prayer_point( $prayer );
        $prayer['id'] = Pray4Movement_Prayer_Points_Utilities::get_last_prayer_point_id();
        $tags = Pray4Movement_Prayer_Points_Utilities::sanitize_tags( $prayer['tags'] );
        Pray4Movement_Prayer_Points_Utilities::insert_all_tags( $prayer['id'], $tags );
        Pray4Movement_Prayer_Points_Utilities::admin_notice( __( 'Prayer Point added successfully!', 'pray4movement_prayer_points' ), 'success' );
    }

    public static function process_edit_prayer_point() {
        if ( !isset( $_POST['edit_prayer_point_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['edit_prayer_point_nonce'] ), 'edit_prayer_point' ) ) {
            $prayer = Pray4Movement_Prayer_Points_Utilities::get_prayer_point_from_url_param();
            return $prayer;
        }
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
            'content' => sanitize_textarea_field( wp_unslash( $_POST['prayer_content'] ) ),
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

    private static function display_prayer_points_for_parent_library( $library_id ) {
        $library = Pray4Movement_Prayer_Points_Utilities::get_prayer_library( $library_id );
        $prayer_points = Pray4Movement_Prayer_Points_Utilities::get_prayer_points( $library_id );
        ?>
        <table style="width: 100%">
            <tr>
                <td>
                    <h1>
                        <img src="<?php Pray4Movement_Prayer_Points_Utilities::display_library_icon( $library['id'] ) ?>" width="35px">
                        <?php echo esc_html( $library['name'] ); ?>
                    </h1>
                </td>
                <td style="text-align: right;">
                    <a href="/wp-admin/admin.php?page=pray4movement_prayer_points&edit_library=<?php echo esc_attr( $library['id'] ); ?>" title="<?php esc_attr_e( 'Edit library', 'pray4movement_prayer_points' ); ?>"><?php esc_html_e( 'Edit', 'pray4movement_prayer_points' ); ?></a> | 
                    <a href="javascript:deleteLibrary( <?php echo esc_attr( $library['id'] ); ?>, `<?php echo esc_attr( $library['name'] ); ?>`);" title="<?php echo esc_attr( 'Delete library', 'pray4movement_prayer_points' ); ?>" style="color:#b32d2e;"><?php esc_html_e( 'Delete', 'pray4movement_prayer_points' ); ?></a>
                </td>
            </tr>
        </table>
        <?php Pray4Movement_Prayer_Points_Utilities::show_delete_library_from_library_screen_script(); ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <?php if ( !empty( $library ) ) : ?>
                        <th colspan="5"><?php echo esc_html( $library['name'] ); ?></th>
                        <th style="text-align: right;"><?php echo esc_html( Pray4Movement_Prayer_Points_Utilities::get_language_flag( $library['language'] ) ); ?></th>
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
        <?php if ( !$prayer_points ) : ?>
            <tr>
                <td colspan="6">
                    <i><?php esc_html_e( 'This Prayer Library is currently empty.', 'pray4movement_prayer_points' ); ?></i>
                </td>
            </tr>
            <?php
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
                        <?php
                        if ( Pray4Movement_Prayer_Points_Utilities::check_user_can( 'edit_posts', false ) ) {
                            ?>
                            <a href="/wp-admin/admin.php?page=pray4movement_prayer_points&edit_prayer=<?php echo esc_html( $prayer['id'] ); ?>"" >Edit</a> 
                            <?php
                        }
                        if ( Pray4Movement_Prayer_Points_Utilities::check_user_can( 'edit_posts', false ) && Pray4Movement_Prayer_Points_Utilities::check_user_can( 'delete_posts', false ) ) {
                            echo esc_html( ' | ' );
                        }
                        if ( Pray4Movement_Prayer_Points_Utilities::check_user_can( 'delete_posts', false ) ) {
                            ?>
                            <a href="javascript:deletePrayer(<?php echo esc_attr( $prayer['id'] ); ?>, `<?php echo esc_attr( $prayer['title'] ); ?>`);" style="color:#b32d2e;">Delete</a>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
        <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <form method="post">
            <?php
            wp_nonce_field( 'add_prayer_point', 'add_prayer_point_nonce' );
            Pray4Movement_Prayer_Points_Utilities::check_user_can( 'publish_posts', false );
            ?>
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
                            <?php Pray4Movement_Prayer_Points_Utilities::display_bible_book_dropdown(); ?>
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
                        <td><input type="hidden" name="prayer_library_id" value="<?php echo esc_html( $library_id ); ?>"></td>
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
            function deletePrayer(prayerId, prayerTitle) {
                if(confirm(`Delete the '${prayerTitle}' Prayer Point?`)) {
                    jQuery.ajax( {
                        type: 'POST',
                        contentType: 'application/json; charset=utf-8',
                        dataType: 'json',
                        url: window.location.origin + '/wp-json/pray4movement-prayer-points/v1/delete_prayer_point/' + prayerId,
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                        },
                        success: deletePrayerSuccess(prayerId, prayerTitle),
                    } );
                }
            }

            function deletePrayerSuccess(prayerId, prayerTitle) {
                jQuery( '#delete-prayer-' + prayerId ).remove();
                    let adminNotice = `
                        <div class="notice notice-success is-dismissible">
                            <p>'${prayerTitle}' Prayer Point deleted successfully!</p>
                        </div>
                    `;
                    jQuery('#post-body-content').prepend(adminNotice);
            }
        </script>
        <?php
    }

    private function display_prayer_points_for_child_library( $child_library_id ) {
        $child_library = Pray4Movement_Prayer_Points_Utilities::get_prayer_library( $child_library_id );
        $parent_library_id = Pray4Movement_Prayer_Points_Utilities::get_parent_library_id_from_child_id( $child_library_id );
        $parent_prayer_points = Pray4Movement_Prayer_Points_Utilities::get_full_prayer_points( $parent_library_id );
        ?>
        <input type="hidden" id="child-library-id" value="<?php echo esc_attr( $child_library_id ); ?>">
        <table style="width: 100%">
            <tr>
                <td>
                    <h1>
                        <img src="<?php Pray4Movement_Prayer_Points_Utilities::display_library_icon( $child_library['id'] ) ?>" width="35px">
                        <?php echo esc_html( $child_library['name'] ); ?>
                    </h1>
                </td>
                <td style="text-align: right;">
                    <a href="/wp-admin/admin.php?page=pray4movement_prayer_points&edit_library=<?php echo esc_attr( $child_library_id ); ?>" title="<?php esc_attr_e( 'Edit library', 'pray4movement_prayer_points' ); ?>"><?php esc_html_e( 'Edit', 'pray4movement_prayer_points' ); ?></a> | 
                    <a href="javascript:deleteLibrary( <?php echo esc_attr( $child_library_id ); ?>, `<?php echo esc_attr( $child_library['name'] ); ?>`);" title="<?php echo esc_attr( 'Delete library', 'pray4movement_prayer_points' ); ?>" style="color:#b32d2e;"><?php esc_html_e( 'Delete', 'pray4movement_prayer_points' ); ?></a>
                </td>
            </tr>
        </table>
        <?php foreach ( $parent_prayer_points as $parent_prayer_point ): ?>
        <table id="prayer-point-table-<?php echo esc_html( $parent_prayer_point['id'] ); ?>" class="widefat striped">
            <thead>
                <tr>
                    <th colspan="2">#<?php echo esc_html( $parent_prayer_point['id'] ); ?></th>
                    <th style="text-align:right;">
                        <?php Pray4Movement_Prayer_Points_Utilities::display_translation_flags( $parent_library_id, $child_library_id ); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $child_prayer_point = Pray4Movement_Prayer_Points_Utilities::get_child_prayer_point_from_parent_id( $parent_prayer_point['id'], $child_library['language'] );
                ?>
                <tr>
                    <td>
                        <b><?php esc_html_e( 'Title', 'pray4movement-prayer-points' ); ?></b>
                    </td>
                    <td style="width:30%;">
                        <?php echo esc_html( $parent_prayer_point['title'] ); ?>
                    </td>
                    <td>
                        <input type="text" id="title-<?php echo esc_attr( $parent_prayer_point['id'] ); ?>" size="35" value="<?php if ( !empty( $child_prayer_point['title'] ) ) { echo esc_html( $child_prayer_point['title'] ); } ?>">
                    </td>
                </tr>
                <tr>
                    <td>
                        <b><?php esc_html_e( 'Content', 'pray4movement-prayer-points' ); ?></b>
                    </td>
                    <td>
                        <?php echo esc_html( $parent_prayer_point['content'] ); ?>
                    </td>
                    <td>
                        <textarea id="content-<?php echo esc_attr( $parent_prayer_point['id'] ); ?>" cols="35" rows="6"><?php if ( !empty( $child_prayer_point['content'] ) ) { echo esc_html( $child_prayer_point['content'] ); } ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td>
                        <b><?php esc_html_e( 'Reference', 'pray4movement_prayer_points' ); ?></b>
                    </td>
                    <td>
                        <?php echo esc_html( $parent_prayer_point['reference'] ); ?>
                    </td>
                    <td>
                        <?php echo esc_html( Pray4Movement_Prayer_Points_Utilities::get_book_translation( $parent_prayer_point['book'], $child_library['language'] ) ); ?> <?php echo esc_html( $parent_prayer_point['verse'] ); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <b><?php esc_html_e( 'Tags', 'pray4movement-prayer-points' ); ?></b>
                    </td>
                    <td>
                        <?php echo esc_html( str_replace( ',', ', ', $parent_prayer_point['tags'] ) ); ?><br>
                    </td>
                    <td>
                        <input type="text" id="tags-<?php echo esc_attr( $parent_prayer_point['id'] ); ?>" size="35" value="<?php Pray4Movement_Prayer_Points_Utilities::display_tags( $parent_prayer_point['id'], $child_library['language'] ); ?>">
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left;" colspan="2">
                        <a href="admin.php?page=pray4movement_prayer_points&edit_prayer=<?php echo esc_attr( $parent_prayer_point['id'] ); ?>"><?php echo esc_html( 'edit', 'pray4movement_prayer_points' ); ?></a>
                    </td>
                    <td style="text-align: right;">
                        <button class="button" onclick="saveChildPrayerPoint(<?php echo esc_attr( $parent_prayer_point['id'] ); ?>);"><?php esc_html_e( 'Save', 'pray4movement-prayer-points' ); ?></button>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <?php endforeach; ?>
        <?php Pray4Movement_Prayer_Points_Utilities::show_delete_library_from_library_screen_script(); ?>
        <script>
            function saveChildPrayerPoint( parentPrayerPointId ) {
                var titleInputBox = jQuery(`#title-${parentPrayerPointId}`);
                var contentInputBox = jQuery(`#content-${parentPrayerPointId}`);
                titleInputBox.attr('style', 'border: 1px solid black;');
                contentInputBox.attr('style', 'border: 1px solid black;');
                if ( titleInputBox[0].value === '' ) {
                    titleInputBox.attr('style', 'border: 1px solid red;');
                    return;
                }
                if ( contentInputBox[0].value === '' ) {
                    contentInputBox.attr('style', 'border: 1px solid red;');
                    return;
                }
                var title = titleInputBox[0].value;
                var content = jQuery(`#content-${parentPrayerPointId}`)[0].value;
                var child_library_id = jQuery('#child-library-id')[0].value;
                jQuery.ajax( {
                        type: 'POST',
                        contentType: 'application/json; charset=utf-8',
                        dataType: 'json',
                        url: window.location.origin + `/wp-json/pray4movement-prayer-points/v1/save_child_prayer_point/${parentPrayerPointId}/${child_library_id}/${title}/${content}`,
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                        },
                        success: saveChildPrayerPointTags(parentPrayerPointId),
                } );
            }

            function saveChildPrayerPointTags( parentPrayerPointId ) {
                var tags = jQuery(`#tags-${parentPrayerPointId}`)[0].value;
                if (!tags ) {
                    tags = '{null_tags}'
                }
                var libraryLanguage = '<?php echo esc_html( Pray4Movement_Prayer_Points_Utilities::get_language_from_library( $child_library_id ) ); ?>';
                jQuery.ajax( {
                        type: 'POST',
                        contentType: 'application/json; charset=utf-8',
                        dataType: 'json',
                        url: window.location.origin + `/wp-json/pray4movement-prayer-points/v1/save_child_prayer_point_tags/${parentPrayerPointId}/${libraryLanguage}/${tags}`,
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                        },
                        success: savePrayerPointSuccess(),
                } );
            }
            
            function savePrayerPointSuccess(parentPrayerPointId) {
                let adminNotice = `
                    <div class="notice notice-success is-dismissible">
                        <p><?php esc_html_e( 'Prayer Point updated successfully!', 'pray4movement-prayer-points' ); ?></p>
                    </div>
                `;
                jQuery(`#prayer-point-table-${parentPrayerPointId}`).prepend(adminNotice);
            }
        </script>
        <?php
    }
}

class Pray4Movement_Prayer_Points_Edit_Prayer {
    public function content() {
        Pray4Movement_Prayer_Points_Utilities::check_user_can( 'edit_posts' );
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
        <form method="post">
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
                    <?php Pray4Movement_Prayer_Points_Utilities::display_bible_book_dropdown(); ?>
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
        Pray4Movement_Prayer_Points_Utilities::check_user_can( 'publish_posts' );
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
                </div>
            </div>
        </div>
        <?php
    }

    public function main_prayer_points_import_column() {
        $this->check_for_import_prayer_nonce();
        ?>
        <form method="post" enctype="multipart/form-data">
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
                        <?php
                        $prayer_libraries = Pray4Movement_Prayer_Points_Utilities::get_parent_prayer_libraries();
                        ?>
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
                        <td><?php esc_html_e( 'Data has header row', 'pray4movement_prayer_points' ); ?></td>
                        <td><input type="checkbox" name="has_header"></td>
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
            $has_header = $this->get_has_header_boolean();
            $csv_data = $this->prepare_prayer_data_from_csv_file( $file_tmp_name, $has_header );
            $this->add_prayer_points_from_csv_data( $csv_data, $has_header );
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

    private function prepare_prayer_data_from_csv_file( $file_tmp_name, $has_header ) {
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

    private function get_has_header_boolean() {
        $has_header = false;
        if ( !isset( $_POST['import_prayer_points_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['import_prayer_points_nonce'] ), 'import_prayer_points' ) || !isset( $_FILES['import-file']['tmp_name'] ) ) {
            return false;
        }

        if ( isset( $_POST['has_header'] ) ) {
            if ( $_POST['has_header'] === 'on' ) {
                $has_header = true;

            }
        }
        return $has_header;
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

    private function add_prayer_points_from_csv_data( $csv_data, $has_header = false ) {
        $insert_count = 0;
        $linecount = 0;
        if ( $has_header ) {
            array_shift( $csv_data );
        }
        foreach ( $csv_data as $csv_prayer ) {
            $prayer = self::get_prayer_data_from_prepared_csv_data( $csv_prayer );
            if ( !is_null( $prayer['title'] ) || !is_null( $prayer['content'] ) ) {
                if ( Pray4Movement_Prayer_Points_Utilities::check_prayer_point_exists( $prayer['hash'] ) ) {
                    continue;
                }
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
        Pray4Movement_Prayer_Points_Utilities::check_user_can( 'read' );
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-3">
                    <div id="post-body-content">
                        <?php $this->main_prayer_points_column(); ?>
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
                        url: window.location.origin + `/wp-json/pray4movement-prayer-points/v1/get_prayer_points_localized/${libraryId}`,
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

class Pray4Movement_Prayer_Points_Localize_Prayers {
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder">
                    <div id="post-body-content">
                        <?php $this->main_localization_column(); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function main_localization_column() {
        $this->process_new_localization_rule();
        ?>
        <table class="widefat striped">
            <thead>
                <h1><?php echo esc_html( 'Localization Rules', 'pray4movement_prayer_points' ); ?></h1>
                <tr>
                    <th><?php echo esc_html( 'Library', 'pray4movement_prayer_points' ); ?></th>
                    <th><?php echo esc_html( 'Rule', 'pray4movement_prayer_points' ); ?></th>
                    <th><?php echo esc_html( 'Actions', 'pray4movement_prayer_points' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php self::display_localization_rules(); ?>
            </tbody>
        </table>

        <form method="post">
            <?php wp_nonce_field( 'new_localization_rule', 'new_localization_rule_nonce' ); ?>
            <table class="widefat" style="margin-top:12px;">
                <thead>
                    <tr>
                        <th colspan="3">New Rule</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="3">
                            <input type="text" name="new-rule-from" placeholder="<?php echo esc_attr( 'from', 'pray4movement_prayer_points' ); ?>" required>
                            <input type="text" name="new-rule-to" placeholder="<?php echo esc_attr( 'to', 'pray4movement_prayer_points' ); ?>" required>
                            <?php Pray4Movement_Prayer_Points_Utilities::display_all_libraries_dropdown(); ?>
                            <button class="button"><?php echo esc_html( 'Save', 'pray4movement_prayer_points' ); ?></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
        <script>
            function deleteLocalizationRule(libraryId, ruleId) {
                if(confirm(`Delete localization rule?`)) {
                    jQuery.ajax({
                        type: 'POST',
                        contentType: 'application/json; charset=utf-8',
                        dataType: 'json',
                        url: window.location.origin + `/wp-json/pray4movement-prayer-points/v1/delete_localization_rule/${libraryId}/${ruleId}`,
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                        },
                        success: deleteLocalizationRuleSuccess(libraryId, ruleId),
                    });
                }
            }

            function deleteLocalizationRuleSuccess(libraryId, ruleId) {
                jQuery(`#p4m-localization-rule-${libraryId}_${ruleId}`).remove();
                    let adminNotice = `
                        <div class="notice notice-success is-dismissible">
                            <p>Localization rule deleted successfully!</p>
                        </div>
                    `;
                jQuery('.nav-tab-wrapper').before(adminNotice);
            }
        </script>
        </script>
        <?php
    }

    private static function display_localization_rules() {
        $libraries = Pray4Movement_Prayer_Points_Utilities::get_prayer_libraries();

        foreach ( $libraries as $library ) {
            $library_rules = maybe_unserialize( $library['rules'] );
            if ( !isset( $current_library ) || $library['id'] !== $current_library ) {
                ?>
                <tr>
                    <th colspan="3"><b><?php echo esc_html( $library['name'] ); ?></b> <?php echo esc_html( Pray4Movement_Prayer_Points_Utilities::get_language_flag( $library['language'] ) ); ?></th>
                </tr>
                <?php
                $current_library = $library['id'];
            }
            if ( !$library_rules ) {
                ?>
                <tr>
                    <td></td>
                    <td><i><?php esc_html_e( 'No rules yet', 'p4m_prayer_points' ); ?></i></td>
                    <td></td>
                </tr>
                <?php
                continue;
            }
            foreach ( $library_rules as $rule ) {
                // var_dump( $library_rules );die();
                ?>
            <tr id="p4m-localization-rule-<?php echo esc_attr( $library['id'] . '_' . $rule['id'] ); ?>">
                <td></td>
                <td><?php echo esc_html( $rule['replace_from'] ); ?> → <?php echo esc_html( $rule['replace_to'] ); ?></td>
                <td><a href="#" onclick="javascript:deleteLocalizationRule(<?php echo esc_attr( $library['id'] ); ?>, <?php echo esc_attr( $rule['id'] ); ?>);" style="color:#b32d2e;">Delete</a></td>
            </tr>
            <?php }
        }
    }

    private static function process_new_localization_rule() {
        if ( !isset( $_POST['new_localization_rule_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['new_localization_rule_nonce'] ), 'new_localization_rule' ) ) {
            return;
        }

        if ( !isset( $_POST['new-rule-from'] ) || !isset( $_POST['new-rule-to'] ) || !isset( $_POST['library-id'] ) ) {
            return;
        }

        $rule = [
            'id' => self::get_rule_autoincrement_by_library_id( sanitize_text_field( wp_unslash( $_POST['library-id'] ) ) ),
            'library_id' => sanitize_text_field( wp_unslash( $_POST['library-id'] ) ),
            'replace_from' => sanitize_text_field( wp_unslash( $_POST['new-rule-from'] ) ),
            'replace_to' => sanitize_text_field( wp_unslash( $_POST['new-rule-to'] ) ),
        ];

        self::save_localization_rule( $rule );
    }

    private static function save_localization_rule( $new_rule ) {
        $rules = Pray4Movement_Prayer_Points_Utilities::get_localization_rules_by_library_id( $new_rule['library_id'] );
        if ( !$rules ) {
            $rules = [];
        }
        $rules[] = $new_rule;
        $rules = maybe_serialize( $rules );

        global $wpdb;
        $test = $wpdb->update(
            $wpdb->prefix.'dt_prayer_points_lib',
            [ 'rules' => $rules ],
            [ 'id' => $new_rule['library_id'] ],
            [ '%s' ],
            [ '%d' ]
        );
        if ( !$test ) {
            Pray4Movement_Prayer_Points_Utilities::admin_notice( __( 'Could not add new localization rule to table', 'pray4movement_prayer_points' ), 'error' );
            var_dump( $wpdb->last_error );
            return;
        }
        Pray4Movement_Prayer_Points_Utilities::admin_notice( __( 'Localization rule created successfully!', 'pray4movement_prayer_points' ), 'success' );
    }

    private static function get_rule_autoincrement_by_library_id( $library_id ) {
        $rules = Pray4Movement_Prayer_Points_Utilities::get_localization_rules_by_library_id( $library_id );
        $rules = maybe_unserialize( $rules );
        $autoincrement = 1;
        if ( !empty( $rules ) ) {
            $autoincrement = max( array_column( $rules, 'id' ) ) + 1;
        }
        return $autoincrement;
    }
}