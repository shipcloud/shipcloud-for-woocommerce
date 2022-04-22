<div id="shipcloud-parcel-settings">
	<table class="form-table">
		<tbody>
			<tr>
				<th><label for="width"><?php _e( 'Width', 'shipcloud-for-woocommerce' ); ?></label></th>
				<td>
					<input type="text" name="width" value="<?php echo $width; ?>"/> <?php _e( 'cm', 'shipcloud-for-woocommerce' ); ?>
				</td>
			</tr>
			<tr>
				<th><label for="height"><?php _e( 'Height', 'shipcloud-for-woocommerce' ); ?></label></th>
				<td>
					<input type="text" name="height" value="<?php echo $height; ?>"/> <?php _e( 'cm', 'shipcloud-for-woocommerce' ); ?>
				</td>
			</tr>
			<tr>
				<th><label for="length"><?php _e( 'Length', 'shipcloud-for-woocommerce' ); ?></label></th>
				<td>
					<input type="text" name="length" value="<?php echo $length; ?>"/> <?php _e( 'cm', 'shipcloud-for-woocommerce' ); ?>
				</td>
			</tr>
			<tr>
				<th><label for="weight"><?php _e( 'Weight', 'shipcloud-for-woocommerce' ); ?></label></th>
				<td>
					<input type="text" name="weight" value="<?php echo $weight; ?>"/> <?php _e( 'kg', 'shipcloud-for-woocommerce' ); ?>
				</td>
			</tr>
			<tr>
				<th><label for="shipcloud_is_standard_parcel_template"><?php _e( 'Standard template', 'shipcloud-for-woocommerce' ); ?></label></th>
				<td>
					<input type="checkbox" name="shipcloud_is_standard_parcel_template" <?php if ($shipcloud_is_standard_parcel_template) { ?>checked=checked<?php } ?>/>
				</td>
			</tr>
			<tr>
				<th><label for="shipcloud_csp_wrapper"><?php _e( 'Shipping carrier', 'shipcloud-for-woocommerce' ); ?></label></th>
				<td id="shipcloud_csp_wrapper">
					<div id="shipcloud_csp_wrapper">
						<select name="shipcloud_carrier" rel="shipcloud_carrier"></select>

						<select name="shipcloud_carrier_service" rel="shipcloud_carrier_service"></select>

						<select name="shipcloud_carrier_package" rel="shipcloud_carrier_package"></select>
					</div>
				</td>
			</tr>
		</tbody>
	</table>

	<script type="application/javascript">
		jQuery(function ($) {
			$('#shipcloud_csp_wrapper').shipcloudMultiSelect(wcsc_carrier).select(
				<?php echo json_encode( $selected_carrier ); ?>
			);
		});
	</script>
</div>