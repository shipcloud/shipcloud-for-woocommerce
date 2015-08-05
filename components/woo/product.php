<?php
/**
 * WooCommerce Product component
 *
 * Loading Product component for WooCommerce
 *
 * @author awesome.ug <contact@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

Copyright 2015 (contact@awesome.ug)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( !defined( 'ABSPATH' ) ) exit;

class WC_Shipcloud_Product{

    public static function init(){
        add_action( 'woocommerce_product_options_shipping', array( __CLASS__, 'shipping_option' ) );
        add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_shipping_option' ) , 10, 2 );
    }

    public static function shipping_option(){
        $parcel_templates = WCSCParcel_templates::get();

        $options = array();

        foreach( $parcel_templates AS $parcel_template ){
            $options[ $parcel_template[ 'ID' ] ] = $parcel_template[ 'post_title' ];
        }

        // Stock status
        woocommerce_wp_select(
            array(
                'id' => '_wcsc_parcel',
                'wrapper_class' => 'hide_if_variable',
                'label' => __( 'Parcel', 'wcsc-locale' ),
                'options' => $options,
                'desc_tip' => true,
                'description' => __( 'Select Parcel which will be used to send product', 'wcsc-locale' )
            )
        );
    }

    public static function save_shipping_option( $post_id, $post ){

        if ( isset( $_POST['_wcsc_parcel'] ) ) {
            update_post_meta( $post_id, '_wcsc_parcel', wc_clean( $_POST['_wcsc_parcel'] ) );
        }
    }
}
WC_Shipcloud_Product::init();