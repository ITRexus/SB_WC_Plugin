<?php
include_once("order.php");
include_once("counter.php");
include_once("address.php");
if(isset($_POST['username'])){$address = New Address($_POST['username']);}
class OrderProcess{
	function OrderProcess(){
		global $order, $counter;
		if(isset($_POST['subadditem'])){
			$this->additem($_POST['prodId'], $_POST['quantity']);
			echo "Successfully Added To Cart";
		}
		if(isset($_POST['subremoveitem'])){
			$this->removeitem($_POST['prodId']);
			echo "Successfully Removed From Cart";
		}
		if(isset($_POST['subfinalizeorder'])){
			$orderString = $this->finalizeorder();
			$filetowrite = "/home/strongbo/docs/edi/" . $counter->getCounter("doccounter");
			$fh = fopen($filetowrite,'w') or die("Can't open file");
			fwrite($fh, $orderString);
			fclose($fh);
			echo /*$counter->getCounter("PONum")*/"1412064340";
		}
	}	
   function additem($prodId, $quantity){
   	global $order;
      $order->itemList[$prodId] = $quantity + $order->itemList[$prodId];
		$this->write_order();
   }
   function removeitem($prodId){
   	global $order;
		foreach( $order->itemList as $key => $value ){          
         if ($key == $prodId){
            unset( $order->itemList[ $key ] );
         }
		}
		$this->write_order();
   }
   function finalizeorder(){
   	global $database, $session, $order, $counter, $address;
   	$STAddr = $address->getShipTo($_POST['STAddr']);
   	$BTAddr = $address->getBillTo($_POST['BTAddr']);
   	$delim = '~';
   	$eoldelim = "`\n";
		$StringToWrite =	"ISA" . $delim . "00" . $delim . '          ' . $delim . "00" . $delim . '          ' . $delim;
		$StringToWrite .= 'ZZ' . $delim . str_pad($session->username, 15) . $delim . "ZZ" . $delim . str_pad($session->client_name, 15) . $delim;
		$StringToWrite .= date("ymd") . $delim . date("Hi") . $delim . 'U' . $delim . '00401' . $delim . str_pad($counter->increment("ISA"),9,'0', STR_PAD_LEFT) . $delim;
		$StringToWrite .= '0' . $delim . 'P' . $delim . '>' . $eoldelim;
		$StringToWrite .=	"GS" . $delim . "PO" . $delim . $session->username . $delim . $session->client_name . $delim;
		$StringToWrite .= date("Ymd") . $delim . date("His") . $delim . $counter->increment("GS") . $delim . 'X' . $delim . '004010' . $eoldelim;
		
		$StringToWrite .= "ST" . $delim . "850" . $delim . str_pad($counter->increment("ST"),4,'0', STR_PAD_LEFT) . $eoldelim;
		$StringToWrite .= "BEG" . $delim . '00' . $delim . 'SA' . $delim . /* counter->increment("PONum") */ "1412064340" . $delim . '' . $delim . date("Ymd") . $eoldelim;
		// Ship To / Bill To---------------------------------------------------------------------------------------------
		$StringToWrite .= "N1" . $delim . 'ST' . $delim . $STAddr['Name'] . $eoldelim;
		$StringToWrite .= "N3" . $delim . $STAddr['Addr_Line1'];
		$STAddr['Addr_Line2'] != '' ? $StringToWrite .= $delim . $STAddr['Addr_Line2'] . $eoldelim : $StringToWrite .= $eoldelim;
		$StringToWrite .= "N4" . $delim . $STAddr['City'] . $delim . $STAddr['State']. $delim . $STAddr['Zip'] . $eoldelim;
		
		$StringToWrite .= "N1" . $delim . 'BT' . $delim . $BTAddr['Name'] . $eoldelim;
		$StringToWrite .= "N3" . $delim . $BTAddr['Addr_Line1'];
		$BTAddr['Addr_Line2'] != '' ? $StringToWrite .= $delim . $BTAddr['Addr_Line2'] . $eoldelim : $StringToWrite .= $eoldelim;
		$StringToWrite .= "N4" . $delim . $BTAddr['City'] . $delim . $BTAddr['State']. $delim . $BTAddr['Zip'] . $eoldelim;
		//-------------------------------------------------------------------------------------------------------------------
		// Line Items--------------------------------------------------------------------------------------------------------
		$connection = mysql_connect('localhost', 'parts_admin', 'partsuserpass') or die(mysql_error());
      mysql_select_db('parts_catalog_db', $connection) or die(mysql_error());
		$linesTotal = 0;	
	foreach( $order->itemList as $key => $value ){
		$query = 'select * from freshandeasy_parts_list where product_id = "'.$key.'";';
		$result = mysql_query($query, $connection);
		$name  = mysql_result($result,0,"product_name");
		$photo_dir  = mysql_result($result,0,"photo_directory");
		$description  = mysql_result($result,0,"product_description");
		$price  = mysql_result($result,0,"price_in_dollars");
		$uom  = mysql_result($result,0,"uom");
		$quantity  = $value;
		$ID  = $key;
		$StringToWrite .= 'PO1' . $delim . '' . $delim . $quantity . $delim . $uom . $delim . $price . $delim . '' . $delim . 'UK' . $delim. $ID . $eoldelim;
		$description = substr($description, 0, 79);
		if($description != ''){ $StringToWrite .= 'PID' . $delim . 'F' . $delim . '' . $delim . '' . $delim . '' . $delim . str_replace("\n", '', $description) . $eoldelim; }
		$linesTotal++;
	}
		$StringToWrite .= "CTT" . $delim . $linesTotal . $eoldelim;
		mysql_close($connection);
		//-------------------------------------------------------------------------------------------------------------------
		$StringToWrite .= "SE" . $delim . $this->LineCount($StringToWrite, $eoldelim) . $delim . str_pad($counter->getCounter("ST"),4,'0', STR_PAD_LEFT) . $eoldelim;		
		$StringToWrite .= "GE" . $delim . '1' . $delim . $counter->getCounter("GS") . $eoldelim;
		$StringToWrite .= "IEA" . $delim . '1' . $delim . str_pad($counter->getCounter("ISA"),9,'0', STR_PAD_LEFT) . $eoldelim;
		$filetowrite = "/home/strongbo/public_html/" . $session->client_name . '/outToSB/' . $session->username . "-" . date("md-Hms") . ".850";
		$fh = fopen($filetowrite,'w') or die("Can't open file");
		fwrite($fh, $StringToWrite);
		fclose($fh);
		unlink('/home/strongbo/public_html/cgi-bin/tmp/' . $order->order_id);
		$database->query('update ' . TBL_USERS . ' set order_id="" where username="' . $session->username . '";');
      /* Make connection to database */
      $ordersConnection = mysql_connect('localhost', 'orders_admin', 'orderspass') or die(mysql_error());
      mysql_select_db('orders', $ordersConnection) or die(mysql_error());
      $q = "INSERT INTO orders VALUES ('". str_pad(/*$counter->getCounter("PONum")*/"1412064340",9,'0', STR_PAD_LEFT) ."', 'Open', '". $session->username ."', '".date("ymdHis")."', '')";
      $result = mysql_query($q, $ordersConnection);
      $q2 = "INSERT INTO orders_status (associated_order, order_status, edi_file, sequence_num) "
      	. "VALUES ('". str_pad(/*$counter->getCounter("PONum")*/"1412064340",9,'0', STR_PAD_LEFT) ."', 'Sent', '".$counter->increment("doccounter")."', '1')";
      $newResult = mysql_query($q2, $ordersConnection);
      mysql_close($ordersConnection);
		return $StringToWrite;
   }
   function write_order(){
   	global $order;
		$output = '<?php $itemList = ';
		$output .= var_export($order->itemList, true);
		$output .= ";\n?>";
		$fh = fopen('/home/strongbo/public_html/cgi-bin/tmp/' . $order->order_id,'w') or die("Can't open file");
		fwrite($fh, $output);
		fclose($fh);
   }
	function LineCount($string, $delim){
		$a=0;
		$textAr = explode($delim, $string);
		foreach ($textAr as $i) {
			$a++;
		}
		return ($a-2);
	}
}
$orderProcess = new OrderProcess;
?>