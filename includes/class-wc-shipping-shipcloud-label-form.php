<?php

/**
 * WC_Shipping_Shipcloud_Label_Form represents a labels formular.
 *
 * @category 	Class
 * @package 	WC_Shipping_Shipcloud
 * @author   	Daniel Muenter <info@msltns.com>
 * @license 	GPL 3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'WC_Shipping_Shipcloud_Label_Form' ) ) {
	
	class WC_Shipping_Shipcloud_Label_Form {
		
		/**
		 * @var array $allowed_carriers
		 */
		protected $allowed_carriers;

		/**
		 * @var \WC_Shipcloud_Order
		 */
		protected $order;

		/**
		 * @var \Shipcloud\Api
		 */
		private $shipcloud_api;

		private $template_file;

    private $bulk_action;
		/**
		 * Create new bulk view.
		 *
		 * @param string                      $template_file    Path to the template.
		 * @param \WC_Shipcloud_Order         $order
		 * @param \Shipcloud\Domain\Carrier[] $allowed_carriers List of carriers that can be selected.
		 * @param \Shipcloud\Api              $shipcloud_api    Connection to the API.
     * @param boolean                     $bulk_action      true, when label form is called in bulk 
     *                                                      action
		 */
		public function __construct( $template_file, $order, $allowed_carriers, $shipcloud_api, $bulk_action ) {
			$this->template_file 	= $template_file;
			$this->order 			= $order;
			$this->allowed_carriers = $order->get_allowed_carriers();
			$this->shipcloud_api 	= null;
      $this->bulk_action = $bulk_action;
		}

		/**
		 * @return \Shipcloud\Domain\Carrier[]
		 */
		public function get_allowed_carriers() {
			return $this->allowed_carriers;
		}

		/**
		 * @return \Shipcloud\Api
		 */
		public function get_shipcloud_api() {
			return $this->shipcloud_api;
		}

		/**
		 * @return \WC_Shipcloud_Order
		 */
		public function get_order() {
			return $this->order;
		}

    /**
     * @return boolean
     */
    public function is_bulk_action() {
      return $this->bulk_action;
    }
		/**
		 * Pre-render content.
		 *
		 * @return string
		 */
		public function render() {
			ob_start();

			$this->dispatch();

			return ob_get_clean();
		}

		/**
		 * Send content to client.
		 */
		public function dispatch() {
			require $this->get_template_file();
		}
	
		protected function get_calculated_weight() {
			return $this->get_order()->get_calculated_weight();
		}

		/**
		 * @return mixed
		 */
		protected function get_template_file() {
			return $this->template_file;
		}
		
		protected function is_auto_weight_calculation_on() {
			return $this->get_order()->is_auto_weight_calculation_on();
		}
		
		protected function get_wc_order() {
			return $this->get_order()->get_wc_order();
		}
		
		protected function get_description() {
			return $this->get_order()->get_description();
		}
		
		protected function get_shipping_method_name() {
			return $this->get_order()->get_shipping_method_name();
		}
		
		protected function get_global_reference_number() {
			return $this->get_order()->get_global_reference_number();
		}

		protected function carrier_email_notification_enabled() {
			return $this->get_order()->carrier_email_notification_enabled();
		}

		protected function email_notification_enabled() {
			return $this->get_order()->email_notification_enabled();
		}

		protected function shipcloud_email_notification_enabled() {
			return $this->get_order()->shipcloud_email_notification_enabled();
		}

		protected function get_email_for_notification() {
			return $this->get_order()->get_email_for_notification();
		}
		
		/*****************************************************************
         *
         *		UTILITIES
         *
         *****************************************************************/

        /**
         * Getting option (overwrite instance values if there option of instance is empty
         *
         * @param string $key
         * @param null   $empty_value
         * @return mixed|string
         */
        private function get_option( string $key, $empty_value = null ) {
            return WC_Shipping_Shipcloud_Utils::get_option( $key, $empty_value );
        }

        /**
         * Output an admin notice.
         *
         * @param string 	$message 		Debug message.
         * @param string 	$type    		Message type.
         * @param bool 		$dismissible    Message type.
         * @return void
         */
        private function add_admin_notice( $message, $type = 'info', $dismissible = true ) {
            WC_Shipping_Shipcloud_Utils::add_admin_notice( $message, $type, $dismissible );
        }

		/**
		 * Output a debug message.
		 *
		 * @param string 	$message 	Debug message.
		 * @param string 	$level   	Debug level.
         * @param mixed 	$context	The Debug context.
		 * @return void
		 */
        private function log( $message, $level = 'info', $context = [] ) {
            WC_Shipping_Shipcloud_Utils::log( $message, $level, $context );
        }
	}
}
