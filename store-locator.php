<?php 
/**
 * Plugin Name: Store Locator 
 * Plugin URI: 
 * Version: 0.0.1 
 * Author: CBM Web Development
 * License: GPLv2 
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: store_locator 
 */
 
 define( 'STORE_LOCATOR_URI', plugin_dir_url( __FILE__ ));
 define( 'STORE_LOCATOR_PATH', plugin_dir_path( __FILE__ ));
 define( 'SITE_URL', get_site_url() );
 define( 'STORE_LOCATOR_TEXTDOMAIN', 'store_locator');
 define( 'STORE_LOCATOR_VERSION', '0.0.1');
 define ( 'MAPS_API_KEY', 'AIzaSyDld488a9g_-3n1q9Z9ZI43Ijb_6H-U2rk');
 
 include (STORE_LOCATOR_PATH . '/includes/class-store-locator.php');
 
 include (STORE_LOCATOR_PATH . '/blocks/locator-block/locator-block.php');
 
 // Include ACF fields
 foreach(glob(STORE_LOCATOR_PATH . '/includes/acf/groups/*_active.php') as $file ){
     include($file);
 }