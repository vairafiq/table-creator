<?php
/* 
    Define All Constants 
*/
global $wpdb;
// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );
if ( !defined('ATTC_VERSION') ) { define('ATTC_VERSION', '1.2.1'); }
if ( !defined('ATTC_DIR') ) { define('ATTC_DIR', plugin_dir_path(__FILE__)); }
if ( !defined('ATTC_URL') ) { define('ATTC_URL', plugin_dir_url(__FILE__)); }
if ( !defined('ATTC_CLASS_DIR') ) { define('ATTC_CLASS_DIR', ATTC_DIR.'includes/classes/'); }
if ( !defined('ATTC_VIEWS_DIR') ) { define('ATTC_VIEWS_DIR', ATTC_DIR.'views/'); }
//if ( !defined('ATTC_VIEWS_URL') ) { define('ATTC_VIEWS_URL', ATTC_URL.'views/'); }
if ( !defined('ATTC_LIB_DIR') ) { define('ATTC_LIB_DIR', ATTC_DIR.'libs/'); }
if ( !defined('ATTC_TEMPLATES_DIR') ) { define('ATTC_TEMPLATES_DIR', ATTC_DIR.'templates/'); }
if ( !defined('ATTC_ADMIN_ASSETS') ) { define('ATTC_ADMIN_ASSETS', ATTC_URL.'admin/assets/'); }
if ( !defined('ATTC_PUBLIC_ASSETS') ) { define('ATTC_PUBLIC_ASSETS', ATTC_URL.'public/assets/'); }
if ( !defined('ATTC_TEXTDOMAIN') ) { define('ATTC_TEXTDOMAIN', 'table-generator-by-aazztech'); }
if ( !defined('ATTC_LANG_DIR') ) { define('ATTC_LANG_DIR', dirname(plugin_basename( __FILE__ ) ) . '/languages'); }
if ( !defined('ATTC_PLUGIN_NAME') ) { define('ATTC_PLUGIN_NAME', 'Table Generator by AazzTech'); }
if ( !defined('ATTC_TBL_NAME') ) { define('ATTC_TBL_NAME', $wpdb->prefix.'attc_table'); }
if ( !defined('ATTC_TC_LINE__') ) { define('ATTC_TC_LINE__', __FILE__); }
if ( !defined('ATTC_ALERT_MSG') ) { define('ATTC_ALERT_MSG', __('You do not have the right to access this file directly', ATTC_TEXTDOMAIN)); }

