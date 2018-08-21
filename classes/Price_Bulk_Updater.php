<?php

class Price_Bulk_Updater {
    public function __construct() {
        add_action('plugins_loaded', array($this, 'hook_plugins_loaded'));

        add_action('admin_enqueue_scripts', array($this, 'hook_admin_enqueue_scripts'));
        add_action('admin_init', array($this, 'hook_admin_init'));
        add_action('admin_menu', array($this, 'hook_admin_menu'));

        $this->templates_dir = plugin_dir_path(__FILE__) . '../templates/';
        $this->assets_url = plugins_url('assets/', dirname(__FILE__));

        include_once(plugin_dir_path(__FILE__) . '../classes/Price_Bulk_Updater_Message.php');
    }

    public static function hook_activate() {
        // In the options store a message function to call on the next admin init
        update_option(PRICE_BULK_UPDATER_NAMESPACE . '-notice', 'admin_notice_activate');
    }

    public function hook_admin_init() {
        if ($notice = get_option(PRICE_BULK_UPDATER_NAMESPACE . '-notice')) {
            add_action('admin_notices', array($this, $notice));
            delete_option(PRICE_BULK_UPDATER_NAMESPACE . '-notice');
        }
    }

    public function hook_admin_enqueue_scripts($hook) {
        if ($hook !== 'product_page_price-bulk-updater') {
            return;
        }
        wp_enqueue_style('price-bulk-updater-styles', $this->assets_url . 'price-bulk-updater-styles.css');
    }

    public function hook_plugins_loaded() {
        if (!defined('WC_VERSION')) {
            // no woocommerce :(
            if (is_admin()) {
                // TODO show warning
            }
        }
    }

    /**
     * Add the "Price Bulk Updater" submenu to the WooCommerce sidebar menu
     */
    public function hook_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=product',
            __('Price Bulk Updater', PRICE_BULK_UPDATER_NAMESPACE),
            __('Price Bulk Updater', PRICE_BULK_UPDATER_NAMESPACE),
            'manage_options',
            PRICE_BULK_UPDATER_ADMIN_URL_NAME,
            array($this, 'hook_price_bulk_updater_settings_page')
        );
    }

    /**
     * Rendering and handling the admin tab
     */
    public function hook_price_bulk_updater_settings_page() {
        if (isset($_POST['action']) && $_POST['action'] === 'update') {
            check_admin_referer('price_bulk_updater', 'price_bulk_updater_options');

            $old = trim($_POST['old']);
            $new = trim($_POST['new']);

            // gather what to update
            $updater = new Price_Bulk_Updater();
            $updater->update_prices($old, $new);
        }

        include $this->templates_dir . 'admin-form.php';
    }

    public function update_prices($old, $new) {
        global $wpdb;

        if ($old === $new) {
            $this->notice(
                __('Old and new price are the same, nothing updated', PRICE_BULK_UPDATER_NAMESPACE),
                'error'
            );
            return;
        }

        $updated = $wpdb->update($wpdb->prefix . 'postmeta', 
            array("meta_value" => $new), 
            array("meta_value" => $old, "meta_key" => "_regular_price" )
        );

        if (!$updated) {
            $this->notice(
                __('Old price did not match any products, nothing updated', PRICE_BULK_UPDATER_NAMESPACE),
                'error'
            );
        } else {
            $this->notice(
                sprintf( __('Updated %d product(s) from old price (%s) to new price (%s)', PRICE_BULK_UPDATER_NAMESPACE),
                $updated, $old, $new
            ));
        }
    }

    public static function admin_notice_activate() {
        $this->notice(__('WooCommerce Price Bulk Updater is now activated and available under the Products sidebar menu.', PRICE_BULK_UPDATER_NAMESPACE));
    }

    public static function notice($message, $type = "success", $dismissible = true) {
        echo '<div class="notice notice-' . $type . ' ' . ( $dismissible ? 'is-dismissible' : '' ) . '">
            <p>' . $message . '</p>
        </div>';        
    }
}