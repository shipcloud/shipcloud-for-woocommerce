<?php
/**
 * Class Woocommerce_Shipcloud_API
 *
 * API Base class for Shipclod.io
 *
 * @author awesome.ug <very@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/API
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
class Woocommerce_Shipcloud_API{
    private $api_key;
    private $api_Url;
    private $content_type = 'application/json; charset=utf-8';

    public function __construct( $apiKey ){
        $this->api_key = base64_encode( $apiKey );
        $this->api_url = 'https://api.shipcloud.io/v1';
    }

    /**
     * set the cURL url: combining url-endpoint + action
     * @param $action
     * @return string
     */
    private function set_url( $action ) {
        $url = $this->api_url . '/' . $action;
        return $url;
    }

    /**
     * Set the standard cURl header here
     * @param $action
     * @param $method
     * @return array
     */
    private function set_opts( $action, $method ) {
        return array(
            CURLOPT_URL => $this->set_url( $action ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_USERAGENT => 'SC-client-php/0.2'
        );
    }
	
	public function get_rates( $params ){
		$action = 'shipment_quotes';
		
		$request = $this->send_request( $action, $params, 'POST' );
		
		if( 200 == $request[ 'header' ]['status'] ):
			return $request[ 'body' ];
		else:
			return FALSE;
		endif;
	}
	
	public function update_carriers(){
		$shipment_carriers = $this->get_carriers();
		update_option( 'woocommerce_shipcloud_carriers', $shipment_carriers );
		return $shipment_carriers;
	}
	
	public function get_carriers( $force_update = FALSE ){
		$shipment_carriers = get_option( 'woocommerce_shipcloud_carriers' );
		
		if( '' == $shipment_carriers || $force_update )
			$shipment_carriers = $this->update_carriers();
		
		return $shipment_carriers;
	}

    public function request_carriers(){
        $action = 'carriers';
        $request = $this->send_request( $action );

        if( 200 == $request[ 'header' ]['status'] ):
            return $request[ 'body' ];
        else:
            return FALSE;
        endif;
    }

    public function request_pickup( $params ){
        $action = 'pickup_requests';
        $request = $this->send_request( $action, $params, 'POST' );

        if( 200 == $request[ 'header' ]['status'] ):
            return $request[ 'body' ];
        else:
            return FALSE;
        endif;
    }
	
    /**
     * Sends a request to the API
     * @param string $action
     * @param array  $params
     * @param string $method
     * @return array
     */
    public function send_request( $action = '', $params = array(), $method = 'GET' ) {

        // Set basic header
        $header[] = "Authorization: Basic ".$this->api_key;
        $chOpts = $this->set_opts( $action, $method );

        // init Curl
        $ch = curl_init();

        if ( $method === 'POST' || $method === 'PUT') {
            if ( is_array($params) ) {
                $data_string = json_encode( $params );
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                $header[] = 'Content-Type: application/json';
                $header[] = 'Content-Length: ' . strlen($data_string);
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt_array($ch, $chOpts);

        // get the response
        $responseBody = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);

        if ($responseBody === false) {
            $responseBody = array('error' => curl_error($ch));
        }
        curl_close($ch);

        if ($this->content_type === $responseInfo['content_type']) {
            $responseBody = json_decode($responseBody, true);
        }

        // return the response
        return array(
            'header' => array(
                'request'       => $responseInfo['url'],
                'status'        => $responseInfo['http_code'],
                'request_size'  => $responseInfo['request_size'],
                'total_time'    => $responseInfo['total_time'],
            ),
            'body' => $responseBody
        );
    }
}