<?php

require_once( '_woocommerce_shipcloud.php' );

class Config extends WoocommerceShipcloud_Tests
{
	public function testSetupPlugin()
	{
		$this->login();
		$this->cleanup_config();

		$this->byId( 'woocommerce_shipcloud_enabled' )->click();

		$this->byId( 'woocommerce_shipcloud_api_key' )->value( '9f784473673a3f195157061ece467532' );

		$this->byName( 'save' )->click();

		$this->assertTrue( $this->byId( 'woocommerce_shipcloud_enabled' )->selected() );
		$this->assertSame( '9f784473673a3f195157061ece467532', $this->byId( 'woocommerce_shipcloud_api_key' )->attribute( 'value' ) );

		if( $this->byId( 'woocommerce_shipcloud_enabled' )->selected() )
		{
			$this->byId( 'woocommerce_shipcloud_allowed_carriers_dhl' )->click();
			$this->byId( 'woocommerce_shipcloud_allowed_carriers_ups' )->click();
			$this->byId( 'woocommerce_shipcloud_allowed_carriers_hermes' )->click();

			$this->select( $this->byId( 'woocommerce_shipcloud_calculate_products_type' ) )->selectOptionByValue( 'order' );
			$this->byId( 'woocommerce_shipcloud_standard_price_products' )->value( '4,9' );

			$this->select( $this->byId( 'woocommerce_shipcloud_calculation_type_shipment_classes' ) )->selectOptionByValue( 'order' );
			$this->byId( 'woocommerce_shipcloud_standard_price_shipment_classes' )->value( '6,9' );

			$default_company = 'Musterfirma';
			$default_first_name = 'Maria';
			$default_last_name = 'Mustermann';
			$default_street = 'Teststraße';
			$default_street_nr = '99';
			$default_postcode = '55555';
			$default_city = 'Musterstadt';

			$this->byId( 'woocommerce_shipcloud_sender_company' )->value( $default_company );
			$this->byId( 'woocommerce_shipcloud_sender_first_name' )->value( $default_first_name );
			$this->byId( 'woocommerce_shipcloud_sender_last_name' )->value( $default_last_name );
			$this->byId( 'woocommerce_shipcloud_sender_street' )->value( $default_street );
			$this->byId( 'woocommerce_shipcloud_sender_street_nr' )->value( $default_street_nr );
			$this->byId( 'woocommerce_shipcloud_sender_postcode' )->value( $default_postcode );
			$this->byId( 'woocommerce_shipcloud_sender_city' )->value( $default_city );

			$this->byId( 'woocommerce_shipcloud_debug' )->click();

			$this->byName( 'save' )->click();

			$this->assertSame( 'order', $this->byId( 'woocommerce_shipcloud_calculate_products_type' )->attribute( 'value' ) );
			$this->assertSame( '4,9', $this->byId( 'woocommerce_shipcloud_standard_price_products' )->attribute( 'value' ) ) ;

			$this->assertSame( 'order', $this->byId( 'woocommerce_shipcloud_calculation_type_shipment_classes' )->attribute( 'value' ) );
			$this->assertSame( '6,9', $this->byId( 'woocommerce_shipcloud_standard_price_shipment_classes' )->attribute( 'value' ) );

			$this->assertTrue( $this->byId( 'woocommerce_shipcloud_allowed_carriers_dhl' )->selected() );
			$this->assertTrue( $this->byId( 'woocommerce_shipcloud_allowed_carriers_ups' )->selected() );
			$this->assertTrue( $this->byId( 'woocommerce_shipcloud_allowed_carriers_hermes' )->selected() );

			$this->assertTrue( $this->byId( 'woocommerce_shipcloud_debug' )->selected() );

			$this->assertSame( $default_company, $this->byId( 'woocommerce_shipcloud_sender_company' )->attribute( 'value' ) );
			$this->assertSame( $default_first_name, $this->byId( 'woocommerce_shipcloud_sender_first_name' )->attribute( 'value' ) );
			$this->assertSame( $default_last_name, $this->byId( 'woocommerce_shipcloud_sender_last_name' )->attribute( 'value' ) );
			$this->assertSame( $default_street, $this->byId( 'woocommerce_shipcloud_sender_street' )->attribute( 'value' ) );
			$this->assertSame( $default_street_nr, $this->byId( 'woocommerce_shipcloud_sender_street_nr' )->attribute( 'value' ) );
			$this->assertSame( $default_postcode, $this->byId( 'woocommerce_shipcloud_sender_postcode' )->attribute( 'value' ) );
			$this->assertSame( $default_city, $this->byId( 'woocommerce_shipcloud_sender_city' )->attribute( 'value' ) );
		}
	}
}
