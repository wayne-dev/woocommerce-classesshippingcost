<?php
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'Sadec_Product_Shipping' ) ) {
	class Sadec_Product_Shipping {
		protected static $_instance = null;
		public function __construct() {
			if ( ! isset( $_SESSION ) ){
				session_start();
			}				
			add_action( 'woocommerce_shipping_methods', array( $this, 'add_sadeconline_shipping_method' ), 10, 2 );
			add_action( 'woocommerce_after_shipping_rate', array( $this, 'cart_shipping_info' ), 10, 2 );
		}
		public function cart_shipping_info( $method, $index ){
			if ( $method->id == 'classes_shipping' && isset($_SESSION['sadeconline_info_cart']) ) {
				$info_cart = ($_SESSION['sadeconline_info_cart']);
				$html = '';	
				ob_start();
				?>
				<div class = 'sd_cuntom_shipping <?php echo $method->id;?>_shipping'>
						<?php foreach($info_cart as $info){ 
								$shipping_cost = (!$info['shipping_cost'])? '<span class="woocommerce-Price-amount amount">'.__( 'Free!', 'sadeconline' ).'</span>' : wc_price($info['shipping_cost']) ;
						?>
							<h5 colspan = 2><?php echo $info['title']?></h5>
							<?php if($info['subtotal']) { ?>
						<p>
							<b><?php _e('Subtotal: ', 'sadeconline'); ?></b>
							<span class = 'shipping_price'><?php echo wc_price($info['subtotal'])?></span>
						</p>
						<?php } ?>
						<p>
							<b><?php _e('Shipping cost222: ', 'sadeconline'); ?></b>
							<span class = 'shipping_price'><?php echo $shipping_cost; ?></span>
						</p>
						<?php } ?>
				</div>
				<style>
				ul#shipping_method li .sd_cuntom_shipping{display:none}
				ul#shipping_method li input:checked ~ .sd_cuntom_shipping{display:block}
				.sd_cuntom_shipping p b {}
				</style>
				<?php					
				$html = ob_get_contents();
				ob_end_clean();					
				echo ($html);
			}						
		}
		public function add_sadeconline_shipping_method( $methods ) {
			$methods[] = 'sadeconline_Shipping_Method';
			return $methods;
		}
		public static function instance() {
			if ( is_null( Sadec_Product_Shipping::$_instance ) ) {
				Sadec_Product_Shipping::$_instance = new Sadec_Product_Shipping();
			}
			return Sadec_Product_Shipping::$_instance;
		}
	}
	Sadec_Product_Shipping::instance();
}
?>