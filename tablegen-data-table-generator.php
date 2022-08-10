<?php
/**
Plugin Name: TableGen - Data Table Generator 
Plugin URI: https://exlac.com/
Description: A very powerful but easy-to-use plugin to create unlimited general tables or big data table in WordPress. You can also create big data table by importing a csv files or adding data manually. You will love to use this plugin for its elegant interface.
Version: 1.0.6
Author: Exlac
Author URI: https://exlac.com
License: GPLv2 or later
Text Domain: table-generator-by-aazztech
*/


/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2016 AazzTech.com.
*/

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );
if ( !defined( 'ATTC_BASE' ) ) { define( 'ATTC_BASE', plugin_basename( __FILE__ )); }

// Load plugin config
require_once 'config.php';
require_once 'includes/ATTC_helper_functions.php';
// main plugin class
require_once 'Main.php';

if ( class_exists( 'Table_generator_by_aazztech' ) ) { // Instantiate the plugin class
    global $ATTC; // short form of AazzTech Table Generator
    $ATTC = new Table_generator_by_aazztech;
    $ATTC->helper->check_req_php_version();
    $ATTC->warn_if_unsupported_wp();
    register_activation_hook(__FILE__, array($ATTC, 'prepare_plugin'));
    register_deactivation_hook(__FILE__, array('Table_generator_by_aazztech', 'remove_plugin_data'));
    $ATTC->init();
}