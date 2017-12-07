<?php

$style = $_POST['imgsrc'];
$carharttdir = 'http://s7d9.scene7.com/is/image/Carhartt/';
$notfoundImage = '/home/strongbo/public_html/haws2/wp-content/plugins/itrexus_woocommerce/notfound.jpg';

$angles = array( '', '_AVB', '_AVB2', '_AVR', '_AVL', '_AVI' );

$angleImages = array(
		'html' => '',
	);

foreach ( $angles as $var ){
	if( ! files_identical($notfoundImage, $carharttdir . $style . $var)){
		$angleImages['html'] .= '<img src="'.$carharttdir . $style . $var.'" alt="' . $style . '" title="' . $style . '" style="width: 75px; height: 85px;" class="extra-angle">';
	}
}
echo json_encode($angleImages);



//Compare 2 files
function files_identical($fn1, $fn2) {
	$READ_LEN = 4096;

	if(!$fp1 = fopen($fn1, 'rb'))
		return FALSE;

	if(!$fp2 = fopen($fn2, 'rb')) {
		fclose($fp1);
		return FALSE;
	}

	$same = TRUE;
	while (!feof($fp1) and !feof($fp2))
		if(fread($fp1, $READ_LEN) !== fread($fp2, $READ_LEN)) {
			$same = FALSE;
			break;
		}

	if(feof($fp1) !== feof($fp2))
		$same = FALSE;

	fclose($fp1);
	fclose($fp2);

	return $same;
}

?>