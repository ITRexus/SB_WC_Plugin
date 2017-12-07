
jQuery( document ).ready(function( jQuery ) {
	getLabels();
	jQuery( document ).on( 'woocommerce_variation_select_change', '.variations_form', function() {
		getLabels();
	});
	jQuery( 'body' )
		.on( 'click', 'a.edit-order-item', function() {
			jQuery( this ).closest( 'table' ).find( '.meta' ).hide();
			return false;
		});
	jQuery( 'body' )
		.on( 'click', '.colorbox', function() {
			imgsrc = jQuery( this ).children( '.imgsrc' ).text();
			imgdiv = jQuery(".images");
			imghtml = '<a href="http://strongboxui.com/haws2/wp-content/uploads/2015/04/' + imgsrc + '.jpeg" itemprop="image" class="woocommerce-main-image zoom" title="' + imgsrc + '" data-rel="prettyPhoto"><img width="600" height="650" src="http://strongboxui.com/haws2/wp-content/uploads/2015/04/' + imgsrc + '.jpeg" class="attachment-shop_single wp-post-image" alt="' + imgsrc + '" title="' + imgsrc + '"></a>';
			imgdiv.html(imghtml);
			jQuery.ajax({
				type: 'POST',
				url: 'http://strongboxui.com/haws2/wp-content/plugins/itrexus_woocommerce/find_angles.php',
				data: {
					imgsrc: imgsrc,
				},
				datatype: 'json',
				success: function(json){
					response = JSON.parse(json);
					imgdiv.html(imgdiv.html() + response['html']);
				},
			});
		});
	jQuery( '.colorbox' ).first().click();
	jQuery( 'body' )
	.on( 'click', '.extra-angle', function() {
		imgsrc = jQuery(this).attr( 'src' );
		imgtitle = jQuery(this).attr( 'title' );
		imgdiv = jQuery(".images");
		var imghtml = '<a href="' + imgsrc + '" itemprop="image" class="woocommerce-main-image zoom" title="' + imgtitle + '" data-rel="prettyPhoto"><img width="600" height="650" src="' + imgsrc + '" class="attachment-shop_single wp-post-image" alt="' + imgtitle + '" title="' + imgtitle + '"></a>';
		jQuery.ajax({
			type: 'POST',
			url: 'http://strongboxui.com/haws2/wp-content/plugins/itrexus_woocommerce/find_angles.php',
			data: {
				imgsrc: imgtitle,
			},
			datatype: 'json',
			success: function(json){
				response = JSON.parse(json);
				imghtml = imghtml + response['html'];
				imgdiv.html(imghtml);
			},
		});
	});
    jQuery('.dropdown_category1').on('click', function(){
        window.location.replace(jQuery(this).attr('link'));
    });
    jQuery('.dropdown_category2').on('click', function(){
        window.location.replace(jQuery(this).attr('link'));
    });
    jQuery('.dropdown_category3').on('click', function(){
        window.location.replace(jQuery(this).attr('link'));
    });
	jQuery('#xlsxsubmit').on('click', function() {
	    var file_data = jQuery('#xlsxuploadfile').prop('files')[0];
		var form_data = new FormData();
		form_data.append('file', file_data);
		jQuery.ajax({
					url: '/addxlsx.php', // point to server-side PHP script 
					dataType: 'text',  // what to expect back from the PHP script, if anything
					cache: false,
					contentType: false,
					processData: false,
					data: form_data,                         
					type: 'post',
					success: function(php_script_response){
						alert(php_script_response); // display response from the PHP script, if any
					}
		 });
	});
});

function getLabels(){
	jQuery.each(jQuery(".image-label"), function(){
		_lab = jQuery(this);
			_label = _lab;
			jQuery.ajax({
				type: 'POST',
				url: 'http://strongboxui.com/haws2/wp-content/plugins/itrexus_woocommerce/colorcodes.php',
				data: {
					id: _label.val(),
				},
				datatype: 'json',
				success: function(json){
					response = JSON.parse(json);
					label = jQuery(".image-label[value='" + response['id'] + "']");
					label.html(response['html']);
					label.css({
						width: '50px',
						height: 'auto',
					});
				},
			});
	});
}