<?php

/**
 * WC_Shipping_Shipcloud_Order_Labels_Bulk represents a bulk order label.
 *
 * @category 	Class
 * @package 	WC_Shipping_Shipcloud
 * @author   	Daniel Muenter <info@msltns.com>
 * @license 	GPL 3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'WC_Shipping_Shipcloud_Order_Labels_Bulk' ) ) {
	
	class WC_Shipping_Shipcloud_Order_Labels_Bulk {
	
		protected $allowed_carriers;
		
		protected $order;

		private $shipcloud_api;

		private $template_file;
		
		private $label_form;

    private $bulk_action;

		/**
		 * Create new bulk view.
		 *
		 * @param string                      $template_file    Path to the template.
		 * @param WC_Shipcloud_Order          $order
		 * @param \Shipcloud\Domain\Carrier[] $allowed_carriers List of carriers that can be selected.
		 * @param \Shipcloud\Api              $shipcloud_api    Connection to the API.
		 */
		public function __construct( $template_file, $order ) {
			$this->template_file 	= $template_file;
			$this->order 			= $order;
			$this->allowed_carriers = $order->get_allowed_carriers();
			$this->shipcloud_api 	= null;
      $this->bulk_action = true;
			
			require_once __DIR__ . '/class-wc-shipping-shipcloud-label-form.php';

			$this->label_form = new WC_Shipping_Shipcloud_Label_Form(
				dirname( __FILE__ ) . '/templates/template-label-form.php',
				$order,
				$this->allowed_carriers,
				$this->shipcloud_api,
        $this->bulk_action
			);

		}

		/**
		 * Associative array of carrier id and display name.
		 *
		 * @return string[]
		 */
		public function get_allowed_carriers() {
			return $this->allowed_carriers;
		}

		/**
		 * Associative array of service id and labels.
		 *
		 * @return string[]
		 */
		public function get_services() {
			return WC_Shipping_Shipcloud_Utils::get_carrier_services_list();
		}

		/**
		 * @return Woocommerce_Shipcloud_API
		 */
		public function get_shipcloud_api() {
			return $this->shipcloud_api;
		}

	  /**
	   * @return WC_Shipcloud_Order
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

		/**
		 * @return mixed
		 */
		protected function get_template_file() {
			return $this->template_file;
		}

		public function get_parcel_templates() {
			return $this->get_order()->get_parcel_templates();
		}

		public function parcel_templates_html() {
			$parcel_templates = $this->get_parcel_templates();
			
			ob_start();
			require( dirname( __FILE__ ) . '/templates/template-order-parcel-templates.php' );
			return ob_get_clean();
		}

		public function parcel_form_html() {
			// return $this->order->parcel_form();
			return $this->label_form->render();
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

		protected function get_global_reference_number() {
			return $this->get_order()->get_global_reference_number();
		}

		public function additional_services_form() {
			return $this->get_order()->additional_services_form();
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
