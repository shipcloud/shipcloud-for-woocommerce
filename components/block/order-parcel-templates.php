<?php /** @var WC_Shipcloud_Order $this */ ?>
<div class="parcel-templates fifty">

	<div class="parcel-template-field parcels-recommended">
		<label for="parcels_recommended"><?php _e( 'Automatic determined Parcels', 'woocommerce-shipcloud' ); ?></label>
		<?php if ( count( $determined_parcels ) > 0 ) : ?>
			<input type="button" value="<?php _e( '&#8592; Insert', 'woocommerce-shipcloud' ); ?>" class="insert-to-form button"/>
			<select name="parcel_list">
				<option value="none"><?php _e( '[ Select a Parcel ]', 'woocommerce-shipcloud' ); ?></option>

				<?php foreach ( $determined_parcels AS $determined_parcel ): ?>
					<option value="<?php echo $determined_parcel[ 'value' ]; ?>"><?php echo $determined_parcel[ 'option' ]; ?></option>
				<?php endforeach; ?>
			</select>
		<?php else: ?>
			<p><?php _e( 'Please add weight and parcel dimensions in your Products to use automatic calculations.', 'woocommerce-shipcloud' ); ?></p>
		<?php endif; ?>
	</div>

	<div class="parcel-template-field parcels-templates">
		<label for="parcel_templates"><?php _e( 'Your Parcel Templates', 'woocommerce-shipcloud' ); ?></label>
		<?php if ( count( $parcel_templates ) > 0 ) : ?>
			<input type="button" value="<?php _e( '&#8592; Insert', 'woocommerce-shipcloud' ); ?>" class="insert-to-form button"/>
			<select name="parcel_list">
				<option value="none"><?php _e( '[ Select a Parcel ]', 'woocommerce-shipcloud' ); ?></option>

				<?php foreach ( $parcel_templates AS $parcel_template ): ?>
					<option value="<?php echo $parcel_template[ 'value' ]; ?>"><?php echo $parcel_template[ 'option' ]; ?></option>
				<?php endforeach; ?>
			</select>
		<?php else: ?>
			<p><?php echo sprintf( __( 'Please <a href="%s">add parcel templates</a> if you want to use.', 'woocommerce-shipcloud' ), admin_url( 'edit.php?post_type=sc_parcel_template' ) ); ?></p>
		<?php endif; ?>
	</div>

</div>
