<div style="margin: 10px;">
<table style="width: 100%; margin-top: 5px;" border="0" id="yo">
	<tr>
		<td>
			<button onclick="PrintPO(jQuery(this).parents('.showdocChild').html());return false;" class="ITR_PrintButton deleteMe">Print</button>
		</td>
	</tr>
	<tr>
		<?php $date1=FindSegment('GS', 4);?>
		<td style="text-align: left; vertical-align: top;">
			<div class="ITR_HDR">Purchase Order: <?php echo FindSegment('AK2', 2) ?></div>
			<table class="ITR_HDRLine">
			<tr><td>Date Received:</td><td> <?php echo substr($date1,4,2)."/".substr($date1,6,2)."/".substr($date1,0,4); ?></td></tr>
			</table>
		</td>
	</tr>
</table>
<br>

<br>
</div>