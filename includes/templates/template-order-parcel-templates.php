<div class="parcel-form-table">
	
        <label for="parcel_list"><?php _e( 'Your parcel templates', 'shipcloud-for-woocommerce' ); ?></label>
        <?php if ( count( $parcel_templates ) > 0 ) : ?>
            <select name="parcel_list" class="parcel-list">
                <option value="none"><?php _e( 'Select a template', 'shipcloud-for-woocommerce' ); ?></option>
                <?php foreach ( $parcel_templates AS $parcel_template ): ?>
                    <option value="<?php echo $parcel_template['value']; ?>"
                        <?php foreach ( $parcel_template['data'] as $field => $value ): ?>
                            data-<?php echo $field ?>="<?php esc_attr_e( $value ) ?>"
                        <?php endforeach; ?>
                        <?php if ( $parcel_template['shipcloud_is_standard_parcel_template'] ) : ?>
                            selected=selected
                        <?php endif; ?>
                    >
                        <?php echo $parcel_template['option']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <p><?php echo sprintf( __( 'Please <a href="%s">add parcel templates</a> if you want to use.', 'shipcloud-for-woocommerce' ), admin_url( 'edit.php?post_type=sc_parcel_template' ) ); ?></p>
        <?php endif; ?>
		
</div>
