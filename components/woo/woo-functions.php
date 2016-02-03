<?php
/**
 * WooCommerce Functions
 *
 * Loading extensions for Woo
 *
 * @author  awesome.ug <very@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 (very@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if( !defined( 'ABSPATH' ) )
{
	exit;
}

/**
 * Ordering Package by Shipping Class
 *
 * @param $package Package given on
 *
 * @return array $shipping_classes
 */
function wcsc_order_package_by_shipping_class( $package )
{
	$shipping_classes = array();

	foreach( $package[ 'contents' ] as $item_id => $values )
	{
		if( $values[ 'data' ]->needs_shipping() )
		{
			$found_class = $values[ 'data' ]->get_shipping_class();

			if( !isset( $shipping_classes[ $found_class ] ) )
			{
				$shipping_classes[ $found_class ] = array();
			}

			$shipping_classes[ $found_class ][ $item_id ] = $values;
		}
	}

	return $shipping_classes;
}

/**
 * Calculate Needed Parcels by Ordered package
 *
 * @param $ordered_package
 */
function wcsc_get_order_parcels( $ordered_package )
{
	$settings = get_option( 'woocommerce_shipcloud_settings' );
	$parcels = array();

	foreach( $ordered_package AS $shipping_class => $products )
	{
		if( '' == $shipping_class )
		{
			/**
			 * Products
			 */
			foreach( $products AS $product )
			{
				$length = get_post_meta( $product[ 'product_id' ], '_length', TRUE );
				$width  = get_post_meta( $product[ 'product_id' ], '_width', TRUE );
				$height = get_post_meta( $product[ 'product_id' ], '_height', TRUE );
				$weight = get_post_meta( $product[ 'product_id' ], '_weight', TRUE );

				// If there is missing a dimension, set FALSE
				if( '' == $length || '' == $width || '' == $height || '' == $weight )
				{
					$dimensions = $settings[ 'standard_price_products' ];
				}
				else
				{
					$dimensions = array(
						'length' => $length,
						'width'  => $width,
						'height' => $height,
						'weight' => $weight
					);
				}

				switch ( $settings[ 'calculate_products_type' ] )
				{
					case 'product':

						for( $i = 0; $i < $product[ 'quantity' ]; $i++ )
						{
							$parcels[ 'products' ][] = $dimensions;
						}

						break;

					case 'order':

						$parcels[ 'products' ][] = $dimensions;

						break;
				}
			}
		}
		else
		{
			/**
			 * Shipment Classes
			 */
			$taxonomy = get_term_by( 'name', $shipping_class, 'product_shipping_class' );

			$width  = get_option( 'shipping_class_' . $taxonomy->term_id . '_shipcloud_width' );
			$height = get_option( 'shipping_class_' . $taxonomy->term_id . '_shipcloud_height' );
			$length = get_option( 'shipping_class_' . $taxonomy->term_id . '_shipcloud_length' );
			$weight = get_option( 'shipping_class_' . $taxonomy->term_id . '_shipcloud_weight' );

			// If there is missing a dimension, set FALSE
			if( '' == $length || '' == $width || '' == $height || '' == $weight )
			{
				$dimensions = $settings[ 'standard_price_shipment_classes' ];
			}
			else
			{
				$dimensions = array(
					'length' => $length,
					'width'  => $width,
					'height' => $height,
					'weight' => $weight
				);
			}

			$parcels[ 'shipping_classes' ][] = $dimensions;
		}
	}

	return $parcels;
}

function wcsc_get_shipment_request_data( $sender = FALSE, $recipient, $parcel, $carrier, $service = 'standard' )
{
	$settings = get_option( 'woocommerce_shipcloud_settings' );

	// Use default data if nothing was saved before
	if( '' == $sender || 0 == count( $sender ) )
	{
		$sender = array(
			'first_name' => $settings[ 'sender_first_name' ],
			'last_name'  => $settings[ 'sender_last_name' ],
			'company'    => $settings[ 'sender_company' ],
			'street'     => $settings[ 'sender_street' ],
			'street_no'  => $settings[ 'sender_street_nr' ],
			'zip_code'   => $settings[ 'sender_postcode' ],
			'city'       => $settings[ 'sender_city' ],
			'country'    => $settings[ 'sender_country' ],
		);
	}

	// Use default data if nothing was saved before
	if( '' == $recipient || 0 == count( $recipient ) )
	{
		$order = new WC_Order( $order_id );

		$recipient_street_nr = '';
		$recipient_street = wcsc_explode_street( $order->shipping_address_1 );

		if( is_array( $recipient_street ) )
		{
			$recipient_street_name = $recipient_street[ 'address' ];
			$recipient_street_nr = $recipient_street[ 'number' ];
		}

		$recipient = array(
			'first_name' => $order->shipping_first_name,
			'last_name'  => $order->shipping_last_name,
			'company'    => $order->shipping_company,
			'street'     => $recipient_street_name,
			'street_no'  => $recipient_street_nr,
			'zip_code'   => $order->shipping_postcode,
			'city'       => $order->shipping_city,
			'country'    => $order->shipping_country,
		);
	}

	$data = array(
		'carrier' => $carrier,
		'service' => $service,
		'to'      => $recipient,
		'from'    => $sender,
	    'package' => $parcel
	);

	return $data;
}