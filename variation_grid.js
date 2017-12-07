jQuery( document ).ready(function() {
	var product_id = jQuery( 'div .product' ).attr( 'id' ).split('-')[1];
	jQuery.ajax({
		type: 'POST',
		url: 'http://strongboxui.com/haws2/wp-content/plugins/itrexus_woocommerce/variation_grid.php',
		data: {
			product_id: product_id,
		},
		success: function(html){
			jQuery( document ).find('.product-grid-view').html(html);
		}
	});
			
	jQuery( document ).on( 'click', '.gridOrderCell', function(e) {
		stock	= jQuery(this).attr("stock");
		date 	= jQuery(this).attr("date");
		posX 	= jQuery(this).position().left;
		posY 	= jQuery(this).position().top;
		popup	= jQuery("#gridOrderPopup");
		width	= popup.width();
		height	= popup.height();
		jQuery(popup).data("ordercell", this);
		popup.css({
					"display" 	:	"inline-block",
					"left"		:	posX,
					"top"		:	posY,
		});
		jQuery(popup).children("input").focus();
	});
	
	jQuery( document ).on( 'click', '.gridOrderClose', function(e) {
		popup	= jQuery("#gridOrderPopup");
		popup.css({
					"display" 	:	"none",
		});
		jQuery(popup).children("input").val('');
	});
	
	jQuery( document ).on( 'click', '.gridOrderSubmit', function(e) {
		popup	 		= jQuery("#gridOrderPopup");
		inputQuantity 	= jQuery(popup).children("input");
		quantity 		= jQuery(popup).children("input").val();
		gridOrderCell 	= jQuery(popup).data("ordercell");
		upc 			= jQuery(gridOrderCell).attr("upc");
		style 			= jQuery(gridOrderCell).attr("style");
		size 			= jQuery(gridOrderCell).attr("size");
		dimension		= jQuery(gridOrderCell).attr("dimension");
		variation		= jQuery(gridOrderCell).attr("variation");
		variation_id	= jQuery(gridOrderCell).attr("variation_id");
		jQuery.ajax({
			url		:	"http://strongboxui.com/haws2/wp-content/plugins/itrexus_woocommerce/variation_add_to_cart.php",
			data	:	{
				'quantity'		: quantity,
				'upc'			: upc,
				'style'			: style,
				'size'			: size,
				'dimension'		: dimension,
				'variation'		: variation,
				'variation_id'	: variation_id,
				},
			success	: function(e){
				jQuery(popup).children("input").val('');
				jQuery("#header-sidebar").load(location.href+" #header-sidebar>*","");
			},
		});
		popup.css({
					"display" 	:	"none",
		});
	});
	jQuery( document ).on( 'click', '#btnGridAddToCart', function(e) {
		jQuery( "#GridOrder" ).children().attr("disabled", "true");
		jQuery( "#btnGridAddToCart" ).attr("disabled", "true");
		formData	= jQuery( "#GridOrder" ).serializeArray();
		
		jQuery.ajax({
			type	: 'POST',
			url		:	"http://strongboxui.com/haws2/wp-content/plugins/itrexus_woocommerce/variation_add_to_cart.php",
			data	:	{
				'formData'	: formData,
				},
			success	: function(e){
				location.reload();
			},
		});
	});
	jQuery( document ).on( 'click', '.i', function(e) {
		jQuery( "input", this ).focus();
		
	});
});
