<?php
/**
 * WooCommerce Shipping Classes
 *
 * Class which adds additional functions to Shipping Classes
 *
 * @author awesome.ug <very@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2015 (very@awesome.ug)

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

class WC_Shipcloud_Shippig_Classes{

    /**
     * Initializing functions
     */
    public static function init(){
        add_action( 'product_shipping_class_edit_form_fields', array( __CLASS__, 'shipping_class_edit_form_fields' ), 10, 2 );
        add_action( 'edited_product_shipping_class', array( __CLASS__, 'shipping_class_edit_form_fields_save' ), 10, 2 );

        add_action( 'product_shipping_class_add_form_fields', array( __CLASS__, 'shipping_class_add_form_fields' ), 10, 2 );
        add_action( 'create_product_shipping_class', array( __CLASS__, 'shipping_class_add_form_fields_save' ), 10, 2 );
    }

    /**
     * Selecting Parcel for shipping class on editing Shipment Class
     * @param $tag
     * @param $taxonomy
     */
    public static function shipping_class_edit_form_fields( $tag, $taxonomy ){
        $term_id = $_GET[ 'tag_ID' ]; // $tag doesn't work really, so use $_GET[ 'tag_ID' ]

        $parcels[ 0 ][ 'ID' ] = 0;
        $parcels[ 0 ][ 'post_title' ] = __( 'None', 'woocommerce-shipcloud' );
        $parcels = array_merge( $parcels, WCSC_Parcels::get() );

        $parcel_id = get_option( 'wcsc_shipping_class_' . $term_id . '_parcel_id', 0 );

        $html = '<tr class="form-field">';
        $html.= '<th scope="row">';
        $html.= '<label for="_wcsc_parcel_id">' . __( 'shipcloud Parcel', 'woocommerce-shipcloud' ) . '</label>';
        $html.= '</th>';
        $html.= '<td>';

        $html.= '<select name="_wcsc_parcel_id">';
        foreach( $parcels AS $parcel ){

            $selected = '';
            if( $parcel_id == $parcel[ 'ID' ] )
                $selected = ' selected="selected"';

            $html.='<option value="' . $parcel[ 'ID' ] . '"' . $selected . '>' . $parcel[ 'post_title' ] . '</option>';
        }
        $html.= '</select>';
        $html.= '<p class="description">' . __( 'Select the shipcloud parcel you want to use for this shipping class.', 'woocommerce-shipcloud' ) . '</p>';
        $html.= '</td>';
        $html.= '</tr>';

        echo $html;
    }

    /**
     * Saving Shipping Class data on editing Shipment Class
     * @param int $term_id Term ID
     * @param int $tt_id Term taxonomy ID
     */
    public static function shipping_class_edit_form_fields_save( $term_id, $tt_id ){
        $parcel_id = $_POST[ '_wcsc_parcel_id' ];
        update_option( 'wcsc_shipping_class_' . $term_id . '_parcel_id', $parcel_id );
    }

    /**
     * Selecting Parcel for shipping class on adding Shipment Class
     */
    public static function shipping_class_add_form_fields(){
        $parcels[ 0 ][ 'ID' ] = 0;
        $parcels[ 0 ][ 'post_title' ] = __( 'None', 'woocommerce-shipcloud' );
        $parcels = array_merge( $parcels, WCSC_Parcels::get() );

        $html = '<div class="form-field">';
        $html.= '<label for="cat_Image_url">' . __( 'shipcloud Parcel', 'woocommerce-shipcloud' ) . '</label>';
        $html.= '<select name="_wcsc_parcel_id">';
        foreach( $parcels AS $parcel ){
            $html.='<option value="' . $parcel[ 'ID' ] . '">' . $parcel[ 'post_title' ] . '</option>';
        }
        $html.= '</select>';
        $html.= '<p class="description">' . __( 'Select the shipcloud parcel you want to use for this shipping class.', 'woocommerce-shipcloud' ) . '</p>';
        $html.= '</div>';

        echo $html;
    }

    /**
     * Saving Shipping Class data on adding Shipment Class
     * @param int $term_id Term ID
     * @param int $tt_id Term taxonomy ID
     */
    public static function shipping_class_add_form_fields_save( $term_id, $tt_id ){
        $parcel_id = $_POST[ '_wcsc_parcel_id' ];
        update_option( 'wcsc_shipping_class_' . $term_id . '_parcel_id', $parcel_id );
    }
}
WC_Shipcloud_Shippig_Classes::init();