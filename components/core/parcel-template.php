<?php
/**
 * shipcloud for WooCommerce parcel class
 * Loading parcel functions
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package shipcloudForWooCommerce/Core
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

class WCSC_Parceltemplate_Posttype
{
	const POST_TYPE = 'sc_parcel_template';
	/**
	 * The Single instance of the class
	 *
	 * @var object $_instance
	 *
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
		self::init_hooks();
	}

	/**
	 * Initializing Post type
	 *
	 * @since 1.0.0
	 */
	private static function init_hooks()
	{
		add_action( 'init', array( __CLASS__, 'register_post_types' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ), 10 );
		add_action( 'save_post', array( __CLASS__, 'save' ) );

		add_action( 'admin_notices', array( __CLASS__, 'notice_area' ) );

		add_filter( 'post_updated_messages', array( __CLASS__, 'remove_all_messages' ) );
	}

	public static function admin_enqueue_scripts() {
		wp_enqueue_script( 'wcsc-multi-select' );
	}

	/**
	 * Main Instance
	 *
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
	 * Registering Post type
	 *
	 * @since 1.0.0
	 */
	public static function register_post_types()
	{
		$labels = array(
			'name'               => _x( 'Parcel templates', 'post type general name', 'shipcloud-for-woocommerce' ),
			'singular_name'      => _x( 'Parcel template', 'post type singular name', 'shipcloud-for-woocommerce' ),
			'menu_name'          => _x( 'Parcel templates', 'admin menu', 'shipcloud-for-woocommerce' ),
			'name_admin_bar'     => _x( 'Parcel template', 'add new on admin bar', 'shipcloud-for-woocommerce' ),
			'add_new'            => _x( 'Add new', 'parcel', 'shipcloud-for-woocommerce' ),
			'add_new_item'       => __( 'Add new parcel template', 'shipcloud-for-woocommerce' ),
			'new_item'           => __( 'New parcel template', 'shipcloud-for-woocommerce' ),
			'edit_item'          => __( 'Edit parcel template', 'shipcloud-for-woocommerce' ),
			'view_item'          => __( 'View parcel template', 'shipcloud-for-woocommerce' ),
			'all_items'          => __( 'All parcel templates', 'shipcloud-for-woocommerce' ),
			'search_items'       => __( 'Search parcel templates', 'shipcloud-for-woocommerce' ),
			'parent_item_colon'  => __( 'Parent parcel templates:', 'shipcloud-for-woocommerce' ),
			'not_found'          => __( 'No parcel template found.', 'shipcloud-for-woocommerce' ),
			'not_found_in_trash' => __( 'No parcel templates found in trash.', 'shipcloud-for-woocommerce' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description', 'shipcloud-for-woocommerce' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'edit.php?post_type=shop_order',
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => false
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
     * Builds link where a new template can be created.
     *
	 * @return string
	 */
	public static function get_create_link() {
		return get_admin_url( null, 'post-new.php?post_type=' . self::POST_TYPE );
	}

	/**
	 * Adding Parcels to Woo Menu
	 *
	 * @since 1.0.0
	 */
	public static function add_menu()
	{
		add_submenu_page( 'edit.php?post_type=product', __( 'Parcel templates', 'shipcloud-for-woocommerce' ), __( 'Parcel templates', 'shipcloud-for-woocommerce' ), 'manage_options', 'edit.php?post_type=sc_parcel_template' );
	}

	/**
	 * Adding Metaboxes
	 *
	 * @since 1.0.0
	 */
	public static function meta_boxes()
	{
		add_meta_box( 'box-tools', __( 'Tools', 'shipcloud-for-woocommerce' ), array(
			__CLASS__,
			'box_settings'
		), 'sc_parcel_template', 'normal' );
	}

	/**
	 * Settings Box
	 *
	 * @since 1.0.0
	 */
	public static function box_settings()
	{
		global $post;

		if ( 'sc_parcel_template' != $post->post_type )
		{
			return;
		}

		$shipcloud_api = new Woocommerce_Shipcloud_API( wcsc_shipping_method()->get_option( 'api_key' ) );
		$shipcloud_carriers = $shipcloud_api->get_carriers();

		$carriers = array();
		foreach( $shipcloud_carriers AS $carrier )
		{
			$carriers[ $carrier['name'] ] = $carrier['display_name'];
		}

		$selected_carrier = get_post_meta( $post->ID, 'carrier', true );

		/** @deprecated 2.0.0 Post-Type shall store carrier information properly. */
		if ( ! is_array( $selected_carrier ) ) {
			$tmp              = explode( '_', $selected_carrier, 2 );
			$selected_carrier = array(
				'carrier' => $tmp[0],
				'service' => $tmp[1],
                'package' => null,
			);
		}

		$width            = get_post_meta( $post->ID, 'width', true );
		$height           = get_post_meta( $post->ID, 'height', true );
		$length           = get_post_meta( $post->ID, 'length', true );
		$weight           = get_post_meta( $post->ID, 'weight', true );

		?>
		<div id="shipcloud-parcel-settings">
			<table class="form-table">
				<tbody>
				<tr>
					<th><label for="test"><?php _e( 'Width', 'shipcloud-for-woocommerce' ); ?></label></th>
					<td>
						<input type="text" name="width" value="<?php echo $width; ?>"/> <?php _e( 'cm', 'shipcloud-for-woocommerce' ); ?>
					</td>
				</tr>
				<tr>
					<th><label for="test"><?php _e( 'Height', 'shipcloud-for-woocommerce' ); ?></label></th>
					<td>
						<input type="text" name="height" value="<?php echo $height; ?>"/> <?php _e( 'cm', 'shipcloud-for-woocommerce' ); ?>
					</td>
				</tr>
				<tr>
					<th><label for="test"><?php _e( 'Length', 'shipcloud-for-woocommerce' ); ?></label></th>
					<td>
						<input type="text" name="length" value="<?php echo $length; ?>"/> <?php _e( 'cm', 'shipcloud-for-woocommerce' ); ?>
					</td>
				</tr>
				<tr>
					<th><label for="test"><?php _e( 'Weight', 'shipcloud-for-woocommerce' ); ?></label></th>
					<td>
						<input type="text" name="weight" value="<?php echo $weight; ?>"/> <?php _e( 'kg', 'shipcloud-for-woocommerce' ); ?>
					</td>
				</tr>
				<tr>
					<th><label for="carrier"><?php _e( 'Shipping carrier', 'shipcloud-for-woocommerce' ); ?></label></th>
					<td id="shipcloud_csp_wrapper">
                        <div id="shipcloud_csp_wrapper">
                            <select name="shipcloud_carrier" rel="shipcloud_carrier"></select>

                            <select name="shipcloud_carrier_service"
                                    rel="shipcloud_carrier_service"></select>

                            <select name="shipcloud_carrier_package"
                                    rel="shipcloud_carrier_package"></select>
                        </div>
                    </td>
				</tr>
				</tbody>
			</table>

            <script type="application/javascript">
                jQuery(function ($) {
                    $('#shipcloud_csp_wrapper').shipcloudMultiSelect(wcsc_carrier).select(
                        <?php echo json_encode($selected_carrier); ?>
                    );
                });
            </script>
		</div>
		<?php
	}

	/**
	 * Turn package type to readable label.
	 *
	 * @param string $slug
	 *
	 * @return string The proper label or the slug itself if no label was found.
     *
     * @deprecated 2.0.0 This is duplicate to \WC_Shipcloud_Order::get_package_label and needs to be injected.
	 */
	protected static function get_package_label( $slug ) {
		$labels = array(
			'books'         => _x( 'Books', 'label while creating shipping label', 'shipcloud-woocommerce' ),
			'bulk'          => _x( 'Bulk', 'label for oversize packages', 'shipcloud-woocommerce' ),
			'letter'        => _x( 'Letter', 'label for simple letters', 'shipcloud-woocommerce' ),
			'parcel'        => _x( 'Parcel', 'label for simple packages', 'shipcloud-woocommerce' ),
			'parcel_letter' => _x( 'Parcel letter', 'letter for goods', 'shipcloud-woocommerce' ),
		);

		if ( ! isset( $labels[ $slug ] ) ) {
			return $slug;
		}

		return $labels[ $slug ];
	}

	/**
	 * Saving data
	 *
	 * @param int $post_id
	 *
	 * @since 1.0.0
	 */
	public static function save( $post_id )
	{
		global $wpdb;

		if ( wp_is_post_revision( $post_id ) )
		{
			return;
		}

		$request = array_intersect_key(
			$_POST,
			array(
				'post_type'                 => null,
				'width'                     => null,
				'height'                    => null,
				'length'                    => null,
				'weight'                    => null,
				'shipcloud_carrier'         => null,
				'shipcloud_carrier_service' => null,
				'shipcloud_carrier_package' => null,
			)
		);

		if ( ! array_key_exists( 'post_type', $request ) ) {
			return;
		}

		if ( static::POST_TYPE != $request['post_type'] ) {
			return;
		}

		if ( ! array_key_exists( 'shipcloud_carrier', $request ) ) {
			return;
		}

		$width  = $request['width'];
		$height = $request['height'];
		$length = $request['length'];
		$weight = $request['weight'];


		$post_title = wcsc_get_carrier_display_name( $request['shipcloud_carrier'] )
					  . ' ' . wcsc_api()->get_service_label( $request['shipcloud_carrier_service'] )
					  . ' (' . WC_Shipcloud_Order::instance()->get_package_label( $request['shipcloud_carrier_package'] ) . ')'
					  . ' - ' . $width
					  . ' x ' . $height
					  . ' x ' . $length
					  . ' ' . __( 'cm', 'shipcloud-for-woocommerce' )
					  . ' ' . $weight . __( 'kg', 'shipcloud-for-woocommerce' );

		$where = array( 'ID' => $post_id );
		$wpdb->update( $wpdb->posts, array( 'post_title' => $post_title ), $where );

		update_post_meta(
			$post_id,
			'carrier', array(
				'carrier' => $request['shipcloud_carrier'],
				'service' => $request['shipcloud_carrier_service'],
				'package' => $request['shipcloud_carrier_package'],
			)
		);

		update_post_meta( $post_id, 'width', wc_format_decimal( $width ) );
		update_post_meta( $post_id, 'height', wc_format_decimal( $height ) );
		update_post_meta( $post_id, 'length', wc_format_decimal( $length ) );
		update_post_meta( $post_id, 'weight', wc_format_decimal( $weight ) );
	}

	/**
	 * Notice Area
	 *
	 * @since 1.0.0
	 */
	public static function notice_area()
	{
		echo '<div class="shipcloud-message updated" style="display: none;"><p class="info"></p></div>';
	}

	/**
	 * Removing all messages
	 *
	 * @param array $messages
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public static function remove_all_messages( $messages )
	{
		global $post;

		if ( get_class( $post ) != 'WP_Post' )
		{
			return $messages;
		}

		if ( 'sc_parcel_template' == $post->post_type )
		{
			return array();
		}
	}
}

WCSC_Parceltemplate_Posttype::instance();
