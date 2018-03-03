<?php
/**
 * Price_Bulk_Updater
 *
 * Main plugin class with hooks for rendering and reacting to changes in the
 * admin area
 */
class Price_Bulk_Updater {
    public function __construct() {
        add_action('plugins_loaded', array($this, 'hook_plugins_loaded'));

        add_action('admin_enqueue_scripts', array($this, 'hook_admin_enqueue_scripts'));
        add_action('admin_init', array($this, 'hook_admin_init'));
        add_action('admin_menu', array($this, 'hook_admin_menu'));

        $this->templates_dir = plugin_dir_path(__FILE__) . '../templates/';
        $this->assets_url = plugins_url('assets/', dirname(__FILE__));
    }

    /**
     * Plugin activation hook
     *
     * @return void
     */
    public static function hook_activate() {
        // In the options store a message function to call on the next admin init
        update_option(PRICE_BULK_UPDATER_NAMESPACE . '-notice', 'admin_notice_activate');
    }

    /**
     * Admin code being launched
     *
     * @return void
     */
    public function hook_admin_init() {
        if ($notice = get_option(PRICE_BULK_UPDATER_NAMESPACE . '-notice')) {
            add_action('admin_notices', array($this, $notice));
            delete_option(PRICE_BULK_UPDATER_NAMESPACE . '-notice');
        }
    }

    /**
     * Adding plugin scripts and styles
     *
     * @param string $hook
     * @return void
     */
    public function hook_admin_enqueue_scripts($hook) {
        if ($hook !== 'product_page_price-bulk-updater') {
            return;
        }
        wp_enqueue_style('price-bulk-updater-styles', $this->assets_url . 'price-bulk-updater-styles.css');
        wp_enqueue_script('price-bulk-updater-script', $this->assets_url . 'price-bulk-updater-script.js', array('jquery'), true, true);
    }

    /**
     * After all plugins are loaded check that WooCommerce is available
     *
     * @return void
     */
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
        // handle posted data
        if (isset($_POST['action']) && $_POST['action'] === 'update') {
            check_admin_referer('price_bulk_updater', 'price_bulk_updater_options');

            $old = trim($_POST['old']);
            $new = trim($_POST['new']);

            // gather what to update
            $updater = new Price_Bulk_Updater();
            $updater->update_prices($old, $new);
        }

        // display plugin interface
        include $this->templates_dir . 'admin-form.php';
    }

    /**
     * Update all products matching $old price with $new value
     *
     * @param string $old
     * @param string $new
     * @return boolean false on error or int number of changed prices
     */
    private function update_prices($old, $new) {
        global $wpdb;

        // Some sanity checks first, return early if input is invalid
        if ($old === $new) {
            $this->notice(
                __('Old and new price are the same, nothing updated', PRICE_BULK_UPDATER_NAMESPACE),
                'error'
            );

            return false;
        }

        if (false === $this->validatePriceInput($old)) {
            $this->notice(
                sprintf(
                    __('Old price "%s" invalid. Allowed are numeric values (9 or 9.99) or leaving empty to match products with no price set. No prices were updated.', PRICE_BULK_UPDATER_NAMESPACE),
                    $old
                ),
                'error'
            );

            return false;
        }

        if (false === $this->validatePriceInput($new)) {
            $this->notice(
                __('New price invalid. Allowed are numeric values (9 or 9.99) or leaving empty to set the price to empty. No prices were updated.'),
                'error'
            );

            return false;
        }

        // The actual database update
        $updated = $wpdb->update(
            $wpdb->prefix . 'postmeta',
            array('meta_value' => $new),
            array('meta_value' => $old, 'meta_key' => '_regular_price')
        );

        // React to the query result
        if (!$updated) {
            $this->notice(
                __('Old price did not match any products, nothing updated.', PRICE_BULK_UPDATER_NAMESPACE),
                'error'
            );

            return false;
        } else {
            $this->notice(
                sprintf(
                    __('Updated %d product(s) from old price (%s) to new price (%s).', PRICE_BULK_UPDATER_NAMESPACE),
                    $updated,
                    $old,
                    $new
                )
            );

            return $updated;
        }
    }

    /**
     * Validate the price inputs. Allowed are strings with float format or empty
     *
     * @param string $input
     * @return boolean
     */
    private static function validatePriceInput($input) {
        return $input === '' || preg_match('/[^0-9\.]+/', $input) !== 1;
    }

    /**
     * Callback to display plugin activation notice
     *
     * @return void
     */
    private static function admin_notice_activate() {
        $this->notice(__('WooCommerce Price Bulk Updater is now activated and available under the Products sidebar menu.', PRICE_BULK_UPDATER_NAMESPACE));
    }

    /**
     * General helper to output a Wordpress style notice
     *
     * @param string $message
     * @param string $type of 'success' or 'error'
     * @param boolean $dismissible
     * @return void
     */
    private static function notice($message, $type = 'success', $dismissible = true) {
        echo '<div class="notice notice-' . $type . ' ' . ($dismissible ? 'is-dismissible' : '') . '">
            <p>' . $message . '</p>
        </div>';
    }
}
