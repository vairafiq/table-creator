<?php
defined('ABSPATH') || die('You should not access this file directly.');

if (!class_exists('ATTC_database')):

    class ATTC_database{
        /**
         * @var ATTC_database The one true ATTC_database
         * @since 1.0
         */
        private static $instance;
        /** 
         * alias of the Global $wpdb
         * @var wpdb
         */
        private $db;

        /**
         * Default database charset of the global $wpdb
         * @var string
         */
        private $db_charset;

        /**
         * Present version of the current database of the table maker.
         * @var string
         */
        public $db_version;

        /**
         * Name of the table of the Table Generator that will hold the plugin data
         * @var string
         */
        public $table_name;

        /**
         * Name of the table of the Table Generator that will hold the plugin data
         * @var string
         */
        public $meta_table;

        /**
         * ATTC_database constructor.
         */
        function __construct(){
            global $wpdb;
            $this->db = $wpdb;
            $this->table_name = $wpdb->prefix."at_table_creator";
            $this->meta_table = $wpdb->prefix."at_meta";
            $this->db_charset = $wpdb->get_charset_collate();
            $this->db_version = "1.0";
        }

        /**
         * Singleton, return the instance of this class
         * @return ATTC_database|null
         */
        public static function get_instance(){
            if( ! isset( self::$instance ) && ! ( self::$instance instanceof ATTC_database ) ){
                self::$instance = new ATTC_database;
            }
            return self::$instance;
        }

        /**
         * It creates custom table in the database
         */
        public function create_table(){
            $current_version = get_option('attc_db_version');
//            $current_version = 5; // for test purpose only
            // vail out if the current version of the plugin and the version in the database is the same
            // and the table already exists in the database.
            if(version_compare($current_version, $this->db_version, '=') && $this->db->get_var("SHOW TABLES LIKE '$this->table_name'") == $this->table_name){
                return;
            }
    
            $table1 = "CREATE TABLE IF NOT EXISTS $this->table_name(
                    ID INT(11) unsigned NOT NULL AUTO_INCREMENT,
                    name TINYTEXT COLLATE utf8mb4_unicode_ci NOT NULL,
                    description TINYTEXT COLLATE utf8mb4_unicode_ci NOT NULL,
                    author TINYTEXT COLLATE utf8mb4_unicode_ci NOT NULL,
                    rows INT(11) COLLATE utf8mb4_unicode_ci NOT NULL,
                    cols INT(11) COLLATE utf8mb4_unicode_ci NOT NULL,
                    color TINYTEXT COLLATE utf8mb4_unicode_ci NOT NULL,
                    responsive tinyint(1) NOT NULL DEFAULT '0',
                    content LONGTEXT COLLATE utf8mb4_unicode_ci NOT NULL,
                    date timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY  (ID)
                ) ENGINE=InnoDB $this->db_charset;";

            $table2 = "CREATE TABLE IF NOT EXISTS $this->meta_table(
                    ID INT(11) unsigned NOT NULL AUTO_INCREMENT,
                    table_id INT(11) unsigned NOT NULL,
                    meta_key VARCHAR(255) NOT NULL,
                    meta_value LONGTEXT NOT NULL,
                    PRIMARY KEY  (ID)
                ) ENGINE=InnoDB $this->db_charset;";
//
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
//
            dbDelta( $table1 );
            dbDelta( $table2 );

            // update the database version in the option table with the current database version of the plugin.
            update_option( 'attc_db_version', $this->db_version );
        }

        /**
         * Insert a new record to the $this->table_name
         * @param $name
         * @param $desc
         * @param $author
         * @param $rows
         * @param $cols
         * @param $color
         * @param $responsive
         * @param $content
         * @return bool|int
         */
        public function insert($name, $desc, $author, $rows, $cols, $color, $responsive, $content) {
            $name 	            = wp_strip_all_tags(wp_unslash($name));
            $desc 	            = wp_strip_all_tags(wp_unslash($desc));
            $author 	        = wp_strip_all_tags(wp_unslash($author));
            $rows 		        = intval(wp_unslash($rows));
            $cols 		        = intval(wp_unslash($cols));
            $color 		        = strval(wp_unslash($color));
            $responsive 		= intval(wp_unslash($responsive));
            $content 	        = $this->serialize(wp_unslash($content));
    
            $result = $this->db->insert( $this->table_name, array('name' => $name, 'description'=>$desc, 'author'=> $author, 'rows' => $rows, 'cols' => $cols, 'color' => $color, 'responsive' => $responsive, 'content' => $content ) );
            if($result)
                return $this->db->insert_id;
            return false;
        }

        /**
         * Update a record in the $this->table_name
         * @param $ID
         * @param $name
         * @param $desc
         * @param $author
         * @param $rows
         * @param $cols
         * @param $color
         * @param $responsive
         * @param $content
         * @return false|int It returns the number of row affected and false on failure
         */
        public function update($ID, $name, $desc, $author, $rows, $cols, $color, $responsive, $content){
            
            $name 	            = wp_strip_all_tags(wp_unslash($name));
            $desc 	            = wp_strip_all_tags(wp_unslash($desc));
            $author 	        = wp_strip_all_tags(wp_unslash($author));
            $rows 		        = intval(wp_unslash($rows));
            $cols 		        = intval(wp_unslash($cols));
            $color 		        = strval(wp_unslash($color));
            $responsive 		= intval(wp_unslash($responsive));
            $content 	        = $this->serialize(wp_unslash($content));
    
            return $this->db->update( $this->table_name, array('name' => $name,'description' => $desc, 'author'=>$author, 'rows' => $rows, 'cols' => $cols, 'color' => $color, 'responsive' => $responsive, 'content' => $content ), array( 'ID' => $ID ) );
        
        }

        /**
         * Drop the $this->table_name from the database
         * @return false|int
         */
        public function drop_table() {
            $table1 = "DROP TABLE IF EXISTS $this->table_name;";
            $table2 = "DROP TABLE IF EXISTS $this->meta_table;";
            $result1 = $this->db->query($table1);
            $result2 = $this->db->query($table2);
            return (!empty($result1) && !empty($result2)) ? true : false;

        }

        /** 
         * Delete a record from $this->table_name by the given $ID
         * @param int $ID The ID of the row of $this->table_name 
         * @return false|int
         */
        public function delete($ID){
            if(is_array($ID))
                $ID = sprintf('%s', implode(',', $ID));
            else {
                $ID = sprintf('%d', $ID);
            }
            $sql = $this->db->prepare("DELETE FROM $this->table_name WHERE ID = %d", $ID);
            return $this->db->query($sql);
        }

        /**
         * Get a record from the $this->table_name by  the given $ID
         * @param int $ID the ID of the row of the $this->table_name
         * @return array|null|object
         */
        public function get($ID){
            if( is_array($ID) ){
                $ID = sprintf('(%s)', implode(',', $ID));
            }
            else {
                $ID = sprintf('%d', $ID);
            }
            $row = $this->db->get_row("SELECT * FROM $this->table_name WHERE ID = $ID", ARRAY_A);
            if($row){
                $row['content'] = $this->unserialize($row['content']);
            }
            return $row;
        }

        /**
         * Get all records from the $this->table_name
         * @return array|null|object
         */
        public function get_all(){
            $tables = $this->db->get_results("SELECT * FROM $this->table_name", ARRAY_A);
            // if the table is an array then unserialize the content of the each tables in the array.
            $unserialized_tables = array();
            if(is_array($tables)){
                foreach ($tables as $table) {
                    if (is_array($table)){
                        $new_single_array = array();
                        foreach ($table as $key => $value) {
                            $new_single_array[$key] = ('content' == $key) ? $this->unserialize($value) : $value;
                        }
                        $unserialized_tables[] = $new_single_array;
                    }
                }
            }
            return $unserialized_tables;

        }

        /**
         * Get limited items from the $this->table_name for per page view. 
         * eg. 1, 5 as argument will get records from 1 to 5 records
         * @param int $curr_page the number where the limit will start from
         * @param int $per_page the number where the limit will stop at
         * @param string $output Optional. Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants. Specify the way you would like to return the queried data.
         * @return array|null|object Returns records limited by $curr_page and $per_page from $this->table_name in an associative array
         */
        public function get_page_items($curr_page, $per_page, $output = ARRAY_A){
            
            $start = (($curr_page-1)*$per_page);
            
            $query = "SELECT * FROM $this->table_name ORDER BY ID DESC LIMIT $start, $per_page";
            
            return $this->db->get_results( $query, $output );
            
        }

        /**
         * Return the number of total records in the $this->table_name
         * @return int|null|string
         */
        public function get_count(){
            $count = $this->db->get_var("SELECT COUNT(*) FROM $this->table_name");
            return isset($count) ? $count : 0;
        }


        /**
         * It fetch a single result from the table meta table.
         * @param integer $table_id The ID of the table
         * @param string $key Name of the key that should be fetched from the table meta
         * @param bool $single Whether to return a single row/value or multiple row/value
         * @return array|bool|null|object
         */
        public function get_table_meta($table_id, $key, $single = false)
        {
            $method = ($single) ? 'get_row' : 'get_results'; // set method name dynamically for whether to get single row or multiple row.
           $result = $this->db->$method($this->db->prepare("SELECT meta_value FROM {$this->meta_table} WHERE table_id = %d AND meta_key = %s", $table_id, $key), ARRAY_A);
            // if we have more than one result in the array then return the array after applying array map to unserialize each value of the array
//            return $result;
            if (!$single){
                if (count($result) > 1) {
                    return array_map(function($item){
                        return array_map('maybe_unserialize', $item);
                    }, $result);
                }

                // return the single value
                if(!empty($result)) return maybe_unserialize($result[0]['meta_value']);
                return false;
            }

            // return the single value
            if(!empty($result)) return maybe_unserialize($result['meta_value']);
            return false;

        }

        /**
         * It sets a single result to the table meta table.
         * @param integer $table_id The ID of the table
         * @param string $key Name of the meta key 
         * @param mixed $value value of the meta key that should be stored to the data base. NO need TO serialized
         * @return array|bool|null|object It returns number of meta data inserted
         */
        public function set_table_meta($table_id, $key, $value)
        {
            $insert_id = $this->db->insert($this->meta_table, array('table_id' => $table_id, 'meta_key'=> $key, 'meta_value' => maybe_serialize($value)));
            return !empty($insert_id) ? $insert_id : false ;

        }


        /**
     * It update a single result to the table meta table.
     * @param integer $table_id The ID of the table
     * @param string $key Name of the meta key
     * @param mixed $value value of the meta key that should be stored to the data base. NO need TO serialized
     * @return array|bool|null|object
     */
        public function update_table_meta($table_id, $key, $value)
        {
            $value_exist = $this->get_table_meta($table_id, $key);
            if(!empty($value_exist)){
                // value exist, lets update it
                return $this->db->update(
                    $this->meta_table,
                    array('table_id' => $table_id, 'meta_key'=> $key, 'meta_value' => maybe_serialize($value)),
                    array('table_id' => $table_id, 'meta_key' => $key));

            }else{
                // value does not exist in the db, let's create a record.
                $insert_id = $this->set_table_meta($table_id, $key, $value);
                return !empty($insert_id) ? $insert_id : false ;
            }
        }



        /**
         * It delete a single result to the table meta table.
         * @param integer $table_id The ID of the table
         * @param string $key Name of the meta key
         * @return bool
         */
        public function delete_table_meta($table_id, $key='config')
        {
            $where = (!empty($key)) ? array('table_id' => $table_id, 'meta_key'=> $key) :  array('table_id' => $table_id);
            return $this->db->delete( $this->meta_table, $where);

        }
        
        

        /**
         * @param $item
         * @return string
         */
        private function serialize($item){
            return base64_encode(serialize($item));
        }

        /**
         * @param $item
         * @return mixed
         */
        private function unserialize($item){
            return unserialize(base64_decode($item));
        }


        /**
         * It loads table data with table meta data that is suitable for exporting
         * @param int $ID The ID of the Table to retrieve the data
         * @param bool $load_meta_data Loads table meta data if it is set to true. Default, is true
         * @return array|null|object
         */
        public function load_table_data($ID, $load_meta_data = true){
            if( is_array($ID) ){
                $ID = sprintf('(%s)', implode(',', $ID));
            }
            else {
                $ID = sprintf('%d', $ID);
            }
            $row = $this->db->get_row("SELECT * FROM $this->table_name WHERE ID = $ID", ARRAY_A);
            if($row){
                $row['content'] = $this->unserialize($row['content']);
            }

            if($load_meta_data){
                $row['options'] = $this->get_table_meta($ID, 'config', true);
            }
            return $row;
        }

        /**
         * @return mixed | array
         */
        public function sample_table_for_import()
        {
            $table = array(
                'ID' => false,
                'name' => '',
                'description' => '',
                'author' => get_userdata(get_current_user_id())->display_name,
                'rows' => 0,
                'cols' => 0,
                'color' => '',
                'responsive' => '',
                'content' => array( array( '' ) ), // one empty cell
                'date' => current_time( 'mysql' ),
                'options' => array(
                      't_type' =>  'Y',
                      't_head' =>  'Y',
                      'hover' =>  'Y',
                      'scroll_x' =>  'Y',
                      'width' =>  '',
                      'scroll_y' =>  'N',
                      'height' =>  '',
                      'search' =>  'Y',
                      'pagination' =>  'Y',
                      'entry_list' =>  'Y',
                      'sorting' =>  'Y',
                      'info' =>  'Y',
                      'show_tbl_name' =>  'N',
                      'show_tbl_desc' =>  'N',
                )

            );
            /**
             * Filter the default template/structure of an empty table.
             *
             * @since 1.0.0
             *
             * @param array $table Default template/structure of an empty table.
             */
            return apply_filters( 'attc_table_template', $table );
        }


        /**
         * It prepares the table data to be inserted as a new table or a replacement or merge an existing table
         *
         * @param array $old_table
         * @param array $new_table
         * @return array | mixed it returns an array of table data and meta data
         */
        public function prepare_data_to_insert(array $old_table, array $new_table)
        {

            // Table ID must be the same (if there was an ID already). one note, for new table the ID will be false as the template is returned by sample_table_for_import
            if ( false !== $old_table['ID'] ) {
                if ( $old_table['ID'] !== $new_table['ID'] ) {
                    return new WP_Error( 'prepare_data_to_insert_no_id_match', '', $new_table['ID'] );
                }
            }

            // Name, description, and data array need to exist, data must not be empty, the others could be ''.
            if ( ! isset( $new_table['name'] )
                || ! isset( $new_table['description'] )
                || empty( $new_table['content'] )
                || empty( $new_table['content'][0] ) ) {
                return new WP_Error( 'prepare_data_to_insert_name_description_or_content_not_set' );
            }


            // All checks were successful, replace original values with new ones.
            $prepared_table = array_merge($old_table, $new_table);
            return $prepared_table;
        }


        /**
         * It insert table data and meta data to the appropriate tables
         * @param array $table The Table with full data and options
         * @return int|bool Returns number of row inserted and false on failure
         */
        public function insert_table_and_meta_data( array $table )
        {
            $config = array_pop($table); // remove and return the last options array
            $table['content'] = $this->serialize($table['content']);
            $row_inserted = $this->db->insert($this->table_name, $table);
            if ($this->db->insert_id) {
                $this->set_table_meta($this->db->insert_id, 'config', $config);
                return $this->db->insert_id;
            }
            return $row_inserted;
        }

        public function update_table_and_meta_data()
        {
            // it should replace or append imported data to an existing table. it is a premium extension features
        }
        
        
        
        
    } // ends ATTC_database;

endif;


