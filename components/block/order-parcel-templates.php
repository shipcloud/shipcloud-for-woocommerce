<?php /** @var WC_Shipcloud_Order $this */ ?>
<table class="parcel-form-table">
	<tbody>
		<tr>
			<th>
				<?php _e( 'Your parcel templates', 'shipcloud-for-woocommerce' ); ?>
			</th>
			<td>
				<?php if ( count( $parcel_templates ) > 0 ) : ?>
					<select name="parcel_list">
						<option value="none"><?php _e( '[ Select a parcel ]', 'shipcloud-for-woocommerce' ); ?></option>
						<?php foreach ( $parcel_templates AS $parcel_template ): ?>
							<option value="<?php echo $parcel_template['value']; ?>"
								<?php foreach ( $parcel_template['data'] as $field => $value ): ?>
									data-<?php echo $field ?>="<?php esc_attr_e( $value ) ?>"
								<?php endforeach; ?>
									>
								<?php echo $parcel_template['option']; ?>
							</option>
						<?php endforeach; ?>
					</select>
				<?php else: ?>
					<p><?php echo sprintf( __( 'Please <a href="%s">add parcel templates</a> if you want to use.', 'shipcloud-for-woocommerce' ), admin_url( 'edit.php?post_type=sc_parcel_template' ) ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
	</tbody>
</table>
