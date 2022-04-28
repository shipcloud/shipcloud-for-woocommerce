<div class="error">
	<p><?php echo WC_SHIPPING_SHIPCLOUD_NAME . ' ' . __( "error: Your environment doesn't meet all of the system requirements listed below.", "shipcloud-for-woocommerce" ); ?></p>

	<ul class="ul-disc">
		<li>
			<strong>PHP <?php echo WC_SHIPPING_SHIPCLOUD_REQUIRED_PHP_VERSION; ?>+</strong>
			<em>(<?php echo sprintf( __( "You're running version %s", "shipcloud-for-woocommerce" ), esc_html( PHP_VERSION ) ); ?>)</em>
		</li>

		<li>
			<strong>WordPress <?php echo WC_SHIPPING_SHIPCLOUD_REQUIRED_WP_VERSION; ?>+</strong>
			<em>(<?php echo sprintf( __( "You're running version %s", "shipcloud-for-woocommerce" ), esc_html( $wp_version ) ); ?>)</em>
		</li>

		<?php if ( defined( 'WOOCOMMERCE_VERSION' ) ) : ?>
		<li>
			<strong>Plugin WooCommerce <?php echo WC_SHIPPING_SHIPCLOUD_REQUIRED_WC_VERSION; ?>+</strong>
			<em>(<?php echo sprintf( __( "You're running version %s", "shipcloud-for-woocommerce" ), esc_html( WOOCOMMERCE_VERSION ) ); ?>)</em>
		</li>
		<?php endif; ?>		
	</ul>

	<p><?php echo sprintf( __( "If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to <a href='%s' target='_blank'>the Codex</a>.", "shipcloud-for-woocommerce" ), "https://wordpress.org/support/article/updating-wordpress/" ); ?></p>
</div>
