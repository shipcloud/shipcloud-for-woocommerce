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
    \WC_Shipcloud_Shipping::log('updating an order');
    if ( ! $this->is_authenticated() ) {
      $this->error( 403, __( 'Not authenticated' ) );
      return;
    }

    if ( ! isset( $_REQUEST['shipment'] ) ) {
      $this->error( 400, __( 'Bad request' ) );
      return;
    }

    try {
      $data = $this->sanitize_request( $_REQUEST );
      \WC_Shipcloud_Shipping::log( 'data: '.json_encode($data) );

      $pickup = \WC_Shipcloud_Order::handle_pickup_request($data);

      if (!empty($pickup)) {
          $data['shipment']['pickup'] = $pickup;
      }

      $data = $this->handle_label_format($data);

      $order = $this->shipment_repository->findOrderByShipmentId( $data['shipment']['id'] );
      $sc_order = \WC_Shipcloud_Order::create_order( $order->get_id() );

      $data['shipment']['additional_services'] =
        $this->shipment_repository->additional_services_from_request(
            $data['shipment']['additional_services'],
            $data['shipment']['carrier'],
            $sc_order
        );

            if (array_key_exists('customs_declaration', $data)) {
                $data['shipment']['customs_declaration'] = $this->handle_customs_declaration( $data['customs_declaration'] );
                unset($data['customs_declaration']);
            }

      \WC_Shipcloud_Shipping::log( sprintf('Trying to update shipment %s from order %d', $data['shipment']['id'], $order->get_id()) );
      $this->shipment_repository->update(
        $order,
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
		unset( $data['action'] );
		unset( $data['shipment_order_id'] );

    // unset notification_email if it was not checked
    if (!isset($data['shipcloud_notification_email_checkbox'])) {
      unset( $data['shipcloud_notification_email'] );
    }
		// unset some unwanted input from additional services form
		// if (!isset($data['shipment']['additional_services']['delivery_time']['checked'])) {
		// 	unset($data['shipment']['additional_services']['delivery_time']['timeframe']);
		// }
		// if (!isset($data['shipment']['additional_services']['age_based_delivery']['checked'])) {
		// 	unset($data['shipment']['additional_services']['visual_age_check']['minimum_age']);
		// }
		// unset($data['shipment']['additional_services']['age_based_delivery']);

		return $data;
	}

    private function handle_customs_declaration( $data ) {
        if ('false' === $data['shown']) {
            return null;
        } else {
            $line_items = $data['items'];

            $items = array();
            foreach ( $line_items as $line_item_key => $line_item_data ) {
                $line_item_data['id'] = $line_item_key;
                array_push($items, $line_item_data);
            }

            $data['items'] = $items;
            return $data;
        }
    }
  private function handle_label_format( $data ) {
    $label_format = $data['shipcloud_label_format'];
    unset($data['shipcloud_label_format']);

    if ( isset($label_format) ) {
      $data['shipment']['label'] = array(
        'format' => $label_format
      );
    }

    return $data;
  }
}
