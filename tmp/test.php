<?php

$json = '{
			"tracking_events": [
				{
					"timestamp": "2021-09-14T15:56:39+02:00",
					"location": "Paderborn",
					"status": "package_send",
					"details": "Es wurde eine Sendung angelegt",
					"id": "4bff7566-33f4-4baa-84aa-9fda2d02b77c"
				},
				{
					"timestamp": "2021-08-14T15:56:39+02:00",
					"location": "Paderborn",
					"status": "label_created",
					"details": "Es wurde eine Sendung angelegt",
					"id": "4bff7566-33f4-4baa-84aa-9fda2d02b77c"
				},
				{
					"timestamp": "2021-10-14T15:56:39+02:00",
					"location": "Paderborn",
					"status": "package_delivered",
					"details": "Es wurde eine Sendung angelegt",
					"id": "4bff7566-33f4-4baa-84aa-9fda2d02b77c"
				}
			]
		}';

$package = json_decode( $json, true );

$tracking_events = $package['tracking_events'];

$latest	  = get_latest_tracking_status( $tracking_events );

echo "Latest: {$latest['status']}" . PHP_EOL;




function get_latest_tracking_status( $tracking_events = [] ) {

	if ( empty( $tracking_events ) ) {
		return false;
	}

	usort( $tracking_events, function( $a, $b ) {
		$ad = new DateTime( $a['timestamp'] );
		$bd = new DateTime( $b['timestamp'] );

		if ( $ad == $bd ) {
			return 0;
		}

		return $ad > $bd ? -1 : 1;
	} );

	return array_shift( $tracking_events );
}



function sort_descending( $a, $b ) {
	$ad = new DateTime( $a['timestamp'] );
	$bd = new DateTime( $b['timestamp'] );

	if ( $ad == $bd ) {
		return 0;
	}

	return $ad > $bd ? -1 : 1;
}

function sort_ascending( $a, $b ) {
	$ad = new DateTime( $a['timestamp'] );
	$bd = new DateTime( $b['timestamp'] );

	if ( $ad == $bd ) {
		return 0;
	}

	return $ad < $bd ? -1 : 1;
}


