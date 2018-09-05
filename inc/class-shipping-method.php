<?php
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'sadeconline_Shipping_Method' ) ) {
	class sadeconline_Shipping_Method extends WC_Shipping_Method {

		public function __construct() {
			$this->id                 = 'classes_shipping'; 
			$this->method_title       = __( 'Classes shipping', 'sadeconline' );  
			$this->method_description = __( 'Classes shipping Method for sadeconline', 'sadeconline' ); 

			$this->init();

			$this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
			$this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'sadeconline Shipping', 'sadeconline' );
		}

		function init() {
			add_filter( 'woocommerce_cart_shipping_method_full_label', array( $this, 'cart_price_label' ), 10, 2 );
			$this->init_form_fields(); 
			$this->init_settings(); 

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		function init_form_fields() { 
			$shipping_classes = get_terms( array('taxonomy' => 'product_shipping_class', 'hide_empty' => false ) );

			$form_fields = array(

			 'enabled' => array(
				  'title' => __( 'Enable', 'sadeconline' ),
				  'label' => __( 'Enable', 'sadeconline' ),
				  'type' => 'checkbox',
				  'description' => __( 'Enable this shipping.', 'sadeconline' ),
				  'default' => 'yes'
				  ),

			 'title' => array(
				'title' => __( 'Title', 'sadeconline' ),
				'type' => 'text',
				'description' => __( 'Title to be display on site', 'sadeconline' ),
				'default' => __( 'Custom Shipping', 'sadeconline' )
				  ),
			'group_shipping' => array(
				'title' => __( 'Enable', 'sadeconline' ),
				'label' => __( 'Group all items same shipping class in a package', 'sadeconline' ),
				'type' => 'checkbox',
				  ),
			'min_amount' => array(
				'title' => __( 'Min amout ', 'sadeconline' ). "(".get_woocommerce_currency_symbol().")",
				'type' => 'price',
				  ),
			'cost_others' => array(
				'title' => __( 'Shipping cost normal ', 'sadeconline' ). "(".get_woocommerce_currency_symbol().")",
				'type' => 'price',
				  ),
			'title_1' => array(
				'title' => __( 'Shipping class cost ', 'sadeconline' ) . "(".get_woocommerce_currency_symbol().")",
				'type' => 'title',
				  ),
			  );
			foreach($shipping_classes as $class){
				$form_fields[$class->slug] = array(
					'title' => $class->name,
					'type' => 'price'
				  );
			}
			$this->form_fields =  $form_fields; 

		}

		public function calculate_shipping( $package = array() ) {
			$group_shipping = $this->settings['group_shipping'];
			if(isset($group_shipping) && $group_shipping == 'yes')
				$session_incart = $this->get_session_incart_group($package);
			else
				$session_incart = $this->get_session_incart_single($package);
			$_SESSION['sadeconline_info_cart'] = $session_incart;
		}

		public function get_session_incart_single( $package ) {
			$cost = 0;
			$min_amount = $this->settings['min_amount'];
			$cost_others = $this->settings['cost_others'];
			
			$shipping_classes = get_terms( array('taxonomy' => 'product_shipping_class', 'hide_empty' => false ) );
			$shipping_classes_cost = array();
			foreach($shipping_classes as $class){
				$shipping_classes_cost[$class->slug] = $this->settings[$class->slug];
			}
			$_sd_products = array();
			$session_incart = array() ;
			if ( sizeof( $package['contents'] ) > 0 ) {	
				foreach ( $package['contents'] as $item_id => $values ) 
				{
					if ( $values['quantity'] > 0 && $values['data']->needs_shipping()) {								
						$_product = wc_get_product($values['data']->get_id());
						$shipping_class_id = $_product->get_shipping_class_id();
						$shipping_class = get_term($shipping_class_id,'product_shipping_class');
						if(isset($shipping_classes_cost[$shipping_class->slug])){
							$title = sprintf(__( 'Class %s', 'sadeconline' ),$shipping_class->name);
							$shipping_cost_sub = $shipping_classes_cost[$shipping_class->slug];
						}else{
							$title = __( 'Normal shipping', 'sadeconline' );
							$shipping_cost_sub = $cost_others ;
						}
						$session_incart[$item_id] = array(
							'title' =>  $title,
							'subtotal' => '' ,
							'shipping_cost' => $shipping_cost_sub
						);
						$cost += $shipping_cost_sub;

					}
				}
				$rate = array(
					'id' => $this->id,
					'label' => $this->title,
					'cost' => $cost
				);

				$this->add_rate( $rate );
			}
			return $session_incart;			
		}

		public function get_session_incart_group( $package ) {
			$cost = 0;
			$min_amount = $this->settings['min_amount'];
			$cost_others = $this->settings['cost_others'];
			
			$shipping_classes = get_terms( array('taxonomy' => 'product_shipping_class', 'hide_empty' => false ) );
			$shipping_classes_cost = array();
			foreach($shipping_classes as $class){
				$shipping_classes_cost[$class->slug] = $this->settings[$class->slug];
			}
			$_sd_products = array();
			if ( sizeof( $package['contents'] ) > 0 ) {	
				foreach ( $package['contents'] as $item_id => $values ) 
				{
					
					if ( $values['quantity'] > 0 && $values['data']->needs_shipping()) {								
						$_product = wc_get_product($values['data']->get_id());
						$shipping_class_id = $_product->get_shipping_class_id();
						$shipping_class = get_term($shipping_class_id,'product_shipping_class');
						
						$product_data = array(
								'line_total' => $values['line_total'],
								'price' => $_product->get_price(),
								'qty' => $values['quantity'],
								'id' => $_product->get_id()
							);			
						if(! empty( $shipping_class ) && ! is_wp_error( $shipping_class )){
							$key = $shipping_class->slug;
						}else{
							$key = 'others' ;
						}			
						$_sd_products[$key][] = $product_data;
					}
				}

				$session_incart = array() ;
				$tmp = $_sd_products['others'];
				unset ($_sd_products['others']) ;
				ksort($_sd_products);
				$_sd_products['others'] = $tmp;
				foreach($_sd_products as $cat_id => $group_pro){
					if (is_array($group_pro) || is_object($group_pro)){
						$subtotal = $this->get_subtotal($group_pro);
						$shipping_cost_sub = 0 ;
						$shipping_class = get_term_by( "slug", $cat_id, "product_shipping_class" );
						
						if(! empty( $shipping_class ) && ! is_wp_error( $shipping_class )){
							$title = sprintf(__( 'Class %s', 'sadeconline' ),$shipping_class->name);
							$shipping_cost_sub = $shipping_classes_cost[$cat_id];
						}else{
							$title = __( 'Normal shipping', 'sadeconline' );
							$shipping_cost_sub = $cost_others;
						}
						$shipping_cost_sub = ($subtotal < $min_amount)? $shipping_cost_sub : 0 ;
						$session_incart[$cat_id] = array(
							'title' =>  $title,
							'subtotal' => $subtotal ,
							'shipping_cost' => $shipping_cost_sub
						);
						$cost += $shipping_cost_sub;
					}
				}
				$rate = array(
					'id' => $this->id,
					'label' => $this->title,
					'cost' => $cost
				);

				$this->add_rate( $rate );
			}
			return $session_incart;
		}

		public function get_subtotal( $products ) {
			$sub_total = 0;
			foreach($products as $pro){
				$sub_total += $pro['line_total']  ;
			}
			return $sub_total;
		}
		
		public function cart_price_label( $label, $method ) {
			if ( !(int)$method->cost )  {
				$label =str_replace(__( 'Free!', 'sadeconline' ), '0.00', $label);
			} 
			return $label;
		}
	}
	new sadeconline_Shipping_Method();
}
?>