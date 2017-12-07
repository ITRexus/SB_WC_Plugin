<?php
/** Loads the WordPress Environment and Template */
ob_start();
define('WP_USE_THEMES', false);
require_once('/home/strongbo/public_html/haws2/wp-blog-header.php');
get_header();
ob_end_clean();
//add_action('plugins_loaded', 'add_variation');

/*function add_variation(){
	$quantity 		= $_GET['quantity'];
	$product_id 	= $_GET['upc'];
	if($quantity > 0){
		ob_start();
		$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_GET['upc'] ) );
		$quantity = empty( $_GET['quantity'] ) ? 1 : apply_filters( 'woocommerce_stock_amount', $_GET['quantity'] );
		$variation_id = $_GET['variation_id'];
		$variation  = $_GET['variation'];
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		$variations = array();
		$variations["attribute_pa_style"] = $_GET["style"];
		$variations["attribute_pa_size"] = $_GET["size"];
		$variations["attribute_pa_dimension"] = $_GET["dimension"];
		if ( WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations  ) ) {
			do_action( 'woocommerce_ajax_added_to_cart', $product_id );
			if ( get_option( 'woocommerce_cart_redirect_after_add' ) == 'yes' ) {
				wc_add_to_cart_message( $product_id );
			}
	
			// Return fragments
			WC_AJAX::get_refreshed_fragments();
		} else {
			$this->json_headers();
	
			// If there was an error adding to the cart, redirect to the product page to show any errors
			$data = array(
				'error' => true,
				'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
				);
			echo json_encode( $data );
		}
		die();
	}
	echo "Quantity: $quantity, ID: $product_id, Variation ID: $variation_id, Variation: $variation";
}
*/

$formData = $_REQUEST['formData'];
ob_start();
foreach($formData as $input){
	if($input['value'] > 0){
		$txt = explode('-', $input['name']);
		$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $txt[3] ) );
		$quantity = empty( $input['value'] ) ? 1 : apply_filters( 'woocommerce_stock_amount', $input['value'] );
		$variation_id = $txt[4];
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		$variations = array();
		$variations["attribute_pa_style"] = $txt[5];
		$variations["attribute_pa_size"] = $txt[7];
		$variations["attribute_pa_dimension"] = $txt[6];
		if ( WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations  ) ) {
			do_action( 'woocommerce_ajax_added_to_cart', $product_id );
		} else {
			$this->json_headers();

			// If there was an error adding to the cart, redirect to the product page to show any errors
			$data = array(
				'error' => true,
				'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
				);
			echo json_encode( $data );
		}
		
	}
	//echo "Quantity: $quantity, ID: $product_id, Variation ID: $variation_id, Variation: $variation";
}
ob_end_clean();
?>