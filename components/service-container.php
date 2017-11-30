<?php

namespace Shipcloud;

function build_container() {
	/*
	 * Public services.
	 */
	$service_config = array(
		'\\Shipcloud\\Api' => function () {
			return new Api(
				wcsc_shipping_method()->get_option( 'api_key' ),
				'plugin.woocommerce.z4NVoYhp'
			);
		},
		'\\Woocommerce_Shipcloud_API' => function () {
			$options = get_option( 'woocommerce_shipcloud_settings' );

			$api_key = null;
			if ( isset( $options['api_key'] ) ) {
				$api_key = $options['api_key'];
			}

			return new \Woocommerce_Shipcloud_API( $api_key );
		},
		'\\Shipcloud\\Repository\\ShipmentRepository' => function () {
			return new \Shipcloud\Repository\ShipmentRepository();
		},
	);


	if ( is_admin() && get_current_user() ) {
		// As admin we have special services.
		$service_config = array_merge(
			$service_config,
			array(
				'\\Shipcloud\\Controller\\LabelController'    => function ( \Shipcloud\ServiceContainer $container ) {
					return new \Shipcloud\Controller\LabelController(
						$container->get( '\\Shipcloud\\Api' ),
						$container->get( '\\Shipcloud\\Repository\\ShipmentRepository' )
					);
				},
			)
		);
	}

	return new \Shipcloud\ServiceContainer( $service_config );
}

/**
 * @return \Shipcloud\ServiceContainer
 */
function container() {
	static $container;

	if ( ! $container ) {
		$container = build_container();
	}

	return $container;
}


