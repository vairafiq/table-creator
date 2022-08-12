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
            $name = !empty($t_opt['table_name']) ? sanitize_text_field($t_opt['table_name']) : esc_html__('No Name', ATTC_TEXTDOMAIN);
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