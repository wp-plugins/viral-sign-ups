<?php
/*
Plugin Name: Viral Sign Ups | Free
Plugin URI: http://www.viralsignups.com
Author: ViralSignUps
Author URI: http://www.viralsignups.com
Description: Launch a Customer Referral Campaign in Minutes. Viral Sign Ups allows your customers to easily refer friends and earn incentives for referrals.
Version: 1.0 
License: GPLv2 or later
Text Domain: viralsignups
*/

/*  Copyright 2014  ViralSignUps support@viralsignups.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Configurations
require_once dirname( __FILE__ ) . '/config.php';
require_once VSU_LIB_DIR . 'class-vsu-manager.php';

// Setup Globals
global $vsu_manager, $vsu_settings, $vsu_api;
$vsu_manager = new VSU_Manager(); // main plugin manager
$vsu_settings = array(); // global storage for settings

// Load
$vsu_manager->library();

// Initialization
add_action( 'init', array( $vsu_manager, 'init' ) );

// Admin initialization
if ( is_admin() ) {
    global $vsu_admin_manager;
    $vsu_admin_manager = new VSU_Admin_Manager(); // admin manager
    add_action( 'init', array( $vsu_admin_manager, 'init' ) );
}

// Run
$vsu_manager->run();
