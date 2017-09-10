<?php

/*
 * Public services.
 */
$service_config = array(
	'\\Shipcloud\\Api' => function () {
		return new Shipcloud\Api(
			wcsc_shipping_method()->get_option( 'api_key' ),
			'plugin.woocommerce.z4NVoYhp'
		);
	}
);

if ( is_admin() && get_current_user() ) {
	// As admin we have special services.
	$service_config = array_merge(
		$service_config,
		array(
			'\\Shipcloud\\Controller\\LabelController' => function ( \Shipcloud\ServiceContainer $container ) {
				return new \Shipcloud\Controller\LabelController( $container->get( '\\Shipcloud\\Api' ) );
			}
		)
	);
}

$_wcsc_container = new \Shipcloud\ServiceContainer( $service_config );

/**
 * @return \Shipcloud\ServiceContainer
 */
function _wcsc_container() {
	global $_wcsc_container;

	return $_wcsc_container;
}

