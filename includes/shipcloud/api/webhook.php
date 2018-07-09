<?php
/**
 * Contains class to access the API for carrier information.
 */

namespace Shipcloud\Api;

use Shipcloud\Api;
// use Shipcloud\Domain\Webhook;

/**
 * Access the API for carrier information.
 *
 * @author  André Cedik <andre@shipcloud.io>
 * @since   1.8.0
 */
class Webhook {
	/**
	 * @var Api
	 */
	private $api;

	/**
	 * Webhook constructor.
	 *
	 * @param Api $api
	 */
	public function __construct( Api $api ) {
		$this->api = $api;
	}

	/**
	 * Create a webhook
	 *
	 * @return array
	 */
	public function create() {
        $data = array(
            'url' => WC()->api_request_url( 'shipcloud', true ),
            'event_types' => array('*')
        );

        $response = $this->api->request( 'webhooks', $data, 'POST' );
        $response_payload = $response->getPayload();

        add_option( 'woocommerce_shipcloud_catch_all_webhook_id', $response_payload['id'] );

        return $response_payload;
    }
}
