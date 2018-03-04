<?php
/**
 * Searching for WooCommerce products matching certian params
 */
class Price_Bulk_Updater_Product_Search {

    /**
     * Allowed search params and their default values
     *
     * @var array
     */
    private static $defaults = array(
        'price' => null,
        'sale' => null,
        'search' => null,
        'include_drafts' => false,
        'include_trash' => false
    );

    /**
     * Array holding provided search params
     *
     * @var array
     */
    private $options = array();

    /**
     * Create a new Search unit, optionally pass in search params
     *
     * @param array $params
     */
    public function __construct($params = null) {
        $this->match($params);
    }

    /**
     * Store the passed in params for the next search
     *
     * @param array $params
     * @return void
     */
    public function match($params = null) {
        if (is_array($params) && !empty($params)) {
            $params = $this->filter_params($params);
            $this->options = array_merge(self::$defaults, $params);
        }
    }

    /**
     * Return products matching the current search params
     *
     * @return array
     */
    public function results() {
        global $wpdb;

        $searches = array();

        if (null !== $this->options['price']) {
            array_push($searches, array(
                'sql' => "(m.meta_key = '_regular_price' AND m.meta_value = '%s')",
                'value' => $this->options['price']
            ));
        }
        if (null !== $this->options['sale']) {
            array_push($searches, array(
                'sql' => "(m.meta_key = '_sale_price' AND m.meta_value = '%s')",
                'value' => $this->options['sale']
            ));
        }
        if (null !== $this->options['search']) {
            array_push($searches, array(
                'sql' => "(p.post_title LIKE '%%%s%%')",
                'value' => $this->options['search']
            ));
        }

        $filters = empty($searches) ? '1=1' : implode(' OR ', array_map(function ($item) {
            return $item['sql'];
        }, $searches));

        $sql = $wpdb->prepare(
            "
                SELECT ID, post_title, meta_value, meta_key FROM wp_posts p 
                LEFT JOIN wp_postmeta m
                ON p.ID = m.post_id
                WHERE p.post_type = 'product' AND p.post_status = 'publish'
                AND (" . $filters . ')
                GROUP BY p.ID
            ',
            array_map(function ($item) {
                return $item['value'];
            }, $searches)
        );

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Filter any passed in search params to contain only valid search items
     *
     * @param array $params
     * @return array
     */
    private static function filter_params($params = null) {
        if (!is_array($params)) {
            return array();
        }

        return array_intersect_key($params, self::$defaults);
    }

    /**
     * Expose the allowed search params (keys of the defaults array)
     *
     * @return array
     */
    public static function params() {
        return array_keys(self::$defaults);
    }
}
