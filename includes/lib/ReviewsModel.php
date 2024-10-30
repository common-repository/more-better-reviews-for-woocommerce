<?php
/**
 * DB Reviews Model
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/includes/lib
 */

class ReviewsModel {
    /**
     * Table name in the database
     *
     * @var string
     */
    private static $table_name = 'wp2lwtsr_reviews';
    private static $table_meta_name = 'wp2lwtsr_reviewmeta';

    private static function get_schema() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_name;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            $collate = $wpdb->get_charset_collate();
        }

        $sql = "CREATE TABLE {$table_name} (
  id BIGINT UNSIGNED NOT NULL auto_increment,
  order_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  email varchar(100) NOT NULL,
  status varchar(15) NOT NULL,
  review_link text NOT NULL,
  review_message mediumtext NOT NULL,
  review_created datetime NOT NULL default '0000-00-00 00:00:00',
  review_sent datetime NULL default null,
  PRIMARY KEY  (id),
  UNIQUE KEY order_email (order_id,email)
) $collate;";

        return $sql;
    }

    private static function get_meta_schema() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_meta_name;
        $max_index_length = 191;
        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            $collate = $wpdb->get_charset_collate();
        }

        $sql = "
CREATE TABLE {$table_name} (
    meta_id bigint(20) unsigned NOT NULL auto_increment,
	wp2lwtsr_review_id bigint(20) unsigned NOT NULL default '0',
	meta_key varchar(255) default NULL,
	meta_value longtext,
	PRIMARY KEY  (meta_id),
	KEY review_id (wp2lwtsr_review_id),
	KEY meta_key (meta_key($max_index_length))
) {$collate};
        ";

        return $sql;
    }

    public static function get($id, $return_type = 'OBJECT') {
        global $wpdb;

        $table = $wpdb->prefix . self::$table_name;

        $sql = "SELECT * FROM {$table} WHERE id = '".$id."' ORDER BY id DESC LIMIT 1";

        return $wpdb->get_results( $sql, $return_type );
    }

    public static function get_by_id($id) {
        global $wpdb;

        $table = $wpdb->prefix . self::$table_name;

        $sql = "SELECT * FROM {$table} WHERE id = '".$id."' ORDER BY id DESC LIMIT 1";

        return $wpdb->get_row( $sql, ARRAY_A );
    }

    public static function get_by($field, $value, $single = true, $return_type = 'ARRAY_A') {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;
        $meta_table = $wpdb->prefix . self::$table_meta_name;
        $sql = "SELECT * FROM {$table} WHERE {$field} = '{$value}' ORDER BY id DESC";

        if ($single) {
            $result = $wpdb->get_row( $sql, ARRAY_A );

            if (!empty($result)) {
                $meta_sql = "SELECT * FROM {$meta_table} WHERE wp2lwtsr_review_id = '{$result['id']}'";
                $meta_result = $wpdb->get_results( $meta_sql, ARRAY_A );
                $result['review_meta'] = array();

                if (!empty($meta_result)) {
                    foreach ($meta_result as $single_meta) {
                        $result['review_meta'][$single_meta['meta_key']][] = $single_meta['meta_value'];
                    }
                }
            }

            return $result;
        } else {
            $results = $wpdb->get_results( $sql, ARRAY_A );

            if (!empty($results)) {
                foreach ($results as $res_i => $result) {
                    if (!empty($result)) {
                        $meta_sql = "SELECT * FROM {$meta_table} WHERE wp2lwtsr_review_id = '{$result['id']}'";
                        $meta_result = $wpdb->get_results( $meta_sql, ARRAY_A );
                        $results[$res_i]['review_meta'] = array();

                        if (!empty($meta_result)) {
                            foreach ($meta_result as $single_meta) {
                                $results[$res_i]['review_meta'][$single_meta['meta_key']][] = $single_meta['meta_value'];
                            }
                        }
                    }
                }
            }

            return $results;
        }
    }

    public static function get_by_order_id($order_id) {
        global $wpdb;

        $table = $wpdb->prefix . self::$table_name;

        $sql = "SELECT * FROM {$table} WHERE order_id = '".$order_id."' ORDER BY id DESC LIMIT 1";

        return $wpdb->get_results( $sql );
    }

    public static function get_all($order = 'DESC') {
        global $wpdb;

        $table = $wpdb->prefix . self::$table_name;

        $sql = "SELECT 	id, order_id, email, status, review_created, review_sent FROM {$table} ORDER BY id " . $order;

        return $wpdb->get_results( $sql );
    }

    public static function get_with_template($order = 'DESC', $args = array(), $select = '*') {
        global $wpdb;

        $table = $wpdb->prefix . self::$table_name;

        $sql = "SELECT {$select} FROM {$table} WHERE status NOT IN ('pending')";

        if (!empty($args['created_from']) && !empty($args['created_till'])) {
            $sql .= " AND ( ( review_created >= '{$args['created_from']}' AND review_created <= '{$args['created_till']}' ) )";
        } else if (!empty($args['created_from'])) {
            $sql .= " AND review_created >= '{$args['created_from']}'";
        } else if (!empty($args['created_till'])) {
            $sql .= " AND review_created <= '{$args['created_till']}'";
        }

        $sql .= " ORDER BY id " . $order;

        return $wpdb->get_results( $sql );
    }

    public static function get_all_by($group_by = false) {
        global $wpdb;

        $table = $wpdb->prefix . self::$table_name;

        $sql = "SELECT id, order_id, email, status, review_created, review_sent FROM {$table} ORDER BY id DESC";

        $result = $wpdb->get_results( $sql, ARRAY_A );

        if (!empty($result) && !empty($group_by) && in_array($group_by, array('id', 'order_id', 'email'))) {
            $temp_result = array();

            foreach ($result as $item) {
                $temp_result[$item[$group_by]] = $item;
            }

            $result = $temp_result;
        }

        return $result;
    }

    public static function get_last_by_email($email, $type = ARRAY_A, $select = '*') {
        global $wpdb;

        $table = $wpdb->prefix . self::$table_name;

        $sql = "SELECT {$select} FROM {$table} WHERE email = '".$email."' ORDER BY id DESC LIMIT 1";

        return $wpdb->get_row( $sql, $type );
    }

    public static function get_not_reviewed($group_by = false) {
        global $wpdb;

        $table = $wpdb->prefix . self::$table_name;

        $sql = "SELECT * FROM {$table} WHERE status IN ('transferred', 'transfered', 'woo-sent') ORDER BY id DESC";

        $result = $wpdb->get_results( $sql, ARRAY_A);

        if (!empty($result) && !empty($group_by) && in_array($group_by, array('id', 'order_id', 'email'))) {
            $temp_result = array();

            foreach ($result as $item) {
                $temp_result[$item[$group_by]] = $item;
            }

            $result = $temp_result;
        }

        return $result;
    }

    public static function get_sent_not_reviewed($group_by = false, $type = ARRAY_A, $select = '*') {
        global $wpdb;

        $table = $wpdb->prefix . self::$table_name;

        $sql = "SELECT {$select} FROM {$table} WHERE status IN ('transferred', 'transfered', 'woo-sent') ORDER BY id DESC";

        $result = $wpdb->get_results( $sql, $type);

        if (!empty($result) && !empty($group_by) && in_array($group_by, array('id', 'order_id', 'email'))) {
            $temp_result = array();

            foreach ($result as $item) {
                $temp_result[$item[$group_by]] = $item;
            }

            $result = $temp_result;
        }

        return $result;
    }

    public static function get_finished($group_by = false) {
        global $wpdb;

        $table = $wpdb->prefix . self::$table_name;

        $sql = "SELECT * FROM {$table} WHERE status IN ('outdated', 'reviewed') ORDER BY id DESC";

        $result = $wpdb->get_results( $sql, ARRAY_A);

        if (!empty($result) && !empty($group_by) && in_array($group_by, array('id', 'order_id', 'email'))) {
            $temp_result = array();

            foreach ($result as $item) {
                $temp_result[$item[$group_by]] = $item;
            }

            $result = $temp_result;
        }

        return $result;
    }

    public static function create($data) {
        global $wpdb;

        $table = $wpdb->prefix . self::$table_name;

        $wpdb->insert( $table, $data );

        $id = (int) $wpdb->insert_id;

        return $id;
    }

    public static function update($data) {
        global $wpdb;
        $id = $data['id'];
        unset ($data['id']);

        $table = $wpdb->prefix . self::$table_name;

        $result = $wpdb->update( $table,
            $data,
            array( 'id' => $id )
        );

        return $id;
    }

    public static function update_meta($review_id, $meta_key, $meta_value) {
        global $wpdb;
        $wpdb->wp2lwtsr_reviewmeta = $wpdb->prefix.'wp2lwtsr_reviewmeta';

        return update_metadata( 'wp2lwtsr_review', $review_id, $meta_key, $meta_value );
    }

    public static function delete_meta($review_id, $meta_key, $meta_value = '', $delete_all = true) {
        global $wpdb;
        $wpdb->wp2lwtsr_reviewmeta = $wpdb->prefix.'wp2lwtsr_reviewmeta';

        return delete_metadata( 'wp2lwtsr_review', $review_id, $meta_key, $meta_value, $delete_all );
    }

    public static function get_meta($review_id, $meta_key, $single = true) {
        global $wpdb;
        $wpdb->wp2lwtsr_reviewmeta = $wpdb->prefix.'wp2lwtsr_reviewmeta';

        return get_metadata( 'wp2lwtsr_review', $review_id, $meta_key, $single );
    }

    public static function delete($id) {
        global $wpdb;

        $table = $wpdb->prefix . self::$table_name;
        $table_meta = $wpdb->prefix . self::$table_meta_name;

        $wpdb->delete( $table_meta, array( 'wp2lwtsr_review_id' => $id ) );

        return $wpdb->delete( $table, array( 'id' => $id ) );
    }

    public static function deleteAll() {
        global $wpdb;

        $table = $wpdb->prefix . self::$table_name;

        $sql = "DELETE FROM {$table} WHERE 1=1";

        return $wpdb->query($sql);
    }

    public static function create_table() {
        global $wpdb;

        $wpdb->hide_errors();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta( self::get_schema() );
    }

    public static function create_meta_table() {
        global $wpdb;

        $wpdb->hide_errors();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta( self::get_meta_schema() );
    }

    public static function get_rating_links_options() {
        $array = array(
            'wtsr_ts_review_link' => __('Trusted Shops review link', 'more-better-reviews-for-woocommerce'),
            'wtsr_product_url' => __('Product page link', 'more-better-reviews-for-woocommerce'),
            'wtsr_custom_link' => __('Custom review link', 'more-better-reviews-for-woocommerce'),
        );

        $array['wtsr_all_reviews_page'] = __('All products review page link', 'more-better-reviews-for-woocommerce');

        return $array;
    }

    public static function get_default_rating_links() {
        $wtsr_all_reviews_page = get_option('wtsr_all_reviews_page', false);

        if ($wtsr_all_reviews_page) {
            $is_page_exists = get_post($wtsr_all_reviews_page);

            if (empty($is_page_exists)) {
                delete_option('wtsr_all_reviews_page');
                $wtsr_all_reviews_page = false;
            }
        }

        return array(
            'one_star' => 'wtsr_all_reviews_page',
            'two_star' => 'wtsr_all_reviews_page',
            'three_star' => 'wtsr_all_reviews_page',
            'four_star' => 'wtsr_all_reviews_page',
            'five_star' => 'wtsr_all_reviews_page',
            'custom_link' => array (
                'one_star' => '',
                'two_star' => '',
                'three_star' => '',
                'four_star' => '',
                'five_star' => '',
            ),
        );
    }

    public static function get_filtered_orders_for_generation($order_status, $date_from, $date_till) {
        global $wpdb;

        $sql = "SELECT p.ID, p.post_date, p.post_date_gmt, p.post_status, pm.meta_key, pm.meta_value, r.id
FROM {$wpdb->posts} AS p
LEFT JOIN {$wpdb->postmeta} AS pm
ON p.ID = pm.post_id
LEFT JOIN {$wpdb->prefix}wp2lwtsr_reviews AS r
ON p.ID = r.order_id
WHERE p.post_type = 'shop_order'
AND p.post_status = '{$order_status}'
AND p.post_date BETWEEN '{$date_from}' AND '{$date_till}'
AND (
    pm.meta_key = '_billing_first_name' OR
    pm.meta_key = '_billing_last_name' OR
    pm.meta_key = '_billing_company' OR
    pm.meta_key = '_created_via' OR
    pm.meta_key = '_billing_email'
)
AND r.id IS NULL
ORDER BY p.ID DESC";

        $mysql_result = $wpdb->get_results($sql, ARRAY_A);
        $mysql_orders = array();

        foreach ($mysql_result as $item) {
            if (empty($mysql_orders[$item['ID']]['post_date']))
                $mysql_orders[$item['ID']]['post_date'] = $item['post_date'];

            if (empty($mysql_orders[$item['ID']]['post_date_gmt']))
                $mysql_orders[$item['ID']]['post_date_gmt'] = $item['post_date_gmt'];

            if (empty($mysql_orders[$item['ID']]['post_status']))
                $mysql_orders[$item['ID']]['post_status'] = $item['post_status'];

            if (!empty($item['meta_key']) && isset($item['meta_value'])) {
                if ('_billing_email' === $item['meta_key'])
                    $mysql_orders[$item['ID']][$item['meta_key']] = TSManager::is_email_valid($item['meta_value']) ? $item['meta_value'] : '';
                else
                    $mysql_orders[$item['ID']][$item['meta_key']] = $item['meta_value'];
            }
        }

        return $mysql_orders;
    }
}
