<?php
//Get the variation id, download all variations with the related sku, and populate the product grid with quantities.

//SKU = select meta_value from wp_postmeta where post_id="PRODUCT_ID" AND meta_key="_sku";
//VARIATIONS = select post_id from wp_postmeta where meta_value="SKU" and meta_key="_sku";
//QUANTITY = select meta_value from wp_postmeta where meta_key="_stock" and post_id="VARIATION";
require_once('/home/strongbo/public_html/haws2/wp-load.php');
ini_set("auto_detect_line_endings", true);
if(!empty($_REQUEST['diplay_option'])){
	$display_option=$_REQUEST['display_option'];
}else{
	$display_option='each';
}
$columnwidth = '65 px';
$product_id = $_POST['product_id'];
$colorcodeFile = fopen('/home/strongbo/public_html/haws2/wp-content/plugins/itrexus_woocommerce/colorcodes.csv', "r");
$colorcodes = array();
 while (($data = fgetcsv($colorcodeFile, 1600, ",")) !== FALSE) {
        $colorcodes[strtoupper($data[0])] = ucwords($data[1]);
    }
?>
<?php if(is_user_logged_in()){ ?>
<input class="button" id="btnGridAddToCart" value="Add To Cart" type="submit"></input>
<?php } ?>
<form id="GridOrder">
<?php
if ($product_id){
	$variationsArr = array();
	$sizesArr = array();
	$lengthsArr = array();
	$stylesArr = array();
	$sku = strtoupper(get_meta($product_id, "sku"));
	$variations = $wpdb->get_results( 'SELECT post_id,meta_value FROM '.$wpdb->prefix.'postmeta where UPPER(meta_value) LIKE "'.$sku.'%" AND meta_key="attribute_pa_style" ORDER BY meta_value ASC;' );
	foreach ( $variations as $variation )
	{	
		$post_id=$variation->post_id;
		$size = get_meta($post_id, "attribute_pa_size");
		$style = strtoupper(get_meta($post_id, "attribute_pa_style"));
		$length = get_meta($post_id, "attribute_pa_dimension");
		$upc = get_meta($post_id, "upc");
		$stock = get_meta($post_id, "stock");
		$date = get_meta($post_id, "availability_date");
		$sizesArr[] = strtoupper($size);
		$lengthsArr[] = strtoupper($length);
		$stylesArr[] = $style;
		$variationsArr[$post_id] = array(
			'size' => strtoupper($size),
			'style' => $style,
			'length' => strtoupper($length),
			'stock' => $stock,
			'date' => $date,
			'upc' => $upc,
			'variation' => $variation_id,
			'variation_id' => $post_id,
		);
	}
$sizesArr = array_unique($sizesArr);
$lengthsArr = array_unique($lengthsArr);
$stylesArr = array_unique($stylesArr);
usort($sizesArr, "cmpsize");
usort($lengthsArr, "cmpsize");
usort($variationsArr, "cmp");
//	WRITE THE TABLE
//echo "<p>".print_r($sizesArr)."</p>";
	if($display_option == 'each'){ ?>

	<?php foreach($stylesArr as $style) : ?>
		<?php $i=0; ?>
		<div class="rowh">
			<div class="colorh" data-style="<?php echo $style; ?>">
				Color
			</div>
			<?php foreach($sizesArr as $size) : ?>
				<div class="itemh">
					<?php echo $size=='TLL' ? 'TALL' : $size; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="rowc">
			<?php foreach($lengthsArr as $length) : ?>
				<div class="itemc">
					<?php echo $length=='TLL' ? 'TALL' : $length; ?>
				</div>
			<?php endforeach; ?>
		</div>
			<div class="r">
				<div class="icolor" data-style="<?php echo $style; ?>">
					<img style="background-image: url(<?php echo 'http://strongboxui.com/haws2/pix.php?f='.$style.'_SW'?>)"></img>
					<span><?php echo $colorcodes[substr($style,-3,3)];?></span>
				</div>
				<?php
				foreach($lengthsArr as $length) : ?>
					<div class="sep r<?php echo $i%2; $i++;?>">
						<div class="pricestock">
                        <?php/*
							<span>Price</span>
                          */?>
                        <span>Inventory</span>
						</div>
						
					<?php
					/*
						| 2589763 | 70702   | _bulkdiscount_discount_2 | 5.6        |
						| 2589762 | 70702   | _bulkdiscount_quantity_2 | 48         |
						| 2589761 | 70702   | _bulkdiscount_discount_1 | 4          |
						| 2589760 | 70702   | _bulkdiscount_quantity_1 | 7 				*/
					foreach($sizesArr as $size) :
						foreach ($variationsArr as $variation ) :
							if( $variation['style'] == $style && $variation['size'] == $size && $variation['length'] == $length ) : ?>
								<div class="i">
								<?php /*	<span class="price"><?php $product = wc_get_product( $variation['variation_id'] ); echo $product->get_price(); ?><a>0-7: <span>$<?php echo $product->get_price()?></span><br> 8-48: <span>$<?php echo $product->get_price() * .96?></span><br>49+ <span>$<?php echo $product->get_price() * .944?></span></a></span> */?>
									<span class="inventory">
									<?php
										if($variation['stock'] > 0){
											echo $variation['stock'];
											?>
									</span>
                                    <?php if(is_user_logged_in()){ ?>
									<span class="orderquantity">
										<input type="text" autocomplete="off" class="quan" style="width: 85%;" id="<?php echo $variation['variation_id'];?>" name="<?php echo $variation['variation_id'] . '-' . $variation['stock'] . '-' . $variation['date'] . '-' . $variation['upc'] . '-' . $variation['variation_id'] . '-' . $variation['style'] . '-' . $variation['length'] . '-' . $variation['size'];?>"></input>
									</span>
                                    <?php } ?>
									<?php
										} elseif($variation['stock'] == '') {
											?>
											<span class="orderquantity">&nbsp;</span>
											<?php
										} elseif($variation['stock'] == 0){
											$dateAvailable = $variation["date"];
											$date=substr($dateAvailable,4,2) . '/' .  substr($dateAvailable,6,2) . '/<wbr>' .  substr($dateAvailable,0,4);
											echo '<span class="alert">0<a>This item is currently out of stock and will be available '. $date .'</a></span>';
											?>
									</span>
									<span class="orderquantity">
										<input type="text" autocomplete="off" class="quan" style="width: 85%;" id="<?php echo $variation['variation_id'];?>" name="<?php echo $variation['variation_id'] . '-' . $variation['stock'] . '-' . $variation['date'] . '-' . $variation['upc'] . '-' . $variation['variation_id'] . '-' . $variation['style'] . '-' . $variation['length'] . '-' . $variation['size'];?>"></input>
									</span>
									<?php
										}
									?>
								</div>
                                <div class="vertsep"></div>
							<?php endif;
						endforeach;
					endforeach;?>
					</div>
				<?php endforeach; 
				 ?>
			</div>
		</div>
	<?php endforeach; ?>
</form>
<?php
	}
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
}

if (! function_exists('get_meta') ){
	function get_meta($post_id, $meta_id){
		global $wpdb;
		if(substr($meta_id,0,12) != "attribute_pa"){ $meta_id = "_" . $meta_id; }
		return $wpdb->get_results( 'SELECT meta_value FROM '.$wpdb->prefix.'postmeta where meta_key="'.$meta_id.'" AND post_id="'.$post_id.'";', ARRAY_N )[0][0];
	}
}

function cmp($a, $b){
	//$a and $b are populated by the $variationsArr arrays keys
	$sizes = array(
		"XXS" => 0,
		"2XS" => 0,
		"XS" => 1,
		"XSM" => 1,
		"S" => 2,
		"SML" => 2,
		"M" => 3,
		"MED" => 3,
		"L" => 4,
		"LRG" => 4,
		"XLG" => 5,
		"XL" => 5,
		"XXL" => 6,
		"2XL" => 6,
		"3XL" => 7,
		"4XL" => 8,
		"5XL" => 9,
	);
	if(is_numeric($a["size"])){
		 if($a["size"] < $b["size"]){ return -1; }
		 if($a["size"] > $b["size"]){ return 1; }
	} else {
		$asize = $sizes[$a["size"]];
		$bsize = $sizes[$b["size"]];
		if($asize < $bsize){ return -1; }
		if($asize > $bsize){ return 1; }
	}
	if($a["length"] == $b["length"]){ return 0; }
	return($a["length"] > $b["length"]) ? 1 : -1;
}

function cmpsize($a, $b){
	//$a and $b are populated by the $variationsArr arrays keys
	$sizes = array(
		"XXS" => 0,
		"2XS" => 0,
		"SHT" => 0,
		"XS" => 1,
		"XSM" => 1,
		"S" => 2,
		"SML" => 2,
		"M" => 3,
		"MED" => 3,
		"REG" => 3,
		"L" => 4,
		"TLL" => 4,
		"LRG" => 4,
		"XLG" => 5,
		"XL" => 5,
		"XXL" => 6,
		"2XL" => 6,
		"3XL" => 7,
		"4XL" => 8,
		"5XL" => 9,
	);
	if(is_numeric($a)){
		if($a == $b){ return 0; }
		if($a < $b){ return -1; }
		if($a > $b){ return 1; }
	} else {
		$asize = $sizes[strtoupper($a)];
		$bsize = $sizes[strtoupper($b)];
		if($asize == $bsize){ return 0; }
		if($asize < $bsize){ return -1; }
		if($asize > $bsize){ return 1; }
	}
}
?>