<?php
// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if( !class_exists( 'ATTC_helper' ) ) :

/**
 * Class ATTC_helper
 * It provides useful helper methods to use throughout the plugin
 */
class ATTC_helper {

    /*
     * Set text for pagination
     * First value is for previous item
     * Second value is for next item
     * @var array
     */
    private $nav_text =  array(
        '<i class="fa fa-arrow-left" aria-hidden="true"></i> Prev',
        'Next <i class="fa fa-arrow-right" aria-hidden="true"></i>'
    );

    private $nonce_action = 'ATTC_nonce_action';


    private $nonce_name = 'ATTC_nonce';


    private $q_tags = 'strong,em,link,block,del,ins,img,ul,ol,li,code,close';


    public function __construct(){
        if ( ! defined('ABSPATH') ) { return; }
        add_action('init', array( $this, 'check_req_php_version' ), 100 );

        // include any helper library here.
        if(file_exists(ATTC_LIB_DIR.'Aq_Resize.php')){
            include ATTC_LIB_DIR.'Aq_Resize.php';
        }
    }


    public function verifyNonce( ){
        //global $ADL_LP;
        $nonce      = (!empty($_REQUEST[$this->nonceName()])) ? $_REQUEST[$this->nonceName()] : null ;
        $nonceAction  = $this->nonceAction();
        if( !wp_verify_nonce( $nonce, $nonceAction ) ) return false;
        return true;
    }


    public function nonceAction(){
        return $this->nonce_action;
    }


    public function nonceName(){
        return $this->nonce_name;
    }


    public function check_req_php_version( ){
        if ( version_compare( PHP_VERSION, '5.4', '<' )) {
            add_action( 'admin_notices', array($this, 'ATTC_notice'), 100 );


            // deactivate the plugin because required php version is less.
            add_action( 'admin_init', array($this, 'ATTC_deactivate_self'), 100 );

            return;
        }
    }


    public function ATTC_notice() { ?>
        <div class="error"> <p>
                <?php
                echo ATTC_PLUGIN_NAME.' requires minimum PHP 5.4 to function properly. Please upgrade PHP version. The Plugin has been auto-deactivated.. You have PHP version '.PHP_VERSION;
                ?>
            </p></div>
        <?php
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }


    public function ATTC_deactivate_self() {
        deactivate_plugins( ATTC_BASE ); // deactivate the plugin
    }


    /**
     * Darken or lighten a given hex color and return it.
     * @param string $hex Hex color code to be darken or lighten
     * @param int $percent The number of percent of darkness or brightness
     * @param bool|true $darken Lighten the color if set to false, otherwise, darken it. Default is true.
     *
     * @return string
     */
    public function adjust_brightness($hex, $percent, $darken = true) {
        // determine if we want to lighten or draken the color. Negative -255 means darken, positive integer means lighten
        $brightness = $darken ? -255 : 255;
        $steps = $percent*$brightness/100;

        // Normalize into a six character long hex string
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
        }

        // Split into three parts: R, G and B
        $color_parts = str_split($hex, 2);
        $return = '#';

        foreach ($color_parts as $color) {
            $color   = hexdec($color); // Convert to decimal
            $color   = max(0,min(255,$color + $steps)); // Adjust color
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
        }

        return $return;
    }


    /**
     * Prints pagination for custom post
     * @param object $loop POSTS loop
     * @param int $paged
     * @param array $nav_text Previous and next text for pagination
     *
     * @return string
     */
    public function show_pagination( $loop, $paged = 1, $nav_text = array('&laquo; Prev','Next &raquo;')){
        $html = '<div class="attc-pagination">';
        $largeNumber = 999999999; // we need a large number here
        $html .= paginate_links( array(
            'base' => str_replace( $largeNumber, '%#%', esc_url( get_pagenum_link( $largeNumber ) ) ),
            'format' => '?paged=%#%',
            'current' => max( 1, $paged ),
            'total' => $loop->max_num_pages,
            'prev_text' => __($nav_text[0], ATTC_TEXTDOMAIN),
            'next_text' => __($nav_text[1], ATTC_TEXTDOMAIN),
        ) );

        $html .= '</div >';
        return $html;
    }


    /**
     * Lists of html tags that are allowed in a content
     * @return array List of allowed tags in a content
     */
    public function allowed_html() {
        return array(
            'i' => array(
                'class' => array(),
            ),
            'strong' => array(
                'class' => array(),
            ),
            'em' => array(
                'class' => array(),
            ),
            'a' => array(
                'class' => array(),
                'href' => array(),
                'title' => array(),
                'target' => array(),
            ),

        );
    }


    public function get_nav_text() {
        return $this->nav_text;
    }


    public function get_q_tags() {
        return $this->q_tags;
    }

    /**
     * Display date in a human readable form. It generally displays relative time eg. 1 hour ego, 5 mins ago etc.
     * @param string      $d    Optional. Format to use for retrieving the time the post
     *                          was written. Either 'G', 'U', or php date format defaults
     *                          to the value specified in the time_format option. Default empty.
     * @param int|WP_Post $post_or_ID WP_Post object or ID. Default is global $post object.
     * @return string Formatted date string in a relative time form for human readability.
     */
    public function get_human_date( $post_or_ID, $d='U' ) {
        return human_time_diff( get_the_time($d, $post_or_ID), current_time('timestamp') ) . ' ago';
    }

    /**
     * It returns the name of the author and his/her post/pages link
     * @param int $post_author_ID The id of the author of the page or the post
     * @param string $post_type The name of the post type to link users. eg. page, post etc
     *
     * @return string It returns the name of the author of a page or post and display a link to the post or page.
     */
    public function get_author_name_and_post_link( $post_author_ID = 1, $post_type='post' ) {
        return "<a href='" . get_admin_url() . "/edit.php?post_type={$post_type}&author={$post_author_ID}' > " . get_the_author_meta('display_name', $post_author_ID) . "</a>";
    }

    /**
     * It returns the url to a specific admin page with an id, page, and action query string.
     * @param int $id The id of the post or page to edit
     * @param string $action    The name of the action to add to the url such as create , edit, delete etc
     * @param string $page  The name of the page where you would like to send the link.
     * @param bool $echo  Echo out the url string by default. Set false to return the value instead of echoing.
     *
     * @return string It returns the url to a specific admin page with an id, page, and action query string.
     */
    public function get_attc_action_link( $id=0, $action='edit', $page='attc-create-template', $echo=true) {
        $url = get_admin_url() . "admin.php?page={$page}&action={$action}&id={$id}";
        if (!$echo) return $url;
        echo $url;

    }


    /**
     * Calculate the column index (number) of a column header string (example: A is 1, AA is 27, ...).
     *
     * For the opposite, @see number_to_letter().
     *
     * @since 1.0.0
     *
     * @param string $column Column string.
     * @return int $number Column number, 1-based.
     */
    public static function letter_to_number( $column ) {
        $column = strtoupper( $column );
        $count = strlen( $column );
        $number = 0;
        for ( $i = 0; $i < $count; $i++ ) {
            $number += ( ord( $column[ $count - 1 - $i ] ) - 64 ) * pow( 26, $i );
        }
        return $number;
    }

    /**
     * "Calculate" the column header string of a column index (example: 2 is B, AB is 28, ...).
     *
     * For the opposite, @see letter_to_number().
     *
     * @since 1.0.0
     *
     * @param int $number Column number, 1-based.
     * @return string $column Column string.
     */
    public static function number_to_letter( $number ) {
        $column = '';
        while ( $number > 0 ) {
            $column = chr( 65 + ( ( $number - 1 ) % 26 ) ) . $column;
            $number = floor( ( $number - 1 ) / 26 );
        }
        return $column;
    }



}

endif;
