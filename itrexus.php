<?php
/*  
Plugin Name: ITRexus Woocommerce
Plugin URI: http://ITRexus.com
Description: Add bonus functionality to the Woocommerce plugin
Version: 0.1
Author: Jonathan Kenyon
Author URI: http://itrexus.com
*/

define ('ITR_PLUGIN_SELF_DIRNAME', basename(dirname(__FILE__)), true);
define( 'ITR_WC_PLUGIN_FILE', __FILE__ );

if(isset($_SESSION)){
	session_start();
}


if ( ! class_exists( 'ITRexus_Woocommerce' ) ) :

/*
	Admin Menu
		-Add the Sub Menus
*/
final class ITRexus_Woocommerce {

	public $version = '0.1';
	
	protected static $_instance = null;
	
	public function __construct(){
		// Check and Update Version
		add_action( 'admin_init', array( $this, 'Check_Version' ), 5 );
		register_activation_hook( ITR_WC_PLUGIN_FILE, array( $this, 'Install' ) );
		//if( ! wp_next_scheduled( 'minute_tasks') ) {
		//	wp_schedule_event( time(), 'minute', 'minute_tasks' );
		//}
		$this->Create_Actions();
        update_option('ITR_IN_ORDER_CHECK', 0);
        
        add_action('plugins_loaded', array( $this, after_plugins_includes ) );
        $this->includes();
	}
	
	/**
	 * Main ITRexus_Woocommerce Instance
	 *
	 * Ensures only one instance of ITRexus_Woocommerce is loaded or can be loaded.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function Check_Version(){
		if ( ! defined( 'IFRAME_REQUEST' ) && ( get_option( 'itrexus_woocommerce_version' ) != $this->version )) {
			$this->Install();
			do_action( 'itrexus_woocommerce_updated' );
		}
	}
	
	public function Submenus(){
		global $current_user;
		get_currentuserinfo();
		add_submenu_page( 'woocommerce' , __( 'Edit Vendors' ), __( 'Vendors' ), 'manage_woocommerce', 'wc_vend',  array( $this, 'Write_Vendor_Page'));
		add_submenu_page( 'woocommerce' , __( 'Administrator Tools' ), __( 'Administrator Tools' ), 'manage_woocommerce', 'wc_itradmintools',  array( $this, 'ITR_Admin_Tools'));
		if ( $current_user->user_login == "itrexus" ){
			add_submenu_page( 'woocommerce' , __( 'ITRexus Tools' ), __( 'ITRexus Tools' ), 'manage_woocommerce', 'wc_itrtools',  array( $this, 'ITR_Tools'));
		}
	}
	 /*
		Ensure that this plugin is loaded last
	*/
	function this_plugin_last() {
		// ensure path to this file is via main wp plugin path
		$wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
        $admin_bar_path = 'itrexus_admin_bar/admin_bar.php';
		$this_plugin = plugin_basename(trim($wp_path_to_this_file));
		$active_plugins = get_option('active_plugins');
		$this_plugin_key = array_search($this_plugin, $active_plugins);
		$admin_bar_plugin_key = array_search($admin_bar_path, $active_plugins);
		//array_splice($active_plugins, $admin_bar_plugin_key, 1);
		//array_push($active_plugins, $admin_bar_path);
		array_splice($active_plugins, $this_plugin_key, 1);
		array_push($active_plugins, $this_plugin);
		update_option('active_plugins', $active_plugins);
	}
	/*
		Shop Manager Tools and Utilities Page
	*/
	public function ITR_Admin_Tools() {
        global $wpdb;
        $tabs = array(
                        'messages'      => 'Messages',
                        'prices'        => 'Price Update',
                        'missing_price' => 'Product Prices',
        );
        reset($tabs);
		$active_tab = isset($_GET['ittab']) ? $_GET['ittab'] : key($tabs);
        /* Tabbed View  -------     */
        ?>
        <ul class="subsubsub">
            <?php foreach($tabs as $tab => $value){?>
               
               <li><a href="<?php echo admin_url( 'admin.php?page=wc_itradmintools&ittab=' . $tab ) ?>" style="text-shadow: none; text-decoration: none; <?php echo $tab == $active_tab ? 'color: #000000; font-weight: 700; cursor: default;' : ''; ?>"><?php echo $value; ?></a> <?php echo $value == end($tabs) ? '' : '|'; ?> </li>
               
            <?php } ?>
        </ul>
        <div style="width: <?php echo count($tabs) * 90?>px; height: 1px; border-bottom: 1px solid; border-color: #B2B2B2; margin-top: 50px;"></div>
        <?php
        /* Tabs         -------     */
        switch($active_tab){
            case 'messages': ?>
                <div class="itr_messages" style="padding: 25px; width: 1250px;">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#unseen">Unseen</a></li>
                        <li><a data-toggle="tab" href="#seen">Seen</a></li>
                    </ul>
                    <div class="tab-content" style="overflow-y: scroll; height: 700px;">
                        <div id="unseen" class="tab-pane active">
                        <table style="width: 100%;">
                            <thead>
                                <tr>
                                    <td style="font-weight: 700;">Message Type</td>
                                    <td style="font-weight: 700; padding-left: 65px;">Message</td>
                                    <td style="font-weight: 700; padding-left: 65px;">Date</td>
                                </tr>
                            </thead>
                            <tbody>
                    <?php   $messages = $wpdb->get_results("select * from " . $wpdb->prefix . "itrmessages where seen=0;");
                            $msgcount = 0;
                            foreach( $messages as $message){ ?>
                                <tr style="background: <?php echo ($msgcount & 1 ? '#FFFFFF' : '#F5F5F5'); ?>">
                                    <td style="padding-left: 10px;"><?php echo $this->get_error_message_type($message->msgtype); ?></td>
                                    <td style="padding-left: 75px;"><?php echo $message->message; ?></td>
                                    <td style="padding-left: 75px;"><?php echo date('M j Y g:i A', strtotime($message->msgdate)); ?></td>
                                </tr>
                    <?php   $msgcount++;
                            } 
                    ?>
                            </tbody>
                        </table>
                        </div>
                        <div id="seen" class="tab-pane">
                        <table style="width: 100%;">
                            <thead>
                                <tr>
                                    <td style="font-weight: 700;">Message Type</td>
                                    <td style="font-weight: 700; padding-left: 65px;">Message</td>
                                    <td style="font-weight: 700; padding-left: 65px;">Date</td>
                                </tr>
                            </thead>
                            <tbody>
                    <?php   $messages = $wpdb->get_results("select * from " . $wpdb->prefix . "itrmessages where seen=1;");
                            $msgcount = 0;
                            foreach( $messages as $message){ ?>
                                <tr style="background: <?php echo ($msgcount & 1 ? '#FFFFFF' : '#F5F5F5'); ?>">
                                    <td style="padding-left: 10px;"><?php echo $this->get_error_message_type($message->msgtype); ?></td>
                                    <td style="padding-left: 75px;"><?php echo $message->message; ?></td>
                                    <td style="padding-left: 75px;"><?php echo date('M j Y g:i A', strtotime($message->msgdate)); ?></td>
                                </tr>
                    <?php   $msgcount++;
                            } ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
                <?php $wpdb->query("update " . $wpdb->prefix . "itrmessages set seen=1 where seen=0;"); ?>
          <?php break; ?>
      <?php case 'prices': ?>
                <div style="padding: 25px; width: 350px;">
                    <span style="font-size: 15px; font-weight: bold;">Upload Price Sheet:</span>
                    <br />
                    <input type="file" id="xlsxuploadfile">
                    <input class="button" id="xlsxsubmit" value="Upload file">
                    <br />
                </div>
          <?php break; ?>
      <?php case 'missing_price': ?>
                <div style="padding: 25px;">
                    <?php
                    if ( isset($_REQUEST['action']) /*&& 'updateprices' == $_REQUEST['action']*/ ) {
                        ?>
                        <h3>Successfully Updated Product Prices</h3>
                        <?php
                        $skus = $_REQUEST['post_ids'];
                        $regprices = $_REQUEST['regprice'];
                        $bigprices = $_REQUEST['bigprice'];
                        foreach($skus as $key => $sku){
                            //GET ALL POSTS WITH SKU
                            $variations = $wpdb->get_results("SELECT * from " . $wpdb->prefix . "postmeta where meta_key='_sku' and meta_value='". $sku . "';");
                            $post_ids = array();
                            foreach ($variations as $variation){
                            //UPDATE REGULAR Price
                                if ( $regprices[$key] != '' ){
                                    $wpdb->query("update " . $wpdb->prefix . "postmeta set meta_value='".$regprices[$key]."' where meta_key='_price' and post_id='".$variation->post_id."';");
                                    echo '<p>' . $regprices[$key] . ' -- ' . $variation->post_id . '</p>';
                                }
                                $post_ids[] = $variation->post_id;
                            }
                            $price_list = $_REQUEST['pricelist' . $sku];
                            //IF SET, UPDATE BIGTALL PRICE
                            if ( $bigprices[$key] != '' ){
                                if ( isset( $price_list ) ){
                                    foreach( $price_list as $size ){
                                        $size_post_id = $wpdb->get_results("SELECT post_id from ". $wpdb->prefix . "postmeta where meta_key='attribute_pa_size' and meta_value='" . $size . "' and post_id IN ('" . implode("','", $post_ids) . "');", ARRAY_N)[0][0];
                                        $wpdb->query("update " . $wpdb->prefix . "postmeta set meta_value='".$regprice[$key]."' where meta_key='_price' and post_id='".$size_post_id."';");
                                        echo '<p>' . $regprices[$key] . ' -- ' . $size_post_id . '</p>';
                                    }
                                }
                            }
                        }
                    } else {
                    ?>
                    <h3>Missing Product Prices</h3>
                    <form action="" method="post" name="updateprices-form" id="updateprices-form" class="validate" novalidate="novalidate">
                    <input name="action" type="hidden" value="updateprices" />
                    <?php wp_nonce_field( 'updateprices', '_wpnonce_updateprices' ) ?>
                    <div style="width: 1250px; height: 600px; overflow-y: scroll;">
                    <table style="width: 950px;">
                        <thead>
                            <tr>
                                <td style="font-weight: 700;">Product Information</td>
                                <td style="font-weight: 700; padding-left: 65px;">Price (Reg)</td>
                                <td style="font-weight: 700; padding-left: 65px;">Big/Tall Sizes</td>
                                <td style="font-weight: 700; padding-left: 65px;">Price (Big/Tall)</td>
                            </tr>
                        </thead>
                        <tbody>
                <?php   
                
                        $firstprices = $wpdb->get_results( 'SELECT post_id,meta_value FROM '.$wpdb->prefix.'postmeta where meta_key="_price" and meta_value="123456789" GROUP BY post_id ASC;' );
                        $procount = 0;
                        $skulist = array();
                        foreach( $firstprices as $price){
                            $skulist[] = $wpdb->get_results("select meta_value from " . $wpdb->prefix . "postmeta where post_id='".$price->post_id."' and meta_key='_sku' Group By post_id;", ARRAY_N)[0][0];
                        }
                        $skus = implode('","', array_unique($skulist));
                        $variations = $wpdb->get_results( 'SELECT post_id,meta_value FROM '.$wpdb->prefix.'postmeta where meta_key="_sku" and meta_value IN ("' . $skus . '") GROUP BY meta_value ASC;' );
                        $procount = 0;
                        foreach( $variations as $variation){
                            $products = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_value='".$variation->meta_value."' and meta_key='_sku' Group By post_id;");
                            $pricetest = 0;
                            $test = false;
                            $test1 = false;
                            $test2 = false;
                            $AllSizes = array();
                            foreach( $products as $product ){
                                $prices = $wpdb->get_results("select * from " . $wpdb->prefix . "postmeta where meta_key='_price' and post_id='".$product->post_id."' Group By post_id;");
                                foreach( $prices as $price ){
                                    if( $price->meta_value == '123456789' || $price->meta_value == '10.50' ){
                                        $test1 = true;
                                    }
                                    if( $price->meta_value != '123456789' && $price->meta_value != '10.50' ){
                                        $test2 = true;
                                        $pricetest = $price->meta_value;
                                    }
                                    if( $test1 && $test2 ){ $test = true; break; }
                                }
                                $sizes = $wpdb->get_results("select * from " . $wpdb->prefix . "postmeta where meta_key='attribute_pa_size' and post_id='".$product->post_id."' GROUP BY meta_value;");
                                foreach( $sizes as $size ){
                                    $AllSizes[] = $size->meta_value;
                                }
                            /*    if( $test ){                                    
                                    foreach( $prices as $pro2 ){
                                        $wpdb->query("update " . $wpdb->prefix . "postmeta set meta_value='".$pricetest."' where post_id='".$pro2->post_id."' and (meta_key='_price' or meta_key='_regular_price');");
                                    }
                                    break; 
                                }  */
                            }
                            if($test || $test1 ){
                                $fewsizes = array_unique($AllSizes);
                                usort($fewsizes, "cmpsize");
                                 ?>   
                                    <tr style="background: <?php echo ($procount & 1 ? '#FFFFFF' : '#F5F5F5'); ?>">
                                        <td style="padding-left: 10px;">
                                        <input type="hidden" name="post_ids[]" id="post_ids[]" value="<?php echo $variation->meta_value ?>"></input>
                                        <?php // echo $variation->meta_value; 
                                        $post = get_post( $variation->post_id );
                                        if ( $post->post_parent > 0 ) {
                                            echo '<a href="'. get_edit_post_link( $post->post_parent ) .'">'. get_the_title( $post->post_parent ) .'</a>';
                                        } else {
                                            echo 'Style# ' . $variation->meta_value;
                                        }
                                        ?></td>
                                        <td style="padding-left: 75px;"><span class="orderquantity"><input type="text" autocomplete="off" class="quan" style="width: 85px;" id="<?php echo 'regprice[]'?>" name="<?php echo 'regprice[]'?>"></input></span></td>
                                        <td style="padding-left: 75px;"><select style="width: 100px;" id="<?php echo 'pricelist[]'?>" name="<?php echo 'pricelist'.$variation->meta_value.'[]'?>" size="4" multiple>                                    
                                                                            <?php   foreach( $fewsizes as $size ){
                                                                                    echo '<option>' . strtoupper($size) . '</option>';
                                                                                }
                                                                            ?>
                                                                        </select>
                                        </td>
                                        <td style="padding-left: 75px;"><span class="orderquantity"><input type="text" autocomplete="off" class="quan" style="width: 85px;" id="<?php echo 'bigprice[]'?>" name="<?php echo 'bigprice[]';?>"></input></span></td>
                                    </tr>
                        <?php   $procount++;
                            }
                        }
                ?>
                        </tbody>
                    </table>
                    </div>
                    <input type="submit" class="btn btn-flat-red" name="update_price" value="Update Prices"></input>
                    </form>
                    <?php } ?>
                </div>
          <?php break; ?>
  <?php } ?>
        
        
        <?php  
        //Errors Tab
		//echo "<p>" . $price_sheet . "</p>";
	}
	/*
		ITRexus Tools and Utilities Page
	*/
	public function ITR_Tools() {
		global $wpdb;
		if ( isset($_REQUEST['action']) && 'updateswatches' == $_REQUEST['action'] ) {
			check_admin_referer( 'updateswatches', '_wpnonce_updateswatches' );
			$swatches = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'woocommerce_termmeta where meta_key="order_pa_style";' );
			foreach ( $swatches as $swatch )
			{
				$term_id = $swatch->woocommerce_term_id;
				$skustyle = $wpdb->get_row( 'SELECT name FROM '.$wpdb->prefix.'terms where term_id="'.$term_id.'";' );
			//	$TERM_ID = select term_id from wp_terms where name=$SKUCOLOR_SW;
				$alreadyGot = $wpdb->get_results(
                        'SELECT
                        COUNT(*) AS TOTALCOUNT
                        FROM '.$wpdb->prefix.'woocommerce_termmeta
                        WHERE ( meta_key = "pa_style_yith_wccl_value" AND woocommerce_term_id="'.$term_id.'" )'
                    );
				$count = $alreadyGot[0]->TOTALCOUNT; //if there's any duplicate, it'd return 1
				// if the count return 1, update the table, but else insert the data
				if( $count > 0 ) {
					$wpdb->update(
						$wpdb->prefix.'woocommerce_termmeta',
						array(
							'meta_value'			=> 'http://strongboxui.com/haws2/pix.php?f='.$skustyle->name.'_SW'
						),
						array(
							'meta_key' 				=> 'pa_style_yith_wccl_value',
							'woocommerce_term_id' 	=> $term_id
						)
					);
				} else {
					$wpdb->insert( 
						$wpdb->prefix.'woocommerce_termmeta',
						array(
							'meta_key' 				=> 'pa_style_yith_wccl_value',
							'woocommerce_term_id' 	=> $term_id,
							'meta_value'			=> 'http://strongboxui.com/haws2/pix.php?f='.$skustyle->name.'_SW'
						)
					);
				}
				/*
				$wpdb->query( 
						'INSERT INTO '.$wpdb->prefix.'woocommerce_termmeta (meta_value, meta_key, woocommerce_term_id) VALUES ("http://strongboxui.com/haws2/pics/'.$skustyle->name.'_SW.jpg", "pa_style_yith_wccl_value", "'.$term_id.'") ON DUPLICATE KEY UPDATE;
						'
				);
				*/
			}
		}
		
		if ( isset($_REQUEST['action']) && 'updatebackorders' == $_REQUEST['action'] ) {
			check_admin_referer( 'updatebackorders', '_wpnonce_updatebackorders' );
			$products = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'pmxi_posts;' );
			set_time_limit(1500);
			foreach ( $products as $product )
			{
				$post_id = $product->post_id;
				$alreadyGot = $wpdb->get_results(
                        'SELECT
                        COUNT(*) AS TOTALCOUNT
                        FROM '.$wpdb->prefix.'postmeta
                        WHERE ( meta_key = "_backorders" AND post_id="'.$post_id.'" )'
                    );
				$count = $alreadyGot[0]->TOTALCOUNT; //if there's any duplicate, it'd return 1
				// if the count return 1, update the table, but else insert the data
				if( $count > 0 ) {
			/*		$wpdb->update(
						$wpdb->prefix.'postmeta',
						array(
							'meta_value'	=> 'notify'
						),
						array(
							'meta_key'	 	=> '_backorders',
							'post_id' 		=> $post_id
						)
					); */
				} else {
					$wpdb->insert( 
						$wpdb->prefix.'postmeta',
						array(
							'meta_key' 		=> '_backorders',
							'post_id' 		=> $post_id,
							'meta_value'	=> 'notify'
						)
					);
				}
				/*
				$wpdb->query( 
					'INSERT INTO '.$wpdb->prefix.'postmeta (meta_value, meta_key, post_id) VALUES ("notify", "_backorders", "'.$post_id.'") ON DUPLICATE KEY UPDATE;'
				);
				*/
			}
		}
		
		if ( isset($_REQUEST['action']) && 'serialize-form' == $_REQUEST['action'] ) {
			check_admin_referer( 'serialize-form', '_wpnonce_serialize' );
			$dump=stripslashes($_REQUEST['serialize-form-text']);
			eval("\$serializedText=$dump;");
			$serializedText = serialize($serializedText);
			echo '<p>' . $serializedText . '</p>';
		}
		if(isset($_REQUEST['action']) && 'bulkproducts' == $_REQUEST['action']){
            $tier1 = 1.31;
            $tier2 = 1.20;
            $tier3 = 1.18;
            update_option('_bulkdiscount_quantity_1', 7);
            update_option('_bulkdiscount_discount_1', (1 - ($tier2 / $tier1))*100);
            update_option('_bulkdiscount_quantity_2', 48);
            update_option('_bulkdiscount_discount_2', (1 - ($tier3 / $tier1))*100);
            update_option('_bulkdiscount_text_info', '');
            update_option('_bulkdiscount_enabled', "yes");
            update_option('_bulkdiscount_quantity_3', '');
            update_option('_bulkdiscount_discount_3', '');
            update_option('_bulkdiscount_quantity_4', '');
            update_option('_bulkdiscount_discount_4', '');
            update_option('_bulkdiscount_quantity_5', '');
            update_option('_bulkdiscount_discount_5', '');
            /*
            _bulkdiscount_discount_1
            _bulkdiscount_discount_2
            _bulkdiscount_discount_3
            _bulkdiscount_discount_4
            _bulkdiscount_discount_5
            _bulkdiscount_enabled
            _bulkdiscount_quantity_1
            _bulkdiscount_quantity_2
            _bulkdiscount_quantity_3
            _bulkdiscount_quantity_4
            _bulkdiscount_quantity_5
            _bulkdiscount_text_info
			/*
            $query = 'select * from (select * from ' . $wpdb->prefix . 'postmeta where meta_key="_sku" order by post_id ASC) As tmp_table group by meta_value;';
			$products = $wpdb->get_results( $query );
			foreach ($products as $product){
                /*
                    Tiers - 31%
                    Tiers - 20%
                    Tiers - 18%
                
                $productsquery = 'select post_id from ' . $wpdb->prefix . 'postmeta where meta_key="_sku" and meta_value="'.$product->meta_value.'";';
                $variations = $wpdb->get_results( $productsquery );
                foreach ($variations as $variation){
                    echo '<p>' . $variation->post_id . '</p>';
                    /*update_post_meta( $variation->post_id, "_bulkdiscount_text_info", '' );
                    update_post_meta( $variation->post_id, "_bulkdiscount_enabled", "yes" );
                    update_post_meta( $variation->post_id, "_bulkdiscount_quantity_1", 7 );
                    update_post_meta( $variation->post_id, "_bulkdiscount_discount_1", $tier2 / $tier1 );
                    update_post_meta( $variation->post_id, "_bulkdiscount_quantity_2", 48 );
                    update_post_meta( $variation->post_id, "_bulkdiscount_discount_2", $tier3 / $tier1 );
                    
                    update_post_meta( $variation->post_id, "_bulkdiscount_quantity_3", '' );
                    update_post_meta( $variation->post_id, "_bulkdiscount_discount_3", '' );
                    update_post_meta( $variation->post_id, "_bulkdiscount_quantity_4", '' );
                    update_post_meta( $variation->post_id, "_bulkdiscount_discount_4", '' );
                    update_post_meta( $variation->post_id, "_bulkdiscount_quantity_5", '' );
                    update_post_meta( $variation->post_id, "_bulkdiscount_discount_5", '' );
                    
                }
			}
            */
		}
		if(isset($_REQUEST['action']) && 'testbutton' == $_REQUEST['action']){
			global $wpdb;
            $order_id="55341EMB55925";
            $product_id="87563";
            $the_order = wc_get_order( $order_id );
            foreach( $the_order->get_items() as $item ){
                $_product = apply_filters( 'woocommerce_order_item_product', $the_order->get_product_from_item( $item ), $item );
                $propost = get_post($item['variation_id']);
                preg_match('/Style.*/', $propost->post_title, $PID);
                echo '<p>' . $matches[0] . '</p>';
            }
           /* $newImg = '/home/strongbo/ImgNotFound.png';
            $ite = new RecursiveDirectoryIterator("/home/strongbo/public_html/haws2/wp-content/uploads/");

            $bytestotal=0;
            $nbfiles=0;
            foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {
				$size = getjpegsize($filename);
				if ($size[0] == 70 && $size[1] == 60){
                    copy($newImg, $filename);
                //echo "$filename\n";
				}
            } */
			/*$price_list = $wpdb->get_results( "select meta_id,meta_value from ". $wpdb->prefix . "postmeta where meta_key='_regular_price' OR meta_key='_price';" );
            foreach ( $price_list as $price ){
                if( $price->meta_value != 123456789 ){
                    $new_price = $price->meta_value * 1.048;
                    if( $wpdb->query('UPDATE '.$wpdb->prefix.'postmeta SET meta_value="'.$new_price.'" WHERE meta_id="'.$wpdb->meta_id.'";') ){
                        echo '<p>SUCCESS</p>';
                    }
                }
            } */
            
		}
		
		if ( isset($_REQUEST['action']) && 'checkorders' == $_REQUEST['action'] ) {
			check_admin_referer( 'checkorders', '_wpnonce_checkorders' );
			$this->Check_EDI();
		}
		if ( isset($_REQUEST['action']) && 'unserialize-form' == $_REQUEST['action'] ) {
			check_admin_referer( 'unserialize-form', '_wpnonce_unserialize' );
			$unserializedText = unserialize(stripslashes($_REQUEST['unserialize-form-text']));
			echo '<p>' . addslashes(stripslashes($_REQUEST['unserialize-form-text'])) . '</p>';
			echo '<p>' . var_export($unserializedText, true) . '</p>';
		}
		$check=false;
		if(isset($_REQUEST['action']) && 'link-all-variations' == $_REQUEST['action']){
			$check=true;
			$_SESSION['offset'] = 0;
		}
		($_SESSION['offset'] > 0) ?  $check=true : '';
		($_SESSION['done']) ? $check=false : '';
		if ( $check ) {
			if(!isset($_SESSION['offset'])){
				$_SESSION['offset'] = 0;
			}
			$posts = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'posts where post_parent="" and post_type="product" limit 10 offset ' . $_SESSION['offset'] . ';' );
			$_SESSION['offset'] += 10;
			$a = 0;
			foreach ( $posts as $post )
			{
				$post_id = intval( $post->ID );

				if ( ! $post_id ) {
					continue;
				}
				$a++;

				$variations = array();
				$_product   = wc_get_product( $post_id, array( 'product_type' => 'variable' ) );
				// Put variation attributes into an array
				foreach ( $_product->get_attributes() as $attribute ) {

					if ( ! $attribute['is_variation'] ) {
						continue;
					}

					$attribute_field_name = 'attribute_' . sanitize_title( $attribute['name'] );

					if ( $attribute['is_taxonomy'] ) {
						$options = wc_get_product_terms( $post_id, $attribute['name'], array( 'fields' => 'slugs' ) );
					} else {
						$options = explode( WC_DELIMITER, $attribute['value'] );
					}

					$options = array_map( 'sanitize_title', array_map( 'trim', $options ) );
					$variations[ $attribute_field_name ] = $options;
				}

				// Quit out if none were found
				if ( sizeof( $variations ) == 0 ) {
					continue;
				}

				// Get existing variations so we don't create duplicates
				$available_variations = array();

				foreach( $_product->get_children() as $child_id ) {
					$child = $_product->get_child( $child_id );

					if ( ! empty( $child->variation_id ) ) {
						$attributes = $child->get_variation_attributes();
						if ( $attributes["attribute_pa_dimension"] == "" ){
							unset($attributes["attribute_pa_dimension"]);
						}
						$available_variations[] = $attributes;
					}
				}

				// Created posts will all have the following data
				$variation_post_data = array(
					'post_title'   => 'Product #' . $post_id . ' Variation',
					'post_content' => '',
					'post_status'  => 'publish',
					'post_author'  => get_current_user_id(),
					'post_parent'  => $post_id,
					'post_type'    => 'product_variation'
				);

				// Now find all combinations and create posts
				if ( ! function_exists( 'array_cartesian' ) ) {

					/**
					 * @param array $input
					 * @return array
					 */
					function array_cartesian( $input ) {
						$result = array();

						while ( list( $key, $values ) = each( $input ) ) {
							// If a sub-array is empty, it doesn't affect the cartesian product
							if ( empty( $values ) ) {
								continue;
							}

							// Special case: seeding the product array with the values from the first sub-array
							if ( empty( $result ) ) {
								foreach ( $values as $value ) {
									$result[] = array( $key => $value );
								}
							}
							else {
								// Second and subsequent input sub-arrays work like this:
								//   1. In each existing array inside $product, add an item with
								//      key == $key and value == first item in input sub-array
								//   2. Then, for each remaining item in current input sub-array,
								//      add a copy of each existing array inside $product with
								//      key == $key and value == first item in current input sub-array

								// Store all items to be added to $product here; adding them on the spot
								// inside the foreach will result in an infinite loop
								$append = array();
								foreach ( $result as &$product ) {
									// Do step 1 above. array_shift is not the most efficient, but it
									// allows us to iterate over the rest of the items with a simple
									// foreach, making the code short and familiar.
									$product[ $key ] = array_shift( $values );

									// $product is by reference (that's why the key we added above
									// will appear in the end result), so make a copy of it here
									$copy = $product;

									// Do step 2 above.
									foreach ( $values as $item ) {
										$copy[ $key ] = $item;
										$append[] = $copy;
									}

									// Undo the side effecst of array_shift
									array_unshift( $values, $product[ $key ] );
								}

								// Out of the foreach, we can add to $results now
								$result = array_merge( $result, $append );
							}
						}

						return $result;
					}
				}

				$variation_ids       = array();
				$added               = 0;
				$possible_variations = array_cartesian( $variations );
				foreach ( $possible_variations as $variation ) {

					// Check if variation already exists
					if ( in_array( $variation, $available_variations ) ) {
						continue;
					}
					$variation_id = wp_insert_post( $variation_post_data );

					$variation_ids[] = $variation_id;

					foreach ( $variation as $key => $value ) {
						update_post_meta( $variation_id, $key, $value );
					}

					$added++;

					do_action( 'product_variation_linked', $variation_id );

				//	if ( $added > WC_MAX_LINKED_VARIATIONS ) {
				//		break;
				//	}
				}

				delete_transient( 'wc_product_children_ids_' . $post_id );
			}
			if($a > 0){
				$page = $_SERVER['PHP_SELF'];
			//	printf("<script>location.href='http://strongboxui.com/haws2/wp-admin/admin.php?page=wc_itrtools'</script>");
				echo "<p>" . $_SESSION['offset'] . "</p>";
			} else {
				$_SESSION['done'] = true;
				$_SESSION['missingdone'] = true;
			}
		}
		
		$missingcheck=false;
		if(isset($_REQUEST['action']) && 'missingimages-form' == $_REQUEST['action']){
			$missingcheck=true;
			$_SESSION['missingoffset'] = 0;
			$_SESSION['missingtext'] = '';
		}
		($_SESSION['missingoffset'] > 0) ?  $missingcheck=true : '';
		if($_SESSION['missingdone']){
			$missingcheck=false;
			$_SESSION['missingdone'] = false;
			$missingimagestext = $_SESSION['missingtext'];
		}
		if ( $missingcheck ) {
			$colorcodeFile = fopen('/home/strongbo/public_html/haws2/wp-content/plugins/itrexus_woocommerce/colorcodes.csv', "r");
			$colorcodes = array();
			 while (($data = fgetcsv($colorcodeFile, 1600, ",")) !== FALSE) {
					$colorcodes[strtoupper($data[0])] = ucwords($data[1]);
				}
			$ma = 0;
			$query = 'select DISTINCT meta_value from ' . $wpdb->prefix . 'postmeta where meta_key="attribute_pa_style" ORDER BY meta_value ASC limit 25 offset ' . $_SESSION['missingoffset'] . ';';
			$_SESSION['missingoffset'] += 25;
			$products = $wpdb->get_results( $query );
			foreach ($products as $variation){
				$color = $colorcodes[substr($variation->meta_value,-3,3)];
				$size = getjpegsize('http://strongboxui.com/haws2/pix.php?f='.$variation->meta_value);
				if ($size[0] == 70 && $size[1] == 60){
					$_SESSION['missingtext'] .= substr($variation->meta_value,0,-3) . " $color". " -- ";
				}
				$size = getjpegsize('http://strongboxui.com/haws2/pix.php?f='.$variation->meta_value . '_SW');
				if ($size[0] == 70 && $size[1] == 60){
					$_SESSION['missingtext'] .= substr($variation->meta_value,0,-3) . " $color" .  ' Swatch' . " -- ";
				}
				$ma++;
			}
			if($ma > 0){
				echo '<p>' . $_SESSION['missingoffset'] . '</p>';
				echo '<p>' . $_SESSION['missingtext'] . '</p>';
				$page = $_SERVER['PHP_SELF'];
				printf("<script>location.href='http://strongboxui.com/haws2/wp-admin/admin.php?page=wc_itrtools'</script>");
			}// else {
				$_SESSION['missingdone'] = true;
				$_SESSION['done'] = true;
			//}
		}
        
        echo is_empty($_SESSION['missingoffset']) ? '' : '<p>' . $_SESSION['missingoffset'] . '</p>';
		?>
		<form action="" method="post" name="updateswatches" id="updateswatches" class="validate" novalidate="novalidate">
		<input name="action" type="hidden" value="updateswatches" />
		<?php wp_nonce_field( 'updateswatches', '_wpnonce_updateswatches' ) ?>
		<input type="submit" name="updateswatches" id="updateswatchessub" class="button action" value="Update Swatches">
		</form>
		
		
		<form action="" method="post" name="updatebackorders" id="updatebackorders" class="validate" novalidate="novalidate">
		<input name="action" type="hidden" value="updatebackorders" />
		<?php wp_nonce_field( 'updatebackorders', '_wpnonce_updatebackorders' ) ?>
		<input type="submit" name="updatebackorders" id="updatebackorderssub" class="button action" value="Set Back Orders To Notify">
		</form>
		
		<form action="" method="post" name="serialize-form" id="serialize-form" class="validate" novalidate="novalidate">
		<input name="action" type="hidden" value="serialize-form" />
		<?php wp_nonce_field( 'serialize-form', '_wpnonce_serialize' ) ?>
		<input type="text" name="serialize-form-text" id="serialize-form-text" value=<?php echo isset($serializedText) ? 'Converted' : '""';?>>
		<input type="submit" name="serialize-form" id="serialize-formsub" class="button action" value="Serialize Text">
		</form>
		
		<form action="" method="post" name="unserialize-form" id="unserialize-form" class="validate" novalidate="novalidate">
		<input name="action" type="hidden" value="unserialize-form" />
		<?php wp_nonce_field( 'unserialize-form', '_wpnonce_unserialize' ) ?>
		<input type="text" name="unserialize-form-text" id="unserialize-form-text" value=<?php echo isset($unserializedText) ? 'Converted' : '""';?>>
		<input type="submit" name="unserialize-form" id="unserialize-formsub" class="button action" value="Unserialize Text">
		</form>
		
		<form action="" method="post" name="link-all-variations" id="link-all-variations" class="validate" novalidate="novalidate">
		<input name="action" type="hidden" value="link-all-variations" />
		<?php wp_nonce_field( 'link-all-variations', '_wpnonce_link-all-variations' ); ?>
		<input type="hidden" name="offset" id="offset" value="0">
		<input type="submit" name="link-all-variations" id="link-all-variationssub" class="button action" value="Link All Variations">
		</form>
		
		<form action="" method="post" name="missingimages-form" id="missingimages-form" class="validate" novalidate="novalidate">
		<input name="action" type="hidden" value="missingimages-form" />
		<?php wp_nonce_field( 'missingimages-form', '_wpnonce_missingimages' ) ?>
		<input type="text" name="missingimages-form-text" id="missingimages-form-text" value=<?php echo isset($missingimagestext) ? '"' . $missingimagestext . '"' : '""';?>>
		<input type="submit" name="missingimages-form" id="missingimages-formsub" class="button action" value="Find Missing Images">
		</form>
		
		<form action="" method="post" name="checkorders" id="checkorders" class="validate" novalidate="novalidate">
		<input name="action" type="hidden" value="checkorders" />
		<?php wp_nonce_field( 'checkorders', '_wpnonce_checkorders' ) ?>
		<input type="submit" name="checkorders" id="checkorderssub" class="button action" value="Check Orders">
		</form>
		
		<form action="" method="post" name="bulkproducts" id="bulkproducts" class="validate" novalidate="novalidate">
		<input name="action" type="hidden" value="bulkproducts" />
		<?php wp_nonce_field( 'bulkproducts', '_wpnonce_bulkproducts' ) ?>
		<input type="submit" name="bulkproducts" id="bulkproductssub" class="button action" value="Add Bulk To All Products">
		</form>
		<form action="" method="post" name="testbutton" id="testbutton" class="validate" novalidate="novalidate">
		<input name="action" type="hidden" value="testbutton" />
		<?php wp_nonce_field( 'testbutton', '_wpnonce_testbutton' ) ?>
		<input type="submit" name="testbutton" id="testbuttonsub" class="button action" value="Test Button">
		</form>
		
		<?php
	}

	/*
		Vendor Page
	*/
	public function Write_Vendor_Page (){
		global $wpdb;
		$imgName = "101760412_SW";
		echo substr($imgName,-6,6);
		/* Messages and Updates */
		//ADD VENDOR
		if ( isset($_REQUEST['action']) && 'addvendor' == $_REQUEST['action'] ) {
			check_admin_referer( 'add-vendor', '_wpnonce_add-vendor' );
			$vendor_name = $_REQUEST['addvendor-name'];
			$vendor_id = strtoupper(str_pad($_REQUEST['addvendor-id'],3,"0",STR_PAD_RIGHT));
			$extra_digit=0;
			$is_added = $wpdb->get_results( 'SELECT vendor_name FROM '.$wpdb->prefix.'woocommerce_vendors WHERE vendor_name = "'.$vendor_name.'";' );
			$id_added = $wpdb->get_results( 'SELECT vendor_id FROM '.$wpdb->prefix.'woocommerce_vendors WHERE vendor_id = "'.$vendor_id.'";' );
			 while( !empty($id_added) ){
				$vendor_id = substr($vendor_id,0,3-strlen($extra_digit)) . $extra_digit;
				$id_added = $wpdb->get_results( 'SELECT vendor_id FROM '.$wpdb->prefix.'woocommerce_vendors WHERE vendor_id = "'.$vendor_id.'";' );
				$extra_digit += 1;
			}
			if(empty($is_added) && empty($id_added)){
				$wpdb->insert( $wpdb->prefix . 'woocommerce_vendors', array(
					'vendor_name' => $vendor_name,
					'vendor_id' => $vendor_id,
				) );
				echo '<div id="message" class="updated"><p>Vendor added</p></div>';
				
			} else {
				echo '<div id="message" class="error"><p>Vendor with name "'. $vendor_name .'" or ID "'. $vendor_id .'" already exists</p></div>';
			}
		}
		//DELETE SELECTED VENDORS
		if ( isset($_REQUEST['action']) && 'deletevendor' == $_REQUEST['action'] ) {
			check_admin_referer( 'delete-vendor', 'delete-vendor' );
			$vendors = $_REQUEST['vendor'];
			foreach($vendors as $vendor){
				$wpdb->delete( $wpdb->prefix . 'woocommerce_vendors', array('vendor_id' => $vendor));
			}
			echo '<div id="message" class="updated"><p>Vendor(s) deleted</p></div>';
		}
		/* Display Vendors */
?>
		<h2><?php echo _e("Vendors");?></h2>
		<form action="" method="post" name="deletevendor" id="deletevendor" class="validate" novalidate="novalidate">
		<input name="action" type="hidden" value="deletevendor" />
		<?php wp_nonce_field( 'delete-vendor', 'delete-vendor' ) ?>
	
		<input type="submit" name="deletevendor" id="deletevendorsub" class="button action" value="Delete Selected">
		<table class="wp-list-table widefat fixed">
			<thead>
			<tr>
				<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><label style="text-shadow: none;" class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></th>
				<th scope="col" id="vendor_name" class="manage-column column-vendor_name sortable desc" style="">Vendor Name</th>
				<th scope="col" id="vendor_id" class="manage-column column-vendor_id sortable desc" style=""><span>Vendor ID</span></th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><label style="text-shadow: none;" class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></th>
				<th scope="col" id="vendor_name" class="manage-column column-vendor_name" style="">Vendor Name</th>
				<th scope="col" id="vendor_id" class="manage-column column-vendor_id" style=""><span>Vendor ID</span></th>
			</tr>
			</tfoot>

			<tbody id="the-list">
			<?php
				$vendors = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'woocommerce_vendors;' );
				foreach ( $vendors as $vendor )
				{
					echo '<tr id="vendor-'.$vendor->vendor_id.'" class="vendor-'.$vendor->vendor_id.' hentry alternate iedit author-other level-0">
							<th scope="row" class="check-column">
								<label style="text-shadow: none;" class="screen-reader-text" for="cb-select-'.$vendor->vendor_id.'">Select Vendor - '.$vendor->vendor_id.'</label>
								<input id="cb-select-'.$vendor->vendor_id.'" name="vendor[]" value="'.$vendor->vendor_id.'" type="checkbox">
								<div class="locked-indicator"></div>
							</th>
							<td class="order_status column-vendor_name">'.$vendor->vendor_name.'</td>
							<td class="order_title column-vendor_id">'.$vendor->vendor_id.'</td>
							</tr>';
				}
			?>
			</tbody>
		</table>
		</form>
		
		
<?php		
		/* Create New Vendor */
		$creating = isset( $_POST['addvendor'] );
		$vendor_name = $creating && isset( $_POST['addvendor-name'] ) ? wp_unslash( $_POST['addvendor-name'] ) : '';
		?>
		<h2><?php echo _e("Add New Vendor"); ?></h2>
		<form action="" method="post" name="addvendor" id="addvendor" class="validate" novalidate="novalidate">
		<input name="action" type="hidden" value="addvendor" />
		<?php wp_nonce_field( 'add-vendor', '_wpnonce_add-vendor' ) ?>

		<table class="form-table">
			<tr class="form-field form-required">
				<th scope="row"><label for="addvendor-name"><?php echo __( 'Vendor Name');?><span class="description"><?php _e('(required)'); ?></span></label></th>
				<td><input name="addvendor-name" type="text" id="addvendor-name" value="<?php echo esc_attr($vendor_name); ?>" /></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="addvendor-id"><?php echo __( 'Vendor ID');?><span class="description"><?php _e('(required)'); ?></span></label></th>
				<td><input name="addvendor-id" type="text" maxlength="3" id="addvendor-id" value="<?php echo esc_attr($vendor_id); ?>" /></td>
			</tr>
		</table>
		<?php submit_button( __( 'Add New Vendor '), 'primary', 'addvendor', true, array( 'id' => 'addvendorsub' ) ); ?>
		</form>
		<?php
	}
	
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/*
		Install the Plugin
	*/
	public function Install() {
		add_option( 'itrexus_woocommerce_version', $this->version);
		global $wpdb;

			$wpdb->hide_errors();

			$collate = '';

			if ( $wpdb->has_cap( 'collation' ) ) {
				if ( ! empty($wpdb->charset ) ) {
					$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
				}
				if ( ! empty($wpdb->collate ) ) {
					$collate .= " COLLATE $wpdb->collate";
				}
			}

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );


			// ITRexus Tables
			$itrexus_tables = "
		CREATE TABLE {$wpdb->prefix}woocommerce_vendors (
		  vendor_name varchar(200) NOT NULL,
		  vendor_id varchar(3) NULL,
		  PRIMARY KEY  (vendor_name),
		  KEY attribute_name (vendor_id)
		) $collate;
		";
			dbDelta( $itrexus_tables );
	}
	/*
		Multivendor Check
	*/
	public function Check_Is_Multivendor(){
		error_log("WTF?");
		echo '<p>Test</p>';
		error_log(var_dump($order_id));
		var_dump($item_id);
		var_dump($added_product);
		var_dump($qty);
		var_dump($args);
	}
	/*
		Grid View For Single Product
	*/
	public function Variation_Grid(){
		global $product,$post;
	}

	function wc_custom_user_redirect( $redirect, $user ) {
		// Get the first of all the roles assigned to the user
		$role = $user->roles[0];

		$dashboard = admin_url();
		$myaccount = get_permalink( wc_get_page_id( 'myaccount' ) );

		if( $role == 'administrator' ) {
			//Redirect administrators to the dashboard
			$redirect = $dashboard;
		} elseif ( $role == 'shop_manager' ) {
			//Redirect shop managers to the dashboard
			$redirect = $dashboard . 'edit.php?post_type=shop_order';
		} elseif ( $role == 'editor' ) {
			//Redirect editors to the dashboard
			$redirect = $dashboard;
		} elseif ( $role == 'author' ) {
			//Redirect authors to the dashboard
			$redirect = $dashboard;
		} elseif ( $role == 'customer' || $role == 'subscriber' ) {
			//Redirect customers and subscribers to the "My Account" page
			$redirect = $myaccount;
		} else {
			//Redirect any other role to the previous visited page or, if not available, to the home
			$redirect = wp_get_referer() ? wp_get_referer() : home_url();
		}

		return $redirect;
	}
	/*
		Write 850 on Checkout
	*/
	function write_EDI_order( $order_id ){
		include_once(ABSPATH . "acc/counter.php");
		global $the_order;
		$counter = new Counter;
		//$order = wc_get_order( $order_id );
		$the_order = wc_get_order( $order_id );
        $PONumber = get_post_meta($order_id, 'order_number', true) == '' ? $order_id : get_post_meta($order_id, 'order_number', true);
        $CustPONumber = get_post_meta($order_id, 'customer_order_number', true) == '' ? '' :  get_post_meta($order_id, 'customer_order_number', true);
		$user_info = get_userdata( $the_order->user_id );
		$username = $user_info->user_login;
		//$vendors = get_vendors( $the_order );
		$delim = '*';
		$eoldelim = "~\r\n";
			$StringToWrite =	"ISA" . $delim . "00" . $delim . '          ' . $delim . "00" . $delim . '          ' . $delim;
			$StringToWrite .= 'ZZ' . $delim . str_pad("HAWSUSA", 15) . $delim . "01" . $delim . str_pad("005211974", 15) . $delim;
			$StringToWrite .= date("ymd") . $delim . date("Hi") . $delim . 'U' . $delim . '00401' . $delim . str_pad($counter->increment('ISA'),9,'0', STR_PAD_LEFT) . $delim;
			$StringToWrite .= '0' . $delim;
            if( get_user_meta(get_current_user_id(), 'ITR_Prod', true) != ''){
                $StringToWrite .= get_user_meta(get_current_user_id(), 'ITR_Prod') ? 'P' : 'T';
            } else {
                $StringToWrite .= 'P';;
            }
            $StringToWrite .= $delim . '>' . $eoldelim;
			$StringToWrite .=	"GS" . $delim . "PO" . $delim . "HAWSUSA" . $delim . "005211974" . $delim;
			$StringToWrite .= date("Ymd") . $delim . date("Hi") . $delim . $counter->increment('GS') . $delim . 'X' . $delim . '004010' . $eoldelim;
			$StringToWrite .= "ST" . $delim . "850" . $delim . str_pad($counter->increment('ST'),4,'0', STR_PAD_LEFT) . $eoldelim;
			$StringToWrite .= "BEG" . $delim . '00' . $delim . 'DS' . $delim;
            $StringToWrite .= ($CustPONumber == '') ? $PONumber : $CustPONumber;
            $StringToWrite .= $delim . '' . $delim . date("Ymd") . $eoldelim;
			// REF Segments		---------------------------------------------------------------------------------------------
			$StringToWrite .= "REF" . $delim . 'IA' . $delim . '0002835697' . $eoldelim;
			$StringToWrite .= "REF" . $delim . 'PK' . $delim . 'PK' . date('y') . str_pad($counter->increment('PK'),8,'0', STR_PAD_LEFT) . $delim . 'HW' . $eoldelim;
            $StringToWrite .= ($CustPONumber == '') ? '' : "REF" . $delim . 'CO' . $delim . $CustPONumber . $eoldelim;
			$StringToWrite .= "REF" . $delim . 'ZZ' . $delim . 'COMMERCIAL' . $eoldelim;
			//REQUESTED SHIP DATE--------------------------------------------------------------------------------------------
			$StringToWrite .= "DTM" . $delim . '010' . $delim . date('Ymd', time() + (3 * 24 * 60 * 60)) . $eoldelim;
			//CARRIER DETAILS	---------------------------------------------------------------------------------------------
			$StringToWrite .= "TD5" . $delim . '' . $delim . '' . $delim . '' . $delim . '' . $delim . 'BEST WAY' . $eoldelim;
			// Ship To / Bill To---------------------------------------------------------------------------------------------
			$StringToWrite .= "N1" . $delim . 'ST' . $delim . $the_order->shipping_first_name . ' ' . $the_order->shipping_last_name . $delim . $eoldelim;
			$StringToWrite .= "N3" . $delim . $the_order->shipping_address_1;
			$the_order->shipping_address_2 != '' ? $StringToWrite .= $delim . $the_order->shipping_address_2 . $eoldelim : $StringToWrite .= $eoldelim;
			$StringToWrite .= "N4" . $delim . $the_order->shipping_city . $delim . $the_order->shipping_state. $delim . $the_order->shipping_postcode . $eoldelim;
			
			$StringToWrite .= "N1" . $delim . 'BT' . $delim . 'HAWS USA INC' . $eoldelim;

/*
			$delim . $the_order->billing_first_name . ' ' . $the_order->billing_last_name . $eoldelim;
			$StringToWrite .= "N3" . $delim . $the_order->billing_address_1;
			$the_order->billing_address_2 != '' ? $StringToWrite .= $delim . $the_order->billing_address_2 . $eoldelim : $StringToWrite .= $eoldelim;
			$StringToWrite .= "N4" . $delim . $the_order->billing_city . $delim . $the_order->billing_state. $delim . $the_order->billing_postcode . $eoldelim;
*/
			//-------------------------------------------------------------------------------------------------------------------
			// Line Items--------------------------------------------------------------------------------------------------------
			try{
				$connection = new mysqli('localhost', 'orders_admin', 'orderspass', 'orders');
			} catch (Exception $e){
				echo $e->getMessage();
			}
		//  mysql_select_db('parts_catalog_db', $connection) or die(mysql_error());
			$linesTotal = 0;	
		foreach( $the_order->get_items() as $item ){
			$_product = apply_filters( 'woocommerce_order_item_product', $the_order->get_product_from_item( $item ), $item );
		//	$query = 'select * from freshandeasy_parts_list where product_id = "'.$key.'";';
		//	$result = mysql_query($query, $connection);
		//	$name  = mysql_result($result,0,"product_name");
		//	$photo_dir  = mysql_result($result,0,"photo_directory");
		//mysql_result($result,0,"product_description");
            $propost = get_post($item['variation_id']);
            preg_match('/Style.*/', $propost->post_title, $PID);
			$price  = $_product->get_regular_price();
			$UPC = get_post_meta( $item['variation_id'], '_upc', true );
		//	$uom  = mysql_result($result,0,"uom");
		//	$quantity  = $value;
		//	$ID  = $key;
			$linesTotal++;
			$StringToWrite .= 'PO1' . $delim . $linesTotal . $delim . absint( $item['qty'] ) . $delim . 'EA' . $delim . $price . $delim . '' . $delim . 'UP' . $delim;
			$StringToWrite .= $UPC;
			$StringToWrite .= $eoldelim;
            if($PID[0] != '') $StringToWrite .= 'PID' . $delim . $delim . $delim . $delim . $delim . substr($PID[0], 0,80) . $eoldelim;
		}
			$StringToWrite .= "CTT" . $delim . $linesTotal . $eoldelim;
			//mysql_close($connection);
			//-------------------------------------------------------------------------------------------------------------------
			$a=0;
			$textAr = explode($eoldelim, $StringToWrite);
			foreach ($textAr as $i) {
				$a++;
			}
			$a = $a-2;
			$StringToWrite .= "SE" . $delim . $a . $delim . str_pad($counter->getCounter('ST'),4,'0', STR_PAD_LEFT) . $eoldelim;		
			$StringToWrite .= "GE" . $delim . '1' . $delim . $counter->getCounter('GS') . $eoldelim;
			$StringToWrite .= "IEA" . $delim . '1' . $delim . str_pad($counter->getCounter('ISA'),9,'0', STR_PAD_LEFT) . $eoldelim;
            if( get_user_meta(get_current_user_id(), 'ITR_Prod', true) != ''){
                $filetowrite = get_user_meta(get_current_user_id(), 'ITR_Prod', true) ? "/usr/bots/csHaws/PROD/InFromHaws/" . $username . "-" . date("md-Hms") . ".850" : "/usr/bots/csHaws/TEST/InFromHaws/" . $username . "-" . date("md-Hms") . ".850";
            } else {
                $filetowrite .= "/usr/bots/csHaws/TEST/InFromHaws/" . $username . "-" . date("md-Hms") . ".850";
            }
			$fh = fopen($filetowrite,'w') or die("Can't open file");
			fwrite($fh, $StringToWrite);
			fclose($fh);
		  /* Make connection to database */ 
		  $ordersConnection = mysql_connect('localhost', 'orders_admin', 'orderspass') or die(mysql_error());
		  mysql_select_db('orders', $ordersConnection) or die(mysql_error());
		  $q2 = "INSERT INTO orders_status (associated_order, order_status, edi_file, sequence_num, user) "
			. "VALUES ('". $order_id ."', 'Sent - Carhartt', '".$counter->increment('doccounter')."', '1', '" . $username . "')";
		  $newResult = mysql_query($q2, $ordersConnection);
		  $filetowrite = "/home/strongbo/docs/edi/" . $counter->getCounter('doccounter');
		  $fh = fopen($filetowrite,'w') or die("Can't open file");
		  fwrite($fh, $StringToWrite);
		  fclose($fh);
		  mysql_close($ordersConnection);
		  return $StringToWrite;
	}
	/*
		Pre Change Order Function
			Capture the order data before it gets written
	*/
	function pre_change_order(){
		include_once(ABSPATH . "acc/counter.php");
		//global $the_order;
		global $wpdb;
		$order_id = absint( $_POST['order_id'] );
        if ( $order_id == '' ){
            $order_item_ids = $_POST['order_item_ids'];

            if ( ! is_array( $order_item_ids ) && is_numeric( $order_item_ids ) ) {
                $order_item_ids = array( $order_item_ids );
            }
            $item_id = $order_item_ids[0];
            $order_id = $wpdb->get_results("Select order_id from " . $wpdb->prefix . "woocommerce_order_items WHERE order_item_id='".$item_id."';", ARRAY_N)[0][0];
        }
		$the_order = wc_get_order( $order_id );
		$GLOBALS['pre_lines'] = $the_order->get_items();
		$GLOBALS['pre_order_id'] = $order_id;
	}
	/*
		Change Order Function
	*/
	function change_order(){
		include_once(ABSPATH . "acc/counter.php");
		//global $the_order;
		global $wpdb;
		global $pre_lines;
		global $pre_order_id;
		$order_change = 0;
		$order_id = absint( $_POST['order_id'] );
        if ( $order_id == '' ){
            $order_id = $pre_order_id;
        }

		// Parse the jQuery serialized items
		$items = array();
		parse_str( $_POST['items'], $items );
		$order = wc_get_order( $order_id );
		$data  = get_post_meta( $order_id );

		// Get the payment gateway
		$payment_gateway = wc_get_payment_gateway_by_order( $order );

		// Get line items
		$line_items          = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
		$line_items_fee      = $order->get_items( 'fee' );
		$line_items_shipping = $order->get_items( 'shipping' );

		if ( 'yes' == get_option( 'woocommerce_calc_taxes' ) ) {
			$order_taxes         = $order->get_taxes();
			$tax_classes         = array_filter( array_map( 'trim', explode( "\n", get_option( 'woocommerce_tax_classes' ) ) ) );
			$classes_options     = array();
			$classes_options[''] = __( 'Standard', 'woocommerce' );

			if ( $tax_classes ) {
				foreach ( $tax_classes as $class ) {
					$classes_options[ sanitize_title( $class ) ] = $class;
				}
			}

			// Older orders won't have line taxes so we need to handle them differently :(
			$tax_data = '';
			if ( $line_items ) {
				$check_item = current( $line_items );
				$tax_data   = maybe_unserialize( isset( $check_item['line_tax_data'] ) ? $check_item['line_tax_data'] : '' );
			} elseif ( $line_items_shipping ) {
				$check_item = current( $line_items_shipping );
				$tax_data = maybe_unserialize( isset( $check_item['taxes'] ) ? $check_item['taxes'] : '' );
			} elseif ( $line_items_fee ) {
				$check_item = current( $line_items_fee );
				$tax_data   = maybe_unserialize( isset( $check_item['line_tax_data'] ) ? $check_item['line_tax_data'] : '' );
			}

			$legacy_order     = ! empty( $order_taxes ) && empty( $tax_data ) && ! is_array( $tax_data );
			$show_tax_columns = ! $legacy_order || sizeof( $order_taxes ) === 1;
		}
		$counter = new Counter;
		$the_order = wc_get_order( $order_id );
		$user_info = get_userdata( $the_order->user_id );
		$username = $user_info->user_login;
		$order_date = date_parse($the_order->order_date);
		//$vendors = get_vendors( $the_order );
		$delim = '*';
		$eoldelim = "~\r\n";
			$StringToWrite =  "ISA" . $delim . "00" . $delim . '          ' . $delim . "00" . $delim . '          ' . $delim;
			$StringToWrite .= 'ZZ' . $delim . str_pad("HAWSUSA", 15) . $delim . "01" . $delim . str_pad("005211974", 15) . $delim;
			$StringToWrite .= date("ymd") . $delim . date("Hi") . $delim . 'U' . $delim . '00401' . $delim . str_pad($counter->increment('ISA'),9,'0', STR_PAD_LEFT) . $delim;
			$StringToWrite .= '0' . $delim . 'P' . $delim . '>' . $eoldelim;
			$StringToWrite .=	"GS" . $delim . "PC" . $delim . "HAWSUSA" . $delim . "005211974" . $delim;
			$StringToWrite .= date("Ymd") . $delim . date("Hi") . $delim . $counter->increment('GS') . $delim . 'X' . $delim . '004010' . $eoldelim;
			
			$StringToWrite .= "ST" . $delim . "860" . $delim . str_pad($counter->increment('ST'),4,'0', STR_PAD_LEFT) . $eoldelim;
			
			$StringToWrite .= "BCH" . $delim . '04' . $delim . 'SA' . $delim . $order_id . $delim . '' . $delim . '1' . $delim . $order_date['year'] . str_pad($order_date['month'],2,'0', STR_PAD_LEFT) . str_pad($order_date['day'],2,'0', STR_PAD_LEFT)  . $delim . '' . $delim . '' . $delim . '' . $delim . '' . $delim . date("Ymd") . $eoldelim;
			$StringToWrite .= "REF" . $delim . 'IA' . $delim . '0002835697' . $eoldelim;
			
			// Ship To / Bill To---------------------------------------------------------------------------------------------
			$StringToWrite .= "N1" . $delim . 'ST' . $delim . $the_order->shipping_first_name . ' ' . $the_order->shipping_last_name . $eoldelim;
			$StringToWrite .= "N3" . $delim . $the_order->shipping_address_1;
			$the_order->shipping_address_2 != '' ? $StringToWrite .= $delim . $the_order->shipping_address_2 . $eoldelim : $StringToWrite .= $eoldelim;
			$StringToWrite .= "N4" . $delim . $the_order->shipping_city . $delim . $the_order->shipping_state. $delim . $the_order->shipping_postcode . $eoldelim;
			/*
			$StringToWrite .= "N1" . $delim . 'BT' . $delim . $the_order->billing_first_name . ' ' . $the_order->billing_last_name . $eoldelim;
			$StringToWrite .= "N3" . $delim . $the_order->billing_address_1;
			$the_order->billing_address_2 != '' ? $StringToWrite .= $delim . $the_order->billing_address_2 . $eoldelim : $StringToWrite .= $eoldelim;
			$StringToWrite .= "N4" . $delim . $the_order->billing_city . $delim . $the_order->billing_state. $delim . $the_order->billing_postcode . $eoldelim;
            */
            //-------------------------------------------------------------------------------------------------------------------
			// Line Items--------------------------------------------------------------------------------------------------------
			try{
				$connection = new mysqli('localhost', 'orders_admin', 'orderspass', 'orders');
			} catch (Exception $e){
				echo $e->getMessage();
			}
			$linesTotal = 0;
            $ITRitems = $the_order->get_items();
            foreach( $ITRitems as $item ){
                $found = FALSE;
                foreach( $pre_lines as $pre_item ){
                    if($item['variation_id'] == $pre_item['variation_id']){
                        if(  $item['qty'] != $pre_item['qty'] ){
                            $product_id = $item['variation_id'];
                            $_product = apply_filters( 'woocommerce_order_item_product', $the_order->get_product_from_item( $item ), $item );
                            $price  = get_post_meta($product_id, '_regular_price', true);
                            $StringToWrite .= 'POC' . $delim . absint($linesTotal + 1) . $delim . 'PQ' . $delim . absint( $item['qty'] ) . $delim . absint( $item['qty'] ) . $delim . 'EA' . $delim . $price . $delim;
                            $StringToWrite .= 'UP' . $delim;
                            $StringToWrite .= get_post_meta($product_id, '_upc', true);
                            $StringToWrite .= $eoldelim;
                            $description = substr($description, 0, 79);
                            if($description != ''){ $StringToWrite .= 'PID' . $delim . 'F' . $delim . '' . $delim . '' . $delim . '' . $delim . str_replace("\n", '', $description) . $eoldelim; }
                            $linesTotal++;
                            $order_change = 1;
                        }
                        $found = TRUE;
                    }
                }
                if ( ! $found ){
                    //ADDED
                    $product_id = $item['variation_id'];
                    $_product = apply_filters( 'woocommerce_order_item_product', $the_order->get_product_from_item( $item ), $item );
                    $price  = get_post_meta($product_id, '_regular_price', true);
                    $StringToWrite .= 'POC' . $delim . absint($linesTotal + 1) . $delim . 'AI' . $delim . absint( $item['qty'] ) . $delim . absint( $item['qty'] ) . $delim . 'EA' . $delim . $price . $delim;
                    $StringToWrite .= 'UP' . $delim;
                    $StringToWrite .= get_post_meta($product_id, '_upc', true);
                    $StringToWrite .= $eoldelim;
                    $linesTotal++;
                    $order_change = 1;
                }
            }
            foreach( $pre_lines as $pre_item ){
                $found = FALSE;
                foreach( $ITRitems as $item ){
                    if ( $item['variation_id'] == $pre_item['variation_id'] ){
                        $found = TRUE;
                    }
                }
                if ( ! $found ){
                    //DELETED
                    $product_id = $item['variation_id'];
                    $_product = apply_filters( 'woocommerce_order_item_product', $the_order->get_product_from_item( $pre_item ), $pre_item );
                    $price  = get_post_meta($product_id, '_regular_price', true);
                    $StringToWrite .= 'POC' . $delim . absint($linesTotal + 1) . $delim . 'DI' . $delim . '0' . $delim . '0' . $delim . 'EA' . $delim . $price . $delim;
                    $StringToWrite .= 'UP' . $delim;
                    $StringToWrite .= get_post_meta($product_id, '_upc', true);
                    $StringToWrite .= $eoldelim;
                    $linesTotal++;
                    $order_change = 1;
                }
            }
			$StringToWrite .= "CTT" . $delim . $linesTotal . $eoldelim;
			ob_start();
			var_dump($pre_lines, $the_order->get_items());
			$tmp = ob_get_clean();
			//-------------------------------------------------------------------------------------------------------------------
			$a=0;
			$textAr = explode($eoldelim, $StringToWrite);
			foreach ($textAr as $i) {
				$a++;
			}
			$a = $a-2;
			$StringToWrite .= "SE" . $delim . $a . $delim . str_pad($counter->getCounter('ST'),4,'0', STR_PAD_LEFT) . $eoldelim;		
			$StringToWrite .= "GE" . $delim . '1' . $delim . $counter->getCounter('GS') . $eoldelim;
			$StringToWrite .= "IEA" . $delim . '1' . $delim . str_pad($counter->getCounter('ISA'),9,'0', STR_PAD_LEFT) . $eoldelim;
		if($order_change == 1){ 
			$filetowrite = "/home/strongbo/public_html/" . 'haws2' . '/outToSB/' . $username . "-" . date("md-Hms") . ".860";
			$fh = fopen($filetowrite,'w') or die("Can't open file");
			fwrite($fh, $StringToWrite);
			fclose($fh);
			$filetowrite = "/home/strongbo/docs/edi/" . $counter->increment('doccounter');
			$fh = fopen($filetowrite,'w') or die("Can't open file");
			fwrite($fh, $StringToWrite);
			fclose($fh);
			  /* Make connection to database */ 
			$ordersConnection = mysql_connect('localhost', 'orders_admin', 'orderspass') or die(mysql_error());
			mysql_select_db('orders', $ordersConnection) or die(mysql_error());
			$q2 = "INSERT INTO orders_status (associated_order, order_status, edi_file, sequence_num, user) "
			. "VALUES ('". $order_id ."', 'Order Changed', '".$counter->getCounter('doccounter')."', '1', '" . $username . "')";
			$newResult = mysql_query($q2, $ordersConnection);  
			mysql_close($ordersConnection);
		}
		  return $StringToWrite;
		
	}
	
	/*
		Grid View For Single Product
	*/
	public function Grid_View(){
?>
	<div class="product-grid-view"></div>
<?php
	}
	/*
		Update Orders Database
	*/
	public function Check_EDI(){
		global $wpdb;
		define('NL_NIX', "\n"); // \n only
		define('NL_WIN', "\r\n"); // \r\n
		define('NL_MAC', "\r");  // \r only
		$conn = new mysqli('localhost', 'orders_admin', 'orderspass', 'orders');
		
		// New or Updated Orders
		$orders = $conn->query("SELECT * FROM orders_status WHERE order_status LIKE 'New Order';");
		while($curorder = $orders->fetch_assoc()){
			$ediFile = $curorder['edi_file'];
			$i=1;
			$file_handle = fopen("/home/strongbo/docs/edi/" . $ediFile, "rb");
			$line_of_text = fgets($file_handle);
			rewind($file_handle);
			if(strlen($line_of_text) > 120){
				$line_of_text = str_replace(substr($line_of_text, 105,1), substr($line_of_text, 105,1) . "\n", $line_of_text);
				file_put_contents("/home/strongbo/docs/edi/" . $ediFile, $line_of_text);
			}
			rewind($file_handle);
			$tmporder = array();
			while (!feof($file_handle) ) {
				$line_of_text = fgets($file_handle);
				if ($i==1){
					$delim = substr($line_of_text, 3, 1);
				}
				$l=strpos($line_of_text, $delim);
				$segname = substr($line_of_text, 0, $l);
				$tmporder[$i] = explode($delim, $line_of_text);
				$i++;  
			}
			$order = array();
			foreach ( $tmporder as $line ) {
				$lastindex = end($line);
				
				if(substr($lastindex, strlen($lastindex)-2, 2) == NL_WIN){
					$line[key($line)] = substr($lastindex, 0, strlen($lastindex)-3);
				}else{
					$line[key($line)] = substr($lastindex, 0, strlen($lastindex)-2);
				}
				reset($line);
				$order[] = $line;
			}
			$order["totalLines"]=$i;
			$purpose = $this->FindSegment($order, "BAK", 1);
			$type = $this->FindSegment($order, "BAK", 2);
			$PONumber = $this->FindSegment($order, "BAK", 3);
			$PODate = $this->FindSegment($order, "BAK", 4);
			$total=0;
			switch ($purpose . $type){
				/*Possible Permutations
				00	Original	--	AC Acknowledge - With Detail and Change
				01	Cancellation--	AD Acknowledge - With Detail, No Change
				02	Add			--	AK Acknowledge - No Detail or Change
				05	Replace		--	RJ Rejected - No Detail
				*/
				Case "00AD":
					// Add the new order to the wordpress database.
					$order_data = array(
						'status'        => 'processing',
						'customer_id'   => $this->FindSegment($order, "N1" , 2, 1 ,"ST"),
						'customer_note' => ''
					);
					$order_data['post_type']     = 'shop_order';
					$order_data['post_status']   = 'wc-processing';
					$order_data['ping_status']   = 'closed';
					$order_data['post_author']   = 1;
					$order_data['post_password'] = uniqid( 'order_' );
					$order_data['post_title']    = sprintf( __( 'Order &ndash; %s', 'woocommerce' ), strftime( _x( '%b %s, %Y @ %I:%M %p', 'Order date parsed by strftime', 'woocommerce' ) ) );
					//$order_id = wp_insert_post( apply_filters( 'woocommerce_new_order_data', $order_data ), true );
					$WCorder = wc_create_order( apply_filters( 'woocommerce_new_order_data', $order_data ) );
                    $order_id = $WCorder->id;
                    
					$shipping_address=array();
					$STPos = $this->FindSegmentPos($order, "N1" , 2, 1 ,"ST");
					$shipping_address['company'] = $this->FindSegment($order, "N1" , 2, 0 ,"", $STPos);
					$shipping_address['address_1'] = $this->FindSegment($order, "N3" , 1, 0 ,"", $STPos);
					$shipping_address['address_2'] = $this->FindSegment($order, "N3" , 2, 0 ,"", $STPos) != "" ? $this->FindSegment($order, "N3" , 2, 0 ,"", $STPos) : '' ;
					$shipping_address['city'] = $this->FindSegment($order, "N4" , 1, 0 ,"", $STPos);
					$shipping_address['state'] = $this->FindSegment($order, "N4" , 2, 0 ,"", $STPos);
					$shipping_address['postcode'] = $this->FindSegment($order, "N4" , 3, 0 ,"", $STPos);
					$shipping_address['country'] = $this->FindSegment($order, "N4" , 4, 0 ,"", $STPos);
					
					/* 	
					$billing_address=array();
					$BTPos = $this->FindSegmentPos($order, "N1" , 2, 1 ,"BT");
					$billing_address['company'] = $this->FindSegment($order, "N1" , 2, 0 ,"", $BTPos);
					$billing_address['address_1'] = $this->FindSegment($order, "N3" , 1, 0 ,"", $BTPos);
					$billing_address['address_2'] = $this->FindSegment($order, "N3" , 2, 0 ,"", $BTPos) != "" ? $this->FindSegment($order, "N3" , 2, 0 ,"", $BTPos) : '' ;
					$billing_address['city'] = $this->FindSegment($order, "N4" , 1, 0 ,"", $BTPos);
					$billing_address['state'] = $this->FindSegment($order, "N4" , 2, 0 ,"", $BTPos);
					$billing_address['postcode'] = $this->FindSegment($order, "N4" , 3, 0 ,"", $BTPos);
					$billing_address['country'] = $this->FindSegment($order, "N4" , 4, 0 ,"", $BTPos);
					*/
					
					update_post_meta( $order_id, '_customer_user', $order_data['customer_id'] );
					update_post_meta( $order_id, 'order_number', $PONumber );
					update_post_meta( $order_id, 'customer_order_number', $this->FindSegment($order, "BAK", 8) );
					
					//$wpdb->query('update wp_posts set ID="' . $PONumber . '" where ID="' . $order_id . '";');
					//$wpdb->query('update wp_posts set guid="http://strongboxui.com/haws2/?post_type=shop_order&p=' . $PONumber . '" where guid="http://strongboxui.com/haws2/?post_type=shop_order&p=' . $order_id . '";');
					//$WCorder = new WC_Order( $PONumber );
					//wp_delete_post($order_id, true);
					
					//$WCorder->set_address( $billing_address, 'billing' );
					$WCorder->set_address( $shipping_address, 'shipping' );
					/*
						Add Items
					*/
					$e = $this->FindSegmentPos($order, "PO1");
					ob_start();
					for ($o=$e; $o<$order['totalLines']; $o++){
						if ($order[$o][0]=="PO1"){
							//Need a variation lookup
							$UPC = $this->FindSegment($order, "PO1" , 7, 6 ,"UP", $o);
							$style = $this->FindSegment($order, "PO1" , 9, 8 ,"ST", $o);
							$quantity = $this->FindSegment($order, "PO1" , 2, 0, "", $o);
							$price = $this->FindSegment($order, "PO1" , 4, 0, "", $o);
							$variation = $wpdb->get_results( 'SELECT post_id FROM '.$wpdb->prefix.'postmeta where meta_value ="'.$UPC.'" AND meta_key="_upc";' )[0]->post_id;
							$variationArr = $wpdb->get_results( 'select * from '.$wpdb->prefix.'postmeta where meta_key LIKE "attribute%" and post_id="'.$variation.'";' );
							$variations = array();
							foreach( $variationArr as $var ){
								$variations[$var->meta_key] = $var->meta_value;
							}
							error_log($variation);
							$_product = wc_get_product( $variation );
							if (empty($variations) ){
								continue;
							}
							$item_id = $WCorder->add_product(
								$_product,
								$quantity,
								array(
									'variation' => $variations,
									'totals'    => array(
										'subtotal'     => $quantity * $price,
										'total'        => $quantity * $price,
									)
								)
							);
							$total += $quantity * $price;
						}
					}
					ob_clean();
					$WCorder->set_total($total);
					do_action( 'woocommerce_add_order_item_meta', $item_id, $values, $cart_item_key );
					$changed_order = $conn->query("UPDATE orders_status SET order_status = 'Received Order' WHERE id='".$curorder['id']."';");
					
					break;
				//With Changes
				Case "00AC":
					// Check Order for changes
                    $order_id = $this->get_post_id_from_order_number($PONumber);
					$WCorder = new WC_Order( $order_id );
					if(!$WCorder->id){
						$this->MessageCreate("ORDERR", "A change was attempted to order " . $order_id . " without the order existing.");
						$changed_order = $conn->query("UPDATE orders_status SET order_status = 'No Matching Order' WHERE id='".$curorder['id']."';");
					} else {
						$WCorderItems = $WCorder->get_items();
						$EDIorderItems = array();
						$e = $this->FindSegmentPos($order, "PO1");
						ob_start();
                        $total = 0;
						for ($o=$e; $o<$order['totalLines']; $o++){
							if ($order[$o][0]=="PO1"){
								//Need a variation lookup
								$line_id = $this->FindSegment($order, "PO1" , 1,  0,"", $o);
								$UPC = $this->FindSegment($order, "PO1" , 7, 6 ,"UP", $o);
								$style = $this->FindSegment($order, "PO1" , 9, 8 ,"ST", $o);
								$quantity = $this->FindSegment($order, "PO1" , 2, 0, "", $o);
								$price = $this->FindSegment($order, "PO1" , 4, 0, "", $o);
								$variation = $wpdb->get_results( 'SELECT post_id FROM '.$wpdb->prefix.'postmeta where meta_value ="'.$UPC.'" AND meta_key="_upc";' )[0]->post_id;
								$_product = wc_get_product( $variation );
								$variationArr = $wpdb->get_results( 'select * from '.$wpdb->prefix.'postmeta where meta_key LIKE "attribute%" and post_id="'.$variation.'";' );
								$variations = array();
								foreach( $variationArr as $var ){
									$variations[$var->meta_key] = $var->meta_value;
								}
								$subtotal = $quantity * $price;
								$total += $quantity * $price;
								if( $order[$o+1][0] == "PO3" ){
									$PO3 = $order[$o+1];
								} elseif ( $order[$o+2][0] == "PO3" ){
									$PO3 = $order[$o+2];							
								} else {
									$PO3 = 'empty';
								}
								$item = array(
									
									'UPC'			=>	$UPC,
									'style'			=>	$style,
									'quantity'		=>	$quantity,
									'price'			=>	$price,
									'variation_id'	=>	$variation,
									'variations'	=>	$variations,
									'product'		=>	$_product,
									'addl_detail'	=>	$PO3,
									'subtotal'		=>	$subtotal,
									
								);
								$EDIorderItems['line_id'] = $item;
							}
						}
						foreach($EDIorderItems as $EDIitem){
							foreach($WCorderItems as $WCitem){
								if($EDIitem['UPC'] == $WCitem['item_meta']['upc']){
									if($EDIitem['quantity'] != $WCitem['item_meta']['qty']){
										$item_args = array();
										// quantity
										if ( isset( $EDIitem['quantity'] ) ) {
											$item_args['qty'] = $EDIitem['quantity'];
										}
										$product = wc_get_product( $WCitem['product_id'] );
										// variations must each have a key & value
										$item_args['variation'] = $EDIitem['variations'];

										// total
										if ( isset( $EDIitem['total'] ) ) {
											$item_args['totals']['total'] = floatval( $total );
										}
										// subtotal
										if ( isset( $EDIitem['subtotal'] ) ) {
											$item_args['totals']['subtotal'] = floatval( $EDIitem['subtotal'] );
										}
										$total += floatval( $EDIitem['subtotal'] );
										$item_id = $order->update_product( $WCitem['product_id'], $product, $item_args);
									}
									continue;
								}
							}
						}
                        $WCorder->set_total($total);
						$changed_order = $conn->query("UPDATE orders_status SET order_status = 'Order Updated' WHERE id='".$curorder['id']."';");
					}
					ob_clean();
				//	*/
					break;
				default:
					break;
				
			}
		}
		
		//New Shipping Documents
		$shipdocs = $conn->query("SELECT * FROM orders_status WHERE order_status LIKE 'New Shipping Doc';");
		while($curdoc = $shipdocs->fetch_assoc()){
			$ediFile = $curdoc['edi_file'];
			$i=1;
			$file_handle = fopen("/home/strongbo/docs/edi/" . $ediFile, "rb");
			$line_of_text = fgets($file_handle);
			rewind($file_handle);
			if(strlen($line_of_text) > 120){
				$line_of_text = str_replace(substr($line_of_text, 105,1), substr($line_of_text, 105,1) . "\n", $line_of_text);
				file_put_contents("/home/strongbo/docs/edi/" . $ediFile, $line_of_text);
			}
			rewind($file_handle);
			$tmporder = array();
			while (!feof($file_handle) ) {
				$line_of_text = fgets($file_handle);
				if ($i==1){
					$delim = substr($line_of_text, 3, 1);
				}
				$l=strpos($line_of_text, $delim);
				$segname = substr($line_of_text, 0, $l);
				$tmporder[$i] = explode($delim, $line_of_text);
				$i++;  
			}
			$shipdoc = array();
			foreach ( $tmporder as $line ) {
				$lastindex = end($line);
				
				if(substr($lastindex, strlen($lastindex)-2, 2) == NL_WIN){
					$line[key($line)] = substr($lastindex, 0, strlen($lastindex)-3);
				}else{
					$line[key($line)] = substr($lastindex, 0, strlen($lastindex)-2);
				}
				reset($line);
				$shipdoc[] = $line;
			}
			$shipdoc["totalLines"]=$i;
			$purpose = $this->FindSegment($shipdoc, "BSN", 1);
			switch ($purpose) {
				case "00":
					$e = $this->FindSegmentPos($shipdoc, "HL", 3, "S");
					for ($o=$e; $o<$shipdoc['totalLines']; $o++){
						if ($shipdoc[$o][0]=="HL" && $shipdoc[$o][3] == "O"){
							$ediID = strval($shipdoc[$o + 1][1]);
							$WCorder = new WC_Order( $ediID );
							if( $WCorder->id == $ediID ){
								$changed_doc = $conn->query("UPDATE orders_status SET order_status = 'Shipped', associated_order = '" . $ediID . "' WHERE id='".$curdoc['id']."';");
							} else {
								$this->MessageCreate("SHPERR", "A shipment was made for order: " . $ediID . " without the order existing.");
								$changed_doc = $conn->query("UPDATE orders_status SET order_status = 'No Matching Order', associated_order = '" . $ediID . "' WHERE id='".$curdoc['id']."';");
							}
						}
					}
				break;
			}
            $emails = WC()->mailer()->get_emails();
            //wp_mail( 'support@itrexus.com', 'Test Shipping Doc', 'Test Message', "Content-Type: text/html\r\n");
            do_action('ITR_received_shipment', $ediFile, $ediID);
		}
	}
	/*function my_wp_nav_menu_args( $args = '' ) {
	  $args['walker'] = new rc_scm_walker();
	  return $args;
	} */
	
	/*
		Color Change For Single Product
	*/
	public function color_select(){
		global $post, $woocommerce, $product, $wpdb;
		$colorcodeFile = fopen('/home/strongbo/public_html/haws2/wp-content/plugins/itrexus_woocommerce/colorcodes.csv', "r");
		$colorcodes = array();
		while (($data = fgetcsv($colorcodeFile, 1600, ",")) !== FALSE) {
			$colorcodes[strtoupper($data[0])] = ucwords($data[1]);
		}
		$product_id = $post->ID;
		$sku = strtoupper(get_meta($product_id, "sku"));
		error_log($sku);
		$variations = $wpdb->get_results( 'SELECT post_id,meta_value FROM '.$wpdb->prefix.'postmeta where UPPER(meta_value) LIKE "'.$sku.'%" AND meta_key="attribute_pa_style" ORDER BY meta_value ASC;' );
		foreach ( $variations as $variation )
		{	
			$style = strtoupper(get_meta($variation->post_id, "attribute_pa_style"));
			$stylesArr[] = $style;
		}
		$stylesArr = array_unique($stylesArr);
		?>
		<?php foreach($stylesArr as $sty): ?>
		<div class="colorbox" style="display: inline-block; margin: 5px; text-align: center; vertical-align: top; width: 94px;">
			<img class="itrImgSel" style="background-image: url(<?php echo 'http://strongboxui.com/haws2/pix.php?f='.$sty.'_SW'?>); width: 65px; height: 35px; display: block; margin-left: 14px;"></img>
			<span style="width: 100%;"><?php echo $colorcodes[substr($sty,-3,3)];?></span>
			<div class="imgsrc" style="display: none;"><?php echo $sty; ?></div>
		</div>
		<?php endforeach; ?>
		<?php
	}
	/*
		Price Grid For Product
	*/
	public function Price_View(){
		global $post, $woocommerce, $product, $wpdb;
        if(is_user_logged_in()){
            $product_id = $post->ID;
            $sku = strtoupper(get_meta($product_id, "sku"));
            $discount1 = get_option("_bulkdiscount_discount_1");
            $discount2 = get_option("_bulkdiscount_discount_2");
            $discount3 = get_option("_bulkdiscount_discount_3");
            $variations = $wpdb->get_results( 'SELECT post_id,meta_value FROM '.$wpdb->prefix.'postmeta where UPPER(meta_value) LIKE "'.$sku.'%" AND meta_key="attribute_pa_style" ORDER BY meta_value ASC;' );
            $PricesArr = array();
            foreach ( $variations as $variation )
            {	
                $price = get_meta($variation->post_id, "regular_price");
                if ($price > 0 && $price != 123456789){
                    $PricesArr[] = $price;
                }
            }
            $PricesArr = array_unique($PricesArr);
            asort($PricesArr);
            $regsize=1;
            ?>
            <div class="priceBox">
                <h2>Pricing</h2>
            <?php foreach($PricesArr as $price): ?>
            <?php if ($regsize > 1): ?>
                    <h3>Big/Tall</h3>
            <?php endif; ?>
                <div class="priceTier">1-6: </div>
                <span>$<?php echo number_format($price * (1 - $discount1/100),2,'.',','); ?></span>
                <div class="priceTier">7-47: </div>
                <span>$<?php echo number_format($price * (1 - $discount2/100),2,'.',','); ?></span>
                <div class="priceTier">48+: </div>
                <span>$<?php echo number_format($price * (1 - $discount3/100),2,'.',','); ?></span>
                <div class="clear"></div>
        <?php   $regsize++; ?>
            <?php endforeach; ?>
            </div>
		<?php
        }
	}
    
    public function inline_save(){
        global $wpdb;

        error_log("TTTTTTTEEEEEEEEEEESSSSSSTTTTTTTTTT;");    
        if ( ! isset($_POST['post_ID']) || ! ( $post_ID = $_POST['post_ID'] ) )
            wp_die();
        
        $data = &$_POST;
        
        $child_posts = $wpdb->get_results("Select post_id IN (SELECT * from " . $wpdb->prefix . "postmeta WHERE meta_value='" . $data["_sku"] . "') Group By post_id;");
        error_log("Select post_id IN (SELECT * from " . $wpdb->prefix . "postmeta WHERE meta_value='" . $data["_sku"] . "') Group By post_id;");    
        foreach ($child_posts as $child){
            error_log( '<p>' . $child->post_id . '</p>');
        }
        wp_die();
    }
	
	/*
		Actions
	*/
	public function Create_Actions() {
            add_action('admin_menu', array($this, 'Submenus'));
            add_action('woocommerce_update_order_item', array($this, 'Check_Is_Multivendor'));
            add_action('woocommerce_variation_display', array($this, 'Variation_Grid'));
            add_action('woocommerce_variation_display', array($this, 'Grid_View'));
            add_action("activated_plugin", array($this, "this_plugin_last"));
            add_action('woocommerce_checkout_order_processed', array($this, 'write_EDI_order'));
            add_action('ITR_Pre_Change_Order', array($this, 'pre_change_order') );
            add_action('ITR_Change_Order', array($this, 'change_order') );
            add_action('wp_ajax_inline_save', array($this, 'inline_save'), 2 );
            remove_action('woocommerce_after_single_product_summary','woocommerce_output_product_data_tabs');
            remove_action('woocommerce_single_product_summary','woocommerce_template_single_price');
            remove_action('woocommerce_single_product_summary','woocommerce_template_single_excerpt');
            remove_action('woocommerce_single_product_summary','woocommerce_template_single_add_to_cart');
            add_action( 'woocommerce_single_product_summary', 'woocommerce_output_product_data_tabs', 25 );
            add_action( 'woocommerce_single_product_summary', array($this, 'color_select'), 45 );
            add_action( 'woocommerce_single_product_summary', array($this, 'Price_View'), 26 );
            add_action('minute_tasks', array($this, 'Check_EDI'));
            add_action('woocommerce_after_checkout_validation', array($this, 'pre_checkout'));
            add_action('woocommerce_checkout_order_processed', array($this, 'post_checkout'));
            add_action('woocommerce_check_cart_items', array($this, 'duplicate_order'));
            remove_action( 'woocommerce_sidebar', 10);
            add_action( 'yit_header', array($this, 'pre_slider'), 101 ); 
            add_action( 'woocommerce_init', array( $this, 'create_company_class' ) );

            //Scripts
            add_action('woocommerce_variation_display', array($this, 'Variation_Script'));
            wp_enqueue_script( 'ITR_javascript', $this->plugin_url() . '/itrexus.js', array('jquery'));
            wp_enqueue_script( 'ITR_bootstrap', 'http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js', array('jquery'));
            add_filter( 'woocommerce_login_redirect', array($this, 'wc_custom_user_redirect'), 10, 2 );
            add_action( 'wp_enqueue_scripts', function(){ wp_enqueue_style( 'ITR_CSS', $this->plugin_url() . '/itrexus.css'); }, 4000);
            wp_enqueue_style('ITR_bootstrap_css', "http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css");
        
        //Check for custom customer order number
        add_filter('woocommerce_order_number', array($this, 'Check_For_Customer_Order_Number'), 10, 2);
        add_filter('ITR_edit_order_number', array($this, 'Edit_Order_Insert'), 10, 2);
        //Emails
        add_filter('woocommerce_email_classes', array($this, 'ITR_Emails'));
		//add_filter( 'wp_nav_menu_args', array($this, 'my_wp_nav_menu_args') );
        
        
	}
	
	public function Variation_Script() {
		wp_enqueue_style( 'ITR_Variation_Grid_CSS', $this->plugin_url() . '/variation_grid.css');
		wp_enqueue_script( 'ITR_Variation_Grid', $this->plugin_url() . '/variation_grid.js', array('jquery'));
	}
	/*
		EDI Functions
	*/
	public function DocTypetoString($type){
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
	public function FindSegment($order, $seg, $code, $qual = 0, $qualCode = '', $envVal = 0){
		for ($l=$envVal; $l < $order["totalLines"]; $l++){
			if ($order[$l][0] == $seg){
				if($qual>0){
					if ($order[$l][$qual] == $qualCode){
						 return ($order[$l][$code]);
					}
				} else {
					return ($order[$l][$code]);
				}
			}	
		}
	}
	public function FindSegmentPos($order, $seg, $code = '', $qual = 0, $qualCode = '', $envVal=0){
		for ($l=$envVal; $l < $order["totalLines"]; $l++){
			if ($order[$l][0] == $seg){
				if($qual>0){
					if ($order[$l][$qual] == $qualCode){
						return $l;
					}
				} else {
					return $l;
				}
			}
		}
	}
	public function FindQual($qualcode, $segment) {
		$refdb = new SQLite3('defdb/' . substr($segment, 0, 1));
		$defres = $refdb->query("select qualdef from qualifier where qualcode='".$qualcode."' and recordname='".$segment."';");
		$defrow = $defres->fetchArray();	
		return $defrow[0];
	}
	public function RevQual($qualcode, $segment) {
		$refdb = new SQLite3('defdb/' . substr($segment, 0, 1));
		$defres = $refdb->query('select qualcode from qualifier where UPPER(qualdef)=UPPER("'.$qualcode.'") and recordname="'.$segment.'";');
		$defrow = $defres->fetchArray();	
		return $defrow[0];
	}
	public function TPLookup($tpid) {
		return "TP Lookup Function -- $tpid";
	}
	public function carrierLookup($carrierId) {
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
	public function MessageCreate($type, $message){
		global $wpdb;
		$wpdb->insert( 
			$wpdb->prefix.'itrmessages',
			array(
				'msgtype' 				=> $type,
				'message'			 	=> $message,
			)
		);
	}
    
    public function Check_For_Customer_Order_Number($id, $order){
        //Check if the logged in user is an admin. If so, display Admin order number. If not, display the Customer's order number
        $admin_custom_number = get_post_meta($order->id, 'order_number', true);
        $customer_custom_number = get_post_meta($order->id, 'customer_order_number', true);
        if( is_empty($admin_custom_number) ){
            return is_empty($customer_custom_number) ? $id : _x( '#', 'hash before order number', 'woocommerce' ) . $customer_custom_number;
        } else{
            return _x( '#', 'hash before order number', 'woocommerce' ) . $admin_custom_number;
        }
        /*
        if ( current_user_can( 'edit_posts') ){
            $custom_number = get_post_meta($order->id, 'order_number', true);
            return is_empty($custom_number) ? $id : _x( '#', 'hash before order number', 'woocommerce' ) . $custom_number;
        } else {
            $custom_number = get_post_meta($order->id, 'customer_order_number', true);
            return is_empty($custom_number) ? $id : _x( '#', 'hash before order number', 'woocommerce' ) . $custom_number;
        }
        */
    }
    
    public function Edit_Order_Insert($data, $db){
        if ( get_option('ITR_IN_ORDER_CHECK') ){
            $q = "SELECT ID FROM " . $db->prefix . 'posts' . " GROUP BY ID ORDER BY length(ID) DESC, ID DESC Limit 1;";
            $IDq = $db->get_col($q);
            $lastID = $IDq[0];
            $data['ID'] = ++$lastID;
            return $data;
        } else {
			$q = "SELECT ID FROM " . $db->prefix . 'posts' . " GROUP BY ID ORDER BY length(ID) DESC, ID DESC Limit 1;";
			$IDq = $db->get_col($q);
			$lastID = $IDq[0];
			$data['ID'] = ++$lastID;
            return $data;
        }
    }
    
    public function pre_checkout(){
        update_option('ITR_IN_ORDER_CHECK', 1);
    }
    
    public function post_checkout(){
        update_option('ITR_IN_ORDER_CHECK', 0);
    }
    
    public function get_post_id_from_order_number($PONumber){
        global $wpdb;
        $post_id = $wpdb->get_results( 'SELECT post_id FROM '.$wpdb->prefix.'postmeta where meta_value ="'.$PONumber.'" AND meta_key="order_number";' )[0]->post_id;
        if ( isset($post_id)){
            return $post_id;
        } else {
            return $PONumber;
        }
    }
    
    public function ITR_Emails($wc_emails){
		$wc_emails['ITR_Email_Shipment']      = include( 'emails/class-itr-ship-notice.php' );
        //WC()->mailer()->get_emails();
        return $wc_emails;
    }
        
    public function pre_slider(){
        echo '<div style="padding-left: 278px; margin-left: 30px; margin-bottom: 65px; margin-right: 30px;">';
        wc_print_notices();
        echo '</div>';
    }
    
    public function after_plugins_includes(){
		//include_once( 'includes/wc-class-wc_company.php' );
        if( $_GET['prodtest'] != '' ){
            if($_GET['prodtest'] == 'prod'){
                update_user_meta(get_current_user_id(), 'ITR_prod', true);
            } else {
                update_user_meta(get_current_user_id(), 'ITR_prod', false);
            }
        }
    }
    
    private function includes(){
		include_once( 'includes/wc-class-wc_company.php' );
    }
    
    public function create_company_class(){
        WC()->customer = new WC_Company();
//        echo '<p>' . WC()->customer->company_id . '</p>';
    }

    public function duplicate_order_url( $order = null ){
        if ( is_null( $order ) ){
            return '#';
        }   
        if ( is_string( $order ) ){
            $order_id = $order;
        }
        if ( is_a( $order, 'WC_Order' ) ){
            $order_id = $order->id;
        }
        return get_permalink( wc_get_page_id( 'cart' )) . '?o=' . esc_html($order_id);
    }
    
    public function duplicate_order(){
        $order_id = isset($_GET['o']) ? $_GET['o'] : '';
        if ( $order_id == '' ){
            return null;
        }
		$order = new WC_Order( $order_id );
        $orderitems = $order->get_items();
        foreach ( $orderitems as $item ){
            WC()->cart->add_to_cart( $item[ 'product_id' ], $item[ 'qty' ] );
        }
        wp_redirect(get_permalink( wc_get_page_id( 'cart' )));
        exit;
    }
    
    public function get_error_message_type( $errmsg ){
        switch($errmsg){
            case 'ORDERR':
                return "Order Error";
                break;
            case 'SHPERR':
                return "Shipment Error";
                break;
            case 'ITMERRPRICE':
                return "Product Price Missing";
                break;
            default:
                return $errmsg;
                break;
        }
    }
    
}

endif;


function ITR() {
	return ITRexus_Woocommerce::instance();
}

if (! function_exists('get_meta') ){
	function get_meta($post_id, $meta_id){
		global $wpdb;
		if(substr($meta_id,0,12) != "attribute_pa"){ $meta_id = "_" . $meta_id; }
		return $wpdb->get_results( 'SELECT meta_value FROM '.$wpdb->prefix.'postmeta where meta_key="'.$meta_id.'" AND post_id="'.$post_id.'";', ARRAY_N )[0][0];
	}
}
if (! function_exists('arr_diff') ){
	function arr_diff($a1,$a2){
	  foreach($a1 as $k=>$v){
		unset($dv);
		if(is_int($k)){
		  // Compare values
		  if(array_search($v,$a2)===false) $dv=$v;
		  else if(is_array($v)) $dv=arr_diff($v,$a2[$k]);
		  if($dv) $diff[]=$dv;
		}else{
		  // Compare noninteger keys
		  if(!$a2[$k]) $dv=$v;
		  else if(is_array($v)) $dv=arr_diff($v,$a2[$k]);
		  if($dv) $diff[$k]=$dv;
		}    
	  }
	  return $diff;
	}
}
// Retrieve JPEG width and height without downloading/reading entire image.
if (! function_exists('getjpegsize') ){
	function getjpegsize($img_loc) {
		$handle = fopen($img_loc, "rb") or die("Invalid file stream.");
		$new_block = NULL;
		if(!feof($handle)) {
			$new_block = fread($handle, 32);
			$i = 0;
			if($new_block[$i]=="\xFF" && $new_block[$i+1]=="\xD8" && $new_block[$i+2]=="\xFF" && $new_block[$i+3]=="\xE0") {
				$i += 4;
				if($new_block[$i+2]=="\x4A" && $new_block[$i+3]=="\x46" && $new_block[$i+4]=="\x49" && $new_block[$i+5]=="\x46" && $new_block[$i+6]=="\x00") {
					// Read block size and skip ahead to begin cycling through blocks in search of SOF marker
					$block_size = unpack("H*", $new_block[$i] . $new_block[$i+1]);
					$block_size = hexdec($block_size[1]);
					while(!feof($handle)) {
						$i += $block_size;
						$new_block .= fread($handle, $block_size);
						if($new_block[$i]=="\xFF") {
							// New block detected, check for SOF marker
							$sof_marker = array("\xC0", "\xC1", "\xC2", "\xC3", "\xC5", "\xC6", "\xC7", "\xC8", "\xC9", "\xCA", "\xCB", "\xCD", "\xCE", "\xCF");
							if(in_array($new_block[$i+1], $sof_marker)) {
								// SOF marker detected. Width and height information is contained in bytes 4-7 after this byte.
								$size_data = $new_block[$i+2] . $new_block[$i+3] . $new_block[$i+4] . $new_block[$i+5] . $new_block[$i+6] . $new_block[$i+7] . $new_block[$i+8];
								$unpacked = unpack("H*", $size_data);
								$unpacked = $unpacked[1];
								$height = hexdec($unpacked[6] . $unpacked[7] . $unpacked[8] . $unpacked[9]);
								$width = hexdec($unpacked[10] . $unpacked[11] . $unpacked[12] . $unpacked[13]);
								return array($width, $height);
							} else {
								// Skip block marker and read block size
								$i += 2;
								$block_size = unpack("H*", $new_block[$i] . $new_block[$i+1]);
								$block_size = hexdec($block_size[1]);
							}
						} else {
							return FALSE;
						}
					}
				}
			}
		}
		return FALSE;
	}
}

//Compare 2 files
if (! function_exists('files_identical') ){
	function files_identical($fn1, $fn2) {
		$READ_LEN = 4096;
		if(filetype($fn1) !== filetype($fn2))
			return FALSE;

		if(filesize($fn1) !== filesize($fn2))
			return FALSE;

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
}
if (! function_exists('cmpsize') ){
    function cmpsize($a, $b){
	//$a and $b are populated by the $variationsArr arrays keys
	$sizes = array(
		"XXS" => 0,
		"2XS" => 0,
		"SHT" => 0,
		"XS" => 1,
		"XSM" => 1,
		"S" => 2,
		"SML" => 2,
		"M" => 3,
		"MED" => 3,
		"REG" => 3,
		"L" => 4,
		"TLL" => 4,
		"LRG" => 4,
		"XLG" => 5,
		"XL" => 5,
		"XXL" => 6,
		"2XL" => 6,
		"3XL" => 7,
		"4XL" => 8,
		"5XL" => 9,
	);
	if(is_numeric($a)){
		if($a == $b){ return 0; }
		if($a < $b){ return -1; }
		if($a > $b){ return 1; }
	} else {
		$asize = $sizes[strtoupper($a)];
		$bsize = $sizes[strtoupper($b)];
		if($asize == $bsize){ return 0; }
		if($asize < $bsize){ return -1; }
		if($asize > $bsize){ return 1; }
	}
}
}
// Global for backwards compatibility.
$GLOBALS['ITR_woocommerce'] = ITR();
?>