<?php

class Config extends PHPUnit_Extensions_Selenium2TestCase
{
	public function testSetupPlugin()
	{
		$this->login();
		$this->cleanup();

		$this->url( "/wp-admin/admin.php?page=wc-settings&tab=shipping&section=wc_shipcloud_shipping" );

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

	private function login()
	{
		$this->url( "/wp-admin/" );

		$this->byId( 'user_login' )->value( 'wagesve' );
		$this->byId( 'user_pass' )->value( 'tv7r66' );
		$this->byId( 'wp-submit' )->submit();
	}

	private function cleanup()
	{
		$this->url( "/wp-admin/admin.php?page=wc-settings&tab=shipping&section=wc_shipcloud_shipping" );

		if( $this->byId( 'woocommerce_shipcloud_enabled' )->selected() )
		{
			$this->byId( 'woocommerce_shipcloud_enabled' )->click();
		}

		$this->byId( 'woocommerce_shipcloud_api_key' )->clear();

		if( $this->byId( 'woocommerce_shipcloud_debug' )->selected() )
		{
			$this->byId( 'woocommerce_shipcloud_debug' )->click();
		}

		$this->byId( 'woocommerce_shipcloud_sender_company' )->clear();
		$this->byId( 'woocommerce_shipcloud_sender_first_name' )->clear();
		$this->byId( 'woocommerce_shipcloud_sender_last_name' )->clear();
		$this->byId( 'woocommerce_shipcloud_sender_street' )->clear();
		$this->byId( 'woocommerce_shipcloud_sender_street_nr' )->clear();
		$this->byId( 'woocommerce_shipcloud_sender_postcode' )->clear();
		$this->byId( 'woocommerce_shipcloud_sender_city' )->clear();

		$this->byName( 'save' )->click();
	}

	protected function setUp()
	{
		global $argv, $argc;

		$host = 'clean.wp';

		if( $argc == 3 )
		{
			$host = $argv[ 2 ];
		}

		$this->setBrowser( "firefox" );
		$this->setBrowserUrl( "http://" . $host );
		// $this->cleanup();
	}
}
