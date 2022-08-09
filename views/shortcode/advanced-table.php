<?php
$table = !empty($args['table']) ? $args['table'] : array(); // get table data from the passed $args var
is_array($args['table']['config']) ? extract($args['table']['config']): false; // Lets EXTRACT the configuration of the table.



/*Vars for Rendering Table Starts*/

$table_id 		    = (!empty($table['ID'])) ? $table['ID'] : '';
$ID = 'ID_' . rand(1, 100). $table_id;
$name 			    = (!empty($table['name'])) ? $table['name'] : '';
$description 		= (!empty($table['description'])) ? $table['description'] : '';
$rows 				= (!empty($table['rows'])) ? $table['rows'] : 0;
$cols 				= (!empty($table['cols'])) ? $table['cols'] : 0;
$color				= (!empty($table['color'] )) ? $table['color'] : 'default';
$responsive	        = (!empty($table['responsive'])) ? $table['responsive'] : '';
$saved_content 	    = (!empty($table['content'])) ? $table['content'] : '';

/*Vars for Rendering Table Ends*/

/*Vars For JS customization Starts*/
$t_type = (!empty($t_type)) ? $t_type : '';
$t_head = (!empty($t_head)) ? $t_head : '';
$hover = (!empty($hover)) ? $hover : '';
$scroll_x = (!empty($scroll_x)) ? $scroll_x : '';
$scroll_y = (!empty($scroll_y)) ? $scroll_y : '';
$width = (!empty($width)) ? $width : '';
$height = (!empty($height)) ? $height : '';
$search = (!empty($search)) ? $search : '';
$pagination = (!empty($pagination)) ? $pagination : '';
$entry_list = (!empty($entry_list)) ? $entry_list : '';
$sorting = (!empty($sorting)) ? $sorting : '';
$info = (!empty($info)) ? $info : '';
$show_tbl_name = (!empty($show_tbl_name)) ? $show_tbl_name : '';
$show_tbl_desc = (!empty($show_tbl_desc)) ? $show_tbl_desc : '';
/*Vars for JS customization ends*/



/*CODE FOR THEME EXTENSION ONLY*/
if (!empty($args['table']['theme']['name'])  &&  false != get_option('attc_extension_theme_active', false) ){
    $theme = $args['table']['theme'];
    $theme_name = $theme['name'];
    $theme_class = ($theme_name != 'default') ? 'theme '. $theme_name : ''; // only add 'theme theme_name' if not default
    // extract the variable for the selected/current theme
    extract($theme['settings'][$theme_name]);
    // include the selected theme's css from Template directory of The extension.
    include TCE_THEMES_TEMPLATES_DIR . $theme_name . '.php';
}else {
    wp_enqueue_style('attc-public-style');// enqueue the default style from the main plugin if extension is active but the setting has not been saved.
}
?>

<style>
#<?= $ID ?>_wrapper {
        width: <?= !empty($width) ? esc_attr(trim($width)) : '100%'; ?>;
        margin: 0 auto;
        <?=  ('Y' != $show_tbl_name && 'Y' != $show_tbl_desc) ?  "padding-top:30px" : ''; ?>
    }
    #<?= $ID ?>_attc_wrap caption {
        caption-side: bottom;
        margin-top: 20px;
    }
</style>
<div class="container-fluid default <?= (!empty($theme_class))? $theme_class: '' ?> " id="<?= $ID ?>_attc_wrap">
    <div class="attc_tablewrapper">
        <?php
        // show table info if enable
        if ('Y' == $show_tbl_name || 'Y' == $show_tbl_desc) {
            $html = "<div id='{$ID}_table_info_wrap' class='attc_table_heading'>";
                if ('Y'== $show_tbl_name && !empty($name)) {$html .= "<h3 class='table_title'>{$name}</h3>";}
                if ('Y'== $show_tbl_desc && !empty($description)) {$html .= "<p class='table_desc'>{$description}</p>";}
            $html .= "</div>";
            echo $html;
        }
        ?>
            <!-- Table for inserting data starts form here. -->
        <table class="table attc_front_table" id="<?= $ID ?>" cellspacing="0" width="100%">
                    <?php
                    // show a edit table link if the user is logged in and user can edit_post.
                    if (is_user_logged_in() && user_can(get_current_user_id(), 'manage_options')) {
                        echo sprintf('<caption style=""><a class="edit_btn" href="%sadmin.php?page=%s&action=%s&table=%s">%s</a></caption>', get_admin_url(),'create-table-page','edit',$table_id, __('Edit Table', ATTC_TEXTDOMAIN) );
                    }
                    ?>
            <!--         print column header-->
            <?php if ('Y' == $t_head) { ?>
                <thead class="attc-thead">
                <!--Header column ends here-->
                <tr><?php for ($j=0; $j < $cols; $j++) { ?>
                        <th><?php echo isset($saved_content[0][$j]) ? esc_attr(trim($saved_content[0][$j])) : ''; ?></th>
                    <?php } ?>
                </tr>
                <!--Header column ends here-->

                </thead>
            <?php } ?>
            <tbody class="attc-tbody">

            <?php
            // skip the first row from printing in the tbody if it is already used in the thead by increasing the iteration count by 1.
            $i = ('Y' == $t_head) ? 1 : 0;
            for ($i; $i < $rows; $i++) { // Primary loop ($i loop) starts here. It will print one row per iteration ?>

                <?= '<tr>' ?>

                <?php for ($j=0; $j < $cols; $j++){  // 1ST nested loop ($j loop)  starts. It will create all <td>(s) ?>

                    <?= '<td>' ?>

                    <?=  isset($saved_content[$i][$j]) ? do_shortcode(trim($saved_content[$i][$j])) : ''; ?>

                    <?= '</td>' ?>

                <?php } // ends 1ST nested ($j loop ) loop ?>

                <?= '</tr>' ?>
            <?php } // Ends the Primary loop eg. $i loop?>
            </tbody>
        </table>
    </div>
</div>
<!--Customization of Data Table -->


<style>
    #<?= $ID ?>_attc_wrap table.dataTable,
    #<?= $ID ?>_attc_wrap table.dataTable th,
    #<?= $ID ?>_attc_wrap table.dataTable td {
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
    }
</style>

<!--purple theme -->


<script>
    (function ($) {

        function showCustomNav(){
            if($('.navy_blue').length || $('.green').length || $('.gradient').length || $('.gradient_v2').length){
                return {
                    "paginate" : {
                        "previous": "<span class='glyphicon glyphicon-menu-left'></span>",
                        "next": "<span class='glyphicon glyphicon-menu-right'></span>"
                    },
                    search: "_INPUT_",
                    searchPlaceholder: "Search records"
                }
            }else{
                return {
                    search: "_INPUT_",
                    searchPlaceholder: "Search records"
                }
            }
        }
//        Initialize the data table with the dynamic settings

        
        $('#<?= $ID ?>').DataTable({

            <?php if('Y' == $scroll_x) { ?>
            "scrollX": true,
            <?php } ?>
        <?php if('Y' == $scroll_y) { ?>
            "scrollY": <?= !empty($height)? absint(trim($height)) : '300'; ?>,
            <?php } ?>
        <?php if('N' == $entry_list) { ?>
            "lengthChange": false, //entry list
            <?php } ?>
        <?php if('N' == $pagination) { ?>
            "paging": false,
            <?php } ?>
        <?php if('N' == $search) { ?>
            "searching": false,
            <?php } ?>
        <?php if('N' == $sorting) { ?>
            "ordering": false,
            <?php } ?>
        <?php if('N' == $info) { ?>
            "info": false,
            <?php } ?>
            "language": showCustomNav()


        });
        $('#<?= $ID ?>').DataTable().draw();
    })(jQuery);
</script>
