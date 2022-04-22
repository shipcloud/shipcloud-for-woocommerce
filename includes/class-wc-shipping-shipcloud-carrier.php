<?php

/**
 * WC_Shipping_Shipcloud_Carrier represents a serializable carrier object.
 *
 * @category 	Class
 * @package 	WC_Shipping_Shipcloud
 * @author   	Daniel Muenter <info@msltns.com>
 * @license 	GPL 3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'WC_Shipping_Shipcloud_Carrier' ) ) {
	
	class WC_Shipping_Shipcloud_Carrier implements \JsonSerializable {
		
		/**
		 * @var string
		 */
		private $display_name;

		/**
		 * @var string
		 */
		private $name;

		/**
		 * @var array
		 */
		private $package_types;

		/**
		 * @var array
		 */
		private $services;

		/**
		 * @var array
		 */
		private $additional_services;

		/**
		 * @var array
		 */
		private $label_formats;

		/**
		 * Create new carrier.
		 *
		 * @param array  $carrier
		 * @return void
		 */
		public function __construct( array $carrier = [], array $services = [] ) {
			$this->name					= $carrier['name'];
			$this->display_name			= $carrier['display_name'];
			$this->package_types		= $carrier['package_types'];
			$this->additional_services 	= $carrier['additional_services'];
			$this->label_formats		= $carrier['label_formats'];
			$this->services 			= $services;
		}

		/**
		 * Retrieve the internal name.
		 *
		 * @return string
		 */
		public function get_name() {
			return $this->name;
		}

		/**
		 * Get a list of services.
		 *
		 * The unordered array contains the internal names of services which the carrier offers.
		 *
		 * @return array
		 */
		public function get_services() {
			return $this->services;
		}

		/**
		 * Get a list of additional services.
		 *
		 * The unordered array contains the internal names of additional services which the carrier offers.
		 *
		 * @return array
		 */
		public function get_additional_services() {
			if ( is_array( $this->additional_services ) ) {
				return $this->additional_services;
			}
			return [];
		}

		/**
		 * Get a list of label formats.
		 *
		 * The unordered array contains the internal names of label formats which the carrier offers.
		 *
		 * @return array
		 */
		public function get_label_formats() {
			if ( is_array( $this->label_formats ) ) {
				return $this->label_formats;
			}
			return [];
		}

		/**
		 * Get the display name.
		 * 
		 * @return string
		 */
		public function get_display_name() {
			return $this->display_name;
		}

		/**
		 * Get package types.
		 * 
		 * @return array
		 */
		public function get_package_types() {
			return $this->package_types;
		}

		/**
		 * Specify data which should be serialized to JSON.
		 *
		 * This is done for using the original snake_case keys instead of the camelCase properties.
		 * 
		 * @return mixed
		 */
		#[\ReturnTypeWillChange]
		public function jsonSerialize() {
			return array(
				'name'                => $this->get_name(),
				'display_name'        => $this->get_display_name(),
				'services'            => $this->get_services(),
				'package_types'       => $this->get_package_types(),
				'additional_services' => $this->get_additional_services(),
				'label_formats'       => $this->get_label_formats(),
			);
		}
		
	}

}
