<?php
$line = $argv[1];
$job_id = $argv[2];
$pid = getmypid();
$dbh = mysqli_init();
mysqli_real_connect( $dbh, 'localhost', 'haws', 'hawsuserpass', 'HAWS_wordpress');
$dbh->query('UPDATE wp_jobs SET job_status="2", job_pid="' . $pid . '" WHERE job_id="' . $job_id . '";');

/**
 * @since 0.71
 */
define( 'OBJECT', 'OBJECT' );
define( 'object', 'OBJECT' ); // Back compat.

/**
 * @since 2.5.0
 */
define( 'OBJECT_K', 'OBJECT_K' );

/**
 * @since 0.71
 */
define( 'ARRAY_A', 'ARRAY_A' );

/**
 * @since 0.71
 */
define( 'ARRAY_N', 'ARRAY_N' );

eval ('$lines = ' . unserialize(stripslashes($line)) . ';');

$sizes = array(
	"XXS" => 0,
	"2XS" => 0,
	"23" => 0,
	"SHT" => 0,
	"OFA" => 0,
	"ML" => 0,
	"XS" => 1,
	"XSM" => 1,
	"S" => 2,
	"SM" => 2,
	"SML" => 2,
	"M" => 3,
	"MED" => 3,
	"REG" => 3,
	"L" => 4,
	"45" => 4,
	"L1" => 4,
	"TLL" => 4,
	"LRG" => 4,
	"XLG" => 5,
	"XL" => 5,
	"XXL" => 6,
	"LXL" => 6,
	"2XL" => 6,
	"3XL" => 7,
	"4XL" => 8,
	"5XL" => 9,
);

foreach ( $lines as $line ) {
	$styleColor = split('-', $line[3]);
	$style 		= strtoupper(trim($styleColor[0]));
	$color		= strtoupper(trim($styleColor[1]));
    $size = array();
	if(!isset($styleColor[2])){
		$dimension = "REG";
	}elseif( strtoupper(trim($styleColor[2])) == "TALL" ){
		$dimension = "TLL";
	}elseif( strtoupper(trim($styleColor[2])) == "BIG" ){
		$dimension = "TLL";
        $size		= split('-', $line[5]);
        $size[0]	= trim($size[0]);
        $size[1]	= trim($size[1]);
	}else{
        $dimension = "REG";
    }
	$idARR = array();
	$price = $line[6];
	if($dimension == "REG"){
        $a=1;
       /*if($result = $dbh->query('select post_id from wp_postmeta where meta_key="_sku" and UPPER(meta_value)="' . $style . '";')){
            while($row = $result->fetch_object()){
                 //$dbh->query('update wp_postmeta set meta_value="'.$price.'" where post_id="'.$row->post_id.'" AND meta_key="_regular_price";');
            }
		} */
	} elseif($dimension == "TLL"){
        //select * from wp_postmeta where post_id in (select post_id from wp_postmeta where meta_key="attribute_pa_style" and meta_value="$SKU . $COLOR") and meta_key="attribute_pa_dimension" and meta_value="tll";
        if($result = $dbh->query('select * from wp_postmeta where post_id in (select post_id from wp_postmeta where meta_key="_sku" and UPPER(meta_value)="' . $style . '") and meta_key="attribute_pa_dimension" and meta_value="tll";')){
            while($row = $result->fetch_object()){
                 //$dbh->query('update wp_postmeta set meta_value="'.$price.'" where post_id="'.$row->post_id.'" AND meta_key="_regular_price";');
                 //$dbh->query('DELETE FROM wp_postmeta where post_id="'.$row->post_id.'" and meta_key="bigtall";');
                 $dbh->query('INSERT INTO wp_postmeta (meta_value, post_id, meta_key) VALUES ("1", "'.$row->post_id.'", "bigtall");');
            }
        }
        if(is_numeric($size[0]) ){
            //select * from wp_postmeta where post_id in (select post_id from wp_postmeta where meta_key="attribute_pa_style" and meta_value="$SKU . $COLOR") and meta_key="attribute_pa_dimension" and meta_value="tll";
            if($result = $dbh->query('select * from wp_postmeta where post_id in (select post_id from wp_postmeta where meta_key="_sku" and UPPER(meta_value)="' . $style . '") and meta_key="attribute_pa_dimension" and meta_value >= ' . $size[0] . ';')){
                while($row = $result->fetch_object()){
                    //$dbh->query('update wp_postmeta set meta_value="'.$price.'" where post_id="'.$row->post_id.'" AND meta_key="_regular_price";');
                    // $dbh->query('DELETE FROM wp_postmeta where post_id="'.$row->post_id.'" and meta_key="bigtall";');
                     $dbh->query('INSERT INTO wp_postmeta (meta_value, post_id, meta_key) VALUES ("1", "'.$row->post_id.'", "bigtall");');
                }
            }
        }
    }/*else{
        //Loop through the sizes
        //select * from wp_postmeta where post_id in (select post_id from wp_postmeta where meta_key="attribute_pa_style" and meta_value="$SKU . $COLOR") and meta_key="attribute_pa_size" and meta_value="$SIZE";
		for( $i=$sizes[strtoupper($size[0])]; $i<=$sizes[strtoupper($size[1])]; $i++ ){
            if($result = $dbh->query('select * from wp_postmeta where post_id in (select post_id from wp_postmeta where meta_key="attribute_pa_style" and meta_value="' . $style . $color . '") and meta_key="attribute_pa_size";')){
                while($row = $result->fetch_object()){
					foreach(array_keys($sizes,$i) as $tmpsize){
						if(strtoupper($row->meta_value) == strtoupper($tmpsize) ){
                            $dbh->query('update wp_postmeta set meta_value="'.$price.'" where post_id="'.$row->post_id.'" AND meta_key="_price";');
                        }
                    }
                }
            }
        }
    } */
    /*
	//sizes array_keys($sizes, $i) check for each possibility. If not found, then check next.
	if(is_numeric($size[0]) && is_numeric($size[1])){
		for( $i=$size[0]; $i<=$size[1]; $i++ ){
			if($result = $dbh->query('Select * from wp_postmeta where upper(meta_value)="' . $style . $color . '" AND meta_key="attribute_pa_style";')){
				while($row = $result->fetch_object()){
					$post_id = $row->post_id;
					$checksize = get_meta( $dbh, $post_id, 'attribute_pa_size');
					$checkdimension = get_meta( $dbh, $post_id, 'attribute_pa_dimension');
					if(strtoupper($checksize) == $i ){
						if($checkdimension != ''){
							if(strtoupper($checkdimension) == $dimension){
								error_log("found1 -- " . $style . $color);
								$dbh->query('update wp_postmeta set meta_value="'.$price.'" where post_id="'.$post_id.'" AND meta_key="_price";');
								$dbh->query('update wp_postmeta set meta_value="'.$price.'" where post_id="'.$post_id.'" AND meta_key="_regular_price";');
							}
						} else {
							error_log("found2 -- " . $style . $color);
							$dbh->query('update wp_postmeta set meta_value="'.$price.'" where post_id="'.$post_id.'" AND meta_key="_price";');
							$dbh->query('update wp_postmeta set meta_value="'.$price.'" where post_id="'.$post_id.'" AND meta_key="_regular_price";');
						}
					}
				}
			}
			$result->close();
		}
	}elseif(count($size)>1){
		for( $i=$sizes[strtoupper($size[0])]; $i<=$sizes[strtoupper($size[1])]; $i++ ){
			if($result = $dbh->query('Select * from wp_postmeta where upper(meta_value)="' . $style . $color . '" AND meta_key="attribute_pa_style";')){
				while($row = $result->fetch_object()){
					$post_id = $row->post_id;
					$checksize = get_meta( $dbh, $post_id, 'attribute_pa_size');
					$checkdimension = get_meta( $dbh, $post_id, 'attribute_pa_dimension');
					foreach(array_keys($sizes,$i) as $tmpsize){
						if(strtoupper($checksize) == strtoupper($tmpsize) ){
							if($checkdimension != ''){
								if(strtoupper($checkdimension) == $dimension){
									error_log("found3 -- " . $style . $color);
									$dbh->query('update wp_postmeta set meta_value="'.$price.'" where post_id="'.$post_id.'" AND meta_key="_price";');
									$dbh->query('update wp_postmeta set meta_value="'.$price.'" where post_id="'.$post_id.'" AND meta_key="_regular_price";');
								}
							} else {
								error_log("found4 -- " . $style . $color);
								$dbh->query('update wp_postmeta set meta_value="'.$price.'" where post_id="'.$post_id.'" AND meta_key="_price";');
								$dbh->query('update wp_postmeta set meta_value="'.$price.'" where post_id="'.$post_id.'" AND meta_key="_regular_price";');
							}
						}
					}
				}
			}
			$result->close();
		}
	}else{
		if($result = $dbh->query('Select * from wp_postmeta where upper(meta_value)="' . $style . $color . '" AND meta_key="attribute_pa_style";')){
					error_log("foundtest2");
			while($row = $result->fetch_object()){
				$post_id = $row->post_id;
				$checksize = get_meta( $dbh, $post_id, 'attribute_pa_size');
				$checkdimension = get_meta( $dbh, $post_id, 'attribute_pa_dimension');
				foreach(array_keys($sizes,$i) as $tmpsize){
					if(strtoupper($checksize) == strtoupper($tmpsize) ){
						if($checkdimension != ''){
							if(strtoupper($checkdimension) == $dimension){
								error_log("found5 -- " . $style . $color);
								$dbh->query('update wp_postmeta set meta_value="'.$price.'" where post_id="'.$post_id.'" AND meta_key="_price";');
								$dbh->query('update wp_postmeta set meta_value="'.$price.'" where post_id="'.$post_id.'" AND meta_key="_regular_price";');
							}
						} else {
							error_log("found6 -- " . $style . $color);
							$dbh->query('update wp_postmeta set meta_value="'.$price.'" where post_id="'.$post_id.'" AND meta_key="_price";');
							$dbh->query('update wp_postmeta set meta_value="'.$price.'" where post_id="'.$post_id.'" AND meta_key="_regular_price";');
						}
					}
				}
			}
		}
		$result->close();
	} */
}
$dbh->query('delete from wp_jobs WHERE job_id="' . $job_id . '";');
$dbh->close;

function get_results( $handle, $query = null, $output = OBJECT ) {

	$new_array = array();
	if($result = $handle->query( $query )){
		while($row = $result->fetch_object()){
			$new_array[] = array_values( get_object_vars( $row ) );
			return $new_array;
		}
	}
}
function get_meta($handle, $post_id, $meta_id){
	if(substr($meta_id,0,12) != "attribute_pa"){ $meta_id = "_" . $meta_id; }
	return get_results( $handle, 'SELECT meta_value FROM wp_postmeta where meta_key="'.$meta_id.'" AND post_id="'.$post_id.'";', ARRAY_N )[0][0];
}
?>