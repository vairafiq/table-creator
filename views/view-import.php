<?php
$tables = (!empty($args['tables']) && is_array($args['tables'])) ? $args['tables'] : array();

?>
<div class="container-fluid attc_import_view">
    <div class="row">
        <div class="col-md-5 col-md-offset-4">
            <form action="" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('attc_import_table'); ?>
                <div class="upload_content">
                    <div class="upload_wrapper">
                        <input type="file" name="import_file_upload" id="tables-import-file-upload" class="inputfile" />
                        <label for="tables-import-file-upload"><span class="glyphicon glyphicon-upload"></span><p>Choose a file..</p></label>
                    </div>
                    <button class="attc_btn" type="submit" name="button">Import File</button>
                </div>

                <div class="hidden_contetn hidden">
                    <label for="tables-import-format">Import Format:</label>
                    <select id="tables-export-format" name="import[format]">
                        <option value="csv">CSV - Character-Separated Values</option>
                    </select>
                </div>
            </form>
        </div>
    </div>
</div>
