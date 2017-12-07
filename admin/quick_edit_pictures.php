    <?php
include_once( __DIR__ . '/../../../../wp-load.php' );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../../../../' ); // Exit if accessed directly
}
include_once( __DIR__ . '/../../../../wp-admin/includes/template.php' );
$post_id = $_POST['post_id'];
$carharttdir = 'http://s7d9.scene7.com/is/image/Carhartt/';
$notfoundImage = '/home/strongbo/public_html/haws2/wp-content/plugins/itrexus_woocommerce/notfound.jpg';

$angles = array( '', '_AVB', '_AVB2', '_AVR', '_AVL', '_AVI' );

$angleImages = array(
		'html' => '',
	);

ob_start();
add_meta_box( 'woocommerce-product-images', __( 'Product Gallery', 'woocommerce' ), 'WC_Meta_Box_Product_Images::output', 'product', 'side' );
$angleImages['html'] = ob_get_clean();
echo json_encode($angleImages);


/*
ob_start();
?>
<div id="product_images_container" class="<?php echo $post_id; ?>">
    <ul class="product_images">
        <?php
            if ( metadata_exists( 'post', $post_id, '_product_image_gallery' ) ) {
                $product_image_gallery = get_post_meta( $post_id, '_product_image_gallery', true );
            } else {
                // Backwards compat
                $attachment_ids = get_posts( 'post_parent=' . $post_id . '&numberposts=-1&post_type=attachment&orderby=menu_order&order=ASC&post_mime_type=image&fields=ids&meta_key=_woocommerce_exclude_image&meta_value=0' );
                $attachment_ids = array_diff( $attachment_ids, array( get_post_thumbnail_id() ) );
                $product_image_gallery = implode( ',', $attachment_ids );
            }

            $attachments = array_filter( explode( ',', $product_image_gallery ) );

            if ( $attachments ) {
                foreach ( $attachments as $attachment_id ) {
                    echo '<li class="image" data-attachment_id="' . esc_attr( $attachment_id ) . '">
                        ' . wp_get_attachment_image( $attachment_id, 'thumbnail' ) . '
                        <ul class="actions">
                            <li><a href="#" class="delete tips" data-tip="' . __( 'Delete image', 'woocommerce' ) . '">' . __( 'Delete', 'woocommerce' ) . '</a></li>
                        </ul>
                    </li>';
                }
            }
        ?>
    </ul>

    <input type="hidden" id="product_image_gallery" name="product_image_gallery" value="<?php echo esc_attr( $product_image_gallery ); ?>" />

</div>
<p class="add_product_images hide-if-no-js">
    <a href="#" data-choose="<?php _e( 'Add Images to Product Gallery', 'woocommerce' ); ?>" data-update="<?php _e( 'Add to gallery', 'woocommerce' ); ?>" data-delete="<?php _e( 'Delete image', 'woocommerce' ); ?>" data-text="<?php _e( 'Delete', 'woocommerce' ); ?>"><?php _e( 'Add product gallery images', 'woocommerce' ); ?></a>
</p>
<?php
echo print_scripts;
$angleImages['html'] = ob_get_clean();
echo json_encode($angleImages);
    
foreach ( $angles as $var ){
	if( ! files_identical($notfoundImage, $carharttdir . $style . $var)){
		$angleImages['html'] .= '<img src="'.$carharttdir . $style . $var.'" alt="' . $style . '" title="' . $style . '" style="width: 75px; height: 85px;" class="extra-angle">';
	}
}
//echo json_encode($angleImages);


    $params = array(
        'post_id'                             => isset( $post->ID ) ? $post->ID : '',
        'plugin_url'                          => WC()->plugin_url(),
        'ajax_url'                            => admin_url('admin-ajax.php'),
        'woocommerce_placeholder_img_src'     => wc_placeholder_img_src(),
        'add_variation_nonce'                 => wp_create_nonce("add-variation"),
        'link_variation_nonce'                => wp_create_nonce("link-variations"),
        'delete_variation_nonce'              => wp_create_nonce("delete-variation"),
        'delete_variations_nonce'             => wp_create_nonce("delete-variations"),
        'i18n_link_all_variations'            => esc_js( __( 'Are you sure you want to link all variations? This will create a new variation for each and every possible combination of variation attributes (max 50 per run).', 'woocommerce' ) ),
        'i18n_enter_a_value'                  => esc_js( __( 'Enter a value', 'woocommerce' ) ),
        'i18n_enter_a_value_fixed_or_percent' => esc_js( __( 'Enter a value (fixed or %)', 'woocommerce' ) ),
        'i18n_delete_all_variations'          => esc_js( __( 'Are you sure you want to delete all variations? This cannot be undone.', 'woocommerce' ) ),
        'i18n_last_warning'                   => esc_js( __( 'Last warning, are you sure?', 'woocommerce' ) ),
        'i18n_choose_image'                   => esc_js( __( 'Choose an image', 'woocommerce' ) ),
        'i18n_set_image'                      => esc_js( __( 'Set variation image', 'woocommerce' ) ),
        'i18n_variation_added'                => esc_js( __( "variation added", 'woocommerce' ) ),
        'i18n_variations_added'               => esc_js( __( "variations added", 'woocommerce' ) ),
        'i18n_no_variations_added'            => esc_js( __( "No variations added", 'woocommerce' ) ),
        'i18n_remove_variation'               => esc_js( __( 'Are you sure you want to remove this variation?', 'woocommerce' ) )
    );

    wp_localize_script( 'wc-admin-variation-meta-boxes', 'woocommerce_admin_meta_boxes_variations', $params );

//Compare 2 files
function files_identical($fn1, $fn2) {
	$READ_LEN = 4096;

	if(!$fp1 = fopen($fn1, 'rb'))
		return FALSE;

	if(!$fp2 = fopen($fn2, 'rb')) {
		fclose($fp1);
		return FALSE;
	}

	$same = TRUE;
	while (!feof($fp1) and !feof($fp2))
		if(fread($fp1, $READ_LEN) !== fread($fp2, $READ_LEN)) {
			$same = FALSE;
			break;
		}

	if(feof($fp1) !== feof($fp2))
		$same = FALSE;

	fclose($fp1);
	fclose($fp2);

	return $same;
}
*/
?>