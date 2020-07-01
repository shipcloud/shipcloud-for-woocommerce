<?php

require_once( 'woocommerce_shipcloud.php' );

class Config extends WoocommerceShipcloud_Tests
{
	public function testSetupPlugin()
	{
		$this->login();

		$this->cleanup_wcsc_config();
		$this->enter_wcsc_api_data( $api_key );

    $data = array(
      'company'                    => 'Musterfirma',
      'first_name'                 => 'Maria',
      'last_name'                  => 'Mustermann',
      'street'                     => 'Teststraße',
      'street_nr'                  => '55',
      'zip_code'                   => '55555',
      'city'                       => 'Musterstadt',
      'price_products'             => '5',
      'calculate_products_type'    => 'order',
      'price_shipment_classes'     => '7',
      'calculate_shipment_classes' => 'order',
    );

    $this->enter_wcsc_settings_data( $data );
	}
}
