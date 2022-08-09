<?php
global $ATTC;
//  this page contains the settings for individual table
is_array($args['table']['config']) ? extract($args['table']['config']): array();
include 'attc-vars.php'; // includes all required variables
?>

<div class="container-fluid">

    <div class="row">
        <div class="col-lg-6">
            <div class="attc_table_setting_wrap">
                <h2>Table Setting Page</h2>
                <p>You can modify the look and feel of the table on this screen.</p>
                <form action="" method="post" id="attc_setting_form" data-id = <?= $table_id; ?>>

                    

                    <div class="option_slide">
                        <ul class="setting_options">
                            <li>
                                <label for="config[t_type]" class="control-label">Use advanced Table to show table data?</label>
                                <div class="option_toggler">
                                    <a class="<?= ('N' !== $t_type) ? 'active' : ' '; ?>" data-toggle="config_t_type" data-title="Y">YES</a>
                                    <a class="<?= ('N' == $t_type) ? 'active' : ' '; ?>" data-toggle="config_t_type" data-title="N">NO</a>
                                </div>
                                <input type="hidden" name="config[t_type]" id="config_t_type" value="<?= !empty($t_type) ? $t_type: 'Y'; ?>" >
                            </li>

                            <li>
                                <label for="config[t_head]" class="control-label">Use the first row of the table as a table header?</label>
                                <div class="option_toggler">
                                    <a class="<?= ('N' !== $t_head) ? 'active' : ' '; ?>" data-toggle="config_t_head" data-title="Y">YES</a>
                                    <a class="<?= ('N' == $t_head) ? 'active' : ' '; ?>" data-toggle="config_t_head" data-title="N">NO</a>
                                </div>
                                <input type="hidden" name="config[t_head]" id="config_t_head" value="<?= !empty($t_head) ? $t_head: 'Y'; ?>" >
                            </li>

                            <li>
                                <label for="config[hover]" class="control-label">Use hover effect?</label>
                                <div class="option_toggler">
                                    <a class="<?= ('N' !== $hover) ? 'active' : ' '; ?>" data-toggle="config_hover" data-title="Y">YES</a>
                                    <a class="<?= ('N' == $hover) ? 'active' : ' '; ?>" data-toggle="config_hover" data-title="N">NO</a>
                                </div>
                                <input type="hidden" name="config[hover]" id="config_hover" value="<?= !empty($hover) ? $hover: 'Y'; ?>">
                            </li>

                            <li>
                                <label for="config[scroll_x]" class="control-label">Use Horizontal scroll for large table?</label>
                                <div class="option_toggler">
                                    <a class="<?= ('N' !== $scroll_x) ? 'active' : ' '; ?>" data-toggle="config_scroll_x" data-title="Y">YES</a>
                                    <a class=" <?= ('N' == $scroll_x) ? 'active' : ' '; ?>" data-toggle="config_scroll_x" data-title="N">NO</a>
                                </div>
                                <input type="hidden" name="config[scroll_x]" id="config_scroll_x" value="<?= !empty($scroll_x) ? $scroll_x: 'Y'; ?>">
                            </li>

                            <li>
                                <label for="config_width" class="control-label lable_for_input">Specify Table width</label>
                                <div class="option_toggler">
                                    <input type="text" name="config[width]" id="config_width" value="<?= $width; ?>" placeholder="eg. 800px or 100%" >
                                </div>
                            </li>


                            <li>
                                <label for="config[scroll_y]" class="control-label">Use Vertical scroll for large table?</label>
                                <div class="option_toggler">
                                    <a class="<?= ('Y' == $scroll_y) ? 'active' : ' '; ?>" data-toggle="config_scroll_y" data-title="Y">YES</a>
                                    <a class="<?= ('Y' !== $scroll_y) ? 'active' : ' '; ?>" data-toggle="config_scroll_y" data-title="N">NO</a>
                                </div>
                                <input type="hidden" name="config[scroll_y]" id="config_scroll_y" value="<?= !empty($scroll_y) ? $scroll_y: 'N'; ?>">
                            </li>


                            <li>
                                <label for="config_height" class="control-label lable_for_input">Specify Table height</label>
                                <div class="option_toggler">
                                    <input type="text" name="config[height]" id="config_height" value="<?= $height; ?>" placeholder="eg. 300px or 50vh" >
                                </div>
                            </li>

                            <li>
                                <label for="config[search]" class="control-label">Enable searching data in the table?</label>
                                <div class="option_toggler">
                                    <a class="<?= ('N' !== $search) ? 'active' : ' '; ?>" data-toggle="config_search" data-title="Y">YES</a>
                                    <a class="<?= ('N' == $search) ? 'active' : ' '; ?>" data-toggle="config_search" data-title="N">NO</a>
                                </div>
                                <input type="hidden" name="config[search]" id="config_search" value="<?= !empty($search) ? $search: 'Y'; ?>">
                            </li>

                            <li>
                                <label for="config[pagination]" class="control-label">Enable pagination?</label>
                                <div class="option_toggler">
                                    <a class="<?= ('N' !== $pagination) ? 'active' : ' '; ?>" data-toggle="config_pagination" data-title="Y">YES</a>
                                    <a class="<?= ('N' == $pagination) ? 'active' : ' '; ?>" data-toggle="config_pagination" data-title="N">NO</a>
                                </div>
                                <input type="hidden" name="config[pagination]" id="config_pagination" value="<?= !empty($pagination) ? $pagination: 'Y'; ?>">
                            </li>

                            <li>
                                <label for="config[entry_list]" class="control-label">Show entry list drowpdown?</label>
                                <div class="option_toggler">
                                    <a class="<?= ('N' !== $entry_list) ? 'active' : ' '; ?>" data-toggle="config_entry_list" data-title="Y">YES</a>
                                    <a class="<?= ('N' == $entry_list) ? 'active' : ' '; ?>" data-toggle="config_entry_list" data-title="N">NO</a>
                                </div>
                                <input type="hidden" name="config[entry_list]" id="config_entry_list" value="Y">
                            </li>

                            <li>
                                <label for="config[sorting]" class="control-label">Enable Sorting column?</label>
                                <div class="option_toggler">
                                    <a class="<?= ('N' !== $sorting) ? 'active' : ' '; ?>" data-toggle="config_sorting" data-title="Y">YES</a>
                                    <a class="<?= ('N' == $sorting) ? 'active' : ' '; ?>" data-toggle="config_sorting" data-title="N">NO</a>
                                </div>
                                <input type="hidden" name="config[sorting]" id="config_sorting" value="<?= !empty($sorting) ? $sorting: 'Y'; ?>">
                            </li>

                            <li>
                                <label for="config[info]" class="control-label">Show entry info below table?</label>
                                <div class="option_toggler">
                                    <a class="<?= ('N' !== $info) ? 'active' : ' '; ?>" data-toggle="config_info" data-title="Y">YES</a>
                                    <a class="<?= ('N' == $info) ? 'active' : ' '; ?>" data-toggle="config_info" data-title="N">NO</a>
                                </div>
                                <input type="hidden" name="config[info]" id="config_info" value="<?= !empty($info) ? $info: 'Y'; ?>">
                            </li>

                            <li>
                                <label for="config[show_tbl_name]" class="control-label">Show Table Name?</label>
                                <div class="option_toggler">
                                    <a class="<?= ('Y' == $show_tbl_name) ? 'active' : ' '; ?>" data-toggle="config_show_tbl_name" data-title="Y">YES</a>
                                    <a class="<?= ('Y' !== $show_tbl_name) ? 'active' : ' '; ?>" data-toggle="config_show_tbl_name" data-title="N">NO</a>
                                </div>
                                <input type="hidden" name="config[show_tbl_name]" id="config_show_tbl_name" value="<?= !empty($show_tbl_name) ? $show_tbl_name: 'N'; ?>">
                            </li>

                            <li>
                                <label for="config[show_tbl_desc]" class="control-label">Show Table Description?</label>
                                <div class="option_toggler">
                                    <a class="<?=('Y' == $show_tbl_desc) ? 'active' : ' '; ?>" data-toggle="config_show_tbl_desc" data-title="Y">YES</a>
                                    <a class="<?=('Y' !== $show_tbl_desc) ? 'active' : ' '; ?>" data-toggle="config_show_tbl_desc" data-title="N">NO</a>
                                </div>
                                <input type="hidden" name="config[show_tbl_desc]" id="config_show_tbl_desc" value="<?= !empty($show_tbl_desc) ? $show_tbl_desc: 'N'; ?>">
                            </li>
                        </ul>
                        <button type="submit" id="attc-table-setting" class="attc_btn" name="attc-table-setting"><?= __('Save Settings', ATTC_TEXTDOMAIN) ;?></button>
                    </div>
                </form><!-- end form -->
            </div><!-- attc_table_setting_wrap-->
        </div>


    </div><!-- ends row -->
</div> <!-- ends .container-fluid -->
