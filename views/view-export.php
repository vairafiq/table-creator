<?php
$tables = (!empty($args['tables']) && is_array($args['tables'])) ? $args['tables'] : array();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="export_wrapper">
                <h1>Export your table in a CSV Format</h1>
                <form action="" method="post" id="attc_export_form">
                    <?php wp_nonce_field('attc_export_table'); ?>
                    <label for="tables-export-select-all" style="display: none">
                        <input type="checkbox" id="tables-export-select-all" <?= (empty($tables)) ? 'disabled' : ''; ?> > Select
                        all
                    </label>
                   <div class="input-group">
                        <label for="table_list">Table to Export</label>
                        <select class="attc_input_field" name="export[tables][]" id="tables-export">
                            <?php
                                if (count($tables)){
                                    foreach ($tables as $table) {
                                        echo "<option value='{$table['ID']}'>Table ID: {$table['ID']} :: {$table['name']}</option>";
                                    }
                                }else{
                                    echo '<option disabled> No table found.</option>';
                                }

                            ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="export_format">Export Format</label>
                        <select class="attc_input_field" name="export[format]" id="export_format">
                            <option value="csv">CSV - Character-Separated Values</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="export_format">CSV Delimeter</label>
                        <select class="attc_input_field" name="export[csv_delimiter]" id="export_format">
                            <option value=";">; (semicolon)</option>
                            <option selected="selected" value=",">, (comma)</option>
                            <option value="tab">\t (tabulator)</option>
                        </select>
                    </div>

                    <button type="submit" class="attc_btn" name="attc_export_submit" <?= (empty($tables)) ? 'disabled' : ''; ?> >
                        <?= __('Download Table', ATTC_TEXTDOMAIN);?>
                        <span class="glyphicon glyphicon-download-alt"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
