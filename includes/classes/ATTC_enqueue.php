<?php
// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );
if ( !class_exists('ATTC_enqueue') ):

class ATTC_enqueue {


    public function __construct() {

        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 10); // admin scripts & styles
//        add_action('template_redirect', array($this, 'front_end_enqueue_scripts')); //  front-end  styles & scripts
        add_action('wp_enqueue_scripts', array($this, 'front_end_enqueue_scripts')); //  front-end  styles & scripts

    }


    public function admin_enqueue_scripts($page) {
        global $pagenow, $typenow, $ATTC;
        $page = (!empty($_GET['page'])) ? $_GET['page'] : null;
        // I should check for a specific slug in the url to make all the links below available eg. ?views = 'attc_page'
        if ( 'admin.php' == $pagenow && in_array($page, $ATTC->plugin_pages) ) {
            wp_enqueue_style( 'attc-bootstrap', ATTC_ADMIN_ASSETS . 'css/attc-bootstrap.min.css', false, ATTC_VERSION );
            wp_enqueue_style( 'attc-tabs', ATTC_ADMIN_ASSETS . 'css/attc-tabs.css', array('attc-bootstrap'), ATTC_VERSION);
            wp_enqueue_style( 'attc-sweetalert', ATTC_ADMIN_ASSETS . 'css/sweetalert.css', array('attc-bootstrap'), ATTC_VERSION);
            wp_enqueue_style( 'attc-main', ATTC_ADMIN_ASSETS . 'css/attc-main.css', array('attc-bootstrap', 'attc-tabs'), ATTC_VERSION);
            wp_enqueue_script( 'attc-bootstrap-js', ATTC_ADMIN_ASSETS . 'js/attc-bootstrap.min.js', array( 'jquery' ), ATTC_VERSION, true );
            wp_enqueue_script( 'attc-sweetalert-js', ATTC_ADMIN_ASSETS . 'js/sweetalert.min.js', array( 'jquery' ), ATTC_VERSION, true );

            wp_enqueue_script( 'attc-main-js', ATTC_ADMIN_ASSETS . 'js/attc-main.js', array(
                'jquery',
                'jquery-ui-resizable',
                'attc-bootstrap-js',
            ), ATTC_VERSION, true );

            $attc_obj = array(
                'nonceAction' => $ATTC->helper->nonceAction(),
                'nonce'       => wp_create_nonce( $ATTC->helper->nonceName() ),
                'tablegen_nonce' => wp_create_nonce( tablegen_get_nonce_key() ),
                'adminAsset'  => ATTC_ADMIN_ASSETS,
                'ajax_url'  => admin_url( 'admin-ajax.php' ),
            );
            wp_localize_script( 'attc-main-js', 'attc_obj', $attc_obj );

            wp_register_script( 'attc-import-js', ATTC_ADMIN_ASSETS . 'js/attc-import.js', array(
                'jquery',
                'attc-bootstrap-js',
            ), ATTC_VERSION, true );

            if( 'attc-import' === $page ) {
                wp_enqueue_script( 'attc-import-js' );
                wp_localize_script( 'attc-import-js', 'attc_import_data', $attc_obj );
            }
        }

    }


    public function front_end_enqueue_scripts() {
        //scripts
        wp_register_script('attc-datatable-js', ATTC_PUBLIC_ASSETS . 'js/datatables.min.js', array('jquery'), ATTC_VERSION, false);
        wp_register_script('attc-front-main-js', ATTC_PUBLIC_ASSETS . 'js/attc-front-main.js', array('jquery'), ATTC_VERSION);
        wp_enqueue_script('attc-datatable-js');

        // Styles
        wp_register_style( 'attc-bootstrap', ATTC_ADMIN_ASSETS . 'css/attc-bootstrap.min.css', false, ATTC_VERSION );
        wp_register_style('attc-datatable-style', ATTC_PUBLIC_ASSETS . 'css/datatables.min.css', false, ATTC_VERSION);
        wp_register_style('attc-theme-style', ATTC_PUBLIC_ASSETS . 'css/attc-theme.css', array('attc-bootstrap', 'attc-datatable-style'), ATTC_VERSION);

        wp_register_style( 'attc-public-style', ATTC_PUBLIC_ASSETS . 'css/attc-public-style.css', array('attc-bootstrap', 'attc-datatable-style'), ATTC_VERSION );





    }
}


endif;
