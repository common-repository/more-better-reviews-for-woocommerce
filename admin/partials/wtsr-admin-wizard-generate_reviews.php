<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;

$order_statuses = wc_get_order_statuses();
$filter_emails = get_option('wtsr_filter_email_domain', '');

$selected_order_status = !empty($_GET['wtsr_filter_status']) ? trim($_GET['wtsr_filter_status']) : '';
$filter_number = !empty($_GET['wtsr_filter_number']) && in_array(sanitize_text_field($_GET['wtsr_filter_number']), array('50', '100', '200', '500', 'all')) ? sanitize_text_field($_GET['wtsr_filter_number']) : '50';
$start_order_date = !empty($_GET['wtsr_start_order_date']) ? trim($_GET['wtsr_start_order_date']) : date('Y-m-d', (time() - (7 * 24 * 60 * 60)));
$end_order_date = !empty($_GET['wtsr_end_order_date']) ? trim($_GET['wtsr_end_order_date']) : date('Y-m-d', time());

if (empty($selected_order_status)) {
    $selected_order_status = get_option('wtsr_order_status', 'wc-completed');
}

if ('wc-order-created' === $selected_order_status) {
    $selected_order_status = 'wc-completed';
}

$post_status = str_replace('wc-', '', $selected_order_status);

$start_order_date_label = strtotime($start_order_date);
$end_order_date_label = strtotime($end_order_date) + (24 * 60 * 60);
$start_order_date_mysql = $start_order_date;
$end_order_date_mysql = date('Y-m-d', $end_order_date_label);
$mysql_orders = ReviewsModel::get_filtered_orders_for_generation($selected_order_status, $start_order_date_mysql, $end_order_date_mysql);

$license_info = Wtsr_License::get_lecense_info();
$activation_in_progress = get_transient( 'wtsr_activation_in_progress' );
$user_limit = get_option('wtsr_limit_users') * Wtsr_License::get_license_multi();
$days_limit = get_option('wtsr_limit_days');
$license_version = Wtsr_License::get_license_version();

?>
<div style="width:100%;max-width:750px;margin-left:auto;margin-right:auto;">
    <p>
        <?php _e("Congratulations you managed a lot.</br>At this step you can generate review requests from your current orders and activate automatic mode." , 'more-better-reviews-for-woocommerce') ?>
    </p>

    <?php
    // Check if license is free
    $license_version = Wtsr_License::get_license_version();

    if ('free' === $license_version) {
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

        ?>
        <div class="notice notice-warning inline" style="margin-bottom:15px;width:100%;max-width:750px;margin-left:auto;margin-right:auto;">
            <p>
                <strong><?php _e('Better Reviews for WooCommerce', 'more-better-reviews-for-woocommerce') ?></strong>:
                <?php
                $upgrade_url = function_exists('mbrfw_fs') ? mbrfw_fs()->get_upgrade_url() : Wtsr_Settings::get_want_to_buy_license_link();
                $upgrade_link = '<a href="'.$upgrade_url.'" target="_blank">' . __('Buy license', 'more-better-reviews-for-woocommerce') . '</a>';

                if (0 > $user_left || 0 === $user_left) {
                    echo __( 'Review request generating stopped!', 'more-better-reviews-for-woocommerce' );

                    if (!empty($timeout_label)) {
                        $string = ' ' . __( 'Wait %s without generating new review requests.', 'more-better-reviews-for-woocommerce' );
                        echo sprintf($string, $timeout_label );
                    }

                    if (function_exists('mbrfw_fs')) {
                        if (!mbrfw_fs()->is_trial()) {
                            $trial_link = '<a href="'.mbrfw_fs()->get_trial_url().'" target="_blank">'. __('trial here', 'more-better-reviews-for-woocommerce'). '</a>';
                            $trial_string = ' ' . __('To continue testing and generate 5 times the amount of current review requests, start %s.', 'more-better-reviews-for-woocommerce');

                            echo sprintf($trial_string, $trial_link);
                        }
                    }

                    $upgrade_string = '</br>' . __( '%s to generate unlimited review requests instantly.', 'more-better-reviews-for-woocommerce' );
                    echo sprintf($upgrade_string, $upgrade_link);
                } else {
                    $string = __( 'You have whole functionality, but generating review requests is limited to %s request.', 'more-better-reviews-for-woocommerce' );
                    echo sprintf($string , $user_limit );

                    if (!empty($user_count)) {
                        $string = ' ' . __( 'You created already %s review request.', 'more-better-reviews-for-woocommerce' );
                        echo sprintf($string , $user_count );
                    }

                    if (!empty($timeout_label)) {
                        $string = ' ' . __( 'Limit will be reset in %s.', 'more-better-reviews-for-woocommerce' );
                        echo sprintf($string , $timeout_label );
                    }

                    if (function_exists('mbrfw_fs')) {
                        if (!mbrfw_fs()->is_trial()) {
                            $trial_link = '<a href="'.mbrfw_fs()->get_trial_url().'" target="_blank">'. __('trial here', 'more-better-reviews-for-woocommerce'). '</a>';
                            $trial_string = ' ' . __('To continue testing and generate 5 times the amount of current review requests, start %s.', 'more-better-reviews-for-woocommerce');

                            echo sprintf($trial_string, $trial_link);
                        }
                    }

                    $upgrade_string = '</br>' . __( '%s to generate unlimited review requests.', 'more-better-reviews-for-woocommerce' );
                    echo sprintf($upgrade_string, $upgrade_link);
                }
                ?>
            </p>
        </div>
        <?php
    }
    ?>

    <?php require_once dirname(__FILE__) . '/wtsr-admin-wizard-block-license_info.php'; ?>
</div>
<?php

if (empty($mysql_orders)) {
    ?>
    <div class="wtsr-processing-holder" style="padding:10px;">
        <div class="notice notice-warning inline" style="margin-bottom:15px;width:100%;max-width:750px;margin-left:auto;margin-right:auto;">
            <p>
                <?php _e("You do not have any customers orders in your DB." , 'more-better-reviews-for-woocommerce') ?>
                <br>
                <?php _e("You can generate 5 dummy orders for testing purposes by clicking the button <strong>Generate orders</strong>." , 'more-better-reviews-for-woocommerce') ?>
                <br>
                <?php _e("It could take a few seconds, please be patient and do not reload or close this page." , 'more-better-reviews-for-woocommerce') ?>
            </p>
        </div>

        <div class="settings-container">
            <div class="settings-control">
                <button id="wizard-wtsr_generate_orders" class="button button-primary" type="button">
                    <?php _e('Generate 5 Orders', 'more-better-reviews-for-woocommerce'); ?>
                </button>
            </div>
        </div>

        <div class="wtsr-spinner-holder">
            <div class="wtsr-spinner"></div>
        </div>
    </div>
    <?php
} else {
    global $wpdb;
    $product_review_request_bg = $wpdb->get_results( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE '%wp_wtsr_review_request_batch%'", ARRAY_A );

    if (!empty($product_review_request_bg)) {
        $product_review_request_bg_count = count($product_review_request_bg);
    } else {
        $is_ts_review_request_enabled = get_option('wtsr_ts_review_request_enabled');
    }

    $reviews = ReviewsModel::get_all_by('order_id');
    $orders_count = 0;
    $orders_limit = 'all' === $filter_number ? 999999999 : (int) $filter_number;
    $total_orders_ids = array();

    ob_start();
    $emails_array = array();
    $selected_review_ask = get_option('wtsr_review_ask', 'no');
    $reviews_not_allowed = 0;

    include_once dirname(__FILE__) . '/wtsr-admin-generate-order-reviews-table-list.php';
    $orders_list = ob_get_clean();

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
    <div id="reviews_actions" style="margin-bottom:15px;">
        <p>
            <strong><?php _e('Generate review requests for existed orders', 'more-better-reviews-for-woocommerce'); ?></strong>
            (<?php echo $orders_count . ' ' . __('orders available', 'more-better-reviews-for-woocommerce'); ?>)
        </p>
        <form method="get">
            <div id="filter_actions" style="margin-bottom:10px;">
                <input type="hidden" name="page" value="wp2leads-wtsr">
                <input type="hidden" name="tab" value="wizard">
                <input type="hidden" name="wizard_step" value="generate_reviews">

                <?php
                if (!empty($order_statuses)) {
                    include_once dirname(__FILE__) . '/blocks/generate-actions.php';
                    if ('free' === $license_version && 0 === $user_left) {
                        ?>
                        <button
                                id="generate_selected_reviews"
                                class="button button-primary"
                                type="button"
                                data-warningmsg="<?php _e('Are you sure you want to generate requests for selected orders', 'more-better-reviews-for-woocommerce'); ?>"
                                data-notselectedmsg="<?php _e('Select at least one order', 'more-better-reviews-for-woocommerce'); ?>"
                                disabled
                                style="height:34px;line-height:32px;"
                        >
                            <?php _e('Generate for selected', 'more-better-reviews-for-woocommerce'); ?>
                        </button>
                        <?php
                    } else {
                        ?>
                        <button
                                id="generate_selected_reviews"
                                class="button button-primary"
                                type="button"
                                data-warningmsg="<?php _e('Are you sure you want to generate requests for selected orders', 'more-better-reviews-for-woocommerce'); ?>"
                                data-notselectedmsg="<?php _e('Select at least one order', 'more-better-reviews-for-woocommerce'); ?>"
                                style="height:34px;line-height:32px;"
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
                        >
                            <?php
                            $string = __( 'Generate reviews for all %s orders', 'more-better-reviews-for-woocommerce' );
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

    <?php
    if (empty($orders_count) && empty($reviews)) {
        ?>
        <div class="wtsr-processing-holder" style="padding:10px;">
            <div class="notice notice-warning inline" style="margin-bottom:15px;">
                <p>
                    <?php echo sprintf(__('Your do not have orders with status <strong>%s</strong> created in period between <strong>%s</strong> -  <strong>%s</strong>.', 'more-better-reviews-for-woocommerce') , $selected_order_status, $start_order_date, $end_order_date ); ?>
                    <?php _e("You can select another order status or change date period." , 'more-better-reviews-for-woocommerce') ?>
                    <br>
                    <?php _e("Or you can generate 5 dummy orders for testing purposes by clicking the button <strong>Generate orders</strong>." , 'more-better-reviews-for-woocommerce') ?>
                    <?php
                    if ('free' === $license_version) {
                        ?>
                        <?php _e("Dummy orders <strong>will not affect</strong> your Pro Version limitation." , 'more-better-reviews-for-woocommerce') ?>
                        <?php
                    }
                    ?>
                    <br>
                    <?php _e("It could take a few seconds, please be patient and do not reload or close this page." , 'more-better-reviews-for-woocommerce') ?>
                </p>
            </div>

            <div class="settings-container">
                <div class="settings-control">
                    <button id="wizard-wtsr_generate_orders" class="button button-primary" type="button" data-status="<?php echo $selected_order_status; ?>">
                        <?php _e('Generate 5 Orders', 'more-better-reviews-for-woocommerce'); ?>
                    </button>
                </div>
            </div>

            <div class="wtsr-spinner-holder">
                <div class="wtsr-spinner"></div>
            </div>
        </div>
        <?php
    } else {
        include_once dirname(__FILE__) . '/wtsr-admin-generate-order-reviews-table.php';
    }
}
?>

