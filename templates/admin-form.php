<div id="woocommerce-price-updater">
	<form method="POST" action="<?php echo WOOCOMMERCE_PRICE_UPDATER_ADMIN_URL; ?>">
		<?php wp_nonce_field('woocommerce_price_updater', 'woocommerce_price_updater_options'); ?>

		<input type="hidden" name="action" value="update">

		<h1><img id="woocomerce-price-updater-logo" src="<?php echo $this->assets_url . "woocommerce-price-updater-logo.svg"; ?>">Price Updater</h1>

		<div id="woocommerce-price-updater-values">
			<fieldset id="woocommerce-price-updater-match">
				<h2>Match products</h2>
				<p>Update all products that match
					<select name="method">
						<option value="any" selected>ANY</option>
						<option value="all">ALL</option>
					</select>
					of the below criteria:
				</p>
				<div class="woocommerce-price-updater-row woocommerce-price-updater-disabled">
					<label>
						<input type="checkbox">
						<span>Current active price</span>
					</label>
					<input name="price" type="text" disabled>
					<em>Allowed is numeric input (9 or 9.99) or empty value to match products with no price.</em>
				</div>
				<div class="woocommerce-price-updater-row woocommerce-price-updater-disabled">
					<label>
						<input type="checkbox">
						<span>Current regular price</span>
					</label>
					<input name="regular" type="text" disabled>
					<em>Allowed is numeric input (9 or 9.99) or empty value to match products with no price.</em>
				</div>
				<div class="woocommerce-price-updater-row woocommerce-price-updater-disabled">
					<label>
						<input type="checkbox">
						<span>Current sales price</span>
					</label>
					<input name="sale" type="text" disabled>
					<em>Allowed is numeric input (9 or 9.99) or empty value to match products with no sales price.</em>
				</div>
				<div class="woocommerce-price-updater-row woocommerce-price-updater-disabled">
					<label>
						<input type="checkbox">
						<span>Product name contains</span>
					</label>
					<input name="search" type="text" disabled>
					<em>Allowed is alphanumeric input, empty value will be ignored.</em>
				</div>
				<div class="woocommerce-price-updater-row woocommerce-price-updater-disabled">
					<label>
						<input type="checkbox">
						<span>Product in category</span>
					</label>
					<input name="category" type="text" disabled>
					<em>Allowed is alphanumberic input, empty value will be ignored.</em>
				</div>
				<div id="woocommerce-price-updater-matches-wrapper" class="hidden-initially multiple">
					<p>
						<code>0</code> matched product
						<span class="count-mulitple">s</span> will be affected.</p>
					<div id="woocommerce-price-updater-matches"></div>
				</div>
			</fieldset>
			<fieldset id="woocommerce-price-updater-prices">
				<h2>Update prices</h2>
				<p>Select what should be updated. Unchecked values remain as they are.</p>
				<div class="woocommerce-price-updater-row woocommerce-price-updater-disabled">
					<label>
						<input type="checkbox">
						<span>Set current price</span>
					</label>
					<input name="new_price" type="text" disabled>
					<em>Allowed is numeric input (9 or 9.99) or empty value to remove prices from matched products.</em>
				</div>
				<div class="woocommerce-price-updater-row woocommerce-price-updater-disabled">
					<label>
						<input type="checkbox">
						<span>Set regular price</span>
					</label>
					<input name="new_regular" type="text" disabled>
					<em>Allowed is numeric input (9 or 9.99) or empty value to remove prices from matched products.</em>
				</div>
				<div class="woocommerce-price-updater-row woocommerce-price-updater-disabled">
					<label>
						<input type="checkbox">
						<span>Set sales price</span>
					</label>
					<input name="new_sale" type="text" disabled>
					<em>Allowed is numeric input (9 or 9.99) or empty value to remove sales prices from matched products.</em>
					<strong class="woocommerce-price-updater-input-warning">This will immediately set matched products to be sold at this discounted price.</strong>
				</div>
			</fieldset>
		</div>

		<strong id="woocommerce-price-updater-warning">
			<?php _e("<span>DANGER ZONE:</span> This plugin will bluntly update all matched products' prices. Make sure you have a database backup and proceed with care.", WOOCOMMERCE_PRICE_UPDATER_NAMESPACE); ?>
		</strong>

		<?php submit_button('Update prices', 'primary', 'submit', true, array('disabled' => 'disabled')); ?>
	</form>

	<div id="woocommerce-price-updater-donate">
		<div>
			Brought to you by <a href="https://underscoretype.com">Underscore</a>.<br>
			If this plugin is useful to your business, consider making a small donation to support continuous development:
		</div>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="XE73BPCCTCPXL">
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/fi_FI/i/scr/pixel.gif" width="1" height="1">
		</form>
	</div>
</div>