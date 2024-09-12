<?php
// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if(!class_exists('ATTC_ajax_handler')):

/**
 * Class ATTC_ajax_handler.
 * It handles all ajax requests 
 * */
class ATTC_ajax_handler {

    /**
     * Register  hooks for  ajax actions.
     */
    public function __construct(){
        $this->db = ATTC_database::get_instance();
        add_action('wp_ajax_attc_setting_handler', array($this, 'attc_setting_handler'));
        add_action('wp_ajax_delete_attc_table', array($this, 'delete_attc_table'));
        add_action('wp_ajax_update_tablegen_data', array($this, 'update_table'));

        add_action('wp_ajax_tablegen_imort_from_google', array($this, 'import_from_google'));
        add_action('wp_ajax_tablegen_ai_table', array($this, 'tablegen_ai_table'));
        add_action('wp_ajax_create_new_page_with_data', array($this, 'ajax_create_new_page_with_data'));
    }

    // create new page with table data
    public function ajax_create_new_page_with_data() {
    
        // Get the data from the AJAX request
        $title = sanitize_text_field($_POST['title']);
        $content = wp_kses_post($_POST['content']);
    
        // Create a new page
        $new_page_id = wp_insert_post(array(
            'post_title'    => $title,
            'post_content'  => $content,
            'post_status'   => 'draft',  // also it can be 'publish'
            'post_type'     => 'page',
        ));
    
        // Check if the page was created successfully
        if (!is_wp_error($new_page_id)) {
            wp_send_json_success(array('redirect_url' => get_edit_post_link($new_page_id, '')));
        } else {
            wp_send_json_error(array('message' => 'Page creation failed.'));
        }
    
    }
    

    


    // create table (generate data with ai)
    public function tablegen_ai_table() {

        if( ! current_user_can( 'manage_options' ) ) {
            wp_send_json([
                'error' => true,
                'msg' => __( 'You are not allowed to import', 'tablegen-google-sheet-integration' ),
            ]);
        }
        
        $prompt          = ! empty( $_POST['prompt'] ) ? sanitize_text_field( wp_unslash( $_POST['prompt'] ) ) : '';
        $name            = 'AI Table';
        $description     = 'AI table description';

        //$command = "I am creating a table " . $prompt . ". Use appropriate HTML tags to show a pretty format. Don't add anything like 'Here's a possible opening statement' just give me the final output.";
        $command = "I am creating a table " . $prompt . ". Use appropriate HTML tags(use table formate including table borders and all text should align left including heading names) to show a pretty format. Don't add anything like 'Here's a possible opening statement' just give me the final output.";


        $response = tablegen_get_response_from_groq( $command );

        wp_send_json($response);
        
        if( empty( $prompt ) ) {
            wp_send_json([
                'error' => true,
                'msg' => __( 'Please fill up the required field', 'tablegen-google-sheet-integration' ),
            ]);
        }

        $data = [];
        $controller = new ATTC_controller();

        $import = $controller->_import_insert_or_replace_table( 'json', $data, $name, $description, '', 'add');


        if( is_wp_error( $import ) ) {
            wp_send_json([
                'error' => true,
                'msg' => __( 'Error importing data', 'tablegen-google-sheet-integration' ),
            ]);
        }

        wp_send_json([
            'msg' => __( 'Successfully imported to a new table', 'tablegen-google-sheet-integration' ),
        ]);

    }


 

    public function import_from_google() {
       
        if( ! tablegen_verify_nonce() ) {
            wp_send_json([
                'error' => true,
                'msg' => __( 'Invalid nonce!', 'tablegen-google-sheet-integration' ),
            ]);
        }

        if( ! current_user_can( 'manage_options' ) ) {
            wp_send_json([
                'error' => true,
                'msg' => __( 'You are not allowed to import', 'tablegen-google-sheet-integration' ),
            ]);
        }
        

        $sheet_key          = ! empty( $_POST['sheet_key'] ) ? sanitize_text_field( wp_unslash( $_POST['sheet_key'] ) ) : '';
        $sheet_id           = ! empty( $_POST['sheet_id'] ) ? sanitize_text_field( wp_unslash( $_POST['sheet_id'] ) ) : '';
        $name               = ! empty( $_POST['table_name'] ) ? sanitize_text_field( wp_unslash( $_POST['table_name'] ) ) : '';
        $description        = ! empty( $_POST['table_description'] ) ? sanitize_text_field( wp_unslash( $_POST['table_description'] ) ) : '';
        
        if( empty( $sheet_key ) || empty( $name ) ) {
            wp_send_json([
                'error' => true,
                'msg' => __( 'Please fill up the required field', 'tablegen-google-sheet-integration' ),
            ]);
        }

        $url = "https://docs.google.com/spreadsheets/d/$sheet_key/export?format=csv";
        if( ! empty( $sheet_id ) ) {
            $url = "https://docs.google.com/spreadsheets/d/$sheet_key/export?gid=$sheet_id&format=csv";
        }

        $data = file_get_contents( $url );
        $controller = new ATTC_controller();

        $import = $controller->_import_insert_or_replace_table( 'csv', $data, $name, $description, '', 'add');


        if( is_wp_error( $import ) ) {
            wp_send_json([
                'error' => true,
                'msg' => __( 'Error importing data', 'tablegen-google-sheet-integration' ),
            ]);
        }

        wp_send_json([
            'msg' => __( 'Successfully imported to a new table', 'tablegen-google-sheet-integration' ),
        ]);

    }
 

    public function attc_setting_handler()
    {
        if (!empty($_POST['config']) && $this->_is_valid_nonce('_wpnonce', 'attc_update_table' )){
            // prepare all data from the post array
            $table_id = !empty($_POST['id']) ? intval($_POST['id']) : null;
            $data = !empty($_POST['config']) ? $_POST['config'] : array();
            $updated = $this->db->update_table_meta($table_id, 'config', $data);
            // lets echo success as at this point even 0 means no error
            echo 'success';
            wp_die();
        }
        echo 'error';

        wp_die();
    }

    public function delete_attc_table()
    {

        if( ! tablegen_verify_nonce() ) {
            wp_send_json([
                'error' => true,
                'msg' => __( 'Invalid nonce!', 'tablegen-google-sheet-integration' ),
            ]);
        }

        if( ! current_user_can( 'manage_options' ) ) {
            wp_send_json([
                'error' => true,
                'msg' => __( 'You are not allowed to import', 'tablegen-google-sheet-integration' ),
            ]);
        }

        $ID = !empty($_POST['table_id']) ? absint($_POST['table_id']) : 0;

        if( empty( $ID ) ) {
            wp_send_json([
                'error' => true,
                'msg' => __( 'Table is missing', 'tablegen-google-sheet-integration' ),
            ]);
        }

        // we have passed the security, now we can delete the table row safely.
        $result = $this->db->delete($ID); // delete table
        
        if( is_wp_error( $result ) ) {
            wp_send_json([
                'error' => true,
                'msg' => __( 'Error deleting table', 'tablegen-google-sheet-integration' ),
            ]);
        }
        
        $this->db->delete_table_meta($ID); // delete meta table if available

        wp_send_json([
            'msg' => __( 'Table deleted successfully', 'tablegen-google-sheet-integration' ),

        ]);

    }

    public function update_table() {

        // Lets check if we have valid data and the form came from our site before further processing.
        if (!empty($_POST['attc_table_data']) && $this->_is_valid_nonce('_wpnonce', 'attc_update_table' )) {

            // we have the table data and our form came from our site. lets proceed, shall we?
            /*padded_var_dump('after the check');
            padded_var_dump($_POST);*/

            $t = !empty($_POST['attc_table_data']) ? $_POST['attc_table_data'] : array(); // cache all tbl data
            $t_opt = !empty($t['table_options']) ? $t['table_options'] : array(); // get all the tbl option
            $ID = !empty($t['table_id']) ? intval($t['table_id']) : null;
            $name = !empty($t_opt['table_name']) ? sanitize_text_field($t_opt['table_name']) : esc_html__('No Name', 'tablegen');
            $description = !empty($t_opt['table_description']) ? sanitize_textarea_field($t_opt['table_description']) : '';
            $column = !empty($t_opt['current_cols']) ? absint($t_opt['current_cols']) : 0;
            $rows = !empty($t_opt['current_rows']) ? absint($t_opt['current_rows']) : 0;
            $content = !empty($t['table_data'] )? (array) json_decode(wp_unslash( $t['table_data'] ), true): array();
            $this->db->update( $ID, $name, $description, wp_get_current_user()->display_name, $rows, $column, '', 0, $content );
                // the table has been updated successfully, here even $success = 0 means it succeeded now send 'success' message to the js and then stop the script.
                echo 'success';
                wp_die();
                wp_redirect( add_query_arg( array( 'page' => $_GET['page'], 'action' => 'edit', 'table' => $ID, 'updated' => true ), '') );

        }
        // the table could not be updated. So, send 'error' to the js code and stop the script.
        echo 'error';
        wp_die();
    }

    private function _is_valid_nonce($nonceName = '_wpnonce', $action_name = -1)
    {
        return (!empty($_REQUEST[$nonceName]) && wp_verify_nonce($_REQUEST[$nonceName], $action_name));
    }



}


endif;