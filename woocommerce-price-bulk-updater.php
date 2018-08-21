<?php
/**
 * Plugin Name: 	WooCommerce Price Bulk Updater
 * Plugin URI:		http://underscoretype.com
 * Description:		A simple Wordpress WooCommerce plugin that let's you set or replace WooCommerce prices in bulk
 * Version:     	0.0.1
 * WC Tested Up To: 3.2.6
 * Author:      	Underscore
 * Author URI:  	https://underscoretype.com
 * Copyright:   	Copyright 2017-2018 Johannes Neumeier
 */
defined('ABSPATH') or die('Access denied.');

define('PRICE_BULK_UPDATER_NAMESPACE', 'woocommerce-price-bulk-updater');
define('PRICE_BULK_UPDATER_ADMIN_URL_NAME', 'price-bulk-updater');
define('PRICE_BULK_UPDATER_ADMIN_URL', 'edit.php?post_type=product&page=' . PRICE_BULK_UPDATER_ADMIN_URL_NAME);

require_once plugin_dir_path(__FILE__) . 'classes/Price_Bulk_Updater.php';
$updater = new Price_Bulk_Updater();

register_activation_hook(__FILE__, array('Price_Bulk_Updater', 'hook_activate'));