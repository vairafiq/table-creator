<?php
// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ATTC_list_table extends WP_List_Table {

    private $db;

    public function __construct(){

        load_dependencies(['ATTC_database']);

        $this->db = ATTC_database::get_instance();

        global $status, $page;

        parent::__construct( array(
            'singular'  => 'table',
            'plural'    => 'tables',
            'ajax'      => false,
//            'screen' => $_REQUEST['page'],
            'screen' => get_current_screen(),
            
        ) );
    }


    function get_columns(){
        // add fields name that will be displayed on the screen, if the data column is available in the database
        // then those data will be displayed automatically, else add custom data for custom column  in $this->column_default
        $columns = array(
            'cb'	=> '<input type="checkbox" />',
            'ID'	=> __('ID', ATTC_TEXTDOMAIN),
            'name'	=> __('Name', ATTC_TEXTDOMAIN),
            'description'	=> __('Description', ATTC_TEXTDOMAIN),
            'author'	=> __('Author', ATTC_TEXTDOMAIN),
            'shortcode'	=> __('Shortcode',ATTC_TEXTDOMAIN),
            'date' => __('Created at',ATTC_TEXTDOMAIN)
        );
        return $columns;
    }

    function column_default($item, $column_name){
        $item['shortcode'] = "[attc id={$item['ID']}]"; // add data for short code columns
        $item['date'] = beautiful_datetime($item['date'], 'mysql', '<br/>');


        return stripslashes($item[$column_name]);
    }

    function column_name($item){
        //Build row actions
        $actions = array(
            'edit' => sprintf('<a href="?page=%s&action=%s&table=%s">%s</a>', 'create-table-page','edit',$item['ID'], __('Edit', ATTC_TEXTDOMAIN) ),
            'delete' => sprintf('<a href="?page=%s&action=%s&table=%s">%s</a>', $_REQUEST['page'],'delete',$item['ID'],__('Delete', ATTC_TEXTDOMAIN) )
        );

        //Return the title contents
        return sprintf('%1$s %2$s',
            /*$1%s*/ stripslashes($item['name']),
            /*$2%s*/ $this->row_actions($actions)
        );
    }

    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],
            /*$2%s*/ $item['ID']
        );
    }

    function get_bulk_actions() {
        $actions = array(
            'delete'    => __('Delete', ATTC_TEXTDOMAIN)
        );
        return $actions;
    }

    function prepare_items() {
        $per_page		= 20;
        $hidden			= array();
        $columns		= $this->get_columns();
        $sortable		= $this->get_sortable_columns();
        $curr_page		= $this->get_pagenum();
        $total_items	= $this->db->get_count();
        $data           = $this->db->get_page_items($curr_page, $per_page);

        usort( $data, array( &$this, 'sort_data' ) );
        $this->items	= $data;
        $this->_column_headers  = array($columns, $hidden, $sortable);
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );
    }

    public function get_sortable_columns()
    {
        return array(
            'ID'=> array('ID', true),
            'name' => array('name', false),
            'description' => array('description', false),
            'author' => array('author', false),
            'responsive'=> array('responsive', true),
            'date'=> array('date', true),
        );
    }
    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'ID';
        $order = 'desc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }


        $result = strnatcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }

    /**
     * It lets us make a column linkable. Parent's single_row_columns method has been overridden.
     * @param object $item
     */
    function single_row_columns($item) {
        list($columns, $hidden) = $this->get_column_info();
        foreach ($columns as $column_name => $column_display_name) {
            $class = "class='$column_name column-$column_name'";

            $style = '';
            if (in_array($column_name, $hidden))
                $style = ' style="display:none;"';

            $attributes = "$class$style";

            if ('cb' == $column_name) {
                echo  "<td $attributes>";
                echo sprintf('<input type="checkbox" name="id[]" value="%s" />', $item['ID']);
                echo "</td>";
            }
            elseif ('name' == $column_name) {
                echo "<td $attributes>";
                echo sprintf('<strong><a href="?page=%s&action=%s&table=%s">'. $item['name'].'</a>','create-table-page','edit',$item['ID']);
                echo "</a></strong>";

                echo "<div class='row-actions'><span class='edit'>";
                echo sprintf('<a href="?page=%s&action=%s&table=%s">%s</a>','create-table-page','edit',$item['ID'], esc_html__('Edit', ATTC_TEXTDOMAIN));
                echo "</span> | <span class='trash'>";
                echo sprintf('<a class="confirmation" href="?page=%s&action=%s&table=%s&_wpnonce=%s">%s</a>',$_REQUEST['page'],'delete_attc_table',$item['ID'], wp_create_nonce('delete_attc_table'), esc_html__('Delete', ATTC_TEXTDOMAIN));
                echo "</span></div></td>";
            }
            else {
                echo "<td $attributes>";
                echo $this->column_default( $item, $column_name );
                echo "</td>";
            } } }

    function show( ) {
        ?>
        <form method="GET">
            <input type="hidden" name="page" value="<?= $_GET['page'] ?>">
            <?php
                $this->prepare_items();
                $this->display();
            ?>
        </form>
        <?php
    }

    
    
}



