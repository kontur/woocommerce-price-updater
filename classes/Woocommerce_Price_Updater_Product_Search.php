<?php
/**
 * Searching for WooCommerce products matching certian params
 */
class Woocommerce_Price_Updater_Product_Search {
    /**
     * Allowed search params and their default values
     *
     * @var array
     */
    private static $defaults = array(
        'price' => null,
        'regular' => null,
        'sale' => null,
        'search' => null,
        'category' => null,
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
     * @param boolean $require_one_of optional array of keys of which at
     *      least one needs to be present
     * @param boolean $inclusive for deciding if the search is a union
     *      (true, OR query) or a intersection (false, AND query)
     * @return array
     */
    public function results($require_one_of = false, $inclusive = true) {
        global $wpdb;

        // optionally require at least one of the params passed in to be set
        if (!empty($require_one_of) && is_array($require_one_of)) {
            // filter the required keys to contain only those that are set in options and are not the default value
            $required_set = array_filter($require_one_of, function ($required) {
                return isset($this->options[$required]) && $this->options[$required] !== self::$defaults[$required];
            });

            // return empty array if none of the required keys was different from the defaults
            if (0 === sizeof($required_set)) {
                return array();
            }
        }

        $searches = array();

        if (null !== $this->options['price']) {
            array_push($searches, array(
                'sql' => "(price = '%s')",
                'values' => array($this->options['price'])
            ));
        }
        if (null !== $this->options['regular']) {
            array_push($searches, array(
                'sql' => "(regular = '%s')",
                'values' => array($this->options['regular'])
            ));
        }
        if (null !== $this->options['sale']) {
            array_push($searches, array(
                'sql' => "(sale = '%s')",
                'values' => array($this->options['sale'])
            ));
        }
        if (null !== $this->options['search']) {
            array_push($searches, array(
                'sql' => "(p.post_title LIKE '%%%s%%')",
                'values' => array($this->options['search'])
            ));
        }
        if (null !== $this->options['category']) {
            array_push($searches, array(
                'sql' => "(taxonomy = 'product_cat' AND (t.slug LIKE '%%%s%%' OR t.name LIKE '%%%s%%') )",
                'values' => array($this->options['category'], $this->options['category'])
            ));
        }

        $glue = $inclusive ? ' OR ' : ' AND ';

        // make the filter a single OR clause addition
        $filters = empty($searches) ? '1=1' : implode($glue, array_map(function ($item) {
            return $item['sql'];
        }, $searches));

        // this massive query gets a row with the product id, title, status,
        // prices and category names in one go
        // the INNER JOIN on temporary tables is neccesary to be able to group
        // by the post ID while still extracting several joined meta_values
        // the other INNER JOIN are needed to extract the category names

        // TODO test the sub query and join performance on large (1000+) datasets
        $sql = $wpdb->prepare(
            "
                SELECT ID, post_title, post_status,
                GROUP_CONCAT(DISTINCT t.name SEPARATOR ',') AS categories, 
                price, regular, sale
                FROM $wpdb->posts p
                
                INNER JOIN (
                    SELECT post_id, meta_value AS price FROM $wpdb->postmeta WHERE meta_key = '_price'
                ) AS price
                ON price.post_id = p.ID
                
                INNER JOIN (
                    SELECT post_id, meta_value AS regular FROM $wpdb->postmeta WHERE meta_key = '_regular_price'
                ) AS regular_price
                ON price.post_id = regular_price.post_id
                
                INNER JOIN (
                    SELECT post_id, meta_value AS sale FROM $wpdb->postmeta WHERE meta_key = '_sale_price'
                ) AS sale_price	
                ON price.post_id = sale_price.post_id

                INNER JOIN $wpdb->term_relationships rel
                ON rel.object_id = p.ID
                    
                INNER JOIN $wpdb->term_taxonomy tax
                ON tax.term_taxonomy_id = rel.term_taxonomy_id

                INNER JOIN $wpdb->terms t
                ON t.term_id = tax.term_id

                WHERE p.post_type = 'product' 
                AND p.post_status = 'publish'
                AND tax.taxonomy = 'product_cat'
                AND (" . $filters . ')
                
                GROUP BY p.ID
            ',
            // for each filter, get the substitute value to pass to prepare
            // note that some filters may have more than one param, so all are
            // wrapped in array, which array_merge will flatten
            call_user_func_array('array_merge', array_map(function ($item) {
                return $item['values'];
            }, $searches))
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
