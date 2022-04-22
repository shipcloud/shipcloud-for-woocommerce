<?php

/**
 * WC_Shipping_Shipcloud_Privacy class that adds shipcloud specific 
 * information to privacy declaration.
 *
 * @category 	Class
 * @package 	WC_Shipping_Shipcloud
 * @author   	Daniel Muenter <info@msltns.com>
 * @license 	GPL 3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'WC_Shipping_Shipcloud_Privacy' ) ) {
	
	class WC_Shipping_Shipcloud_Privacy extends WC_Abstract_Privacy {
		
		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {
			parent::__construct( __( 'Template', 'shipcloud-for-woocommerce' ) );
		}

		/**
		 * Gets the message of the privacy to display.
		 *
		 * @return string The privacy message.
		 */
		public function get_privacy_message() {
			return wpautop( 
				sprintf( 
					__( 'By using this extension, you may be storing personal data or sharing data with an external service. <a href="%s" target="_blank">Learn more about how this works, including what you may want to include in your privacy policy.</a>', 'shipcloud-for-woocommerce' ), 
					'https://docs.woocommerce.com/document/privacy-shipping/#shipcloud-for-woocommerce' 
				) 
			);
		}
	}

	new WC_Shipping_Shipcloud_Privacy();
}
