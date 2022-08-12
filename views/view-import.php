<?php
$tables = (!empty($args['tables']) && is_array($args['tables'])) ? $args['tables'] : array();

?>


<section class="edit_section_wrapper">
    <!-- <div class="container-fluid">
        <div class="row"> -->
    <div class="col-md-12 no_padding">
        <div class="top_color"></div>
        <div class="board">
            <!--board-inner starts ::: Tab Menu-->
            <div class="board-inner">
                <ul class="nav nav-tabs" id="myTab">
                    <div class="liner"></div>

                    <li class="active"><a href="#home" data-toggle="tab">
                            <p class="round-tabs one">
                                <i class=" glyphicon glyphicon-edit"></i>
                                <span><?php esc_html_e('CSV', ATTC_TEXTDOMAIN); ?></span>
                            </p>
                        </a>


                    </li>

                    <?php if( ! class_exists( 'TableGent_GSI' ) ) { ?>
                    <li>
                        <a href="#table_setting" data-toggle="tab">
                            <p class="round-tabs two">
                                <i class="glyphicon glyphicon-cloud"></i>
                                <span><?php esc_html_e('Google Sheet', ATTC_TEXTDOMAIN); ?></span>
                            </p>
                        </a>
                    </li>

                    <?php
                    }
                    // hook to add tab item
                    do_action('attc_import_tab_menu');
                    ?>
                </ul>
            </div>   <!--Ends board-inner-->


            <div class="tab-content">

                <div class="tab-pane fade in active" id="home">
                    <?php $ATTC->loadView('import/csv', array('table' => $table)); ?>
                </div>  <!--ends .tab-pane   #home-->

                <?php if( ! class_exists( 'TableGent_GSI' ) ) { ?>
                <div class="tab-pane fade" id="table_setting">
                    <?php $ATTC->loadView('import/google-sheet', array('table' => $table)); ?>
                </div>  <!--ends .tab-pane   #table_setting-->

                <?php
                }
                // hook to add tab item/content
                do_action('attc_import_tab_content', $table);
                ?>



                <div class="clearfix"></div>

            </div> <!--Ends tab-content -->

        </div> <!--    end .board -->
    </div>
    <!-- </div>
</div> -->
</section>
