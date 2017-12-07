<div style="margin: 10px;">
<table style="width: 100%; margin-top: 5px;" border="0" id="yo">
	<tr>
		<td>
			<button onclick="PrintPO(jQuery(this).parents('.showdocChild').html());return false;" class="ITR_PrintButton deleteMe">Print</button>
		</td>
	</tr>
	<tr>
		<?php $date1=FindSegment('BSN', 3);
				$dtmsegs=FindSegmentPos('DTM', 1);
				$FOBseg=FindSegmentPos('FOB', 1); ?>
		<td style="text-align: left; vertical-align: top;">
			<div class="ITR_HDR">Advance Ship Notice: <?php echo FindSegment('BSN', 2) ?></div>
			<table class="ITR_HDRLine">
				<?php for($a=$dtmsegs; $a<$totalLines; $a++) {
							if (${"_".$a}[0]=="DTM"){
								echo "<tr><td>".FindQual(${"_".$a}[1],"DTM01") . ": </td><td>" . substr(${"_".$a}[2],4,2)."/".substr(${"_".$a}[2],6,2)."/".substr(${"_".$a}[2],0,4) . "</td></tr>";
							}else {
								break;
							}
						}
						if(${"_".$FOBseg}[0]=="FOB") {
								echo "<tr><td>FOB: </td><td>" . FindQual(${"_".$FOBseg}[1],"FOB01") . "</td></tr>";
						}
				 ?>
			</table>
		</td>
	</tr>
</table>
<?php $BT=FindSegmentPos('N1', 2, 1, 'SF', $e); ?>
<table class="ITR_STHDR_table" style="float: right;">
  <tbody>
    <tr>
      <td class="ITR_STHDR">Ship From</td>
    </tr>
    <tr>
      <td class="ITR_STLine"><?php echo FindSegment('N1', 2, 1, 'SF', $e); ?></td>
    </tr>
    <tr>
      <td class="ITR_STLine"><?php echo ${"_".($BT+1)}[1]; ?></td>
    </tr>
    <tr>
      <td class="ITR_STLine"><?php echo ${"_".($BT+2)}[1].", ".${"_".($BT+2)}[2].", ".${"_".($BT+2)}[3].", ".${"_".($BT+2)}[4]; ?></td>
    </tr>
  </tbody>
</table>

<?php $ST=FindSegmentPos('N1', 2, 1, 'ST', $e); ?>
<table class="ITR_STHDR_table">
  <tbody>
    <tr>
      <td class="ITR_STHDR">Ship To</td>
    </tr>
    <tr>
      <td class="ITR_STLine"><?php echo FindSegment('N1', 2, 1, 'ST', $e); ?></td>
    </tr>
    <tr>
      <td class="ITR_STLine"><?php echo ${"_".($ST+1)}[1]; ?></td>
    </tr>
    <tr>
      <td class="ITR_STLine"><?php echo ${"_".($ST+2)}[1].", ".${"_".($ST+2)}[2].", ".${"_".($ST+2)}[3]; ?></td>
    </tr>
  </tbody>
</table>
<div style="margin-top: 25px;">
<? $packNum = 0;
for($x=1;$x<$totalLines;$x++){
	if (${"_"."$x"}[0] == "HL" && ${"_"."$x"}[3] == "P"){ 
	$packNum++;	
	$carrier = carrierLookup(FindSegment("TD5", 3));
?>
	<h4 class="ITR_LineHDR" style="margin-bottom 5px;">Pack # <span id="PNum"><? echo $packNum ?></span> | Tracking Number:
		<a style="text-decoration: underline;" href="https://www.fedex.com/fedextrack/index.html?tracknumbers=<? echo FindSegment('MAN',2,0,'',$x) ?>&cntry_code=us"><? echo FindSegment('MAN',2,0,'',$x);?></a></h1>

	<table class="ITR_Line_table">
	  <tbody>
	    <tr>
		  <td class="ITR_LineHDR">Customer Part #</td>
		  <td class="ITR_LineHDR">Vendor Part #</td>
	  <!-- td class="ITR_LineHDR">Description</td -->
		  <td class="ITR_LineHDR">Quantity</td>
	    </tr>
	
	<?php $PO1Count=0;
	for ($c=$x; $c<$totalLines; $c++){
			if (${"_".$c}[0]=="HL" && ${"_".$c}[3]=="I"){
	echo		'<tr>';
	echo			'<td class="ITR_Line">'.FindSegment('LIN', 3, 2, 'UK', $c).'</td>';
	echo			'<td class="ITR_Line">'.FindSegment('LIN', 5, 4, 'UP', $c).'</td>';
//	echo			'<td class="ITR_Line"></td>';
	echo			'<td class="ITR_Line">'.FindSegment('SN1', 2, 0, '', $c).'</td>';
	echo			'</tr>';
			}
		}
	?>
	  </tbody>
	</table>
<? }}?>
</div>
</div>