<?php

namespace Shipcloud\Controller;

use Shipcloud\Api;
use Shipcloud\Repository\ShipmentRepository;

class LabelController {
	const AJAX_UPDATE = 'wp_ajax_shipcloud_label_update';

	/**
	 * @var Api
	 */
	private $api;

	/**
	 * @var ShipmentRepository
	 */
	private $shipment_repository;

	/**
	 * Label_Controller constructor.
	 *
	 * @param Api $api
	 */
	public function __construct( Api $api, ShipmentRepository $shipment_repository ) {
		$this->api                 = $api;
		$this->shipment_repository = $shipment_repository;
	}

	public function update() {
		if ( ! $this->is_authenticated() ) {
			$this->error( 403, __( 'Not authenticated' ) );

			return;
		}

		$data = $this->parse_request( $_REQUEST );

		try {
			$this->shipment_repository->update( $_REQUEST['shipment_order_id'], $_REQUEST['shipment_id'], $data );
		} catch ( \Exception $e ) {
			$this->error( 400, $e->getMessage() );

			return;
		}

		wp_send_json_success( $data );
	}

	protected function is_authenticated() {
		return is_admin() && is_user_logged_in();
	}

	protected function error( $httpCode, $errorMessage ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_send_json_error( new \WP_Error( $httpCode, $errorMessage ), $httpCode );
		}
	}

	/**
	 * @return mixed
	 */
	protected function parse_request( $data = null ) {
		if ( null === $data ) {
			$data = $_REQUEST;
		}

		$data['id'] = $data['shipment_id'];

		unset( $data['action'] );
		unset( $data['shipment_id'] );
		unset( $data['shipment_order_id'] );

		return $data;
	}

	protected function store_order_meta( $order_id, $data ) {
		get_post_meta( $order_id, 'shipcloud_shipment_data', $shipment_data );
	}
}
