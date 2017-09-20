<?php

namespace WooCommerce_Shipcloud\Tests\Shipcloud;

use Brain\Monkey;
use Brain\Monkey\Functions;

/**
 *
 *
 * @author  awesome.ug <support@awesome.ug>
 * @package WooCommerceShipCloud/Tests
 * @since   1.3.0
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
 *
 * @see \Woocommerce_Shipcloud_API::get_params_by_carrier()
 */
class GetParamsByCarrierTest extends ShipcloudTestCase {

	/**
	 * @var \Woocommerce_Shipcloud_API
	 */
	protected $api;

	public function setUp() {
		$this->api = new \Woocommerce_Shipcloud_API();
	}

	public function getArgumens() {
		$email = $email = uniqid( 'email', true ) . '@example.org';

		return [
			array(
				'carrier' => 'dhl'
			),
			array(
				'from' => uniqid( 'from', true )
			),
			array(
				'to'    => uniqid( 'to', true ),
				'email' => $email,
			),
			array(
				'description' => uniqid( 'description', true ),
			),
			uniqid( 'createLabel', true ),
			uniqid( 'notificationMail', true ) . '@example.org',
			uniqid( 'carrierMail', true ) . '@example.org',
			uniqid( 'referenceNumber', true ),
			uniqid( 'description', true )
		];
	}

	/**
	 * DHL
	 *
	 * Forwards all data when there is no carrier mail.
	 */
	public function testDhlNormal() {
		$email = $email = uniqid( 'email', true ) . '@example.org';

		// Input
		$return = $this->api->get_params_by_carrier(
			array(
				'carrier' => 'dhl',
				'service' => $service = uniqid( 'service', true )
			),
			$from = array(
				'from' => uniqid( 'from', true )
			),
			$to = array(
				'to'    => uniqid( 'to', true ),
				'email' => $email,
			),
			$package = array(
				'description' => uniqid( 'description', true ),
			),
			$createLabel = uniqid( 'createLabel', true ),
			$notificationEmail = uniqid( 'notificationMail', true ) . '@example.org',
			null,
			$referenceNumber = uniqid( 'referenceNumber', true ),
			$decription = uniqid( 'description', true )
		);

		// Assert output
		$this->assertEquals(
			array(
				'carrier'               => 'dhl',
				'service'               => $service,
				'from'                  => $from,
				'to'                    => $to,
				'package'               => $package,
				'create_shipping_label' => $createLabel,
				'notification_email'    => $notificationEmail,
				'additional_services'   => array(),
				'description'           => $decription,
				'reference_number'      => $referenceNumber,
			),
			$return
		);
	}

	/**
	 * DHL
	 *
	 * When there is a carrier mail given
	 * it will add an "advance_notice" to the additional services.
	 * This contains the carrier mail (as "email")
	 * and the language (as ISO 639-1).
	 */
	public function testDhlWithCarrierMail() {
		$email = $email = uniqid( 'email', true ) . '@example.org';

		// Input
		$return = $this->api->get_params_by_carrier(
			array(
				'carrier' => 'dhl',
				'service' => $service = uniqid( 'service', true )
			),
			$from = array(
				'from' => uniqid( 'from', true )
			),
			$to = array(
				'to'      => uniqid( 'to', true ),
				'country' => uniqid( 'country', true ),
				'email'   => $email,
			),
			$package = array(
				'description' => uniqid( 'description', true ),
			),
			$createLabel = uniqid( 'createLabel', true ),
			$notificationEmail = uniqid( 'notificationMail', true ) . '@example.org',
			$carrierMail = uniqid( 'carrierMail', true ) . '@example.org',
			$referenceNumber = uniqid( 'referenceNumber', true ),
			$decription = uniqid( 'description', true )
		);

		// Assert output
		$this->assertEquals(
			array(
				'carrier'               => 'dhl',
				'service'               => $service,
				'from'                  => $from,
				'to'                    => $to,
				'package'               => $package,
				'create_shipping_label' => $createLabel,
				'notification_email'    => $notificationEmail,
				'additional_services'   => array(
					array(
						'name'       => 'advance_notice',
						'properties' => array(
							'email'    => $carrierMail,
							'language' => i18n_iso_convert( '3166-1-alpha-2', '639-1', strtoupper( $to['country'] ) )
						)
					)
				),
				'description'           => $decription,
				'reference_number'      => $referenceNumber,
			),
			$return
		);
	}

	/**
	 * DPD
	 *
	 * When there is a carrier mail given
	 * it will add an "advance_notice" to the additional services.
	 * This contains the carrier mail (as "email")
	 * and the language (as ISO 639-1).
	 */
	public function testDpdNormal() {
		$email = $email = uniqid( 'email', true ) . '@example.org';

		// Input
		$return = $this->api->get_params_by_carrier(
			array(
				'carrier' => 'dpd',
				'service' => $service = uniqid( 'service', true )
			),
			$from = array(
				'from' => uniqid( 'from', true )
			),
			$to = array(
				'to'      => uniqid( 'to', true ),
				'country' => uniqid( 'country', true ),
				'email'   => $email,
			),
			$package = array(
				'description' => uniqid( 'description', true ),
			),
			$createLabel = uniqid( 'createLabel', true ),
			$notificationEmail = uniqid( 'notificationMail', true ) . '@example.org',
			null,
			$referenceNumber = uniqid( 'referenceNumber', true ),
			$decription = uniqid( 'description', true )
		);

		// Assert output
		$this->assertEquals(
			array(
				'carrier'               => 'dpd',
				'service'               => $service,
				'from'                  => $from,
				'to'                    => $to,
				'package'               => $package,
				'create_shipping_label' => $createLabel,
				'notification_email'    => null,
				'additional_services'   => array(),
				'description'           => $decription,
				'reference_number'      => $referenceNumber,
			),
			$return
		);
	}

	/**
	 * DPD
	 *
	 * When there is a carrier mail given
	 * it will add an "advance_notice" to the additional services.
	 * This contains the carrier mail (as "email")
	 * and the language (as ISO 639-1).
	 */
	public function testDpdWithCarrierMail() {
		$email = $email = uniqid( 'email', true ) . '@example.org';

		// Input
		$return = $this->api->get_params_by_carrier(
			array(
				'carrier' => 'dpd',
				'service' => $service = uniqid( 'service', true )
			),
			$from = array(
				'from' => uniqid( 'from', true )
			),
			$to = array(
				'to'      => uniqid( 'to', true ),
				'country' => uniqid( 'country', true ),
				'email'   => $email,
			),
			$package = array(
				'description' => uniqid( 'description', true ),
			),
			$createLabel = uniqid( 'createLabel', true ),
			$notificationEmail = uniqid( 'notificationMail', true ) . '@example.org',
			$carrierMail = uniqid( 'carrierMail', true ) . '@example.org',
			$referenceNumber = uniqid( 'referenceNumber', true ),
			$decription = uniqid( 'description', true )
		);

		// Assert output
		$this->assertEquals(
			array(
				'carrier'               => 'dpd',
				'service'               => $service,
				'from'                  => $from,
				'to'                    => $to,
				'package'               => $package,
				'create_shipping_label' => $createLabel,
				'notification_email'    => $carrierMail,
				'additional_services'   => array(
					array(
						'name'       => 'advance_notice',
						'properties' => array(
							'email'    => $carrierMail,
							'language' => i18n_iso_convert( '3166-1-alpha-2', '639-1', strtoupper( $to['country'] ) )
						)
					)
				),
				'description'           => $decription,
				'reference_number'      => $referenceNumber,
			),
			$return
		);
	}

	/**
	 * UPS
	 *
	 * All data will be used except the recipient mail.
	 */
	public function testUpsNoRecipientMail() {
		$email = $email = uniqid( 'email', true ) . '@example.org';

		// Input
		$return = $this->api->get_params_by_carrier(
			array(
				'carrier' => 'ups',
				'service' => $service = uniqid( 'service', true )
			),
			$from = array(
				'from'    => uniqid( 'from', true ),
				'country' => $fromCountry = uniqid( 'country', true )
			),
			$to = array(
				'to'      => uniqid( 'to', true ),
				'country' => $fromCountry,
				'email'   => $email,
			),
			$package = array(
				'description' => uniqid( 'description', true ),
			),
			$createLabel = uniqid( 'createLabel', true ),
			$notificationEmail = uniqid( 'notificationMail', true ) . '@example.org',
			$carrierMail = uniqid( 'carrierMail', true ) . '@example.org',
			$referenceNumber = uniqid( 'referenceNumber', true ),
			$decription = uniqid( 'description', true )
		);

		unset( $to['email'] );

		// Assert output
		$this->assertEquals(
			array(
				'carrier'               => 'ups',
				'service'               => $service,
				'from'                  => $from,
				'to'                    => $to,
				'package'               => $package,
				'create_shipping_label' => $createLabel,
				'notification_email'    => $notificationEmail,
				'description'           => $decription,
				'reference_number'      => $referenceNumber,
			),
			$return
		);
	}

	/**
	 * UPS
	 *
	 * When the sender and recipient country differ,
	 * then UPS wants an additional (general) description for those packages.
	 * This will be taken from the packages description.
	 */
	public function testUpsShiftDescription() {
		$email = $email = uniqid( 'email', true ) . '@example.org';

		// Input
		$return = $this->api->get_params_by_carrier(
			array(
				'carrier' => 'ups',
				'service' => $service = uniqid( 'service', true )
			),
			$from = array(
				'from'    => uniqid( 'from', true ),
				'country' => $fromCountry = uniqid( 'country', true )
			),
			$to = array(
				'to'      => uniqid( 'to', true ),
				'country' => uniqid( 'country', true ),
				'email'   => $email,
			),
			$package = array(
				'description' => uniqid( 'description', true ),
			),
			$createLabel = uniqid( 'createLabel', true ),
			$notificationEmail = uniqid( 'notificationMail', true ) . '@example.org',
			$carrierMail = uniqid( 'carrierMail', true ) . '@example.org',
			$referenceNumber = uniqid( 'referenceNumber', true ),
			$description = uniqid( 'description', true )
		);

		unset( $to['email'] );

		// Assert output
		$this->assertEquals(
			array(
				'carrier'               => 'ups',
				'service'               => $service,
				'from'                  => $from,
				'to'                    => $to,
				'package'               => array(),
				'create_shipping_label' => $createLabel,
				'notification_email'    => $notificationEmail,
				'description'           => $description,
				'reference_number'      => $referenceNumber,
			),
			$return
		);
	}

	/**
	 * Other
	 *
	 * All other data will be forwarded as is except for the recipient mail.
	 *
	 */
	public function testOther() {
		$email = $email = uniqid( 'email', true ) . '@example.org';

		// Input
		$return = $this->api->get_params_by_carrier(
			array(
				'carrier' => $someOther = uniqid( 'carrier', true ),
				'service' => $service = uniqid( 'service', true )
			),
			$from = array(
				'from'    => uniqid( 'from', true ),
				'country' => $fromCountry = uniqid( 'country', true )
			),
			$to = array(
				'to'      => uniqid( 'to', true ),
				'country' => uniqid( 'country', true ),
				'email'   => $email,
			),
			$package = array(
				'description' => uniqid( 'description', true ),
			),
			$createLabel = uniqid( 'createLabel', true ),
			$notificationEmail = uniqid( 'notificationMail', true ) . '@example.org',
			$carrierMail = uniqid( 'carrierMail', true ) . '@example.org',
			$referenceNumber = uniqid( 'referenceNumber', true ),
			$description = uniqid( 'description', true )
		);

		unset( $to['email'] );

		// Assert output
		$this->assertEquals(
			array(
				'carrier'               => $someOther,
				'service'               => $service,
				'from'                  => $from,
				'to'                    => $to,
				'package'               => $package,
				'create_shipping_label' => $createLabel,
				'notification_email'    => $notificationEmail,
				'description'           => $description,
				'reference_number'      => $referenceNumber,
			),
			$return
		);
	}

	/**
	 * Other
	 *
	 * Description field
	 * and reference field will be omitted,
	 * when those are not given.
	 *
	 */
	public function testOtherWithoutDecsriptionOrReference() {
		$email = $email = uniqid( 'email', true ) . '@example.org';

		// Input
		$return = $this->api->get_params_by_carrier(
			array(
				'carrier' => $someOther = uniqid( 'carrier', true ),
				'service' => $service = uniqid( 'service', true )
			),
			$from = array(
				'from'    => uniqid( 'from', true ),
				'country' => $fromCountry = uniqid( 'country', true )
			),
			$to = array(
				'to'      => uniqid( 'to', true ),
				'country' => uniqid( 'country', true ),
				'email'   => $email,
			),
			$package = array(
				'description' => $description = uniqid( 'description', true ),
			),
			$createLabel = uniqid( 'createLabel', true ),
			$notificationEmail = uniqid( 'notificationMail', true ) . '@example.org',
			$carrierMail = uniqid( 'carrierMail', true ) . '@example.org',
			null,
			null
		);

		unset( $to['email'] );

		// Assert output
		$this->assertEquals(
			array(
				'carrier'               => $someOther,
				'service'               => $service,
				'from'                  => $from,
				'to'                    => $to,
				'package'               => $package,
				'create_shipping_label' => $createLabel,
				'notification_email'    => $notificationEmail,
			),
			$return
		);
	}


}
