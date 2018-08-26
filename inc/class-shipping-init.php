<?php
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'Sadec_Custom_Shipping' ) ) {
	class Sadec_Custom_Shipping {
		protected static $_instance = null;
		public function __construct() {
			add_action( 'woocommerce_shipping_init', array( $this, 'cart_shipping_info' ), 10, 2 );
			require_once('class-shipping-frontend.php');	
		}		
		public function cart_shipping_info() {
			require_once('class-shipping-method.php');
		}
	}
	new Sadec_Custom_Shipping();
}