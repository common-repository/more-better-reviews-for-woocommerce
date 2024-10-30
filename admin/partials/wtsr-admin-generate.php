<?php
/**
 * @var $wtsr_email_send_via
 * @var $is_woocommerce_reviews_disabled_globally
 */
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$product_review_request_bg = $wpdb->get_results( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE '%wp_wtsr_review_request_batch%'", ARRAY_A );
$is_ts_review_request_enabled = '';

if (!empty($product_review_request_bg)) {
    $product_review_request_bg_count = count($product_review_request_bg);
} else {
    $is_ts_review_request_enabled = get_option('wtsr_ts_review_request_enabled');
}

$order_statuses = wc_get_order_statuses();

$filter_number = !empty($_GET['wtsr_filter_number']) && in_array(sanitize_text_field($_GET['wtsr_filter_number']), array('50', '100', '200', '500', 'all')) ? sanitize_text_field($_GET['wtsr_filter_number']) : '50';
$selected_order_status = !empty($_GET['wtsr_filter_status']) ? sanitize_text_field($_GET['wtsr_filter_status']) : '';
$start_order_date = !empty($_GET['wtsr_start_order_date']) ? sanitize_text_field($_GET['wtsr_start_order_date']) : date('Y-m-d', (time() - (7 * 24 * 60 * 60)));
$end_order_date = !empty($_GET['wtsr_end_order_date']) ? sanitize_text_field($_GET['wtsr_end_order_date']) : date('Y-m-d', time());

if (empty($selected_order_status)) {
    $selected_order_status = get_option('wtsr_order_status', 'wc-completed');
}

if ('wc-order-created' === $selected_order_status) {
    $selected_order_status = 'wc-completed';
}

$end_order_date_label = strtotime($end_order_date) + (24 * 60 * 60);
$start_order_date_mysql = $start_order_date;
$end_order_date_mysql = date('Y-m-d', $end_order_date_label);
$mysql_orders = ReviewsModel::get_filtered_orders_for_generation($selected_order_status, $start_order_date_mysql, $end_order_date_mysql);


$reviews = ReviewsModel::get_all_by('order_id');
$orders_count = 0;
$orders_limit = 'all' === $filter_number ? 999999999 : (int) $filter_number;
$total_orders_ids = array();

ob_start();
$limit_allowed = true;
$license_version = Wtsr_License::get_license_version();

if ('free' === Wtsr_License::get_license_version()) {
    $get_limit_allowed = TSManager::get_limit_allowed();

    if (empty($get_limit_allowed)) {
        $limit_allowed = false;
    }
}

if (!empty($orders) || !empty($mysql_orders)) {
    $emails_array = array();
    $selected_review_ask = get_option('wtsr_review_ask', 'no');
    $reviews_not_allowed = 0;
    $user_limit = get_option('wtsr_limit_users') * Wtsr_License::get_license_multi();
    $days_limit = get_option('wtsr_limit_days');
    $user_count = get_option('wtsr_limit_count', 0);
    $user_count_timeout = get_option('wtsr_limit_count_timeout', 0);
    $timeout_label = '';

    if (!empty($user_count_timeout)) {
        $user_count_timeout_left = $user_count_timeout - time();

        if ($user_count_timeout_left > 86400) {
            $timeout_left = ceil($user_count_timeout_left / 86400);
            $timeout_label = $timeout_left . ' ' . __(' days', 'more-better-reviews-for-woocommerce');
        } else {
            $timeout_left = ceil($user_count_timeout_left / 3600);
            $timeout_label = $timeout_left . ' ' . __(' hours', 'more-better-reviews-for-woocommerce');
        }
    }

    $user_left = (int) $user_limit - (int) $user_count;
    $blocked_emails = array();

    include_once dirname(__FILE__) . '/wtsr-admin-generate-order-reviews-table-list.php';
}

$orders_list = ob_get_clean();
?>

<div class="wtsr-page-header with-btn-holder">
    <h3><?php _e('Generate requests', 'more-better-reviews-for-woocommerce'); ?></h3>

    <div class="buttons-holder">
        <span style="display:inline-block;line-height:28px;margin:0 5px 0 0;">
            <strong><?php _e( 'Next step', 'more-better-reviews-for-woocommerce' ) ?>:</strong>
        </span>

        <a href="?page=wp2leads-wtsr&tab=reviews" class="button button-success">
            <?php _e('View generated requests', 'more-better-reviews-for-woocommerce') ?>
        </a>
    </div>
</div>

<?php
if (!empty($product_review_request_bg)) {
    ?>
    <div class="notice notice-warning inline">
        <p>
            <?php _e("You can't generate reviews requests for existed orders right now, as far as some requests are running in background." , 'more-better-reviews-for-woocommerce') ?>
        </p>
    </div>
    <?php
}
?>

<div style="margin-bottom:15px;">
    <?php
    if ('woocommerce' !== $wtsr_email_send_via) {
        ?>
        <?php _e("Enable auto generate and transfer review requests" , 'more-better-reviews-for-woocommerce') ?>:
        <?php
    } else {
        ?>
        <?php _e("Enable auto generate and email review requests" , 'more-better-reviews-for-woocommerce') ?>:
        <?php
    }
    ?>
    <select name="wtsr_ts_enable_review_request" id="wtsr_ts_enable_review_request" class="form-input" style="max-width:120px;display:inline-block;margin-left:10px;">
        <option value="yes"<?php echo $is_ts_review_request_enabled ? ' selected' : ''; ?>><?php _e('Enabled', 'more-better-reviews-for-woocommerce'); ?></option>
        <option value="no"<?php echo !$is_ts_review_request_enabled ? ' selected' : ''; ?>><?php _e('Disabled', 'more-better-reviews-for-woocommerce'); ?></option>
    </select>

    <button id="wtsr_ts_enable_review_request_btn" type="button" class="button button-primary" style="height:34px;line-height:32px;">
        <?php _e('Save', 'more-better-reviews-for-woocommerce'); ?>
    </button>
</div>

<div id="reviews_actions" style="margin-bottom:15px;">
    <p>
        <strong><?php _e('Generate review requests for existed orders', 'more-better-reviews-for-woocommerce'); ?></strong>
        (<?php echo $orders_count . ' ' . __('orders available', 'more-better-reviews-for-woocommerce'); ?>)
    </p>
    <p>
        <?php
        echo "<pre>";
        // var_dump($blocked_emails);
        echo "</pre>";
        ?>
    </p>
    <form method="get">
        <div id="filter_actions" style="margin-bottom:10px;">
            <input type="hidden" name="page" value="wp2leads-wtsr">
            <input type="hidden" name="tab" value="generate">

            <?php
            if (!empty($order_statuses)) {
                include_once dirname(__FILE__) . '/blocks/generate-actions.php';
                if (!empty($orders_count)) {
                    ?>
                    <button
                            id="generate_selected_reviews"
                            class="button button-primary"
                            type="button"
                            data-warningmsg="<?php _e('Are you sure you want to generate requests for selected orders', 'more-better-reviews-for-woocommerce'); ?>"
                            data-notselectedmsg="<?php _e('Select at least one order', 'more-better-reviews-for-woocommerce'); ?>"
                            style="height:34px;line-height:32px;"
                        <?php echo !empty($product_review_request_bg) || !$limit_allowed ? ' disabled' : ''; ?>
                    >
                        <?php _e('Generate for selected', 'more-better-reviews-for-woocommerce'); ?>
                    </button>

                    <button
                            id="generate_all_selected_reviews"
                            class="button button-primary"
                            type="button"
                            data-warningmsg="<?php _e('Are you sure you want to generate requests for all orders', 'more-better-reviews-for-woocommerce'); ?>"
                            data-notselectedmsg="<?php _e('Select at least one order', 'more-better-reviews-for-woocommerce'); ?>"
                            style="height:34px;line-height:32px;"
                        <?php echo !empty($product_review_request_bg) || !$limit_allowed ? ' disabled' : ''; ?>
                    >
                        <?php
                        if (count($total_orders_ids) < $orders_count) {
                            $string = __( 'Generate reviews for first %s orders', 'more-better-reviews-for-woocommerce' );
                        } else {
                            $string = __( 'Generate reviews for all %s orders', 'more-better-reviews-for-woocommerce' );
                        }

                        echo sprintf($string, count($total_orders_ids));
                        ?>
                    </button>
                    <input type="hidden" id="generate_all_selected_reviews_input" value="<?php echo json_encode($total_orders_ids) ?>">
                    <?php
                }
            }
            ?>

        </div>
    </form>
</div>

<?php include_once dirname(__FILE__) . '/wtsr-admin-generate-order-reviews-table.php'; ?>

<div class="wtsr-page-footer">

</div>
