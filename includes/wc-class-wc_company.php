<?php

class WC_Company extends WC_Customer {
	/*
		Company has sub users.
			-- Sub Users have access to all company processes except creating users.
			
		FEATURE LIST:
			-- Associates WP_Users with Company
			-- Sub Users place orders with Company's id, but have their id associated with the order in WP
			-- Company can create Sub Users
			-- Sub Users have access to all of Company's address information
			
			
		SCENARIO:
			Company 			= User level 5
			Company Sub User 	= User Level 4
			
			Company User ID		= 50
			SubUser User ID 	= 55
			
			All Orders created by Sub User (55) are created with Company ID (50)
            And a customer reference of Sub User (55)
			
			All Inbound Documents are applied to Company ID (50)
			
			Sub User (55) sees all information associated with Company (50)
			
			850 need to send HAWS Company (50)
			
			855 without Company ID (50) gets associated to Company (50) by matching Shipping Address.
			If no Shipping Address exists, then it goes into a pool of unassigned documents.
			Once an inbound Order is assigned to a Company (50) the Shipping Address is added to the Company (50) Shipping Addresses.
			Next time an order comes in with that shipping address, it is assigned to Company (50)
			"ASSOCIATE ALL" Button will assign all existing documents that match to Company(50)
			
			User information is INHERITED from Company.
			So the class pulls the COMPANY information and applies it to the active Customer.
			
			COMPANY (50) needs to be able to track who placed the order.
			
		FILTERS/HOOKS:
			add_filter( 'woocommerce_checkout_customer_id', array( $this, '' ) );
			add_filter( 'woocommerce_checkout_customer_userdata', array( $this, '' ) );
            add_filter( 'woocommerce_new_customer_data', array( $this, 'new_registration' ) );
			add_action( 'woocommerce_init', array($this, 'change_customer_to_company'), 1 );
		
		FUNCTIONS:
			GENERAL:
				-find_company
			ADMIN:
				-admin_page
	}
			
	*/
    	/** Stores customer data as an array */
	protected $_data;

	/** Stores bool when data is changed */
	private $_changed = false;
    
    public $company;
    
    public $company_id;
    
    private $user;
    
    public $is_company;
    
	public function __construct(){
        $this->user = get_user_by( 'id', get_current_user_id() );
        $this->company = $this->find_company();
        $this->company_id = $this->find_company_id();
        
        if( ! is_null(WC()->session) ){
            $this->_data = WC()->session->get( 'customer' );
            if ( empty( $this->_data ) ) {
                $this->_data = array(
                    'country' 				=> esc_html( $this->get_default_country() ),
                    'state' 				=> '',
                    'postcode' 				=> '',
                    'city'					=> '',
                    'address' 				=> '',
                    'address_2' 			=> '',
                    'shipping_country' 		=> esc_html( $this->get_default_country() ),
                    'shipping_state' 		=> '',
                    'shipping_postcode' 	=> '',
                    'shipping_city'			=> '',
                    'shipping_address'		=> '',
                    'shipping_address_2'	=> '',
                    'is_vat_exempt' 		=> false,
                    'calculated_shipping'	=> false
                );
            }

        }
            
        $this->actions_and_filters();

        //Check if the current user is a Company or Customer
        if( $this->company_id == get_current_user_id() ){
            //Is Company
            //$this->update_parent_company( $this->company_id );
            $this->is_company = true;
        } else {
            //Is Customer
            
            
        }
        
		// When leaving or ending page load, store data
		add_action( 'shutdown', array( $this, 'save_data' ), 10 );
	}
    
	public function find_company(){
        
        $cid = get_user_meta( get_current_user_id(), '_wc_company', true);
        if( $cid == ''){ $cid = get_current_user_id(); }
        return $cid != '' ?  get_user_by( 'id', $cid ) :  get_user_by( 'id', get_current_user_id() );
    
    }
	
	public function find_company_id(){
    
        return $this->company->ID;
    
    }
    
    
    public function update_parent_company( $company, $customer_id = null ){
        
        if( is_null($customer_id) ){
            
            $customer_id = get_current_user_id();
            
        }
        
        //Check if the supplied $company is a username of user_id
        if( is_string( $company ) ){
            
            $company_user = get_user_by( 'slug', $company );
            $company_id = $company_user->ID;
            
        } elseif( is_numeric( $company ) ){
            
            $company_id = $company;
            
        }
        
        update_user_meta( $customer_id, '_wc_company', $company_id );
        
    }
    
    private function actions_and_filters(){
		add_filter( 'woocommerce_checkout_get_value', array( $this, 'get_company_value'), 10, 2 );
        add_filter( 'woocommerce_new_customer_data', array( $this, 'new_registration' ) );
        add_filter( 'woocommerce_my_account_my_orders_query', array($this, 'get_company_orders') );
        add_action( 'woocommerce_checkout_order_processed', array($this, 'add_company_to_order') );
    }
    
    public function get_company_value( $placeholder, $input ){
        
        $address_fields = array_merge( WC()->countries->get_address_fields(), WC()->countries->get_address_fields( '', 'shipping_' ) );
        
        if ( is_user_logged_in() && array_key_exists( $input, $address_fields ) ) {
            
            if ( $meta = get_user_meta( $this->company_id, $input, true ) ) {
                return $meta;
            }

            if ( $input == 'billing_email' ) {
                //return  $this->company_id;
            }
        }
        switch ( $input ) {
            case 'billing_country' :
                return apply_filters( 'default_checkout_country', WC()->customer->get_country() ? WC()->customer->get_country() : WC()->countries->get_base_country(), 'billing' );
            case 'billing_state' :
                return apply_filters( 'default_checkout_state', WC()->customer->has_calculated_shipping() ? WC()->customer->get_state() : '', 'billing' );
            case 'billing_postcode' :
                return apply_filters( 'default_checkout_postcode', WC()->customer->get_postcode() ? WC()->customer->get_postcode() : '', 'billing' );
            case 'shipping_country' :
                return apply_filters( 'default_checkout_country', WC()->customer->get_shipping_country() ? WC()->customer->get_shipping_country() : WC()->countries->get_base_country(), 'shipping' );
            case 'shipping_state' :
                return apply_filters( 'default_checkout_state', WC()->customer->has_calculated_shipping() ? WC()->customer->get_shipping_state() : '', 'shipping' );
            case 'shipping_postcode' :
                return apply_filters( 'default_checkout_postcode', WC()->customer->get_shipping_postcode() ? WC()->customer->get_shipping_postcode() : '', 'shipping' );
            default :
                return apply_filters( 'default_checkout_' . $input, null, $input );
        }
    
    }
    
    public function new_registration( $userdata ){
        $userdata['role'] = 'company';
        return $userdata;
    }
    
    public function admin_page(){
        
    }
    
    public function get_company_orders( $orderinfo ){
        if( $this->is_company ){
            $orderinfo['meta_value'] = '';
            $orderinfo['meta_query'] = array(
                array(
                        'key'       =>  '_customer_user',
                        'value'     =>  join(',', $this->get_all_user_ids()),
                        'compare'   =>  'IN',
                )
            );
        } else {
            $orderinfo['meta_value'] = '';
            $orderinfo['meta_query'] = array(
                array(
                        'key'       =>  '_customer_user',
                        'value'     =>  join(',', $this->get_all_user_ids()),
                        'compare'   =>  'IN',
                )
            );
            //$orderinfo['meta_value'] = $this->company_id;
        }
        return $orderinfo;
    }
    
    public function get_all_user_ids(){
        $users = get_users(array(
            'meta_key'  =>  '_wc_company',
            'meta_value'=>  $this->company_id,
        ) );
        $userids = array();
        foreach( $users as $user ){
            $userids[] = $user->ID;
        }
        return $userids;
    }
	
    public function add_company_to_order( $order_id, $posted ){
        update_post_meta($order_id, '_wc_company', $this->company_id);
    }
    
}
?>