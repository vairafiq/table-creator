<?php
// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if(!class_exists('ATTC_controller')):
class ATTC_controller {

    private $default_pages = array('table-generator-all', 'create-table-page', 'attc-import', 'attc-export');

    /*
     * Object for listing custom table data
     * */
    public $list_table;
    /*
     * ATTC_Database Object
     * */
    public $db;
    /*
     * ATTC_importer Object
     * */
    public $importer;

    public function __construct(){
        //load_dependencies(['ATTC_list_table']); // we do not need to load any class
        // because all the classes has been loaded in the main.php and is available to us
        $this->db = ATTC_database::get_instance();
        $this->importer = new ATTC_import();
        add_action('admin_menu', array($this, 'show_admin_menu'));
        add_action( 'admin_init', array($this, 'process_http_data_requests') ); // this will handle all data saving work
        add_action( 'admin_notices', array($this, 'admin_notices') );
        add_shortcode('attc', array($this, 'register_shortocde'));

    }

    public function register_shortocde($atts, $content = null)
    {
        global $ATTC;
        extract(shortcode_atts(array('id'=>0), $atts));
        $data = $this->db->get($id); // get table data from the database using the table ID.

        // if data does not exist then show the table else print no table found
        if (empty($data)) return 'No Table found';

        $data['config'] = $ATTC->database->get_table_meta($id, 'config');

        //Include theme data if the extension is active
        if ( false != get_option('attc_extension_theme_active', false)) {
            $data['theme'] = $ATTC->database->get_table_meta($id, 'theme');
        }
        $t_type = !empty($data['config']['t_type']) ? $data['config']['t_type'] : '';
        $t_head = !empty($data['config']['t_head']) ? $data['config']['t_head'] : '';

        // enqueue all scripts and style
        wp_enqueue_script('attc-front-main-js');

        wp_enqueue_style('attc-datatable-style');

        wp_enqueue_style('attc-theme-style');
        ob_start();
        if ('Y' == $t_type && 'Y' == $t_head){
            $ATTC->loadView('shortcode/advanced-table', array('table' => $data)); // load short code view
        } else {
            $ATTC->loadView('shortcode/normal-table', array('table' => $data)); // load short code view
        }

        return ob_get_clean();

    }


    public function admin_notices(){
        if( !$this->is_attc_page() ) return; // vail if it is not our plugin page

        $html = '<div class="updated" id="attc_global_notification"><p>%s</p></div>';

        //@TODO: later change it into a switch statement

        if(!empty($_GET['added'])):

            echo sprintf($html, __('The table has been created successfully!', ATTC_TEXTDOMAIN) );

        elseif(!empty($_GET['updated']) ):

            echo sprintf($html, __('The table has been updated with the new changes successfully!', ATTC_TEXTDOMAIN) );

        elseif(!empty($_GET['deleted'])):

            echo sprintf($html, __('The table has been deleted successfully!', ATTC_TEXTDOMAIN) );

        elseif(!empty($_GET['action']) && 'import' == $_GET['action'] && !empty($_GET['status']) && 'success' == $_GET['status']):

            echo sprintf($html, __('The table has been imported successfully!', ATTC_TEXTDOMAIN) );

        endif;
    }


    public function process_http_data_requests() {
//        padded_var_dump($_POST);
        if ( !empty($_GET['table'] ) && !empty( $_GET['action'] ) && ('delete' == $_GET['action']) ) {
            $ID = !empty($_GET['table']) ? absint($_GET['table']) : null;
            if ( $this->db->delete($ID) ) {
                wp_redirect( add_query_arg( array( 'page' => $_GET['page'], 'deleted' => true ), '') );
            }

        }

        // process request to edit/update table
        //$this->update_table();

        // process request to add table info to the database and then redirect to the editing page
        $this->create_table();

        // export a table
        if ( !empty( $_POST['export'] ) && is_array( $_POST['export']) && !empty($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'attc_export_table') ) {
            $this->export_table();
        }

        // import a table
        if ( !empty( $_POST['import'] ) && is_array( $_POST['import'] ) && !empty($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'attc_import_table') ) {
            $this->import_table();
        }
    }



    /**
     * It saves new table's column, row, name, and description, to the database
     */
    public function create_table(){
        if ( $this->is_create_page() && $this->_is_valid_form_data() ){
            // important data is available and valid
            $t = $_POST['table'];
            $name = sanitize_text_field($t['name']);
            $description = sanitize_text_field($t['description']);
            $column = absint($t['column']);
            $row = absint($t['row']);
            $author = wp_get_current_user()->display_name;
            $color = !empty($t['color'])? $t['color'] : '#ddd';
            $responsive = !empty($t['responsive'])? $t['responsive'] : 0;
            $content = !empty($t['content'])? $t['content'] : '';
            // data is ready to be added to the database in this stage
            $inserted_id = $this->db->insert($name, $description, $author, $row, $column, $color, $responsive, $content);

            if( $inserted_id ) {
                wp_redirect( add_query_arg( array( 'page' => $_GET['page'], 'action' => 'edit', 'table' => $inserted_id, 'added' => true ), '' ) );
            }
        }
    }

    /**
     * It checks if the submitted form data is valid for adding a new table
     * @return bool
     */
    private function _is_valid_form_data(){
        $t = (!empty($_POST['table']) && is_array($_POST['table'])) ? $_POST['table'] : array();
        if (!empty($t['name']) && !empty($t['column']) && !empty($t['row']) && !empty($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'attc_create_table')){
            return true;
        }
        return false;
    }

    /*PRIVATE HELPER to CHECK WHICH PAGE USER Requested*/
    private function is_create_page()
    {
        return (!empty($_GET['page']) && 'create-table-page' == $_GET['page'] && !isset($_GET['action'])) ? true : false;
    }
    private function is_home_page()
    {
        return (!empty($_GET['page']) && 'table-generator-all' == $_GET['page'] && !isset($_GET['action'])) ? true : false;
    }
    private function is_edit_page()
    {
        return (!empty($_GET['page']) && 'create-table-page' == $_GET['page'] && isset($_GET['action']) && 'edit' == $_GET['action']) ? true : false;

    }
    private function is_export_page()
    {
        return (!empty($_GET['page']) && 'attc-export' == $_GET['page']) ? true : false;
    }
    private function is_import_page()
    {
        return (!empty($_GET['page']) && 'attc-import' == $_GET['page']) ? true : false;
    }
    private function is_attc_page(){
        return !empty($_GET['page']) ? in_array($_GET['page'], $this->default_pages) : false;
    }


    public function show_admin_menu(){
        add_menu_page(
            __('All Tables', ATTC_TEXTDOMAIN),
            __('Table Generator', ATTC_TEXTDOMAIN),
            'manage_options',
            'table-generator-all',
            array($this, 'render_view'),
            'dashicons-list-view',
            20
        );

        add_submenu_page('table-generator-all',
            __('All Tables', ATTC_TEXTDOMAIN),
            __('All Tables', ATTC_TEXTDOMAIN),
            'manage_options',
            'table-generator-all',
            array($this, 'render_view')
        );

        add_submenu_page('table-generator-all',
            __('Create table', ATTC_TEXTDOMAIN),
            __('Create table', ATTC_TEXTDOMAIN),
            'manage_options',
            'create-table-page',
            array($this, 'render_view')
        );


        add_submenu_page('table-generator-all',
            __('Export', ATTC_TEXTDOMAIN),
            __('Export', ATTC_TEXTDOMAIN),
            'manage_options',
            'attc-export',
            array($this, 'show_export_view')
        );

        add_submenu_page('table-generator-all',
            __('Import', ATTC_TEXTDOMAIN),
            __('Import', ATTC_TEXTDOMAIN),
            'manage_options',
            'attc-import',
            array($this, 'show_import_view')
        );

        add_submenu_page('table-generator-all',
            __('Get Extensions', ATTC_TEXTDOMAIN),
            __('<span style="color: #ffc733;">Extensions</span>', ATTC_TEXTDOMAIN),
            'manage_options',
            'attc-extension',
            array($this, 'show_extension_view')
        );



    }

    public function render_view()
    {
        if ( $this->is_home_page() ) { $this->show_all_tables(); }
        elseif ($this->is_create_page()){ $this->show_create_table_creator();}
        elseif ($this->is_edit_page()){ $this->show_edit_view();}


    }


    /**
     *Show extension  view
     */
    public function show_extension_view()
    {
        global $ATTC;
        $ATTC->loadView('view-extension');
    }
    /**
     *Show import table view
     */
    public function show_import_view()
    {
        global $ATTC;
        $tables = $this->db->get_all();
        $ATTC->loadView('view-import', array('tables'=> $tables));
    }

    /**
     *Show export table view
     */
    public function show_export_view()
    {
        global $ATTC;
        $tables = $this->db->get_all();
        $ATTC->loadView('view-export', array('tables'=> $tables));

    }

    /**
     *Show edit table view
     */
    public function show_edit_view() {
        global $ATTC;
        extract($_GET);
        $data= $ATTC->database->get( isset($table) ? $table : 0 );
        $data['config'] = $ATTC->database->get_table_meta($table, 'config');
        $ATTC->loadView('view-create-table-tab', array('table'=> $data));
    }

    /**
     * Show create table view
     */
    public function show_create_table_creator() {
        global $ATTC;

        $ATTC->loadView('view-create-table');

    }

    /**
     * Show the list of all tables
     */
    public function show_all_tables() {
        global $ATTC;
        $list_table = new ATTC_list_table();
        $list_table->prepare_items();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">All Tables</h1>
            <a href="<?= get_admin_url(); ?>admin.php?page=create-table-page" class="page-title-action">Add New</a>
            <?php $list_table->display(); ?>
        </div>
        <?php
    }

    /**
     * Export selected tables.
     *
     * @since 1.0.0
     */
    public function export_table() {

        if ( empty( $_POST['export'] ) || ! is_array( $_POST['export'] ) ) {
            wp_redirect( add_query_arg( array( 'page' => $_GET['page'], 'action' => 'export', 'status' => 'error'), '') );
        } else {
            $export = wp_unslash( $_POST['export'] );
        }


        $exporter = new ATTC_export();

        if ( empty( $export['tables'] ) ) {
            wp_redirect( add_query_arg( array( 'page' => $_GET['page'], 'action' => 'export', 'status' => 'error'), '') );
        }
        if ( empty( $export['format'] ) || ! isset( $exporter->export_formats[ $export['format'] ] ) ) {
            wp_redirect( add_query_arg( array( 'page' => $_GET['page'], 'action' => 'export', 'status' => 'error'), '') );
        }
        if ( empty( $export['csv_delimiter'] ) ) {
            // Set a value, so that the variable exists.
            $export['csv_delimiter'] = '';
        }
        if ( 'csv' === $export['format'] && ! isset( $exporter->csv_delimiters[ $export['csv_delimiter'] ] ) ) {
            wp_redirect( add_query_arg( array( 'page' => $_GET['page'], 'action' => 'export', 'status' => 'error'), '') );
        }

        // Use list of tables from concatenated field if available
        $tables = ( ! empty( $export['tables_list'] ) ) ? explode( ',', $export['tables_list'] ) : $export['tables']; //  get tbl id(s)

            // Load table, with table data, options, and visibility settings.
            $table = $this->db->load_table_data($tables[0]);  // get data of a table
            $download_filename = sprintf( '%1$s-%2$s-%3$s.%4$s', $table['ID'], $table['name'], date( 'Y-m-d' ), $export['format'] );
            $download_filename = sanitize_file_name( $download_filename );
            // Export the table.
            $export_data = $exporter->export_table( $table, $export['format'], $export['csv_delimiter'] );

            // here we will just get the exported_data but the rest vars are given for the callback that may be attached to this hook
            $export_data = apply_filters( 'table-generator_export_data', $export_data, $table, $export['format'], $export['csv_delimiter'] );
            $download_data = $export_data;


        // Send download headers for export file.
        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/octet-stream' );
        header( "Content-Disposition: attachment; filename=\"{$download_filename}\"" );
        header( 'Content-Transfer-Encoding: binary' );
        header( 'Expires: 0' );
        header( 'Cache-Control: must-revalidate' );
        header( 'Pragma: public' );
        header( 'Content-Length: ' . strlen( $download_data ) );
        @ob_end_clean();
        flush();
        echo $download_data;
        exit;
    }

    /**
     * import_table tables.
     *
     * @since 1.0.0
     */
    public function import_table() {
        if ( empty( $_POST['import'] ) || ! is_array( $_POST['import'] ) ) {
            wp_redirect( add_query_arg( array( 'page' => $_GET['page'], 'action' => 'import', 'status' => 'error'), '') );
        } else {
            $import = wp_unslash( $_POST['import'] );
        }
            $this->_import_attc_table( $import );

    }


    /**
     * Import data from existing source (Upload, URL, Server, Direct input).
     *
     * @since 1.0.0
     *
     * @param array $import Submitted form data.
     */
    protected function _import_attc_table( array $import ) {
        if ( ! isset( $import['type'] ) ) {
            $import['type'] = 'add';
        }
        if ( ! isset( $import['existing_table'] ) ) {
            $import['existing_table'] = '';
        }
        if ( ! isset( $import['source'] ) ) {
            $import['source'] = 'file-upload';
        }


        $import_error = true;
        $unlink_file = false;
        $import_data = array();  //initialize the array to store imported data
        switch ( $import['source'] ) {
            case 'file-upload':
                if ( ! empty( $_FILES['import_file_upload'] ) && UPLOAD_ERR_OK === $_FILES['import_file_upload']['error'] ) {
                    $import_data['file_location'] = $_FILES['import_file_upload']['tmp_name'];
                    $import_data['file_name'] = $_FILES['import_file_upload']['name'];
                    $import_error = false;
                    $unlink_file = true;
                }
                break;
        }

        // if error, delete the file
        if ( $import_error ) {
            if ( $unlink_file ) {
                @unlink( $import_data['file_location'] );
            }
            wp_redirect( add_query_arg( array( 'page' => $_GET['page'], 'action' => 'import', 'status' => 'error', 'message' => 'import source is invalid'), '') );
        }

            if ( ! isset( $import_data['data'] ) ) {
                $import_data['data'] = file_get_contents( $import_data['file_location'] );
            }
            if ( false === $import_data['data'] ) {
                wp_redirect( add_query_arg( array( 'page' => $_GET['page'], 'action' => 'import', 'status' => 'error', 'message' => 'error importing, imported data is corrupted'), '') );
            }

            $description = $name = pathinfo($import_data['file_name'], PATHINFO_FILENAME); // extract the file name from uploaded file and use it as table name and description

            // args can be like eg. csv, 'name, age', 'table file name', 'table file name', false, add
            $table_id = $this->_import_insert_or_replace_table( 'csv', $import_data['data'], $name, $description, false, 'add' );

            if ( $unlink_file ) {
                @unlink( $import_data['file_location'] );
            }

            if ( is_wp_error( $table_id ) ) {
                wp_redirect( add_query_arg( array( 'page' => $_GET['page'], 'action' => 'import', 'status' => 'error', 'message' => 'error_import'), '') );
            } else {
                wp_redirect( add_query_arg( array( 'page' => $_GET['page'], 'action' => 'import', 'status' => 'success'), '') );
            }


    }

    /**
     * Import a table by either replacing an existing table or adding it as a new table.
     *
     * @since 1.0.0
     *
     * @param string      $format            Import format.
     * @param string      $data              Data to import.
     * @param string      $name              Name of the table.
     * @param string      $description       Description of the table.
     * @param bool|string $existing_table_id False if table shall be added new, ID of the table to be replaced or appended to otherwise.
     * @param string      $import_type       What to do with the imported data: "add", "replace", "append".
     * @return string|WP_Error WP_Error on error, table ID on success.
     */
    protected function _import_insert_or_replace_table( $format, $data, $name, $description, $existing_table_id, $import_type ) {
        $table_to_import = $this->importer->import_table( $format, $data ); // it will give an array of data eg. array('content' => array());

        if ( false === $table_to_import ) {
            return new WP_Error( 'table_import_import_failed' );
        }

        if ( false === $existing_table_id ) {
            $import_type = 'add';
        }

        switch ( $import_type ) {
            case 'add':
                $existing_table = $this->db->sample_table_for_import();
                if ( isset( $table_to_import['ID'] ) ) {
                }
                if ( ! isset( $table_to_import['name'] ) ) {
                    $table_to_import['name'] = $name;
                }
                if ( ! isset( $table_to_import['description'] ) ) {
                    $table_to_import['description'] = $description;
                }
                $table_to_import['rows'] = count( $table_to_import['content'] );;
                $table_to_import['cols'] = count( $table_to_import['content'][0] );

                break;
            default:
                return new WP_Error( 'table_import_type_invalid', '', $import_type );
        }

        // Merge new or existing table with information from the imported table.
        $table_to_import['ID'] = $existing_table['ID']; // will be false for new table or the existing table ID

        $num_rows = count( $table_to_import['content'] );
        $num_columns = count( $table_to_import['content'][0] );
        $existing_table['rows'] = $num_rows;
        $existing_table['cols'] = $num_columns;
        $existing_table['author'] = !empty($table_to_import['author'])
            ? $table_to_import['author']
            : get_userdata( get_current_user_id() )->display_name;
        // Check if new content is perfect for saving to the db.
        $table = $this->db->prepare_data_to_insert( $existing_table, $table_to_import); // existing table is the template table, and table_to_import is the uploaded table
        if ( is_wp_error( $table ) ) {
            // Add an error code to the existing WP_Error.
            $table->add( 'table_import_table_prepare', '' );
            return $table;
        }


        // add new table.
        $table_id = $this->db->insert_table_and_meta_data($table);

        if ( is_wp_error( $table_id ) ) {
            // Add an error code to the existing WP_Error.
            $table_id->add( 'table_import_table_save_or_add', '' );
            return $table_id;
        }

        return $table_id; // success, so return table id
    }




}

endif;
