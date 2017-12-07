<?php
function DocTypetoString($type){
		switch($type){
			case "PO":
				return "Purchase Order";
				break;
			case "IN":
				return "Invoice";
				break;
			case "PR":
				return "Acknowledgment";
				break;
			case "SH":
				return "Advance Ship Notice";
				break;
		}
}
function FindSegment($seg, $code, $qual = 0, $qualCode = '', $envVal = 0){
	global $totalLines;
	for ($l=$envVal; $l < $totalLines; $l++){
		global ${"_" . $l};
		if (${"_" . $l}[0] == $seg){
			if($qual>0){
				if (${"_" . $l}[$qual] == $qualCode){
					 return (${"_" . $l}[$code]);
				}
			} else {
				return (${"_" . $l}[$code]);
			}
		}	
	}
}
function FindSegmentPos($seg, $code, $qual = 0, $qualCode = '', $envVal=0){
	global $totalLines;
	for ($l=$envVal; $l < $totalLines; $l++){
		global ${"_" . $l};
		if (${"_" . $l}[0] == $seg){
			if($qual>0){
				if (${"_" . $l}[$qual] == $qualCode){
					return $l;
				}
			} else {
				return $l;
			}
		}
	}
}
function FindQual($qualcode, $segment) {
	$refdb = new SQLite3('defdb/' . substr($segment, 0, 1));
	$defres = $refdb->query("select qualdef from qualifier where qualcode='".$qualcode."' and recordname='".$segment."';");
	$defrow = $defres->fetchArray();	
	return $defrow[0];
}
function RevQual($qualcode, $segment) {
	$refdb = new SQLite3('defdb/' . substr($segment, 0, 1));
	$defres = $refdb->query('select qualcode from qualifier where UPPER(qualdef)=UPPER("'.$qualcode.'") and recordname="'.$segment.'";');
	$defrow = $defres->fetchArray();	
	return $defrow[0];
}
function TPLookup($tpid) {
	return "TP Lookup Function -- $tpid";
}
function carrierLookup($carrierId) {
		switch($carrierId){
			case "FDEG":
				return "FedEx Ground";
				break;
			case "IN":
				return "Invoice";
				break;
			case "PR":
				return "Acknowledgment";
				break;
			case "SH":
				return "Advance Ship Notice";
				break;
		}
}

?>