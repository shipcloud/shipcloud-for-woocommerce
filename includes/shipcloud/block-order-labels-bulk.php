<?php

class WooCommerce_Shipcloud_Block_Order_Labels_Bulk {
	private $template_file;
	private $allowed_carriers;

	/**
	 * @var Woocommerce_Shipcloud_API
	 */
	private $shipcloud_api;

	public function __construct( $template_file, $allowed_carriers, $shipcloud_api ) {
		$this->template_file = $template_file;
		$this->allowed_carriers = $allowed_carriers;
		$this->shipcloud_api = $shipcloud_api;
	}

	/**
	 * @return Woocommerce_Shipcloud_API
	 */
	public function get_shipcloud_api() {
		return $this->shipcloud_api;
	}

	/**
	 * Associative array of service id and labels.
	 *
	 * @return string[]
	 */
	public function get_services() {
		$services = array();

		foreach ( $this->get_shipcloud_api()->get_services() as $id => $settings ) {
			$services[$id] = $settings['name'];
		}

		return $services;
	}

	/**
	 * Associative array of carrier id and display name.
	 *
	 * @return string[]
	 */
	public function get_allowed_carriers() {
		return $this->allowed_carriers;
	}

	public function render() {
		ob_start();
		$this->dispatch();

		return ob_get_clean();
	}

	public function dispatch() {
		require $this->get_template_file();
	}

	/**
	 * @return mixed
	 */
	protected function get_template_file() {
		return $this->template_file;
	}
}
