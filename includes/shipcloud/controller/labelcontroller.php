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
	 * @param Api                $api
	 * @param ShipmentRepository $shipment_repository
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

		if ( ! isset( $_REQUEST['shipment'] ) ) {
			$this->error( 400, __( 'Bad request' ) );

			return;
		}

		$data = $this->sanitize_request( $_REQUEST );

		try {
			$this->shipment_repository->update(
				$this->shipment_repository->findOrderByShipmentId( $data['id'] ),
				$data['id'],
				$data['shipment']
			);
		} catch ( \Exception $e ) {
			$this->error( 400, $e->getMessage() );

			return;
		}

		wp_send_json_success( $data['shipment'] );
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
	 * @param array $data In case of null the current $_REQUEST will be used.
	 *
	 * @return mixed
	 */
	protected function sanitize_request( $data ) {
		$data['id'] = $data['shipment_id'];

		unset( $data['action'] );
		unset( $data['shipment_id'] );
		unset( $data['shipment_order_id'] );

		return $data;
	}
}
