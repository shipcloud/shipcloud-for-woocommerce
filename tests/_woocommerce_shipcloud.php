<?php

require_once( dirname( __FILE__ ) . '/wp-testsuite/_woocommerce.php' );

class WoocommerceShipcloud_Tests extends WooCommerce_Tests
{
	public function enter_wcsc_order_shipping_data( $order_id )
	{
		$this->login();
		$this->go_order( $order_id );

		$this->byName( "parcel_width" )->value( '10' );
		$this->byName( "parcel_height" )->value( '15' );
		$this->byName( "parcel_length" )->value( '20' );
		$this->byName( "parcel_weight" )->value( '5' );
		$this->select( $this->byName( 'parcel_carrier' ) )->selectOptionByValue( 'ups' );

		$this->byId( "shipcloud_create_shipment" )->click();
		sleep( 4 );

		$this->cleanup_wcsc_order_shipping_data_fields();

		$this->byName( "parcel_width" )->value( '7' );
		$this->byName( "parcel_height" )->value( '13' );
		$this->byName( "parcel_length" )->value( '23' );
		$this->byName( "parcel_weight" )->value( '3' );
		$this->select( $this->byName( 'parcel_carrier' ) )->selectOptionByValue( 'dhl' );

		$this->byId( "shipcloud_create_shipment" )->click();
		sleep( 4 );

		$this->save_order();
	}

	public function cleanup_wcsc_order_shipping_data_fields()
	{
		$this->byName( "parcel_width" )->clear();
		$this->byName( "parcel_height" )->clear();
		$this->byName( "parcel_length" )->clear();
		$this->byName( "parcel_weight" )->clear();
	}

	public function cleanup_wcsc_config()
	{
		$this->go_wcsc_settings_page();

		if( $this->byId( 'woocommerce_shipcloud_enabled' )->selected() )
		{
			$this->byId( 'woocommerce_shipcloud_enabled' )->click();
		}

		$this->byId( 'woocommerce_shipcloud_api_key' )->clear();

		if( $this->byId( 'woocommerce_shipcloud_debug' )->selected() )
		{
			$this->byId( 'woocommerce_shipcloud_debug' )->click();
		}

		$this->byId( 'woocommerce_shipcloud_standard_price_products' )->clear();
		$this->byId( 'woocommerce_shipcloud_standard_price_shipment_classes' )->clear();

		$this->byId( 'woocommerce_shipcloud_sender_company' )->clear();
		$this->byId( 'woocommerce_shipcloud_sender_first_name' )->clear();
		$this->byId( 'woocommerce_shipcloud_sender_last_name' )->clear();
		$this->byId( 'woocommerce_shipcloud_sender_street' )->clear();
		$this->byId( 'woocommerce_shipcloud_sender_street_nr' )->clear();
		$this->byId( 'woocommerce_shipcloud_sender_postcode' )->clear();
		$this->byId( 'woocommerce_shipcloud_sender_city' )->clear();

		$this->byName( 'save' )->click();
	}

	public function go_wcsc_settings_page()
	{
		$this->url( '/wp-admin/admin.php?page=wc-settings&tab=shipping&section=wc_shipcloud_shipping' );
	}

	public function enter_wcsc_api_data( $api_key )
	{
		$this->byId( 'woocommerce_shipcloud_api_key' )->value( $api_key );
		$this->save_wcsc_settings();
		$this->assertSame( $api_key, $this->byId( 'woocommerce_shipcloud_api_key' )->attribute( 'value' ) );
	}

	public function save_wcsc_settings()
	{
		$this->byName( 'save' )->click();
	}

	public function enter_wcsc_settings_data( $data )
	{
		$this->byId( 'woocommerce_shipcloud_allowed_carriers_dhl' )->click();
		$this->byId( 'woocommerce_shipcloud_allowed_carriers_ups' )->click();
		$this->byId( 'woocommerce_shipcloud_allowed_carriers_hermes' )->click();

		$this->select( $this->byId( 'woocommerce_shipcloud_calculate_products_type' ) )->selectOptionByValue( $data[ 'calculate_products_type' ] );
		$this->byId( 'woocommerce_shipcloud_standard_price_products' )->value( $data[ 'price_products' ] );

		$this->select( $this->byId( 'woocommerce_shipcloud_calculation_type_shipment_classes' ) )->selectOptionByValue( $data[ 'calculate_shipment_classes' ] );
		$this->byId( 'woocommerce_shipcloud_standard_price_shipment_classes' )->value( $data[ 'price_shipment_classes' ] );

		$this->byId( 'woocommerce_shipcloud_sender_company' )->value( $data[ 'company' ] );
		$this->byId( 'woocommerce_shipcloud_sender_first_name' )->value( $data[ 'first_name' ] );
		$this->byId( 'woocommerce_shipcloud_sender_last_name' )->value( $data[ 'last_name' ] );
		$this->byId( 'woocommerce_shipcloud_sender_street' )->value( $data[ 'street' ] );
		$this->byId( 'woocommerce_shipcloud_sender_street_nr' )->value( $data[ 'street_nr' ] );
		$this->byId( 'woocommerce_shipcloud_sender_postcode' )->value( $data[ 'postcode' ] );
		$this->byId( 'woocommerce_shipcloud_sender_city' )->value( $data[ 'city' ] );

		$this->byId( 'woocommerce_shipcloud_debug' )->click();

		$this->save_wcsc_settings();

		$this->assertTrue( $this->byId( 'woocommerce_shipcloud_allowed_carriers_dhl' )->selected() );
		$this->assertTrue( $this->byId( 'woocommerce_shipcloud_allowed_carriers_ups' )->selected() );
		$this->assertTrue( $this->byId( 'woocommerce_shipcloud_allowed_carriers_hermes' )->selected() );

		$this->assertEquals( $data[ 'calculate_products_type' ], $this->byId( 'woocommerce_shipcloud_calculate_products_type' )->attribute( 'value' ) );
		$this->assertEquals( $data[ 'price_products' ], $this->byId( 'woocommerce_shipcloud_standard_price_products' )->attribute( 'value' ) );

		$this->assertEquals( $data[ 'calculate_shipment_classes' ], $this->byId( 'woocommerce_shipcloud_calculation_type_shipment_classes' )->attribute( 'value' ) );
		$this->assertEquals( $data[ 'price_shipment_classes' ], $this->byId( 'woocommerce_shipcloud_standard_price_shipment_classes' )->attribute( 'value' ) );

		$this->assertEquals( $data[ 'company' ], $this->byId( 'woocommerce_shipcloud_sender_company' )->attribute( 'value' ) );
		$this->assertEquals( $data[ 'first_name' ], $this->byId( 'woocommerce_shipcloud_sender_first_name' )->attribute( 'value' ) );
		$this->assertEquals( $data[ 'last_name' ], $this->byId( 'woocommerce_shipcloud_sender_last_name' )->attribute( 'value' ) );
		$this->assertEquals( $data[ 'street' ], $this->byId( 'woocommerce_shipcloud_sender_street' )->attribute( 'value' ) );
		$this->assertEquals( $data[ 'street_nr' ], $this->byId( 'woocommerce_shipcloud_sender_street_nr' )->attribute( 'value' ) );
		$this->assertEquals( $data[ 'postcode' ], $this->byId( 'woocommerce_shipcloud_sender_postcode' )->attribute( 'value' ) );
		$this->assertEquals( $data[ 'city' ], $this->byId( 'woocommerce_shipcloud_sender_city' )->attribute( 'value' ) );

		$this->assertTrue( $this->byId( 'woocommerce_shipcloud_debug' )->selected() );
	}

	public function enable_wcsc_plugin()
	{
		if( !$this->is_wcsc_enabled() )
		{
			$this->byId( 'woocommerce_shipcloud_enabled' )->click();
			$this->save_wcsc_settings();
			$this->assertTrue( $this->byId( 'woocommerce_shipcloud_enabled' )->selected() );
		}
	}

	public function is_wcsc_enabled()
	{
		return $this->byId( 'woocommerce_shipcloud_enabled' )->selected();
	}

	public function disable_wcsc_plugin()
	{
		if( $this->is_wcsc_enabled() )
		{
			$this->byId( 'woocommerce_shipcloud_enabled' )->click();
			$this->save_wcsc_settings();
			$this->assertFalse( $this->byId( 'woocommerce_shipcloud_enabled' )->selected() );
		}
	}
}
