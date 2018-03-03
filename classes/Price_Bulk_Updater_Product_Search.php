<?php

class Price_Bulk_Updater_Product_Search {
    public function __construct($price = null, $sale = null, $search = null, $include_drafts = false, $include_trash = false) {
        $this->price = $price;
        $this->sale = $sale;
        $this->search = $search;
    }

    public function results() {
        global $wpdb;

        $searches = array();

        if (null !== $this->price) {
            array_push($searches, array(
                "sql" => "(m.meta_key = '_regular_price' AND m.meta_value = '%s')",
                "value" => $this->price
            ));
        }
        if (null !== $this->sale) {
            array_push($searches, array(
                "sql" => "(m.meta_key = '_sale_price' AND m.meta_value = '%s')",
                "value" => $this->sale
            ));
        }
        if (null !== $this->search) {
            array_push($searches, array(
                "sql" => "(p.post_title LIKE '%%%s%%')",
                "value" => $this->search
            ));
        }

        $sql = $wpdb->prepare("
                SELECT ID, post_title, meta_value, meta_key FROM wp_posts p 
                LEFT JOIN wp_postmeta m
                ON p.ID = m.post_id
                WHERE p.post_type = 'product' AND p.post_status = 'publish'
                AND (" . implode(' OR ', array_map(function ($item) {
                    return $item['sql'];
                }, $searches)) . ")
                GROUP BY p.ID
            ", 
            array_map(function ($item) {
                return $item['value'];
            }, $searches)
        );
        
        return $wpdb->get_results($sql, ARRAY_A);
    }
}