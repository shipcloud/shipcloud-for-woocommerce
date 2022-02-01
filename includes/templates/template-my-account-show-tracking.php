<?php
	
?>
<h2>
    <?php _e( 'Tracking & Tracing', 'shipcloud-for-woocommerce' ); ?>
</h2>

<?php
if ( ! empty( $shipment_ids ) ) {
	foreach ( $shipment_ids as $shipment_id ) {
	    $tracking_events = get_post_meta( $order->get_id(), 'shipment_' . $shipment_id . '_trackingevent' );
	    $carrier_tracking_number = '';
	    foreach ( $shipments_data as $shipment_data ) {
	        if ( $shipment_data['id'] === $shipment_id ) {
	            $carrier_tracking_number = isset( $shipment_data['carrier_tracking_no'] ) ? $shipment_data['carrier_tracking_no'] : false;
	            // check to see if label url is present because GLS doesn't create a tracking number right away
	            if ( isset( $shipment_data['label_url'] ) ) {
					
					$tracking_url = !empty( $shipment_data['tracking_url'] ) ? $shipment_data['tracking_url'] : '';
					if ( empty( $tracking_url ) && ! empty( $carrier_tracking_number ) ) {
						$tracking_url = WC_Shipping_Shipcloud_Utils::get_carrier_tracking_url( $shipment_data['carrier'], $carrier_tracking_number );
					}
					
					if ( ! empty( $tracking_url ) && ! empty( $carrier_tracking_number ) ) {
						?>
		                <p>
		                    <strong><?php _e( 'Carrier', 'shipcloud-for-woocommerce' ); ?>:</strong> <?php echo WC_Shipping_Shipcloud_Utils::get_carrier_display_name( $shipment_data['carrier'] ); ?>, <strong><?php _e( 'Trackingnumber', 'shipcloud-for-woocommerce' ); ?>:</strong> <a href="<?php echo $tracking_url . "?utm_source=shipcloud-for-woocommerce_myaccount"; ?>" target="_blank"><?php echo $carrier_tracking_number; ?></a>
		                </p>
						<?php
					}
	                ?>
	                
					<?php if ( count( $tracking_events ) > 0 ) : ?>
						<table class="woocommerce-table woocommerce-table--order-details shop_table order_details shipcloud__tracking">
							<thead>
								<tr>
									<th><?php _e( 'Date', 'shipcloud-for-woocommerce' ); ?></th>
									<th></th>
									<th><?php _e( 'Details', 'shipcloud-for-woocommerce' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$tracking_events = array_reverse( $tracking_events );
								foreach ( $tracking_events as $tracking_event ) {
									$occured_at_timestamp = strtotime( $tracking_event['occured_at'] ); 
									?>
									<tr>
										<td>
											<span class="shipcloud__tracking--date"><?php echo strftime( '%d.%m.%Y, %H:%M', $occured_at_timestamp ); ?>&nbsp;Uhr</span><!-- &nbsp;<span class="shipcloud__tracking--status">
												<?php //echo WC_Shipping_Shipcloud_Utils::get_status_string( $tracking_event['type'] ); ?>
											</span> -->
										</td>
										<td>
											<?php echo WC_Shipping_Shipcloud_Utils::get_status_icon( $tracking_event['status'] ); ?>
										</td>
										<td>
											<?php echo $tracking_event['details'] ?>
											<span class="shipcloud__tracking--location">(<?php echo $tracking_event['location'] ?>)</span>
										</td>
									</tr>
									<?php
								} 
							?>
							</tbody>
						</table>
					<?php endif; ?>
					
					<?php 
				}
				else {
					?>
					<p><?php _e( 'There is no shipping information available yet.', 'shipcloud-for-woocommerce' ); ?></p>
					<?php
				}
			}
		}
	}
}
else {
	?>
	<p><?php _e( 'No shipping found.', 'shipcloud-for-woocommerce' ); ?></p>
	<?php
}