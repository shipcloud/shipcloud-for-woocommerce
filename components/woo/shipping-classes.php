<?php
/**
 * WooCommerce Shipping Classes
 * Class which adds additional functions to Shipping Classes
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *          Copyright 2017 (support@awesome.ug)
 *          This program is free software; you can redistribute it and/or modify
 *          it under the terms of the GNU General Public License, version 2, as
 *          published by the Free Software Foundation.
 *          This program is distributed in the hope that it will be useful,
 *          but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          GNU General Public License for more details.
 *          You should have received a copy of the GNU General Public License
 *          along with this program; if not, write to the Free Software
 *          Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) )
{
	exit;
}

class WC_Shipcloud_Shippig_Classes
{

	/**
	 * The Single instance of the class
	 *
	 * @var object $_instance
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Construct
	 *
	 * @since 1.0.0
	 */
	private function __construct()
	{
		$this->init_hooks();
	}

	/**
	 * Initializing functions
	 *
	 * @since 1.0.0
	 */
	public function init_hooks()
	{
	    // WooCommerce until 2.5
		add_action( 'product_shipping_class_edit_form_fields', array( $this, 'shipping_class_edit_form_fields' ), 10, 2 );
		add_action( 'edited_product_shipping_class', array( $this, 'shipping_class_edit_form_fields_save' ), 10, 1 );

		add_action( 'product_shipping_class_add_form_fields', array( $this, 'shipping_class_add_form_fields' ), 10, 1 );
		add_action( 'create_product_shipping_class', array( $this, 'shipping_class_edit_form_fields_save' ), 10, 1 );

        // WooCommerce since 2.6
        add_filter( 'woocommerce_shipping_classes_columns', array( $this, 'add_shipping_class_columns' ) );
        add_action( 'woocommerce_shipping_classes_column_shipcloud-parcel-width', array( $this, 'add_shipping_class_row_width' ) );
        add_action( 'woocommerce_shipping_classes_column_shipcloud-parcel-height', array( $this, 'add_shipping_class_row_height' ) );
        add_action( 'woocommerce_shipping_classes_column_shipcloud-parcel-length', array( $this, 'add_shipping_class_row_length' ) );
        add_action( 'woocommerce_shipping_classes_column_shipcloud-parcel-weight', array( $this, 'add_shipping_class_row_weight' ) );

		add_action( 'woocommerce_shipping_classes_save_class', array( $this, 'save_class' ), 10, 2 );
		add_filter( 'woocommerce_get_shipping_classes', array( $this, 'extend_terms' ), 10, 1 );
	}

	/**
	 * Main Instance
	 *
	 * @return object
	 * @since 1.0.0
	 */
	public static function instance()
	{
		if ( is_null( self::$_instance ) )
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

    /**
     * Adding Shipcloud columns to shipping classes (since WooCommerce 2.6)
     *
     * @param $columns
     *
     * @return array
     * @since 1.1.0
     */
	public function add_shipping_class_columns( $columns )
    {
        $shipcloud_columns = array(
            'shipcloud-parcel-width'    => __( 'shipcloud parcel width (cm)', 'shipcloud-for-woocommerce' ),
            'shipcloud-parcel-height'  => __( 'shipcloud parcel height (cm)', 'shipcloud-for-woocommerce' ),
            'shipcloud-parcel-length'   => __( 'shipcloud parcel length (cm)', 'shipcloud-for-woocommerce' ),
            'shipcloud-parcel-weight'   => __( 'shipcloud parcel weight (kg)', 'shipcloud-for-woocommerce' ),
        );

        $columns = array_merge( $columns, $shipcloud_columns );

        return $columns;
    }

    /**
     * Adding field for width (since WooCommerce 2.6)
     *
     * @since 1.1.0
     */
    public function add_shipping_class_row_width()
    {
        ?>
        <div class="view">{{ data.width }}</div>
        <div class="edit"><input type="text" name="width[{{ data.term_id }}]" data-attribute="width" value="{{ data.width }}" placeholder="<?php esc_attr_e( '0', 'woocommerce' ); ?>" /></div>
        <?php
    }

    /**
     * Adding field for height (since WooCommerce 2.6)
     *
     * @since 1.1.0
     */
    public function add_shipping_class_row_height()
    {
        ?>
        <div class="view">{{ data.height }}</div>
        <div class="edit"><input type="text" name="height[{{ data.term_id }}]" data-attribute="height" value="{{ data.height }}" placeholder="<?php esc_attr_e( '0', 'woocommerce' ); ?>" /></div>
        <?php
    }

    /**
     * Adding field for length (since WooCommerce 2.6)
     *
     * @since 1.1.0
     */
    public function add_shipping_class_row_length()
    {
        ?>
        <div class="view">{{ data.length }}</div>
        <div class="edit"><input type="text" name="length[{{ data.term_id }}]" data-attribute="length" value="{{ data.length }}" placeholder="<?php esc_attr_e( '0', 'woocommerce' ); ?>" /></div>
        <?php
    }

    /**
     * Adding field for weight (since WooCommerce 2.6)
     *
     * @since 1.1.0
     */
    public function add_shipping_class_row_weight()
    {
        ?>
        <div class="view">{{ data.weight }}</div>
        <div class="edit"><input type="text" name="weight[{{ data.term_id }}]" data-attribute="weight" value="{{ data.weight }}" placeholder="<?php esc_attr_e( '0.0', 'woocommerce' ); ?>" /></div>
        <?php
    }

	/**
	 * Saving class data (since WooCommerce 2.6)
	 *
	 * @param $term_id
	 * @param $data
	 *
	 * @since 1.1.0
	 */
    public function save_class( $term_id, $data )
    {
	    if ( isset( $data['width'] ) )
	    {
            $parcel_width = wc_clean( $data['width'] );
	    }

	    if ( isset( $data['height'] ) )
	    {
            $parcel_height = wc_clean( $data['height'] );
	    }

	    if ( isset( $data['length'] ) )
	    {
            $parcel_length = wc_clean( $data['length'] );
	    }

	    if ( isset( $data['weight'] ) )
	    {
            $parcel_weight = wc_clean( $data['weight'] );
	    }

	    if( is_array( $term_id ) )
	    {
	    	$term_id = $term_id[ 'term_id' ];
	    }

        update_option( 'shipping_class_' . $term_id . '_shipcloud_width', $parcel_width );
        update_option( 'shipping_class_' . $term_id . '_shipcloud_height', $parcel_height );
        update_option( 'shipping_class_' . $term_id . '_shipcloud_length', $parcel_length );
        update_option( 'shipping_class_' . $term_id . '_shipcloud_weight', $parcel_weight );
    }

    /**
     * Extending terms with data from shipcloud fields (since WooCommerce 2.6)
     *
     * @param WP_Term[] $shipping_classes
     *
     * @return WP_Term[] $shipping_classes
     * @since 1.0.0
     */
    public function extend_terms( $shipping_classes )
    {
        foreach( $shipping_classes AS $key => $shipping_class )
        {
            $term_id = $shipping_class->term_id;
            $shipping_classes[ $key ]->width =  get_option( 'shipping_class_' . $term_id . '_shipcloud_width' );
            $shipping_classes[ $key ]->height =  get_option( 'shipping_class_' . $term_id . '_shipcloud_height' );
            $shipping_classes[ $key ]->length =  get_option( 'shipping_class_' . $term_id . '_shipcloud_length' );
            $shipping_classes[ $key ]->weight =  get_option( 'shipping_class_' . $term_id . '_shipcloud_weight' );
        }

        return $shipping_classes;
    }

	/**
	 * Selecting Parcel for shipping class on editing Shipment Class
	 *
	 * @param $tag
	 * @param $taxonomy
	 *
	 * @since 1.0.0
	 * @todo Do we need params here?
	 */
	public function shipping_class_edit_form_fields( $tag, $taxonomy )
	{
		$term_id = $_GET[ 'tag_ID' ]; // $tag doesn't work really, so use $_GET[ 'tag_ID' ]

		$width = get_option( 'shipping_class_' . $term_id . '_shipcloud_width' );
		$height = get_option( 'shipping_class_' . $term_id . '_shipcloud_height' );
		$length = get_option( 'shipping_class_' . $term_id . '_shipcloud_length' );
		$weight = get_option( 'shipping_class_' . $term_id . '_shipcloud_weight' );

		$html = '<tr class="form-field shipcloud-parcel-form-field">';
		$html .= '<th scope="row" colspan="2">';
		$html .= '<h3>' . __( 'Shipment settings', 'shipcloud-for-woocommerce' ) . '</h3>';
		$html .= '</th>';
		$html .= '</tr>';

		$html .= '<tr class="form-field shipcloud-parcel-form-field">';
		$html .= '<th scope="row">';
		$html .= '<label for="shipcloud_parcel_length">' . __( 'Length', 'shipcloud-for-woocommerce' ) . '</label>';
		$html .= '</th>';
		$html .= '<td>';
		$html .= '<input type="text" name="shipcloud_parcel_length" value="' . $length . '" /> ' . __( 'cm', 'shipcloud-for-woocommerce' );
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr class="form-field shipcloud-parcel-form-field">';
		$html .= '<th scope="row">';
		$html .= '<label for="shipcloud_parcel_width">' . __( 'Width', 'shipcloud-for-woocommerce' ) . '</label>';
		$html .= '</th>';
		$html .= '<td>';
		$html .= '<input type="text" name="shipcloud_parcel_width" value="' . $width . '" /> ' . __( 'cm', 'shipcloud-for-woocommerce' );
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr class="form-field shipcloud-parcel-form-field">';
		$html .= '<th scope="row">';
		$html .= '<label for="shipcloud_parcel_height">' . __( 'Height', 'shipcloud-for-woocommerce' ) . '</label>';
		$html .= '</th>';
		$html .= '<td>';
		$html .= '<input type="text" name="shipcloud_parcel_height" value="' . $height . '" /> ' . __( 'cm', 'shipcloud-for-woocommerce' );
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr class="form-field shipcloud-parcel-form-field">';
		$html .= '<th scope="row">';
		$html .= '<label for="shipcloud_parcel_weight">' . __( 'Weight', 'shipcloud-for-woocommerce' ) . '</label>';
		$html .= '</th>';
		$html .= '<td>';
		$html .= '<input type="text" name="shipcloud_parcel_weight" value="' . $weight . '" /> ' . __( 'kg', 'shipcloud-for-woocommerce' );
		$html .= '</td>';
		$html .= '</tr>';

		echo $html;
	}

	/**
	 * Selecting Parcel for shipping class on adding Shipment Class
	 *
	 * @since 1.0.0
	 */
	public function shipping_class_add_form_fields()
	{

		$html = '<h4>' . __( 'Shipment settings', 'shipcloud-for-woocommerce' ) . '</h4>';
		$html .= '<div class="form-field shipment-settings">';
		$html .= '<label for="shipcloud_parcel_length">' . __( 'Length', 'shipcloud-for-woocommerce' ) . '</label>';
		$html .= '<input type="text" name="shipcloud_parcel_length" /> ' . __( 'cm', 'shipcloud-for-woocommerce' );
		$html .= '</div>';
		$html .= '<div class="form-field shipment-settings">';
		$html .= '<label for="shipcloud_parcel_width">' . __( 'Width', 'shipcloud-for-woocommerce' ) . '</label>';
		$html .= '<input type="text" name="shipcloud_parcel_width" /> ' . __( 'cm', 'shipcloud-for-woocommerce' );
		$html .= '</div>';
		$html .= '<div class="form-field shipment-settings">';
		$html .= '<label for="shipcloud_parcel_height">' . __( 'Height', 'shipcloud-for-woocommerce' ) . '</label>';
		$html .= '<input type="text" name="shipcloud_parcel_height"  /> ' . __( 'cm', 'shipcloud-for-woocommerce' );
		$html .= '</div>';
		$html .= '<div class="form-field shipment-settings">';
		$html .= '<label for="shipcloud_parcel_height">' . __( 'Weight', 'shipcloud-for-woocommerce' ) . '</label>';
		$html .= '<input type="text" name="shipcloud_parcel_weight" /> ' . __( 'kg', 'shipcloud-for-woocommerce' );
		$html .= '</div>';
		echo $html;
	}

	/**
	 * Saving Shipping Class data on editing Shipment Class
	 *
	 * @param int $term_id Term ID
	 *
	 * @since 1.0.0
	 */
	public function shipping_class_edit_form_fields_save( $term_id )
	{
		$parcel_length = $_POST[ 'shipcloud_parcel_length' ];
		$parcel_width  = $_POST[ 'shipcloud_parcel_width' ];
		$parcel_height = $_POST[ 'shipcloud_parcel_height' ];
		$parcel_weight = $_POST[ 'shipcloud_parcel_weight' ];

		update_option( 'shipping_class_' . $term_id . '_shipcloud_length', $parcel_length );
		update_option( 'shipping_class_' . $term_id . '_shipcloud_width', $parcel_width );
		update_option( 'shipping_class_' . $term_id . '_shipcloud_height', $parcel_height );
		update_option( 'shipping_class_' . $term_id . '_shipcloud_weight', $parcel_weight );
	}
}

WC_Shipcloud_Shippig_Classes::instance();
