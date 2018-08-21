<?php
/**
 * WooCommerce_Price_Updater_Plugin
 *
 * Main plugin class with hooks for rendering and reacting to changes in the
 * admin area
 */
class Woocommerce_Price_Updater_Plugin {
    private static $price_keys = array('_price', '_regular_price', '_sale_price');
    private static $required_search_keys = array('price', 'regular', 'sale', 'search', 'category');

    public function __construct() {
        add_action('plugins_loaded', array($this, 'hook_plugins_loaded'));

        add_action('admin_enqueue_scripts', array($this, 'hook_admin_enqueue_scripts'));
        add_action('admin_init', array($this, 'hook_admin_init'));
        add_action('admin_menu', array($this, 'hook_admin_menu'));

        add_action('wp_ajax_woocommerce_price_updater_match_products', array($this, 'woocommerce_price_updater_match_products'));

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
        update_option(WOOCOMMERCE_WOOCOMMERCE_PRICE_UPDATER_NAMESPACE . '-notice', 'admin_notice_activate');
    }

    /**
     * Admin code being launched
     *
     * @return void
     */
    public function hook_admin_init() {
        if ($notice = get_option(WOOCOMMERCE_WOOCOMMERCE_PRICE_UPDATER_NAMESPACE . '-notice')) {
            add_action('admin_notices', array($this, $notice));
            delete_option(WOOCOMMERCE_WOOCOMMERCE_PRICE_UPDATER_NAMESPACE . '-notice');
        }
    }

    /**
     * Adding plugin scripts and styles
     *
     * @param string $hook
     * @return void
     */
    public function hook_admin_enqueue_scripts($hook) {
        if ($hook !== 'product_page_woocommerce-price-updater') {
            return;
        }
        wp_enqueue_style('woocommerce-price-updater-styles', $this->assets_url . 'woocommerce-price-updater-styles.css');
        wp_enqueue_script('woocommerce-price-updater-script', $this->assets_url . 'woocommerce-price-updater-script.js', array('jquery'), true, true);
        wp_localize_script('woocommerce-price-updater-script', 'woocommerce_price_updater', array('nonce' => wp_create_nonce('woocommerce_price_updater_match_products')));
    }

    /**
     * Ajax handler for how many products will be affected with the current settings
     *
     * @return void
     */
    public function woocommerce_price_updater_match_products() {
        if (check_ajax_referer('woocommerce_price_updater_match_products', 'nonce')) {
            require_once plugin_dir_path(__FILE__) . 'Woocommerce_Price_Updater_Product_Search.php';

            $params = array();
            foreach (Woocommerce_Price_Updater_Product_Search::params() as $param) {
                // the presence is enough for inclusion, meaning searching for
                // an "empty" price relies the param being sent, and vice versa
                // not including "empty" prices requires the param not to be present
                if (in_array($param, array_keys($_POST))) {
                    $params[$param] = trim($_POST[$param]);
                }
            }
            $method = isset($_POST['method']) && $_POST['method'] === 'all' ? false : true;

            $search = new Woocommerce_Price_Updater_Product_Search($params);
            echo json_encode($search->results(self::$required_search_keys, $method));
        }

        wp_die();
    }

    /**
     * After all plugins are loaded check that WooCommerce is available
     *
     * @return void
     */
    public function hook_plugins_loaded() {
        if (!defined('WC_VERSION')) {
            if (is_admin()) {
                add_action('admin_notices', array($this, 'admin_notice_no_woocommerce'));
            }
        }
    }

    /**
     * Add the "Price Updater" submenu to the WooCommerce sidebar menu
     */
    public function hook_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=product',
            __('Price Updater', WOOCOMMERCE_PRICE_UPDATER_NAMESPACE),
            __('Price Updater', WOOCOMMERCE_PRICE_UPDATER_NAMESPACE),
            'manage_options',
            WOOCOMMERCE_PRICE_UPDATER_ADMIN_URL_NAME,
            array($this, 'hook_woocommerce_price_updater_settings_page')
        );
    }

    /**
     * Rendering and handling the admin tab
     */
    public function hook_woocommerce_price_updater_settings_page() {
        // handle posted data
        if (isset($_POST['action']) && $_POST['action'] === 'update') {
            check_admin_referer('woocommerce_price_updater', 'woocommerce_price_updater_options');

            // since the disabled fields do not get submitted, we can use isset to
            // check for what to include
            $match = array();
            foreach (Woocommerce_Price_Updater_Product_Search::params() as $param) {
                if (isset($_POST[$param])) {
                    $match[$param] = $_POST[$param];
                }
            }

            $new = array();
            if (isset($_POST['new_price'])) {
                $new['_price'] = trim($_POST['new_price']);
            }
            if (isset($_POST['new_regular'])) {
                $new['_regular_price'] = trim($_POST['new_regular']);
            }
            if (isset($_POST['new_sale'])) {
                $new['_sale_price'] = trim($_POST['new_sale']);
            }
            $method = isset($_POST['method']) && $_POST['method'] === 'all' ? false : true;

            $this->update_prices($match, $new, $method);
        }

        // display plugin interface
        include $this->templates_dir . 'admin-form.php';
    }

    /**
     * Update all products matching $old price with $new value
     *
     * @param string $old
     * @param string $new
     * @param boolean $method
     * @return boolean false on error or int number of changed prices
     */
    private function update_prices($match, $new, $method) {
        global $wpdb;

        // validate search prices
        foreach (array('price', 'regular', 'sale') as $key) {
            if (isset($match[$key]) && false === $this->validatePriceInput($match[$key])) {
                $this->notice(
                    sprintf(
                        __('Search price "%s" invalid. Allowed are numeric values (9 or 9.99) or leaving empty to match products with no price set. No prices were updated.', WOOCOMMERCE_PRICE_UPDATER_NAMESPACE),
                        $match[$key]
                    ),
                    'error'
                );

                return false;
            }
        }

        // valide new prices
        foreach (self::$price_keys as $key) {
            if (isset($new[$key]) && false === $this->validatePriceInput($new[$key])) {
                $this->notice(
                    sprintf(
                        __('New price "%s" invalid. Allowed are numeric values (9 or 9.99) or leaving empty to set the price to empty. No prices were updated.'),
                        $new[$key]
                    ),
                    'error'
                );

                return false;
            }
        }

        // The actual database update
        // Use the same search as the AJAX matcher to retrieve a list of matched products
        // Then perform an update with a WHERE IN (ids...) clause
        $search = new Woocommerce_Price_Updater_Product_Search($match);
        $result = $search->results(self::$required_search_keys, $method);
        $updated = array();

        if (empty($result)) {
            $this->notice(
                __('No products matched, nothing updated.', WOOCOMMERCE_PRICE_UPDATER_NAMESPACE),
                'error'
            );

            return false;
        }
        $ids = array_map(function ($item) {
            return ($item['ID']);
        }, $result);

        if (!empty($ids) && is_array($ids)) {
            foreach (self::$price_keys as $meta_key) {
                if (isset($new[$meta_key])) {
                    $sql = $wpdb->prepare(
                        "
                        UPDATE $wpdb->postmeta
                        SET meta_value = '%s'
                        WHERE post_id IN (" . implode(',', $ids) . ")
                        AND meta_key = '%s'
                        ",
                        $new[$meta_key],
                        $meta_key
                    );
                    $updated[$meta_key] = $wpdb->query($sql);
                }
            }
        }

        // React to the query result
        if (empty($updated)) {
            $this->notice(
                __('No product prices changed, nothing updated.', WOOCOMMERCE_PRICE_UPDATER_NAMESPACE),
                'warning'
            );

            return false;
        } else {
            foreach (self::$price_keys as $key) {
                if (isset($updated[$key]) && false !== $updated[$key]) {
                    switch ($key) {
                        case '_price':
                            $which = 'current price';
                            break;

                        case '_regular_price':
                            $which = 'regular price';
                            break;

                        case '_sale_price':
                            $which = 'sales price';
                            break;

                        default:
                            $which = 'current price';
                            break;
                    }

                    $this->notice(
                        sprintf(
                            _n(
                                'Updated %d product to new %s ("%s").',
                                'Updated %d products to new %s ("%s").',
                                $updated[$key],
                                WOOCOMMERCE_PRICE_UPDATER_NAMESPACE
                            ),
                            $updated[$key],
                            $which,
                            $new[$key]
                        )
                    );
                }
            }

            return true;
        }
    }

    /**
     * Validate the price inputs. Allowed are strings with float format or empty
     *
     * @param string $input
     * @return boolean
     */
    private static function validatePriceInput($input) {
        return is_string($input) && ($input === '' || preg_match('/[^0-9\.]+/', $input) !== 1);
    }

    /**
     * Callback to display plugin activation notice
     *
     * @return void
     */
    public static function admin_notice_activate() {
        $this->notice(__('WooCommerce Price Updater is now activated and available under the WooCommerce Products sidebar menu.', WOOCOMMERCE_PRICE_UPDATER_NAMESPACE));
    }

    /**
     * Callback to display warning when no WooCommerce is detected
     *
     * @return void
     */
    public static function admin_notice_no_woocommerce() {
        $this->notice(
            __('WooCommerce Price Updater is activated but WooCommerce does not seem to be installed and activated.', WOOCOMMERCE_PRICE_UPDATER_NAMESPACE),
            'warning'
        );
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
