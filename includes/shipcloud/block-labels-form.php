<?php

/**
 * Labels bulk for shipcloud WooCommerce.
 *
 * @author  awesome.ug <support@awesome.ug>
 * @package shipcloudForWooCommerce
 * @since   1.4.0
 * @license GPL 2
 *          Copyright 2017 (support@awesome.ug)
 *          This program is free software; you can redistribute it and/or modify
 *          it under the terms of the GNU General Public License, version 2, as
 *          published by the Free Software Foundation.
 *          This program is distributed in the hope that it will be useful,
 *          but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          GNU General Public License for more details.
 *          You should have received a copy of the GNU General Public License
 *          along with this program; if not, write to the Free Software
 *          Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
class WooCommerce_Shipcloud_Block_Labels_Form {
	/**
	 * @var \Shipcloud\Domain\Carrier[]
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

	/**
	 * Create new bulk view.
	 *
	 * @param string                      $template_file    Path to the template.
	 * @param \WC_Shipcloud_Order         $order
	 * @param \Shipcloud\Domain\Carrier[] $allowed_carriers List of carriers that can be selected.
	 * @param \Shipcloud\Api              $shipcloud_api    Connection to the API.
	 */
	public function __construct( $template_file, $order, $allowed_carriers, $shipcloud_api ) {
		$this->template_file    = $template_file;
		$this->allowed_carriers = $allowed_carriers;
		$this->shipcloud_api    = $shipcloud_api;
		$this->order            = $order;
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
	 * Ordered shipping method.
	 *
	 * @return string
	 */
	public function get_shipping_method_name() {
		if ( ! $this->get_order() || ! $this->get_order()->get_wc_order() ) {
			return '';
		}

		return $this->get_order()->get_wc_order()->get_shipping_method();
	}

	/**
	 * @return \WC_Shipcloud_Order
	 */
	public function get_order() {
		return $this->order;
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
	
	public function get_calculated_weight() {
		return $this->get_order()->get_calculated_weight();
	}

	/**
	 * @return mixed
	 */
	protected function get_template_file() {
		return $this->template_file;
	}
}
