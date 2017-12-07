<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ITR_Email_Shipment' ) ) :

/**
 * New Order Email
 *
 * An email sent to the admin when a new order is received/paid for.
 *
 * @class 		WC_Email_New_Order
 * @version		2.0.0
 * @package		WooCommerce/Classes/Emails
 * @author 		WooThemes
 * @extends 	WC_Email
 */
class ITR_Email_Shipment extends WC_Email {

	/**
	 * Constructor
	 */
	function __construct() {

		$this->id 				= 'shipment';
		$this->title 			= __( 'Shipment Information Updated', 'woocommerce' );
		$this->description		= __( 'Emails are sent when new shipping documents are imported into the system.', 'woocommerce' );

		$this->heading 			= __( 'Shipment Information Updated', 'woocommerce' );
		$this->subject      	= __( '[{site_title}] Shipment Information Updated (Order #SHIPMENTNUMBER)', 'woocommerce' );
        $this->email_type       = 'html';
		$this->template_html 	= $_SERVER['DOCUMENT_ROOT'] . '/haws2/acc/getDoc.php';
		$this->template_plain 	= $_SERVER['DOCUMENT_ROOT'] . '/haws2/acc/getDoc.php';

		// Triggers for this email
        add_action( 'ITR_received_shipment', array( $this, 'trigger' ), 10, 2 );
		//add_action( 'ITR_received_shipment', array( $this, 'trigger' ) );
		// Call parent constructor
		parent::__construct();

		// Other settings
		//$this->recipient = $this->get_option( 'recipient' );

		if ( ! $this->recipient )
			$this->recipient = get_option( 'admin_email' );
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	function trigger( $ediDoc, $order_id ) {
		$this->shipDoc = $ediDoc;

		/*if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		} */
        $subject = str_replace('SHIPMENTNUMBER', $order_id, $this->get_subject());
        //wp_mail( 'support@itrexus.com', 'Test Shipping Doc', 'Test Message', "Content-Type: text/html\r\n");
		//$this->send( 'granitrocky@gmail.com', $subject, $this->get_content(), $this->get_headers(), $this->get_attachments() );
		$this->send( $this->get_recipient(), $subject, $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html() {
        global $wpdb;
		ob_start();
        $ediFile = $this->shipDoc;
        $isEmail = 1;
        include($this->template_html);
/*		wc_get_template( $this->template_html, array(
			'ediFile' 		=> $this->shipDoc,/*
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => false 
		) ); */
		return ob_get_clean();
	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {
		ob_start();
		wc_get_template( $this->template_plain, array(
			'ediFile' 		=> $this->shipDoc,/*
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => false */
		) );
		return ob_get_clean();
	}

    /**
     * Initialise Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields() {
    	$this->form_fields = array(
			'enabled' => array(
				'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Enable this email notification', 'woocommerce' ),
				'default' 		=> 'yes'
			),
			'recipient' => array(
				'title' 		=> __( 'Recipient(s)', 'woocommerce' ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', 'woocommerce' ), esc_attr( get_option('admin_email') ) ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'subject' => array(
				'title' 		=> __( 'Subject', 'woocommerce' ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'woocommerce' ), $this->subject ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'heading' => array(
				'title' 		=> __( 'Email Heading', 'woocommerce' ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'woocommerce' ), $this->heading ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'email_type' => array(
				'title' 		=> __( 'Email type', 'woocommerce' ),
				'type' 			=> 'select',
				'description' 	=> __( 'Choose which format of email to send.', 'woocommerce' ),
				'default' 		=> 'html',
				'class'			=> 'email_type',
				'options'		=> array(
					'plain'		 	=> __( 'Plain text', 'woocommerce' ),
					'html' 			=> __( 'HTML', 'woocommerce' ),
					'multipart' 	=> __( 'Multipart', 'woocommerce' ),
				)
			)
		);
    }
}

endif;

return new ITR_Email_Shipment();
