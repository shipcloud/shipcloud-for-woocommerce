<?php
/**
 * shipcloud for WooCommerce
 * 
 * @package   WC_Shipping_Shipcloud
 * @author    Daniel Muenter <info@msltns.com>
 * @copyright shipcloud GmbH
 * @license   GPL 3
 * @category  Class

 * @wordpress-plugin
 * Plugin Name: shipcloud for WooCommerce
 * Plugin URI: https://www.wordpress.org/plugins/shipcloud-for-woocommerce/
 * Description: Integrates shipcloud shipment services to your WooCommerce shop.
 * Version: 2.0.5
 * Author: shipcloud GmbH
 * Author URI: https://shipcloud.io
 * Developer: shipcloud GmbH
 * Developer URI: https://developers.shipcloud.io
 * WC requires at least: 5.2
 * WC tested up to: 6.2.0
 * Tested up to: 5.9.1
 * Text Domain: shipcloud-for-woocommerce
 * Domain Path: /languages/
 * Copyright: © 2022 shipcloud GmbH
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the license, or
 * any later version.
 *
 * The plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this plugin. If not, see License URI.
 * 
 */
define( 'WC_SHIPPING_SHIPCLOUD_REQUIRED_PHP_VERSION',	'7.4.2' );
define( 'WC_SHIPPING_SHIPCLOUD_REQUIRED_WP_VERSION',	'5.6.1' );
define( 'WC_SHIPPING_SHIPCLOUD_REQUIRED_WC_VERSION',	'5.2' );

define( 'WC_SHIPPING_SHIPCLOUD_VERSION', 				'2.0.5' );

define( 'WC_SHIPPING_SHIPCLOUD_NAME', 					'shipcloud' );
define( 'WC_SHIPPING_SHIPCLOUD_PREFIX',					'wc_shipcloud' );

define( 'WC_SHIPPING_SHIPCLOUD_INCLUDES_DIR', 			trailingslashit( dirname( __FILE__ ) . '/includes' ) );
define( 'WC_SHIPPING_SHIPCLOUD_TEMPLATES_DIR',			trailingslashit( dirname( __FILE__ ) . '/templates' ) );
define( 'WC_SHIPPING_SHIPCLOUD_VIEWS_DIR', 				trailingslashit( dirname( __FILE__ ) . '/views' ) );
define( 'WC_SHIPPING_SHIPCLOUD_VENDOR_DIR', 			trailingslashit( dirname( __FILE__ ) . '/vendor' ) );

define( 'WC_SHIPPING_SHIPCLOUD_LANGUAGES_DIR',			basename( dirname( __FILE__ ) ) . '/languages' );

define( 'WC_SHIPPING_SHIPCLOUD_JS_DIR',					plugin_dir_url( __FILE__ ) . 'assets/js' );
define( 'WC_SHIPPING_SHIPCLOUD_CSS_DIR',				plugin_dir_url( __FILE__ ) . 'assets/css' ); 
define( 'WC_SHIPPING_SHIPCLOUD_IMG_DIR',				plugin_dir_url( __FILE__ ) . 'assets/images' ); 

define( 'WC_SHIPPING_SHIPCLOUD_HOME_URL', 				trailingslashit( home_url() ) );

define( 'WC_SHIPPING_SHIPCLOUD_OPTIONS_NAME',			'woocommerce_shipcloud_settings' );
define( 'WC_SHIPPING_SHIPCLOUD_AFFILIATE_ID',			'plugin.woocommerce.z4NVoYhp' );

define( 'WC_SHIPPING_SHIPCLOUD_CPT_PARCEL_TEMPLATE', 	'sc_parcel_template' );
define( 'WC_SHIPPING_SHIPCLOUD_AJAX_UPDATE', 			'wp_ajax_shipcloud_label_update' );


require_once( WC_SHIPPING_SHIPCLOUD_VENDOR_DIR . 'autoload.php' );

require_once __DIR__ . '/includes/class-wc-shipping-shipcloud-utils.php';
require_once __DIR__ . '/includes/class-wc-shipping-shipcloud-api-adapter.php';
require_once __DIR__ . '/includes/class-wc-shipping-shipcloud-order.php';
require_once __DIR__ . '/includes/class-wc-shipping-shipcloud-order-bulk.php';
require_once __DIR__ . '/includes/class-wc-shipping-shipcloud-shipping-classes.php';
require_once __DIR__ . '/includes/class-wc-shipping-shipcloud-cpt-parcel-template.php';
require_once __DIR__ . '/includes/class-wc-shipping-shipcloud-carrier.php';
require_once __DIR__ . '/includes/class-wc-shipping-shipcloud-webhook.php';

class WooCommerce_Shipping_Shipcloud {

	/**
	 * Instance of this class.
	 *
	 * @var WooCommerce_Shipping_Shipcloud
	 */
	private static $instance;
	
	/**
	 * Get the class instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the plugin's public actions.
	 */
	public function __construct() {
		
		load_plugin_textdomain( 'shipcloud-for-woocommerce', false, WC_SHIPPING_SHIPCLOUD_LANGUAGES_DIR );
		
		add_action( 'woocommerce_shipping_init',								array( $this, 'shipping_method_init' ) );
		
		add_filter( 'woocommerce_general_settings', 							array( $this, 'filter_wc_general_settings' ), 999 );
		
		/* 
		 * Add very late to prevent manipulation by other plugins (e.g. Germanized).
		 * @see https://vendidero.de/tickets/ergaenzung-aller-versandmethoden-mit-germanized-einstellungen
		 */
		add_filter( 'woocommerce_shipping_methods',								array( $this, 'add_method' ), 9999 );
		add_action( 'wp_ajax_shipcloud_dismiss_upgrade_notice',					array( $this, 'dismiss_upgrade_notice' ) );
		
		if ( is_admin() ) {
			add_action( 'admin_menu', 			 								array( $this, 'add_menu_item' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 	array( $this, 'plugin_links' ) );
			add_filter( 'plugin_row_meta', 										array( $this, 'plugin_row_meta' ), 10, 2 );
			add_action( 'admin_notices', 										array( $this, 'upgrade_notice' ) );
			add_action( 'admin_enqueue_scripts', 								array( $this, 'admin_enqueue_scripts' ) );
			
			// Product editor enhancements
			add_action( 'woocommerce_product_options_shipping', 		array( $this, 'add_product_meta_fields' ) );
			add_action( 'woocommerce_process_product_meta', 			array( $this, 'save_product_meta_fields' ) );
		}
		else {
			// add frontend related code here
			add_action( 'wp_enqueue_scripts', 							array( $this, 'enqueue_scripts' ) );
		}
    add_shortcode( 'shipcloud_orderid', 'shipcloud-for-woocommerce' );
	}
	
	public static function activate_plugin() {
		$instance = self::get_instance();
		$instance->log( "activate_plugin called" );
		$instance->maybe_install();
	}
	 
	public static function deactivate_plugin() {
		self::get_instance()->log( "deactivate_plugin called" );
	}
	
	/**
	 * Checks the plugin version.
	 *
	 * @return bool
	 */
	public function maybe_install() {
		
		// Only need to do this for versions less than current version to migrate settings
		// to shipping zone instance.
		$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;
		if ( ! $doing_ajax && ! defined( 'IFRAME_REQUEST' ) 
			// && version_compare( get_option( 'wc_shipcloud_version' ), WC_SHIPPING_SHIPCLOUD_VERSION, '<' )
		) {
			
			$this->install();
			
			$active_plugins = get_option( 'active_plugins', array() );
			foreach ( $active_plugins as $key => $active_plugin ) {
				if ( $active_plugin === 'shipcloud-for-woocommerce/woocommerce-shipcloud.php' ) {
					$active_plugins[ $key ] = 'shipcloud-for-woocommerce/woocommerce-shipping-shipcloud.php';
				}
			}
			update_option( 'active_plugins', $active_plugins );
			
			update_option( 'wc_shipcloud_version', WC_SHIPPING_SHIPCLOUD_VERSION );
		}

		return true;
	}

	/**
	 * Update/migration script.
	 *
	 */
	public function install() {
		
		$this->log( "install called" );
		
		// Get all saved settings and cache it.
		$shipcloud_settings = WC_Shipping_Shipcloud_Utils::get_plugin_options();
		$old_version 		= 'shipcloud-for-woocommerce/woocommerce-shipcloud.php';
		if ( $this->plugin_is_active( $old_version ) ) {
			deactivate_plugins( $old_version, true );
		}

		// Settings exists.
		if ( $shipcloud_settings ) {
			
			global $wpdb;

			$sql  = "SELECT * FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = 'shipcloud_shipment_data';";
			$rows = $wpdb->get_results( $sql );
			
			$count = 0;
			foreach( $rows as $row ) {
				$meta_id 		= $row->meta_id;
				$meta_value 	= $row->meta_value;
				$shipment_data 	= maybe_unserialize( $meta_value );
				if ( array_key_exists( 'sender_first_name', $shipment_data ) ) {
					$shipment_data = WC_Shipping_Shipcloud_Utils::convert_postmeta_to_shipment( $shipment_data );
					$shipment_data = maybe_serialize( $shipment_data );
					
					$sql = "UPDATE `{$wpdb->prefix}postmeta` SET `meta_value` = '{$shipment_data}' WHERE `meta_id` = '{$meta_id}';";
					$wpdb->query( $sql );
					$count++;
				}
			}
		}
		
		update_option( 'wc_shipcloud_version', WC_SHIPPING_SHIPCLOUD_VERSION );
		
		$this->log( "Upgrade finished. Changed {$count} datasets." );
	}
	
	/**
	 * Plugin page links.
	 *
	 * @param array $links Plugin links.
	 * @return array Plugin links.
	 */
	public function plugin_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shipcloud' ) . '">' . __( 'Settings', 'shipcloud-for-woocommerce' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	/**
	 * Plugin page links to support and documentation
	 *
	 * @param  array  $links List of plugin links.
	 * @param  string $file Current file.
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {
			$row_meta = array(
				
			);
			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}

	/**
	 * Include needed files.
	 *
	 * @return void
	 */
	public function shipping_method_init() {
		include_once __DIR__ . '/includes/class-wc-shipping-shipcloud.php';
	}
	
	/**
	 * Add shipcloud shipping method.
	 *
	 *
	 * @param array $methods Shipping methods.
	 * @return array Shipping methods.
	 */
	public function add_method( $methods ) {
		$methods[WC_SHIPPING_SHIPCLOUD_NAME] = 'WC_Shipping_Shipcloud';
		/* Restore natural order. */
		ksort( $methods );		
		return $methods;
	}
	
	/**
	 * Adds some form fields to WC general settings
	 *
	 * @param array $settings
	 * @return array
	 */
	public function filter_wc_general_settings( $settings ) {
		
		$company = [
			[
				'title'    => __( 'Company', 'shipcloud-for-woocommerce' ),
				'desc'     => __( 'The name of your business.', 'shipcloud-for-woocommerce' ),
				'id'       => 'woocommerce_store_company',
				'default'  => '',
				'type'     => 'text',
				'desc_tip' => true,
			]
		];
		
		$settings = WC_Shipping_Shipcloud_Utils::array_insert( $settings, 1, $company );
		
		$contact = [
			[
				'title'    => __( 'Contact', 'shipcloud-for-woocommerce' ),
				'desc'     => __( 'The name of the contact person.', 'shipcloud-for-woocommerce' ),
				'id'       => 'woocommerce_store_manager',
				'default'  => '',
				'type'     => 'text',
				'desc_tip' => true,
			]
		];
		
		$settings = WC_Shipping_Shipcloud_Utils::array_insert( $settings, 2, $contact );
		
		$postcode = [ $settings[7] ];
		unset( $settings[7] );
		
		$settings = WC_Shipping_Shipcloud_Utils::array_insert( $settings, 5, $postcode );
		
		return $settings;
	}
	
	/**
	 * Add shipcloud specific custom fields to products
	 *
	 * @return void
	 */
	public function add_product_meta_fields() {
	    woocommerce_wp_text_input(
	        array(
	            'id' 			=> 'shipcloud_hs_tariff_number',
	            'label' 		=> __( 'HS tariff number', 'shipcloud-for-woocommerce' ),
	            'desc_tip' 		=> true,
	            'description' 	=> __( 'Harmonized System Tariff Number', 'shipcloud-for-woocommerce' ),
	        )
	    );

	    woocommerce_wp_select(
	        array(
	            'id'      => 'shipcloud_origin_country',
	            'label'   => __( 'Origin country', 'shipcloud-for-woocommerce' ),
	            'options' => array_merge(
	                array(
	                    '' => ''
	                ),
	                WC()->countries->countries
	            ),
	        )
	    );
	}
	
	/**
	 * Save custom product data
	 *
	 * @param string $post_id
	 * @return void
	 */
	public function save_product_meta_fields( $post_id ) {
	    $product = wc_get_product( $post_id );

	    $hs_tariff_number = isset( $_POST['shipcloud_hs_tariff_number'] ) ? $_POST['shipcloud_hs_tariff_number'] : '';
	    $origin_country = isset( $_POST['shipcloud_origin_country'] ) ? $_POST['shipcloud_origin_country'] : '';

	    $product->update_meta_data( 'shipcloud_hs_tariff_number', sanitize_text_field( $hs_tariff_number ) );
	    $product->update_meta_data( 'shipcloud_origin_country', sanitize_text_field( $origin_country ) );
	    $product->save();
	}
	
	/**
	 * Show the user a notice for plugin updates.
	 *
	 * @return void
	 */
	public function upgrade_notice() {
		
		$show_notice = get_option( 'woocommerce_shipcloud_show_upgrade_notice' );

		if ( 'yes' !== $show_notice ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$query_args 	 = array( 'page' => 'wc-settings', 'tab' => 'shipping' );
		$zones_admin_url = add_query_arg( $query_args, get_admin_url() . 'admin.php' );
		/*
		?>
		<div class="notice notice-success is-dismissible wc-shipcloud-notice">
			<p><?php echo sprintf( __( 'Template now supports shipping zones. The zone settings were added to a new Template method on the "Rest of the World" Zone. See the zones %1$shere%2$s ', 'shipcloud-for-woocommerce' ), '<a href="' . $zones_admin_url . '">', '</a>' ); ?></p>
		</div>

		<script type="application/javascript">
			jQuery( '.notice.wc-shipcloud-notice' ).on( 'click', '.notice-dismiss', function () {
				wp.ajax.post('shipcloud_dismiss_upgrade_notice');
			});
		</script>
		<?php
		*/
	}

	/**
	 * Turn of the dismisable upgrade notice.
	 *
	 * @return void
	 */
	public function dismiss_upgrade_notice() {
		update_option( 'woocommerce_shipcloud_show_upgrade_notice', 'no' );
	}
	
	/**
	 * Add admin menu.
	 *
	 * @return void
	 */
	public function add_menu_item() {
		
		add_menu_page( 
			__( 'shipcloud Settings', 'shipcloud-for-woocommerce' ), 
			__( 'shipcloud', 'shipcloud-for-woocommerce' ),
			'manage_woocommerce',
			WC_SHIPPING_SHIPCLOUD_OPTIONS_NAME . '_menu',
			array( $this, 'get_settings_page' ),
			WC_SHIPPING_SHIPCLOUD_IMG_DIR . '/shipcloud.png',
			58
		);
		
		add_submenu_page( 
			WC_SHIPPING_SHIPCLOUD_OPTIONS_NAME . '_menu',
			__( 'Parcel templates', 'shipcloud-for-woocommerce' ), 
			__( 'Parcel templates', 'shipcloud-for-woocommerce' ), 
			'manage_woocommerce', 
			'edit.php?post_type=' . WC_SHIPPING_SHIPCLOUD_CPT_PARCEL_TEMPLATE 
		);
		
	}
	
	/**
	 * Redirects to shipcloud settings page.
	 *
	 * @return void
	 */
	public function get_settings_page() {
		wp_redirect( admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shipcloud' ) );
	}
	
	/**
	 * Assets to enqueue in admin.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		
		wp_register_style( 'shipcloud-admin', WC_SHIPPING_SHIPCLOUD_CSS_DIR . '/shipcloud-admin.css', false );
		wp_register_style( 'jquery-multiselect', WC_SHIPPING_SHIPCLOUD_CSS_DIR . '/jquery.multiselect.css', false );
		wp_enqueue_style( 'shopcloud-fa', WC_SHIPPING_SHIPCLOUD_CSS_DIR . '/fontawesome.min.css', false );
    wp_enqueue_style( 'shopcloud-fa-solid', WC_SHIPPING_SHIPCLOUD_CSS_DIR . '/fontawesome.solid.min.css', false );
		
		wp_register_script( 'jquery-multiselect', WC_SHIPPING_SHIPCLOUD_JS_DIR . '/jquery.multiselect.js', array( 'jquery' ) );
		wp_register_script( 'shipcloud-label', WC_SHIPPING_SHIPCLOUD_JS_DIR . '/shipcloud-label.js', array( 'jquery' ) );
        wp_register_script( 'shipcloud-label-form', WC_SHIPPING_SHIPCLOUD_JS_DIR . '/shipcloud-label-form.js', array( 'jquery' ) );
        wp_register_script( 'shipcloud-filler', WC_SHIPPING_SHIPCLOUD_JS_DIR . '/shipcloud-filler.js', array( 'jquery' ) );
        wp_register_script( 'shipcloud-shipments', WC_SHIPPING_SHIPCLOUD_JS_DIR . '/shipcloud-shipments.js', array( 'jquery', 'jquery-serialize-object', 'wp-backbone' ) );
		wp_register_script( 'shipcloud-admin', WC_SHIPPING_SHIPCLOUD_JS_DIR . '/shipcloud-admin.js', array( 'jquery', 'jquery-ui-sortable' ) );
		
		$localized_strings = array(
			// standard texts
			'yes'					=> __( 'Yes', 'shipcloud-for-woocommerce' ),
			'no'					=> __( 'No', 'shipcloud-for-woocommerce' ),
			'force_delete_dialog'	=> __( 'Force delete Dialog', 'shipcloud-for-woocommerce' ),
			// multiselect texts
			'placeholder' 			=> __( 'Select options', 'shipcloud-for-woocommerce' ),
			'search' 				=> __( 'Search', 'shipcloud-for-woocommerce' ),
			'selected' 				=> __( ' selected', 'shipcloud-for-woocommerce' ),
			'selectAll'				=> __( 'Select all', 'shipcloud-for-woocommerce' ),
			'unselectAll'			=> __( 'Unselect all', 'shipcloud-for-woocommerce' ),
			'noneSelected'			=> __( 'None Selected', 'shipcloud-for-woocommerce' ),
		);
		wp_localize_script( 'shipcloud-shipments', 'wcsc_translate', $localized_strings );
		wp_localize_script( 'shipcloud-admin', 'wcsc_translate', $localized_strings );
		
		$contents_types = WC_Shipping_Shipcloud_Utils::get_customs_declaration_contents_types();
		wp_localize_script( 'shipcloud-admin', 'shipcloud_customs_declaration_contents_types', $contents_types );
		
		wp_enqueue_script( 'jquery-serialize-json', WC_SHIPPING_SHIPCLOUD_JS_DIR . '/jquery.serializejson.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'shipcloud-fa', WC_SHIPPING_SHIPCLOUD_JS_DIR . '/fontawesome.min.js', false );
    wp_enqueue_script( 'shipcloud-fa-solid', WC_SHIPPING_SHIPCLOUD_JS_DIR . '/fontawesome.solid.min.js', false );
		
	}
	
	public function enqueue_scripts() {
		wp_enqueue_style( 'shopcloud-fa', WC_SHIPPING_SHIPCLOUD_CSS_DIR . '/fontawesome.min.css', false );
    wp_enqueue_style( 'shopcloud-fa-solid', WC_SHIPPING_SHIPCLOUD_CSS_DIR . '/fontawesome.solid.min.css', false );
		wp_enqueue_script( 'shipcloud-fa', WC_SHIPPING_SHIPCLOUD_JS_DIR . '/fontawesome.min.js', false );
    wp_enqueue_script( 'shipcloud-fa-solid', WC_SHIPPING_SHIPCLOUD_JS_DIR . '/fontawesome.solid.min.js', false );
		wp_enqueue_script( 'shipcloud-frontend', WC_SHIPPING_SHIPCLOUD_JS_DIR . '/shipcloud-frontend.js', false );
	}
	
	/**
	 * Checks if a plugin is activated.
	 *
	 * @param string $plugin the plugin to be checked
	 * @return bool
	 */
	private function plugin_is_active( $plugin = '' ) {
		if ( empty( $plugin ) ) {
			return false;
		}
	    if( !function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );	
		}
	    return ( is_plugin_active( $plugin ) );
	}

	/**
	 * Output an admin notice.
	 *
	 * @param string 	$message 		Debug message.
	 * @param string 	$type    		Message type.
	 * @param bool 		$dismissible    Message type.
	 * @return void
	 */
	private function add_admin_notice( $message, $type = 'info', $dismissible = true ) {
		WC_Shipping_Shipcloud_Utils::add_admin_notice( $message, $type, $dismissible );
	}

	/**
	 * Output a debug message.
	 *
	 * @param string 	$message 	Debug message.
	 * @param string 	$level   	Debug level.
     * @param mixed 	$context	The Debug context.
	 * @return void
	 */
	public function log( $message, $level = 'info', $context = [] ) {
		WC_Shipping_Shipcloud_Utils::log( $message, $level, $context );
	}
	
}


/*****************************************************************
 *
 *		BOOTSTRAP
 *
 *****************************************************************/


/**
 * Checks if the system requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */
function shipcloud_requirements_met() {
	
	global $wp_version;
	
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    
	if ( version_compare( PHP_VERSION, WC_SHIPPING_SHIPCLOUD_REQUIRED_PHP_VERSION, '<' ) ) {
		return false;
	}

	if ( version_compare( $wp_version, WC_SHIPPING_SHIPCLOUD_REQUIRED_WP_VERSION, '<' ) ) {
		return false;
	}

	if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		return false;
	}
	
	if ( defined( 'WOOCOMMERCE_VERSION' ) ) {
		if ( version_compare( WOOCOMMERCE_VERSION, WC_SHIPPING_SHIPCLOUD_REQUIRED_WC_VERSION, '<' ) ) {
			return false;
		}
	}
	
	return true;
}

/**
 * Prints an error that the system requirements weren't met.
 */
function shipcloud_requirements_not_met() {
	global $wp_version;

	require_once( WC_SHIPPING_SHIPCLOUD_TEMPLATES_DIR . "requirements-error.php" );
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets 
 * loaded if the plugin requirements are met. Otherwise older PHP 
 * installations could crash when trying to parse it.
 */
function shipcloud_load() {
	if ( shipcloud_requirements_met() ) {
		if ( class_exists( 'WooCommerce_Shipping_Shipcloud' ) ) {
			$GLOBALS[WC_SHIPPING_SHIPCLOUD_PREFIX] = WooCommerce_Shipping_Shipcloud::get_instance();
		}
	} else {
		add_action( 'admin_notices', 'shipcloud_requirements_not_met' );
	}
}
add_action( 'plugins_loaded', 'shipcloud_load', 9999 );


if ( ! function_exists( 'shipcloud_activate' ) ) {
	function shipcloud_activate() {
		$instance = WooCommerce_Shipping_Shipcloud::get_instance();
		$instance->log( "activate_plugin called" );
		$instance->maybe_install();
	}
}
register_activation_hook( __FILE__, 'shipcloud_activate' );
