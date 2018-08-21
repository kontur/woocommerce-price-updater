<div id="woocommerce-price-bulk-updater">
	<form method="POST" action="<?php echo PRICE_BULK_UPDATER_ADMIN_URL; ?>">
		<?php wp_nonce_field('price_bulk_updater', 'price_bulk_updater_options'); ?>

		<input type="hidden" name="action" value="update">

		<h1>Price Bulk Updater</h1>

		<strong id="woocommerce-price-bulk-updater-warning">
			<?php _e("DANGER ZONE: This plugin will bluntly update all matched products' prices. Make sure you have a database backup and proceed with care.", PRICE_BULK_UPDATER_NAMESPACE); ?>
		</strong>

		<div id="woocommerce-price-bulk-updater-values">
			<fieldset>
				<label>Old prices to replace
					<input name="old" type="text">
				</label>
			</fieldset>
			<fieldset>
				<label>New prices
					<input name="new" type="text">
				</label>
			</fieldset>
		</div>
		<?php submit_button(); ?>

	</form>
</div>