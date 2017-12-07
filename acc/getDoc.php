<? 
define('NL_NIX', "\n"); // \n only
define('NL_WIN', "\r\n"); // \r\n
define('NL_MAC', "\r");  // \r only
$ediFile = $_POST['ediFile'];
$i=1;
$file_handle = fopen("/home/strongbo/docs/edi/" . $ediFile, "rb");
$line_of_text = fgets($file_handle);
rewind($file_handle);
if(strlen($line_of_text) > 120){
	$line_of_text = str_replace(substr($line_of_text, 105,1), substr($line_of_text, 105,1) . "\n", $line_of_text);
	file_put_contents($fileNameOut, $line_of_text);
}
rewind($file_handle);
while (!feof($file_handle) ) {
	$line_of_text = fgets($file_handle);
	if ($i==1){
		$delim = substr($line_of_text, 3, 1);
	}
	$l=strpos($line_of_text, $delim);
	$segname = substr($line_of_text, 0, $l);
	${"_" . "$i"} = explode($delim, $line_of_text);
	$lastindex = end(${"_" . "$i"});
	if(substr($lastindex, strlen($lastindex)-2, 2) == NL_WIN){
		${"_" . "$i"}[key(${"_" . "$i"})] = substr($lastindex, 0, strlen($lastindex)-3);
	}else{
		${"_" . "$i"}[key(${"_" . "$i"})] = substr($lastindex, 0, strlen($lastindex)-2);
	}
	reset(${"_" . "$i"});
	$i++;
}
$totalLines=$i;
include_once('ediFunctions.php');
include(FindSegment("GS", 1) . '.php');
?>