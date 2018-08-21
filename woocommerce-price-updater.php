<?php
/**
 * Plugin Name: 	Woo Price Updater
 * Plugin URI:		https://wordpress.org/plugins/woo-price-updater
 * Description:		A simple Wordpress WooCommerce plugin to set or replace WooCommerce prices in bulk
 * Version:     	0.1.0
 * WC Tested Up To: 3.4.4
 * Author:      	Underscore
 * Author URI:  	https://underscoretype.com
 * Copyright:   	Copyright 2018 Johannes 'kontur' Neumeier
 */
defined('ABSPATH') or die('Access denied.');

define('WOOCOMMERCE_PRICE_UPDATER_NAMESPACE', 'woo-price-updater');
define('WOOCOMMERCE_PRICE_UPDATER_ADMIN_URL_NAME', 'woo-price-updater');
define('WOOCOMMERCE_PRICE_UPDATER_ADMIN_URL', 'edit.php?post_type=product&page=' . WOOCOMMERCE_PRICE_UPDATER_ADMIN_URL_NAME);

require_once plugin_dir_path(__FILE__) . 'classes/Woocommerce_Price_Updater_Plugin.php';
require_once plugin_dir_path(__FILE__) . 'classes/Woocommerce_Price_Updater_Product_Search.php';
$updater = new Woocommerce_Price_Updater_Plugin();

register_activation_hook(__FILE__, array('Woocommerce_Price_Updater_Plugin', 'hook_activate'));
