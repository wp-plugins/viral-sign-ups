<?php

# General
define( 'VSU_VERSION', '1.0.3' ); // plugin version
define( 'VSU_WEBSITE', 'http://viralsignups.com/' ); // Website URL
define( 'VSU_EXTEND_FREE_MEMBERSHIP_URL', 'http://www.viralsignups.com/pricing/' ); // Link to more info about free membership options
define( 'VSU_MY_ACCOUNT_URL', 'https://www.viralsignups.com/myaccount/' ); // Link to My Account page
define( 'VSU_UPGRADE_MEMBERSHIP_URL', 'http://www.viralsignups.com/pricing/' ); // Link to My Account page
# Directories
define( 'VSU_PLUGIN_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) ); // plugin uri
define( 'VSU_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) ); // plugin directory
define( 'VSU_LIB_DIR', VSU_PLUGIN_DIR . 'lib/' ); // directory to library classes
define( 'VSU_INC_DIR', VSU_PLUGIN_DIR . 'inc/' ); // directory to included files
define( 'VSU_ASSETS_URI', VSU_PLUGIN_URI . 'assets/' ); // uri to general assets
define( 'VSU_ASSETS_ADMIN_URI', VSU_ASSETS_URI . 'admin/' ); // uri to assets used in admin screen