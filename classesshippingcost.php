<?php
/**
 * Plugin Name: Classes shipping cost
 * Plugin URI: http://sadeconline.com/
 * Description: Classes shipping cost Method for WooCommerce
 * Version: 1.0.1
 * Author: Wayne Nguyen
 * Author URI: http://sadeconline.com/
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: classesshippingcost
 * Text Domain: sadeconline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	require_once('inc/class-shipping-init.php');	
}
