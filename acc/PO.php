<div style="margin: 10px;">
<table style="width: 100%; margin-top: 5px;" border="0" id="yo">
	<tr>
		<td>
			<button onclick="PrintPO(jQuery(this).parents('.showdocChild').html());return false;" class="ITR_PrintButton deleteMe">Print</button>
		</td>
	</tr>
	<tr>
		<?php $date1=FindSegment('BEG', 5);
				$dtmsegs=FindSegmentPos('DTM', 1);
				$FOBseg=FindSegmentPos('FOB', 1); ?>
		<td style="text-align: left; vertical-align: top;">
			<div class="ITR_HDR">Purchase Order: <?php echo FindSegment('BEG', 3) ?></div>
			<table class="ITR_HDRLine">
			<tr><td>Purchase Order Date:</td><td> <?php echo substr($date1,4,2)."/".substr($date1,6,2)."/".substr($date1,0,4); ?></td></tr>
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
<br>

<br>
<?php $BT=FindSegmentPos('N1', 2, 1, 'BT', $e); ?>
<table class="ITR_STHDR_table" style="float: right;">
  <tbody>
    <tr>
      <td class="ITR_STHDR">Bill To</td>
    </tr>
    <tr>
      <td class="ITR_STLine"><?php echo FindSegment('N1', 2, 1, 'BT', $e); ?></td>
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

<table class="ITR_Line_table">
  <tbody>
    <tr>
      <td class="ITR_LineHDR">SKU</td>
      <td class="ITR_LineHDR">UPC</td>
  <!-- td class="ITR_LineHDR">Description</td -->
      <td class="ITR_LineHDR">Quantity</td>
      <td class="ITR_LineHDR">Unit Price</td>
      <td class="ITR_LineHDR">UoM</td>      
      <td class="ITR_LineHDR">Total</td>
    </tr>
<?php  for ($o=$e; $o<$totalLines; $o++){
		if (${"_".$o}[0]=="PO1"){
echo		'<tr>';
echo			'<td class="ITR_Line">'.FindSegment('PO1', 7, 6, 'UK', $o).'</td>';
echo			'<td class="ITR_Line">'.FindSegment('PO1', 9, 8, 'UP', $o).'</td>';
//echo			'<td class="ITR_Line">'
//					.FindSegment('PID', 5, 0, '', $o).'</td>';
echo			'<td class="ITR_Line">'.FindSegment('PO1', 2, 0, '', $o).'</td>';
echo			'<td class="ITR_Line">'.FindSegment('PO1', 4, 0, '', $o).'</td>';
echo			'<td class="ITR_Line">'.FindQual(FindSegment('PO1', 3, 0, '', $o),'PO103').'</td>';
echo			'<td class="ITR_Line">$'.(floatval(FindSegment('PO1', 2, 0, '', $o)) * floatval(FindSegment('PO1', 4, 0, '', $o))).'</td>';
echo		'</tr>';
		}
	}
?>
  </tbody>
</table>
</div>