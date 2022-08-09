<?php
// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );
/**
 * It loads files from a given directory using require_once.
 * @param string|array $files list of the names of file or a single file name to be loaded. Default: all
 * @param string $directory  the location of the files
 * @param string $ext  the ext of the files to be loaded
 * @return mixed|null it requires all the files in a given directory
 */
if (!function_exists('load_dependencies')):
    function load_dependencies($files = 'all', $directory=ATTC_CLASS_DIR, $ext='.php')
    {
        if (!file_exists($directory)) return ; // vail if the directory does not exist

        switch ($files){
            case is_array($files) && 'all' !== strtolower($files[0]):
                // include one or more file looping through the $files array
                load_some_file($files, $directory);
                break;
            case !is_array($files) && 'all' !== $files:
                //load a single file here
                (file_exists($directory.$files.$ext)) ? require_once $directory.$files.$ext: null;
                break;
            case 'all' == $files || 'all' == strtolower($files[0]):
                // load all php file here
                load_all_files($directory);
                break;
        }

        return false;

    }
endif;

/**
 * It loads all files that has the extension named $ext from the $dir
 * @param string $dir Name of the directory
 * @param string $ext Name of the extension of the files to be loaded
 */
if (!function_exists('load_all_files')):
    function load_all_files($dir='', $ext='.php'){
        if (!file_exists($dir)) return;
        foreach (scandir($dir) as $file) {
            // require once all the files with the given ext. eg. .php
            if( preg_match( "/{$ext}$/i" , $file ) ) {
                require_once( $dir . $file );
            }
        }
    }
endif;

/**
 * It loads one or more files but not all files that has the $ext from the $dir
 * @param string|array $files the array of files that should be loaded
 * @param string $dir Name of the directory
 * @param string $ext Name of the extension of the files to be loaded
 */

if (!function_exists('load_some_file')):

    function load_some_file($files=array(),$dir='', $ext='.php')
    {
        if (!file_exists($dir)) return; // vail if directory does not exist

        if(is_array($files)) {  // if the given files is an array then
            $files_to_loads = array_map(function ($i) use($ext){ return $i.$ext; }, $files);// add '.php' to the end of all files
            $found_files = scandir($dir); // get the list of all the files in the given $dir
            foreach ($files_to_loads as $file_to_load) {
                in_array($file_to_load, $found_files) ? require_once $dir.$file_to_load : null;
            }
        }

    }
endif;

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

if (!function_exists('attc_letter_to_number')):

    function attc_letter_to_number( $column ) {
        $column = strtoupper( $column );
        $count = strlen( $column );
        $number = 0;
        for ( $i = 0; $i < $count; $i++ ) {
            $number += ( ord( $column[ $count - 1 - $i ] ) - 64 ) * pow( 26, $i );
        }
        return $number;
    }

endif;
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
if (!function_exists('attc_number_to_letter')):

    function attc_number_to_letter( $number ) {
        $column = '';
        while ( $number > 0 ) {
            $column = chr( 65 + ( ( $number - 1 ) % 26 ) ) . $column;
            $number = floor( ( $number - 1 ) / 26 );
        }
        return $column;
    }
endif;

if (!function_exists('padded_var_dump')):

    function padded_var_dump($data){
        echo "<div style='margin-left: 200px;'>";
        var_dump($data);
        echo "</div>";
    }
endif;

if (!function_exists('list_file_name')):
    /**
     * It returns a list of names of all files which are not hidden files
     * @param string $path
     * @return array
     */
    function list_file_name($path = __DIR__){
        $file_names = array();
        foreach (new DirectoryIterator($path) as $fileInfo) {
            if($fileInfo->isDot()) continue;
            $file_names[] =  $fileInfo->getFilename();
        }
        return $file_names;
    }

endif;

if (!function_exists('list_file_path')):
    /**
     * It returns a list of path of all files which are not hidden files
     * @param string $path
     * @return array
     */
    function list_file_path($path = __DIR__){
        $file_paths = array();
        foreach (new DirectoryIterator($path) as $fileInfo) {
            if($fileInfo->isDot()) continue;
            $file_paths[] =  $fileInfo->getRealPath();
        }
        return $file_paths;
    }

endif;

    if (!function_exists('beautiful_datetime')):
        function beautiful_datetime( $datetime, $type = 'mysql', $separator = ' ' ) {
            if ( 'mysql' === $type ) {
                return mysql2date( get_option( 'date_format' ), $datetime ) . $separator . mysql2date( get_option( 'time_format' ), $datetime );
            } else {
                return date_i18n( get_option( 'date_format' ), $datetime ) . $separator . date_i18n( get_option( 'time_format' ), $datetime );
            }
        }

        endif;

