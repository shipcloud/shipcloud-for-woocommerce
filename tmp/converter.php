<?php

$json = file_get_contents( 'shipment.json' );

$arr = json_decode( $json, true );

$keys = [
	'from' 	=> 'sender_',
	'to' 	=> 'recipient_',
];

$result = [];
foreach ( $arr as $key => $value ) {
	if ( is_array( $value ) ) {
		if ( in_array( $key, array_keys( $keys ) ) ) {
			$key = $keys[$key];
		}
		foreach( $value as $k => $v ) {
			if ( is_array( $v ) ) {
				if ( $key === 'packages' ) {
					foreach( $v as $i => $c ) {
						$result[$i] = $c;
					}
				}
			}
			else {
				$result["{$key}{$k}"] = $v;
			}
		}
	}
	else {
		$result[$key] = $value;
	}
}

echo json_encode( $result );