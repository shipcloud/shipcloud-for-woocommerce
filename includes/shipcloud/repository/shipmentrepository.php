<?php

namespace Shipcloud\Repository;


class ShipmentRepository {
	public function update( $order_id, $shipment_id, $data ) {
		$order = \WooCommerce::instance()->order_factory->get_order( $order_id );

		foreach ( $order->get_meta_data() as $meta_value ) {
			if ( 'shipcloud_shipment_data' !== $meta_value->key ) {
				continue;
			}

			if ( $meta_value->value['id'] !== $shipment_id ) {
				continue;
			}

			$order->update_meta_data(
				'shipcloud_shipment_data',
				array_merge( $meta_value->value, $this->translate_data( $data ) ),
				$meta_value->id
			);

			$order->save_meta_data();

			break;
		}
	}

	public function translate_data( $data ) {
		$data = array_replace_recursive(
			array(
				'to'   => array(
					'company'    => '',
					'first_name' => '',
					'last_name'  => '',
					'street'     => '',
					'street_no'  => '',
					'city'       => '',
					'zip_code'   => '',
					'country'    => '',
				),
				'from' => array(
					'company'    => '',
					'first_name' => '',
					'last_name'  => '',
					'street'     => '',
					'street_no'  => '',
					'city'       => '',
					'zip_code'   => '',
					'country'    => '',
				)
			),
			$data
		);

		return array(
			// Sender
			'sender_company'       => $data['from']['company'],
			'sender_first_name'    => $data['from']['first_name'],
			'sender_last_name'     => $data['from']['last_name'],
			'sender_street'        => $data['from']['street'],
			'sender_street_no'     => $data['from']['street_no'],
			'sender_city'          => $data['from']['city'],
			'sender_zip_code'      => $data['from']['zip_code'],
			'sender_state'         => $data['from']['state'],
			'country'              => $data['country'],
			// Recipient
			'recipient_company'    => $data['to']['company'],
			'recipient_first_name' => $data['to']['first_name'],
			'recipient_last_name'  => $data['to']['last_name'],
			'recipient_street'     => $data['to']['street'],
			'recipient_street_no'  => $data['to']['street_no'],
			'recipient_city'       => $data['to']['city'],
			'recipient_zip_code'   => $data['to']['zip_code'],
			'recipient_state'      => $data['to']['state'],
			'recipient_country'    => $data['to']['country'],
		);
	}

}
