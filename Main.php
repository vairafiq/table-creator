<?php
// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );


final class Table_generator_by_aazztech{

    private $req_wp_version = '4.0';
    public $plugin_pages = array('table-generator-all', 'create-table-page', 'attc-support', 'attc-export', 'attc-import','attc-extension');

    public function __construct(){
        // Don't let the class/plugin instantiate outside of WordPress
        if ( ! defined('ABSPATH') ) { die( ATTC_ALERT_MSG ); }
        //Load dependencies
        $this->load_dependencies('all', ATTC_CLASS_DIR);
        $this->controller = new ATTC_controller();
        $this->enqueuer = new ATTC_enqueue();
        $this->helper = new ATTC_helper();
        $this->database = new ATTC_database();
        $this->ajax = new ATTC_ajax_handler();
    }


    /**
     * It loads files from a given directory using require_once.
     * @param string|array $files list of the names of file or a single file name to be loaded. Default: all
     * @param string $directory  the location of the files
     * @param string $ext  the ext of the files to be loaded
     * @return mixed|null it requires all the files in a given directory
     */
    public function load_dependencies($files = 'all', $directory=ATTC_CLASS_DIR, $ext='.php')
    {
        if (!file_exists($directory)) return ; // vail if the directory does not exist

        switch ($files){
            case is_array($files) && 'all' !== strtolower($files[0]):
                // include one or more file looping through the $files array
                $this->load_some_file($files, $directory);
                break;
            case !is_array($files) && 'all' !== $files:
                //load a single file here
                (file_exists($directory.$files.$ext)) ? require_once $directory.$files.$ext : null;
                break;
            case 'all' == $files || 'all' == strtolower($files[0]):
                // load all php file here
                $this->load_all_files($directory);
                break;
        }

        return false;

    }


    /**
     * It loads all files that has the extension named $ext from the $dir
     * @param string $dir Name of the directory
     * @param string $ext Name of the extension of the files to be loaded
     */
    public function load_all_files($dir='', $ext='.php'){
        if (!file_exists($dir)) return;
        foreach (scandir($dir) as $file) {
            // require once all the files with the given ext. eg. .php
            if( preg_match( "/{$ext}$/i" , $file ) ) {
                require_once( $dir . $file );
            }
        }
    }

    /**
     * It loads one or more files but not all files that has the $ext from the $dir
     * @param string|array $files the array of files that should be loaded
     * @param string $dir Name of the directory
     * @param string $ext Name of the extension of the files to be loaded
     */
    public function load_some_file($files=[],$dir='', $ext='.php')
    {
        if (!file_exists($dir)) return; // vail if directory does not exist

            if(is_array($files)) {  // if the given files is an array then
                $files_to_loads = array_map(function ($i) use($ext){ return $i.$ext; }, $files);// add '.php' to the end of all files
                $found_files = scandir($dir); // get the list of all the files in the given $dir
                foreach ($files_to_loads as $file_to_load) {
                    in_array($file_to_load, $found_files) ? require_once $dir.$file_to_load : null;
                }
            }

    }


    public static function remove_plugin_data(  ) {
        if(get_option('attc_remove_data_on_uninstall')) {
            $table = ATTC_database::get_instance();
            $table->drop_table();
        }


    }


    /**
     * Prepare plugin to work by creating custom table to store plugin data and set some default options
     */
    public function prepare_plugin() {
        // create a table on the activation of the plugin
        $this->database->create_table();

    }

    /**
     * Initialize the plugin by hooking all actions and filters
     */
    public function init() {
        add_action('admin_init', array($this, 'warn_if_unsupported_wp'));
        add_action('plugins_loaded', array($this, 'load_textdomain' ) );


        // admin hooks and filter
        if ( is_admin() ) {
            add_filter( 'plugin_action_links_' . ATTC_BASE, array($this, 'add_plugin_action_link') );
            //@TODO; we will add plugin row meta links when website is ready and docs is available too.
            add_filter( 'plugin_row_meta', array( $this, 'add_plugin_row_meta_link' ), 10, 2 );
        }

        // Enables shortcode for Widget
        add_filter('widget_text', 'do_shortcode');



    }

    /**
     * It loads html view
     * @param string $name Name of the view to be loaded
     * @param array $args The array of arguments to be passed to the view
     * @return void
     */
    public function loadView( $name, $args = array() ) {
        global $ATTC, $post;
        include(ATTC_VIEWS_DIR.$name.'.php');
    }

    /**
     * It includes any files from the themes directory.
     * @param string $name  Name of the file from the Themes directory eg. 'style1/index'
     * @param array $args   Optional Values passed to the views to be used there.
     */
    public function loadTheme( $name, $args = array() ) {
        $name = "themes/{$name}";
        $this->loadView($name, $args);
    }


    /**
     * It adds links to the plugin activation page
     * @param arary $links The array of all default links of a plugin
     *
     * @return array The modified array of all links of a plugin
     */
    public function add_plugin_action_link(array $links) {
        unset($links['edit']); // protect editing the plugin by removing the editing link.
        $links[] = sprintf( '<a href="%s" title="%s">%s</a>', 'admin.php?page=create-table-page', 'Add New', __( 'Add New', 'tablegen' ) );
        $links[] = sprintf( '<a href="%s" title="%s">%s</a>', 'admin.php?page=table-generator-all', 'View All', __( 'View All', 'tablegen' ) );
        return $links;

    }


    public function add_plugin_row_meta_link(array $links, $file)
    {
        if ( ATTC_BASE === $file ) {
            $links[] = '<a href="https://exlac.com/documentation/">' . __( 'Documentation', 'tablegen' ) . '</a>';
            $links[] = '<a href="https://exlac.com/contact-us/">' . __( 'Get Support', 'tablegen' ) . '</a>';
            $links[] = '<a href="https://exlac.com/" title="' . esc_attr__( 'Support Table Generator with your donation!', 'tablegen' ) . '"><strong>' . __( 'Donate', 'tablegen' ) . '</strong></a>';
        }
        return $links;
    }


    /**
     *  It loads the text domain of the plugin
     * @return void
     */
    public function load_textdomain(){
        load_plugin_textdomain('tablegen', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/');
    }


    /**
     * It shows a warning to the user if they use older WordPress Version.
     * @return mixed
     */
    public function warn_if_unsupported_wp() {
        if ( $this->check_minimum_required_wp_version() ) {
            $wp_ver = ! empty( $GLOBALS['wp_version'] ) ? $GLOBALS['wp_version'] : '(undefined)';
            ?>
            <div class="error notice is-dismissible"><p>
                    <?php

                    printf( __( ATTC_PLUGIN_NAME. 'requires WordPress version %1$s or newer. It appears that you are running %2$s. The plugin may not work properly.', 'tablegen' ),
                        $this->req_wp_version,
                        esc_html( $wp_ver )
                    );

                    echo '<br>';

                    printf( __( 'Please upgrade your WordPress installation or download latest version from <a href="%s" target="_blank" title="Download Latest WordPress">here</a>.', 'tablegen' ),
                        'https://wordpress.org/download/'
                    );

                    ?>
                </p></div>
            <?php

            return;
        }
    }

    /**
     * It checks minimum required version of WordPress we defined in $this->req_wp_version
     * @return mixed
     */
    private function check_minimum_required_wp_version() {
        include( ABSPATH . WPINC . '/version.php' ); // get an unmodified $wp_version
        return ( version_compare( $wp_version, $this->req_wp_version, '<' ) );
    }



    
    
    


    /**
     * Generate the complete nonce string, from the nonce base, the action and an item, e.g. table_creator_delete_table_3.
     *
     * @since 1.0.0
     *
     * @param string      $action Action for which the nonce is needed.
     * @param string|bool $item   Optional. Item for which the action will be performed, like "table".
     * @return string The resulting nonce string.
     */
    
    public static function nonce( $action, $item = false ) {
        $nonce = "table_creator_{$action}";
        if ( $item ) {
            $nonce .= "_{$item}";
        }
        return $nonce;
    }

    /**
     * Check whether a nonce string is valid.
     *
     * @since 1.0.0
     *
     * @param string      $action    Action for which the nonce should be checked.
     * @param string|bool $item      Optional. Item for which the action should be performed, like "table".
     * @param string      $query_arg Optional. Name of the nonce query string argument in $_POST.
     * @param bool $ajax Whether the nonce comes from an AJAX request.
     */
    public static function check_nonce( $action, $item = false, $query_arg = '_wpnonce', $ajax = false ) {
        $nonce_action = self::nonce( $action, $item );
        if ( $ajax ) {
            
            check_ajax_referer( $nonce_action, $query_arg );
            
        } else {

            check_admin_referer( $nonce_action, $query_arg );

        }
    }

    /**
     * Calculate the column index (number) of a column header string (example: A is 1, AA is 27, ...).
     *
     * For the opposite, @see number_to_letter().
     *
     * @since 1.0.0
     *
     * @param string $column Column string.
     * @return int $number Column number, 1-based.
     */
    public static function letter_to_number( $column ) {
        $column = strtoupper( $column );
        $count = strlen( $column );
        $number = 0;
        for ( $i = 0; $i < $count; $i++ ) {
            $number += ( ord( $column[ $count - 1 - $i ] ) - 64 ) * pow( 26, $i );
        }
        return $number;
    }

    /**
     * "Calculate" the column header string of a column index (example: 2 is B, AB is 28, ...).
     *
     * For the opposite, @see letter_to_number().
     *
     * @since 1.0.0
     *
     * @param int $number Column number, 1-based.
     * @return string $column Column string.
     */
    public static function number_to_letter( $number ) {
        $column = '';
        while ( $number > 0 ) {
            $column = chr( 65 + ( ( $number - 1 ) % 26 ) ) . $column;
            $number = floor( ( $number - 1 ) / 26 );
        }
        return $column;
    }

    /**
     * Get a nice looking date and time string from the mySQL format of datetime strings for output.
     *
     * @param string $datetime  DateTime string in mySQL format or a Unix timestamp.
     * @param string $type      Optional. Type of $datetime, 'mysql' or 'timestamp'.
     * @param string $separator Optional. Separator between date and time.
     * @return string Nice looking string with the date and time.
     */
    public static function format_datetime( $datetime, $type = 'mysql', $separator = ' ' ) {
        // @TODO: Maybe change from using the stored WP Options to translated date/time schemes, like in https://core.trac.wordpress.org/changeset/35811.
        if ( 'mysql' === $type ) {
            return mysql2date( get_option( 'date_format' ), $datetime ) . $separator . mysql2date( get_option( 'time_format' ), $datetime );
        } else {
            return date_i18n( get_option( 'date_format' ), $datetime ) . $separator . date_i18n( get_option( 'time_format' ), $datetime );
        }
    }

    /**
     * Get the name from a WP user ID (used to store information on last editor of a table).
     *
     * @param int $user_id WP user ID.
     * @return string Nickname of the WP user with the $user_id.
     */
    public static function get_user_display_name( $user_id ) {
        $user = get_userdata( $user_id );
        return ( $user && isset( $user->display_name ) ) ? $user->display_name : __( '<em>unknown</em>', 'tablegen' );
    }



}
