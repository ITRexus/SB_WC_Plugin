<?php

$carstyle = strtoupper(substr($_POST['id'],-3,3));

$colorcodeFile = fopen('/home/strongbo/public_html/haws2/wp-content/plugins/itrexus_woocommerce/colorcodes.csv', "r");
$colorcodes = array();
while (($data = fgetcsv($colorcodeFile, 1600, ",")) !== FALSE) {
    $colorcodes[strtoupper($data[0])] = ucwords($data[1]);
}
echo json_encode(array('html' => '<span style="width: 100%; margin-right: auto; margin-left: auto; font-weight: bold; font-size: 11px; height: auto; line-height: 15px; ">'.$colorcodes[$carstyle].'</span>', 'id' => $_POST['id']));
?>