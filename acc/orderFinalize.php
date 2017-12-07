<?php
/* display a table with the address information in it. Give them the option of creating a new address. */
include('order.php');
include('address.php');
?>
<script type="text/javascript" >
function getAddressST(addrName){
	 username = "<?echo $session->username?>";
	$.ajax({
		url: "php/address.php",
		type: "POST",
		data: {
			subgetaddrst: 1,
			STAddr: addrName,
			username: username,
		},
		success: function(data){
			var AddrArray = $.parseJSON(data);
			$("#STName").html(AddrArray.Name);
			$("#STAddr1").html(AddrArray.Addr_Line1);
			$("#STAddr2").html(AddrArray.Addr_Line2);
			$("#STCityStateZip").html(AddrArray.City + ", " + AddrArray.State + ", " + AddrArray.Zip);
		},
	});
}
function getAddressBT(addrName){
	 username = "<?echo $session->username?>";
	$.ajax({
		url: "php/address.php",
		type: "POST",
		data: {
			subgetaddrbt: 1,
			BTAddr: addrName,
			username: username,
		},
		success: function(data){
			var AddrArray = $.parseJSON(data);
			$("#BTName").html(AddrArray.Name);
			$("#BTAddr1").html(AddrArray.Addr_Line1);
			$("#BTAddr2").html(AddrArray.Addr_Line2);
			$("#BTCityStateZip").html(AddrArray.City + ", " + AddrArray.State + ", " + AddrArray.Zip);
		},
	});
}
	function finalize(){
	 username = "<?echo $session->username?>";
	    $.ajax({
				url: 'php/orderProcess.php',
				type: 'POST',
				data: {
					subfinalizeorder: 1,
					BTAddr: $("#BTSelect").find(":selected").text(),
					STAddr: $("#STSelect").find(":selected").text(),
					username: username,
				},
				success: function (data) {
					$("#shoppingCart").trigger("click");
					alert("Order Successfully Sent");
					$("#popupClose").trigger("click");
				}
			});
			return false;
	}
$('#AddrDisplay').ready(function(){
	$.ajax({
		url: 'php/displayAddress.php',
		type: 'POST',
		data: {
			displayST: 1,
			sessionUse: 1,
		}, 
		success: function (data) {
			$("#ShipToTable").html(data);
			getAddressST($('#STSelect').find(":selected").text());
		}
	});
	$.ajax({
		url: 'php/displayAddress.php',
		type: 'POST',
		data: {
			displayBT: 1,
			sessionUse: 1,
		}, 
		success: function (data) {
			$("#BillToTable").html(data);
			getAddressBT($('#BTSelect').find(":selected").text());
		}
	});
	$('#AddrDisplay').on('change', '#STSelect', function(){
		if($('#STSelect').find(":selected").text() == "Add New..."){
			$.ajax({
				url: 'php/displayAddress.php',
				type: 'POST',
				data: {
					editST: 1,
					sessionUse: 1,
				}, 
				success: function (data) {
					$("#ShipToTable").html(data);
				}
			});
		}
	});
	$('#AddrDisplay').on('change', '#BTSelect', function(){
		if($('#BTSelect').find(":selected").text() == "Add New..."){
			$.ajax({
				url: 'php/displayAddress.php',
				type: 'POST',
				data: {
					editBT: 1,
					sessionUse: 1,
				}, 
				success: function (data) {
					$("#BillToTable").html(data);
				}
			});
		}
	});
});
</script>

<div class="center" style="display: block; width: 750px">

<div style="overflow: hidden;" id="AddrDisplay">
	<div id="ShipToTable" style="display: inline-table; float: left;"></div>
	<div id="BillToTable" style="display: inline-table; float: right;"></div>
</div>

<div style="position: relative; top: 35px;">
<table border="1px" cellpadding="5px" cellspacing="3px" style="margin: 35px;">
<tr style="font-weight: bold;">
	<td style="width: 15%"></td>
	<td>Name</td>
	<td>Product ID</td>
	<td>Description</td>
	<td>Price</td>
	<td>Quantity</td>
	<td>Total</td>
</tr>
<?php
$connection = mysql_connect('localhost', 'parts_admin', 'partsuserpass') or die(mysql_error());
      mysql_select_db('parts_catalog_db', $connection) or die(mysql_error());
	foreach( $order->itemList as $key => $value ){
		$query = 'select * from freshandeasy_parts_list where product_id = "'.$key.'";';
		$result = mysql_query($query, $connection);
		$name  = mysql_result($result,0,"product_name");
		$photo_dir  = mysql_result($result,0,"photo_directory");
		$description  = mysql_result($result,0,"product_description");
		$price  = mysql_result($result,0,"price_in_dollars");
		$default_image  = mysql_result($result,0,"default_image");
		$quantity  = $value;
		$ID  = $key;
		$total = $total + ($price * $quantity);
?>
		<tr>
			<td><img src="<? echo $default_image?>" alt="<? echo $name ?>" width="150px;"></td>
			<td><? echo $name ?></td>
			<td><? echo $ID ?></td>
			<td><textarea readonly style="resize: none; height: 100%; cursor: default;"><? echo $description ?></textarea></td>
			<td>$<? echo $price ?></td>
			<td><? echo $quantity ?></td>
			<td>$<? echo $quantity * $price ?></td>
		</tr>
<?php
	}
mysql_close($connection);
?>
	<tr>
		<td colspan="6"></td>
		<td>$<? echo $total ?></td>
	</tr>
</table>
</div>
<div style="float: right;"><button onclick="finalize();$(this).attr('disabled', 'disabled');">Submit Order</button></div>
</div>