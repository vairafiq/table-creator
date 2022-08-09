<?php

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );
if (!class_exists('ATTC_import')):

class ATTC_import {

    /**
     * File/Data Formats that are available for import.
     *
     * @since 1.0.0
     * @var array
     */
    public $import_formats = array();

    /**
     * Whether ZIP archive support is available in the PHP installation on the server.
     *
     * @since 1.0.0
     * @var bool
     */
    public $zip_support_available = false;

    /**
     * Whether HTML import support is available in the PHP installation on the server.
     *
     * @since 1.0.0
     * @var bool
     */
    public $html_import_support_available = false;

    /**
     * Data to be imported.
     *
     * @since 1.0.0
     * @var string
     */
    protected $import_data;

    /**
     * Imported table.
     *
     * @since 1.0.0
     * @var array
     */
    protected $imported_table = false;

    /**
     * Initialize the Import class.
     *
     * @since 1.0.0
     */
    public function __construct() {
        load_dependencies('all', ATTC_LIB_DIR, '.php');
        /** This filter is documented in the WordPress function unzip_file() in wp-admin/includes/file.php */
        if ( class_exists( 'ZipArchive' ) && apply_filters( 'unzip_file_use_ziparchive', true ) ) {
            $this->zip_support_available = true;
        }

        if ( class_exists( 'DOMDocument' ) && function_exists( 'simplexml_import_dom' ) && function_exists( 'libxml_use_internal_errors' ) ) {
            $this->html_import_support_available = true;
        }

        $this->import_formats = array();
        $this->import_formats['csv'] = __( 'CSV - Character-Separated Values', ATTC_TEXTDOMAIN );

    }

    /**
     * Import a table.
     *
     * @since 1.0.0
     *
     * @param string $format Import format.
     * @param string $data   Data to import.
     * @return bool|array False on error, table array on success.
     */
    public function import_table( $format, $data ) {
        $this->import_data = $data;

        // fix encoding for excel files
        if ( ! in_array( $format, array( 'xlsx', 'xls' ) ) ) {
            $this->fix_table_encoding();
        }

        switch ( $format ) {
            case 'csv':
                $this->import_csv();
                break;
            case 'html':
                $this->import_html();
                break;
            case 'json':
                $this->import_json();
                break;
            case 'xlsx':
                $this->import_xlsx();
                break;
            case 'xls':
                $this->import_xls();
                break;
            default:
                return false;
        }

        return $this->imported_table;
    }

    /**
     * Import CSV data.
     *
     * @since 1.0.0
     */
    protected function import_csv() {

        $csv_parser = new ATTC_CSV_Parser(); // the file is already loaded in the constructor so we can instantiate it.
        $csv_parser->load_data( $this->import_data );
        $delimiter = $csv_parser->find_delimiter();
        $data = $csv_parser->parse( $delimiter );
        $this->imported_table = array( 'content' => $this->pad_array_to_max_cols( $data ) );
    }

    /**
     * Import HTML data.
     *
     * @since 1.0.0
     */
    protected function import_html() {
        if ( ! $this->html_import_support_available ) {
            return false;
        }

        /* Extract table from HTML, pattern: <table> (with eventually class, id, ...
         * . means any character (except newline),
         * * means in any count,
         * ? means non-greedy (shortest possible),
         * is at the end: i: case-insensitive, s: include newline (in .)
         */
        if ( 1 === preg_match( '#<table.*?>.*?</table>#is', $this->import_data, $matches ) ) {
            $temp_data = $matches[0]; // if found, take match as table to import
        } else {
            $this->imported_table = false;
            return;
        }

        // Prepend XML declaration, for better encoding support.
        $temp_data = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . $temp_data;
        if ( function_exists( 'libxml_disable_entity_loader' ) ) {
            // Don't expand external entities (see http://websec.io/2012/08/27/Preventing-XXE-in-PHP.html).
            libxml_disable_entity_loader( true );
        }
        // No warnings/errors raised, but stored internally.
        libxml_use_internal_errors( true );
        $dom = new DOMDocument( '1.0', 'UTF-8' );
        // No strict checking for invalid HTML.
        $dom->strictErrorChecking = false;
        $dom->loadHTML( $temp_data );
        if ( false === $dom ) {
            $this->imported_table = false;
            return;
        }
        $table_html = simplexml_import_dom( $dom );
        if ( false === $table_html ) {
            $this->imported_table = false;
            return;
        }

        $errors = libxml_get_errors();
        libxml_clear_errors();
        if ( ! empty( $errors ) ) {
            $output = '<strong>' . __( 'The imported file contains errors:', ATTC_TEXTDOMAIN ) . '</strong><br /><br />';
            foreach ( $errors as $error ) {
                switch ( $error->level ) {
                    case LIBXML_ERR_WARNING:
                        $output .= "Warning {$error->code}: {$error->message} in line {$error->line}, column {$error->column}<br />";
                        break;
                    case LIBXML_ERR_ERROR:
                        $output .= "Error {$error->code}: {$error->message} in line {$error->line}, column {$error->column}<br />";
                        break;
                    case LIBXML_ERR_FATAL:
                        $output .= "Fatal Error {$error->code}: {$error->message} in line {$error->line}, column {$error->column}<br />";
                        break;
                }
            }
            wp_die( $output, 'Import Error', array( 'response' => 200, 'back_link' => true ) );
        }

        $table = $table_html->body->table;

        $html_table = array(
            'content' => array(),
            'options' => array(),
        );
        if ( isset( $table->thead ) ) {
            $html_table['content'] = array_merge( $html_table['content'], $this->_import_html_rows( $table->thead[0]->tr ) );
            $html_table['options']['t_head'] = true;
        }
        if ( isset( $table->tbody ) ) {
            $html_table['content'] = array_merge( $html_table['content'], $this->_import_html_rows( $table->tbody[0]->tr ) );
        }
        if ( isset( $table->tr ) ) {
            $html_table['content'] = array_merge( $html_table['content'], $this->_import_html_rows( $table->tr ) );
        }
        if ( isset( $table->tfoot ) ) {
            $html_table['content'] = array_merge( $html_table['content'], $this->_import_html_rows( $table->tfoot[0]->tr ) );
            $html_table['options']['table_foot'] = true;
        }

        $html_table['content'] = $this->pad_array_to_max_cols( $html_table['content'] );
        $this->imported_table = $html_table;
    }

    /**
     * Helper for HTML import.
     *
     * @since 1.0.0
     *
     * @param SimpleXMLElement $element XMLElement.
     * @return array SimpleXMLElement exported to an array.
     */
    protected function _import_html_rows( $element ) {
        $rows = array();
        foreach ( $element as $row ) {
            $new_row = array();
            foreach ( $row as $cell ) {
                // Get text between <td>...</td>, or <th>...</th>, possibly with attributes.
                if ( 1 === preg_match( '#<t(?:d|h).*?>(.*)</t(?:d|h)>#is', $cell->asXML(), $matches ) ) {
                    /*
                     * Decode HTML entities again, as there might be some left especially in attributes of HTML tags in the cells,
                     * see http://php.net/manual/en/simplexmlelement.asxml.php#107137 .
                     */
                    $matches[1] = html_entity_decode( $matches[1], ENT_NOQUOTES, 'UTF-8' );
                    $new_row[] = $matches[1];
                } else {
                    $new_row[] = '';
                }
            }
            $rows[] = $new_row;
        }
        return $rows;
    }

    /**
     * Import JSON data.
     *
     * @since 1.0.0
     */
    protected function import_json() {
        $json_table = json_decode( $this->import_data, true );

        // Check if JSON could be decoded.
        if ( is_null( $json_table ) ) {
            // If possible, try to find out what error prevented the JSON from being decoded.
            $json_error = 'The error could not be determined.';
            // @TODO: The `function_exists` check can be removed once support for WP 4.3 is dropped, as a compat function was added in WP 4.4.
            if ( function_exists( 'json_last_error_msg' ) ) {
                $json_error_msg = json_last_error_msg();
                if ( false !== $json_error_msg ) {
                    $json_error = $json_error_msg;
                }
            }
            $output = '<strong>' . __( 'The imported file contains errors:', ATTC_TEXTDOMAIN ) . "</strong><br /><br />JSON error: {$json_error}<br />";
            wp_die( $output, 'Import Error', array( 'response' => 200, 'back_link' => true ) );
        } else {
            // Specifically cast to an array again.
            $json_table = (array) $json_table;
        }

        if ( isset( $json_table['content'] ) ) {
            // JSON data contained a full export.
            $table = $json_table;
        } else {
            // JSON data contained only the data of a table, but no options.
            $table = array( 'content' => array() );
            foreach ( $json_table as $row ) {
                $table['content'][] = array_values( (array) $row );
            }
        }

        $table['content'] = $this->pad_array_to_max_cols( $table['content'] );
        $this->imported_table = $table;
    }


    /**
     * Make sure array is rectangular with $max_cols columns in every row.
     *
     * @since 1.0.0
     *
     * @param array $array Two-dimensional array to be padded.
     * @return array Padded array.
     */
    public function pad_array_to_max_cols( array $array ) {
        $rows = count( $array );
        $rows = ( $rows > 0 ) ? $rows : 1;
        $max_columns = $this->count_max_columns( $array );
        $max_columns = ( $max_columns > 0 ) ? $max_columns : 1;
        // array_map wants arrays as additional parameters, so we create one with the max_columns to pad to and one with the value to use (empty string).
        $max_columns_array = array_fill( 1, $rows, $max_columns );
        $pad_values_array = array_fill( 1, $rows, '' );
        return array_map( 'array_pad', $array, $max_columns_array, $pad_values_array );
    }

    /**
     * Get the highest number of columns in the rows.
     *
     * @since 1.0.0
     *
     * @param array $array Two-dimensional array.
     * @return int Highest number of columns in the rows of the array.
     */
    protected function count_max_columns( array $array ) {
        $max_columns = 0;
        foreach ( $array as $row_idx => $row ) {
            $num_columns = count( $row );
            $max_columns = max( $num_columns, $max_columns );
        }
        return $max_columns;
    }

    /**
     * Fixes the encoding to UTF-8 for the entire string that is to be imported.
     *
     * @since 1.0.0
     *
     * @link http://stevephillips.me/blog/dealing-php-and-character-encoding
     */
    protected function fix_table_encoding() {
        // Check and remove possible UTF-8 Byte-Order Mark (BOM).
        $bom = pack( 'CCC', 0xef, 0xbb, 0xbf );
        if ( 0 === strncmp( $this->import_data, $bom, 3 ) ) {
            $this->import_data = substr( $this->import_data, 3 );
            // If data has a BOM, it's UTF-8, so further checks unnecessary.
            return;
        }

        // Require the iconv() function for the following checks.
        if ( ! function_exists( 'iconv' ) ) {
            return;
        }

        // Check for possible UTF-16 BOMs ("little endian" and "big endian") and try to convert the data to UTF-8.
        if ( "\xFF\xFE" === substr( $this->import_data, 0, 2 ) || "\xFE\xFF" === substr( $this->import_data, 0, 2 ) ) {
            $data = @iconv( 'UTF-16', 'UTF-8', $this->import_data );
            if ( false !== $data ) {
                $this->import_data = $data;
                return;
            }
        }

        // Detect the character encoding and convert to UTF-8, if it's different.
        if ( function_exists( 'mb_detect_encoding' ) ) {
            $current_encoding = mb_detect_encoding( $this->import_data, 'ASCII, UTF-8, ISO-8859-1' );
            if ( 'UTF-8' !== $current_encoding ) {
                $data = @iconv( $current_encoding, 'UTF-8', $this->import_data );
                if ( false !== $data ) {
                    $this->import_data = $data;
                    return;
                }
            }
        }
    }

} 


endif; // Ends class ATTC_import condition
