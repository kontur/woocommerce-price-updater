<div id="woocommerce-price-bulk-updater">
	<form method="POST" action="<?php echo PRICE_BULK_UPDATER_ADMIN_URL; ?>">
		<?php wp_nonce_field('price_bulk_updater', 'price_bulk_updater_options'); ?>

		<input type="hidden" name="action" value="update">

		<h1>Price Bulk Updater</h1>

		<div id="woocommerce-price-bulk-updater-values">
			<fieldset id="woocommerce-price-bulk-updater-match">
				<strong>Match products</strong>
				<div class="woocommerce-price-bulk-updater-row woocommerce-price-bulk-updater-disabled">
					<label>
						<input type="checkbox">
						<span>Current price</span>
					</label>
					<input name="current_price" data-param="price" type="text" disabled>
					<em>Allowed is numeric input (9 or 9.99) or empty value to match products with no price.</em>
				</div>
				<div class="woocommerce-price-bulk-updater-row woocommerce-price-bulk-updater-disabled">
					<label>
						<input type="checkbox">
						<span>Current sales price</span>
					</label>
					<input name="current_sales_price" data-param="sale" type="text" disabled>
					<em>Allowed is numeric input (9 or 9.99) or empty value to match products with no sales price.</em>
				</div>
				<div class="woocommerce-price-bulk-updater-row woocommerce-price-bulk-updater-disabled">
					<label>
						<input type="checkbox">
						<span>Product name contains</span>
					</label>
					<input name="product_name" data-param="search" type="text" disabled>
					<em>Allowed is alphanumeric input, empty value will be ignored.</em>
				</div>
				<div id="woocommerce-price-bulk-updater-matches-wrapper" class="multiple">
					<p><code>0</code> matched product<span class="count-mulitple">s</span> will be affected.</p>
					<div id="woocommerce-price-bulk-updater-matches"></div>
				</div>
			</fieldset>
			<fieldset id="woocommerce-price-bulk-updater-prices">
				<strong>For matched products</strong>
				<div class="woocommerce-price-bulk-updater-row woocommerce-price-bulk-updater-disabled">
					<label>
						<input type="checkbox">
						<span>Set price</span>
					</label>
					<input name="new" type="text" disabled>
					<em>Allowed is numeric input (9 or 9.99) or empty value to remove prices from matched products.</em>
				</div>
				<div class="woocommerce-price-bulk-updater-row woocommerce-price-bulk-updater-disabled">
					<label>
						<input type="checkbox">
						<span>Set sales price</span>
					</label>
					<input name="new" type="text" disabled>
					<em>Allowed is numeric input (9 or 9.99) or empty value to remove sales prices from matched products.</em>
				</div>
			</fieldset>
		</div>

		<strong id="woocommerce-price-bulk-updater-warning">
			<?php _e("DANGER ZONE: This plugin will bluntly update all matched products' prices. Make sure you have a database backup and proceed with care.", PRICE_BULK_UPDATER_NAMESPACE); ?>
		</strong>

		<?php submit_button('Update prices', 'primary', 'submit', true, array("disabled" => "disabled")); ?>

	</form>
</div>