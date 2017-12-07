<?
/**
 * order.php
 */
include_once('login/session.php');
class Order
{
   var $itemList = array();	//The items list
   var $order_id;		//The order id
   /* Class constructor */
   function Order(){
   	global $session, $database;
      $this->time = time();
      $result = $database->query('select * from ' . TBL_USERS . ' where username="' . $session->username . '";');
 		$this->order_id = mysql_result($result,0,"order_id");
 		if($this->order_id == ''){				//Check to see if an order is open for the current user. If not, start a new one.
 			$this->order_id = uniqid("ord_");
     		$database->query('update ' . TBL_USERS . ' set order_id="' . $this->order_id . '" where username="' . $session->username . '";');
			$output = '<?php $itemList = ';
			$output .= var_export($this->itemList, true);
			$output .= ";\n?>";
			$fh = fopen('/home/strongbo/public_html/cgi-bin/tmp/' . $this->order_id,'w') or die("Can't open file");
			fwrite($fh, $output);
			fclose($fh);
 		} else {
 			include('/home/strongbo/public_html/cgi-bin/tmp/' . $this->order_id);
 			$this->itemList = $itemList;
 		}
   }
};
$order = new Order;
?>
