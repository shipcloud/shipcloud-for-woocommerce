<?php
class Config extends PHPUnit_Extensions_Selenium2TestCase
{
	protected function setUp()
	{
		global $argv, $argc;

		$host = 'clean.wp';

		if( $argc == 3 ){
			$host = $argv[ 2 ];
		}

		$this->setBrowser( "firefox" );
		$this->setBrowserUrl( "http://" . $host );
	}

	public function testSetupPlugin()
	{
		$this->url("/wp-admin/");

		$this->byId( 'user_login' )->value( 'wagesve' );
		$this->byId( 'user_pass' )->value( 'tv7r66' );
		$this->byId( 'wp-submit' )->submit();

		$this->url("/wp-admin/admin.php?page=wc-settings&tab=shipping&section=wc_shipcloud_shipping");

		$this->byId( 'woocommerce_shipcloud_enabled' )->click();
		$this->byId( 'woocommerce_shipcloud_api_key' )->value( '9f784473673a3f195157061ece467532' );

		$this->byName( 'save' )->click();

		$this->byId( 'woocommerce_shipcloud_allowed_carriers_dhl' )->click();
		$this->byId( 'woocommerce_shipcloud_allowed_carriers_ups' )->click();
		$this->byId( 'woocommerce_shipcloud_allowed_carriers_hermes' )->click();

		$this->byId( 'woocommerce_shipcloud_debug' )->click();

		$this->byName( 'save' )->click();

		$this->timeout()->implicitWait( 20000 );
	}
}
