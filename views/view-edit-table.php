<?php
$table = !empty($args['table']) ? $args['table'] : array();


$table_id 		    = $table ? $table['ID'] : '';
$name 			    = $table ? $table['name'] : '';
$rows 				= $table ? $table['rows_no'] : 4;
$cols 				= $table ? $table['cols'] : 4;
$subs 				= !empty($table['subs']) ? $table['subs'] : '';
$color				= $table ? $table['color'] : 'default';
$responsive	        = $table ? $table['responsive'] : '';
$saved_content 	    = $table ? $table['content'] : '';

$col_span           = $cols;
$sub_array          = explode(',', $subs);


?>

<div class="container-fluid" id="attc-edit-view-wrap">


    <form autocomplete="off" method="POST" class="attc-form" data-id="<?php echo intval($table_id); ?>" id="attc_edit_form" name="attc-edit-form">

        <div class="row">
            <?php $ATTC->loadView('view-create-table', array('table'=> $table)); ?>

            <!--Table Option -->
            <div class="col-lg-6">
                <div class="attc_table_short_code alert alert-info">
                    <p>Copy and paste the shortcode in you desired page or post.</p>
                    <input type="text" name="" value="[attc id='<?= $table_id;?>']">
                </div>

                <div class="attc_table_setting">
                    <div class="attc_setting_options">
                        <h4>Insert Options</h4>

                        <div class="attc_option">
                            <input type="text" id="col" name="" placeholder="Insert Columns" value="">
                            <button id="icol" class="option_btn" type="button" name="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                        </div>

                        <div class="attc_option">
                            <input id="row" type="text" name="" placeholder="Insert Rows" value="">
                            <button id="irow" class="option_btn" type="button" name="button"><span class="glyphicon glyphicon-plus-sign"></span></button>
                        </div>
                    </div>


                    <div class="attc_setting_options">
                    <h4>Delete Options</h4>
                    <div class="attc_option">
                        <button type="button" id="del_row" class="attc_btn" ><span>Delete Row(s)</span> <span class="delete_icon glyphicon glyphicon-trash"></span></button>
                    </div>

                    <div class="attc_option">
                        <button type="button" id="del_col" class="attc_btn" name="button"><span>Delete Column(s)</span> <span class="delete_icon glyphicon glyphicon-trash"></span></button>
                    </div>
                </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <!--important hidden fields -->
                <input type="hidden" name="table_id" id="table_id" value="<?= $table_id; ?>">
                <input type="hidden" name="attc_current_row" id="attc_current_row" value="<?= $rows; ?>">
                <input type="hidden" name="attc_current_cols" id="attc_current_cols" value="<?= $cols; ?>">
                <?php wp_nonce_field('attc_update_table'); ?>
                <!-- Table for inserting data starts form here. -->
                <table class="table table-bordered attc_edit_table" id="attc_table_admin">
                    <thead class="attc-thead">
                        <tr>
                            <?php for ($j=1; $j <= $cols; $j++){
                                echo (1 === $j) ? '<th class="first_th">ID</th><th class="first_th"></th>': ''; // add extra 2 th on the first column on thead only
                                echo "<th class='letter'><span class='letter'>". attc_number_to_letter($j)."</span><input type='checkbox' class='check'></th>";
                            } ?>
                        </tr>
                    </thead>

                    <tbody class="attc-tbody" id="attc_body">
                        <?php for ($i=0; $i < $rows; $i++) { // Primary loop ($i loop) starts here. It will print one row per iteration
                            $CurrentRow = $i+1;
                        ?>

                        <?= '<tr>'; ?>


                        <?php for ($j=0; $j < $cols; $j++){  // 1ST nested loop ($j loop)  starts. It will create all <td>(s) ?>

                        <?= (0 === $j) ? "<td class='first_td'><span class='index_num'>". $CurrentRow. "</span></td><td class='first_td'><input type='checkbox' class='row_check'></td>": ''; // print $i eg. Counter on first TD of every row ?>
                        <?= '<td>' ?>



                        <textarea
                            class="attc_textarea"
                            placeholder="<?php _e('edit', ATTC_TEXTDOMAIN) ?>"
                            type="text" rows="2" cols="20"
                            name="table_values[<?= $i ?>][<?= $j ?>]"
                        ><?php echo isset($saved_content[$i][$j]) ? esc_html(trim($saved_content[$i][$j])) : ''; ?></textarea>

                    </td>
                    <?php } // ends 1ST nested ($j loop ) loop ?>
                </tr>
                    <?php } // Ends the Primary loop eg. $i loop?>
                    </tbody>
                </table>
                <input type="submit" id="attc-create-table" class="attc_btn" name="attc-create-table" value="<?php  esc_html_e('Update Table', ATTC_TEXTDOMAIN);?>">
            </div>
        </div>
    </form>


</div>
