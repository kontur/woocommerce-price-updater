=== WooCommerce Price Updater ===
Contributors: kontur
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=XE73BPCCTCPXL
Tags: woocommerce, ecommerce, price, bulk, update, sale
Requires at least: 4.6
Tested up to: 5.0
Stable tag: 0.1.0
Requires PHP: 5.6.33
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

A simple Wordpress WooCommerce plugin to set or replace WooCommerce prices in bulk

== Description ==

The plugin lets you update WooCommerce product prices and sales prices. You filter which products should be updated by selecting current price, sales price, name and/or category. Then you select what should be changed for all match products: Regular price, Sales price & Current price

== Installation ==

Recommended: The easiest installation is via the Admin dashboard. Log in to your WordPress Admin area, then:

1. `Plugins` > `Add New`
2. Search for: "WooCommerce Price Updater"
3. Press `Install`
4. Activate the plugin
5. The plugin will be available under `Products` > `Price Updater`

Alternatively: You can also manually install to your server:

1. Upload the plugin files to the `/wp-content/plugins/woocommerce-price-updater` directory
2. Activate the plugin through the `Plugins` screen in WordPress
3. The plugin will be available under `Products` > `Price Updater`


== Frequently Asked Questions ==

= What's the difference between "Regular price", "Current price" & "Sales price"? =

"Current price" is always what is currently shown in the storefront. Unless a product is on sale, this will be the same as the "Regular price". If a product is on sale, it should be the same as the "Sales price".

= Will setting a "Sales price" put the product on sale? =

Yes, but notice that you will want to also update the "Current price". When you set a "Sales price" for a product with a sales beginning or ending the "Sales price" be active only during the selected time.

= How can I narrow down the number of affected products? =

You can use all filters available at the top. Selecting to match `ANY` filters will include any product that matches any of the filters. If you select `ALL` only products that match all of the active filters will be affected.

= Can I see what product will be affected? =

When you select filters you will get an automatically updated list of products that the current selection matches. Those product will be affected by the new prices you set below.

= I'm using a different e-Commerce plugin than WooCommerce, can I still use the plugin? =

Unfortunately not - the functionality is specific to and requires the use of WooCommerce

= Will you implement feature XXX? =

This plugin is a continuous progress. If there is a feature you are missing, feel free to suggest it in the plugin forum.

= I found a bug, what should I do? =

1. Use the `View support forum` and start a new topic
2. Be as descriptive as possible. What happens? What did you expect to happen? Can this be reproduced with specific steps?
3. Provide additional information, if you can: WordPress version, WooCommerce version, PHP version, what browser and version?
4. Be respectful. This plugin is for free and maintaining it and responding to support requests may take a bit.

== Screenshots ==

1. The plugin interface is one powerfull filter and update form located under `Products` > `Price Updater`

== Changelog ==

= 0.1.0 =

* Initial public release

== Upgrade notice ==

* No actions required