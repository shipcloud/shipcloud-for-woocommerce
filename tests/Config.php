<?php

require_once( '_woocommerce_shipcloud.php' );

class Config extends WoocommerceShipcloud_Tests
{
	public function testSetupPlugin()
	{
		$this->login();

		$this->cleanup_wcsc_config();
		$this->enable_wcsc_plugin();
		$this->enter_wcsc_api_data( '9f784473673a3f195157061ece467532' );

		if( $this->is_wcsc_enabled() )
		{
			$data = array(
				'company'                    => 'Musterfirma',
				'first_name'                 => 'Maria',
				'last_name'                  => 'Mustermann',
				'street'                     => 'Teststraße',
				'street_nr'                  => '55',
				'postcode'                   => '55555',
				'city'                       => 'Musterstadt',
				'price_products'             => '5',
				'calculate_products_type'    => 'order',
				'price_shipment_classes'     => '7',
				'calculate_shipment_classes' => 'order',
			);

			$this->enter_wcsc_settings_data( $data );
		}
	}
}
