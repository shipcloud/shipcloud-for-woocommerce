<?php

require_once( dirname(__FILE__) . '/wp-testsuite/_woocommerce.php' );

class WoocommerceShipcloud_Tests extends WooCommerce_Tests
{
	public function add_shipping_to_order( $order_id )
	{
		$this->login();

		$url = "/wp-admin/post.php?post={$order_id}&action=edit";

		$this->url( $url );

		sleep( 5 );

		$this->byName( "parcel_width" )->value( '10' );
		$this->byName( "parcel_height" )->value( '15' );
		$this->byName( "parcel_length" )->value( '20' );
		$this->byName( "parcel_weight" )->value( '5,5' );
	}

	public function cleanup_config()
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
}
