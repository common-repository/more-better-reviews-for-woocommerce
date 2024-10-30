<?php
/**
 * Provide a admin area view for the plugin
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/admin/partials
 * @var $is_woocommerce_reviews_disabled_globally
 * @var $required_plugins_wp2leads
 * @var $is_wp2leads_installed
 * @var $default_send_via
 * @var $wtsr_email_send_via
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$reviews = ReviewsModel::get_all();
$filter_email = !empty($_GET['filter_email']) ? sanitize_text_field($_GET['filter_email']) : '';
$filter_status = !empty($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '';

$start_statistic_date = !empty($_GET['wtsr_start_statistic_date']) ? sanitize_text_field($_GET['wtsr_start_statistic_date']) : date('Y-m-d', (time() - (90 * 24 * 60 * 60)));
$end_statistic_date = !empty($_GET['wtsr_end_statistic_date']) ? sanitize_text_field($_GET['wtsr_end_statistic_date']) : date('Y-m-d', time());

$start_statistic_date_request = strtotime($start_statistic_date);

if (!$start_statistic_date_request) {
    $start_statistic_date_request = time() - (90 * 24 * 60 * 60);
}

$start_statistic_date_request = date('Y-m-d', $start_statistic_date_request);

$end_statistic_date_request = strtotime($end_statistic_date);

if (!$end_statistic_date_request) {
    $end_statistic_date_request = time() + 24 * 60 * 60;
} else {
    $end_statistic_date_request = $end_statistic_date_request + 24 * 60 * 60;
}

$end_statistic_date_request = date('Y-m-d', $end_statistic_date_request);

$reviews_with_template = ReviewsModel::get_with_template('DESC', array(
    'created_from' => $start_statistic_date_request .  ' 00:00:00',
    'created_till' => $end_statistic_date_request .  ' 00:00:00',
), 'id, order_id, email, status, review_created, review_sent');

$transferred = array();
$reviewed = array();
$outdated = array();

foreach ($reviews_with_template as $review_with_template) {
    if ('transferred' === $review_with_template->status || 'woo-sent' === $review_with_template->status) {
        $transferred[] = $review_with_template->id;
    }

    if ('outdated' === $review_with_template->status) {
        $outdated[] = $review_with_template->id;
    }

    if ('reviewed' === $review_with_template->status) {
        $reviewed[] = $review_with_template->id;
    }
}

$transferred_count = count($transferred);
$reviewed_count = count($reviewed);
$outdated_count = count($outdated);

$total = $transferred_count + $reviewed_count + $outdated_count;
$reviewed_percent = 0;
$outdated_percent = 0;

?>
<div class="wtsr-page-header">
    <h3><?php _e('Statistics', 'more-better-reviews-for-woocommerce'); ?></h3>
</div>

<!-- Reviews statistics section start -->
<div id="reviews_statistics" style="margin-bottom:15px;">
    <form method="get">
        <div id="filter_actions" style="margin-bottom:10px;">
            <input type="hidden" name="page" value="wp2leads-wtsr">
            <input type="hidden" name="tab" value="reviews">

            <?php _e('Start / End dates', 'more-better-reviews-for-woocommerce'); ?>:
            <input id="wtsr_start_statistic_date" name="wtsr_start_statistic_date" type="text"
                   value="<?php echo $start_statistic_date; ?>"
                   class="wtsr-datepicker form-input" style="max-width:120px;display:inline-block;" readonly> -

            <input id="wtsr_end_statistic_date" name="wtsr_end_statistic_date" type="text"
                   value="<?php echo $end_statistic_date; ?>"
                   class="wtsr-datepicker form-input" style="max-width:120px;display:inline-block;" readonly>

            <button type="submit" class="button button-primary" style="height:34px;line-height:32px;">
                <?php _e('Get statistics', 'more-better-reviews-for-woocommerce'); ?>
            </button>
        </div>
    </form>

    <?php
    if (!empty($total)) {
        $transferred_percent = round(( $transferred_count / $total ) * 100, 2);
        $reviewed_percent = round(( $reviewed_count / $total ) * 100, 2);
        $outdated_percent = round(( $outdated_count / $total ) * 100, 2);
        ?>

        <div class="reviews_statistics_item">
            <div class="reviews_statistics_label">
                <?php _e('Reviewed', 'more-better-reviews-for-woocommerce'); ?>: <strong>(<?php echo $reviewed_count ?> - <?php echo $reviewed_percent ?>%)</strong>
            </div>
            <div class="reviews_statistics_bar">
                <div class="wtsr_progress_container" style="width:100%;max-width:600px;">
                    <div class="wtsr_progress">
                        <div class="wtsr_progress_bar" style="width:<?php echo $reviewed_percent ?>%;height:18px;background-color:#0e642e!important;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="reviews_statistics_item">
            <div class="reviews_statistics_label">
                <?php _e('Outdated', 'more-better-reviews-for-woocommerce'); ?>: <strong>(<?php echo $outdated_count ?> - <?php echo $outdated_percent ?>%)</strong>
            </div>
            <div class="reviews_statistics_bar">
                <div class="wtsr_progress_container" style="width:100%;max-width:600px;">
                    <div class="wtsr_progress">
                        <div class="wtsr_progress_bar" style="width:<?php echo $outdated_percent ?>%;height:18px;background-color:#920a09!important;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="reviews_statistics_item">
            <div class="reviews_statistics_label">
                <?php _e('Waiting', 'more-better-reviews-for-woocommerce'); ?>: <strong>(<?php echo $transferred_count ?> - <?php echo $transferred_percent ?>%)</strong>
            </div>
            <div class="reviews_statistics_bar">
                <div class="wtsr_progress_container" style="width:100%;max-width:600px;">
                    <div class="wtsr_progress">
                        <div class="wtsr_progress_bar" style="width:<?php echo $transferred_percent ?>%;height:18px;background-color:#bbb!important;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="reviews_statistics_item">
            <div class="reviews_statistics_label">
                <?php _e('Totally transferred', 'more-better-reviews-for-woocommerce'); ?>: <strong>(<?php echo $total ?>)</strong>
            </div>
            <div class="reviews_statistics_bar">
                <div class="wtsr_progress_container" style="width:100%;max-width:600px;">
                    <div class="wtsr_progress">
                        <div class="wtsr_progress_bar" style="width:100%;height:18px;background-color:#bbb!important;"></div>
                        <?php
                        if (!empty($outdated_percent)) {
                            ?>
                            <div class="wtsr_progress_bar" style="width:<?php echo $outdated_percent + $reviewed_percent ?>%;height:18px;background-color:#920a09!important;"></div>
                            <?php
                        }
                        ?>
                        <div class="wtsr_progress_bar" style="width:<?php echo $reviewed_percent ?>%;height:18px;background-color:#0e642e!important;"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>
<!-- Reviews statistics section end -->
<?php

$wtsr_send_woo_email_schedule_all = get_option(Wtsr_Cron::$schedule_option, array());
?>
<hr>
<div class="wtsr-page-header">
    <h3><?php _e('Reviews', 'more-better-reviews-for-woocommerce'); ?></h3>
</div>

<!-- Reviews filter section start -->
<div id="reviews_actions" style="margin-bottom:15px;">
    <form method="get">
        <div id="filter_actions" style="margin-bottom:10px;">
            <input type="hidden" name="page" value="wp2leads-wtsr">
            <input type="hidden" name="tab" value="reviews">
            <?php _e('Status', 'more-better-reviews-for-woocommerce'); ?>:
            <select name="filter_status" id="filter_status" class="form-input" style="max-width:150px;display:inline-block;margin-right:15px;">
                <option value="">-- <?php _e('select status', 'more-better-reviews-for-woocommerce'); ?> --</option>
                <option value="pending"<?php echo 'pending' === $filter_status ? ' selected' : '' ?>>pending</option>
                <option value="ready"<?php echo 'ready' === $filter_status ? ' selected' : '' ?>>ready</option>
                <option value="transferred"<?php echo 'transferred' === $filter_status ? ' selected' : '' ?>>transferred</option>
                <option value="woo-sent"<?php echo 'woo-sent' === $filter_status ? ' selected' : '' ?>>woo-sent</option>
                <option value="reviewed"<?php echo 'reviewed' === $filter_status ? ' selected' : '' ?>>reviewed</option>
                <option value="outdated"<?php echo 'outdated' === $filter_status ? ' selected' : '' ?>>outdated</option>
                <option value="cancelled"<?php echo 'cancelled' === $filter_status ? ' selected' : '' ?>>cancelled</option>
            </select>

            <?php _e('Email', 'more-better-reviews-for-woocommerce'); ?>:
            <input type="text" name="filter_email" id="filter_email" value="<?php echo $filter_email ?>" class="form-input" style="max-width:200px;display:inline-block;margin-right:10px;">

            <button type="submit" class="button button-primary" style="height:34px;line-height:32px;"><?php _e('Apply filter', 'more-better-reviews-for-woocommerce'); ?></button>

            <a href="?page=wp2leads-wtsr&tab=reviews" class="button button-primary" style="height:34px;line-height:32px;"><?php _e('Remove filter', 'more-better-reviews-for-woocommerce'); ?></a>

            <?php
            if ('woocommerce' === $wtsr_email_send_via) {
                ?>
                <button
                        id="wtst-send-selected-reviews"
                        type="button" class="button button-primary"
                        style="height:34px;line-height:32px;"
                        data-warningmsg="<?php _e('Are you sure you want to send review request emails to the selected?', 'more-better-reviews-for-woocommerce'); ?>"
                        data-notselectedmsg="<?php _e('Select at least one request', 'more-better-reviews-for-woocommerce'); ?>"
                >
                    <?php _e('Send selected requests', 'more-better-reviews-for-woocommerce'); ?>
                </button>
                <?php
            }
            ?>

            <button
                    id="remove_selected_reviews"
                    class="button button-danger"
                    type="button"
                    data-warningmsg="<?php _e('Are you sure you want to remove selected reviews', 'more-better-reviews-for-woocommerce'); ?>"
                    data-notselectedmsg="<?php _e('Select at least one review', 'more-better-reviews-for-woocommerce'); ?>"
                    style="height:34px;line-height:32px;"
            >
                <?php _e('Delete selected', 'more-better-reviews-for-woocommerce'); ?>
            </button>
        </div>
    </form>
</div>
<!-- Reviews filter section end -->

<p>
    <?php _e('Click <strong>"View"</strong> to see generated review requests', 'more-better-reviews-for-woocommerce'); ?>
</p>

<!-- Reviews list section start -->
<table id="reviews-list" class="wp-list-table widefat fixed striped pages">
    <thead>
        <tr>
            <td class="manage-column column-cb check-column">
                <label class="screen-reader-text" for="cb-select-all-1"><?php echo __( 'Select All' ) ?></label>
                <input id="cb-select-all-1" type="checkbox" />
            </td>
            <th class="column-primary"><?php _e('Request #', 'more-better-reviews-for-woocommerce'); ?></th>
            <th><?php _e('Order ID', 'more-better-reviews-for-woocommerce'); ?></th>
            <th><?php _e('Email', 'more-better-reviews-for-woocommerce'); ?></th>
            <th><?php _e('Status', 'more-better-reviews-for-woocommerce'); ?></th>
            <?php
            if ('woocommerce' === $wtsr_email_send_via) {
                ?>
                <th><?php _e('Woo Email Scheduled', 'more-better-reviews-for-woocommerce'); ?></th>
                <?php
            }
            ?>
            <th><?php _e('Created', 'more-better-reviews-for-woocommerce'); ?></th>
            <th><?php _e('Status changed', 'more-better-reviews-for-woocommerce'); ?></th>
            <th></th>
        </tr>
    </thead>

    <tbody id="the-list">
    <?php
    if (!empty($reviews)) {
        foreach ($reviews as $review) {
            if (!empty($filter_email)) {
                $email_to_check = strtolower($review->email);
                $email_filter = strtolower($filter_email);

                if (false === strpos($email_to_check, $email_filter)) {
                    continue;
                }
            }

            if (!empty($filter_status)) {
                if ('transferred' === $filter_status) {
                    if ('transferred' !== $review->status && 'transfered' !== $review->status ) {
                        continue;
                    }
                } else {
                    if ($filter_status !== $review->status ) {
                        continue;
                    }
                }
            }
            $review_created = get_date_from_gmt( $review->review_created, 'Y-m-d H:i' );

            if (!empty($review->review_sent)) {
                $review_sent = get_date_from_gmt( $review->review_sent, 'Y-m-d H:i' );
            } else {
                $review_sent = '';
            }

            $review_status = 'transfered' === $review->status ? 'transferred' : $review->status;
            $tag_label = '';

            if (in_array($review_status, array('outdated', 'reviewed'))) {
                $tag_label = ' (' . sprintf(__('prefix + %s sent to KT', 'more-better-reviews-for-woocommerce') , $review_status ) . ')';
            } elseif ('transferred' === $review_status) {
                $tag_label = ' (' . sprintf(__('prefix + %s sent to KT', 'more-better-reviews-for-woocommerce') , 'ready' ) . ')';
            }

            if ('woocommerce' === $wtsr_email_send_via) {
                $tag_label = '';
            }
            ?>
            <tr>
                <th class="check-column">
                    <input id="cb-select-<?php echo $review->id; ?>" type="checkbox" value="<?php echo $review->id; ?>">
                </th>

                <td class="column-primary has-row-actions">
                    <strong>
                        <?php
                        if ('pending' === $review_status) {
                            ?>
                            <?php echo $review->id; ?>
                            <?php
                        } else {
                            ?>
                            <a href="?page=wp2leads-wtsr&tab=reviews&review_id=<?php echo $review->id; ?>"><?php echo $review->id; ?></a>

                            <a class="button button-small button-success" href="?page=wp2leads-wtsr&tab=reviews&review_id=<?php echo $review->id; ?>">
                                <?php _e('View', 'more-better-reviews-for-woocommerce'); ?>
                            </a>
                            <?php
                        }
                        ?>

                    </strong>

                    <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                </td>
                <td data-colname="<?php _e('Order ID', 'more-better-reviews-for-woocommerce'); ?>"><?php echo $review->order_id; ?></td>
                <td data-colname="<?php _e('Email', 'more-better-reviews-for-woocommerce'); ?>"><?php echo $review->email; ?></td>
                <td data-colname="<?php _e('Status', 'more-better-reviews-for-woocommerce'); ?>"><?php echo $review_status . $tag_label; ?></td>
                <?php
                if ('woocommerce' === $wtsr_email_send_via) {
                    ?>
                    <td>
                        <?php
                        if (!empty($wtsr_send_woo_email_schedule_all[$review->id]["schedule"])) {
                            ?>
                            <ul style="margin: 0;">
                                <?php
                                $schedule = $wtsr_send_woo_email_schedule_all[$review->id]["schedule"];

                                foreach ($schedule as $date) {
                                    ?>
                                    <li>
                                        <?php echo get_date_from_gmt( date( 'Y-m-d H:i', $date ), 'Y-m-d H:i' ); ?>
                                        <span class="wtsr-icon-cancel wtsr-icon-cancel-schedule-email" data-review="<?php echo $review->id; ?>" data-order="<?php echo $review->order_id; ?>" data-schedule="<?php echo $date; ?>" title="<?php _e('Delete scheduled email', 'more-better-reviews-for-woocommerce'); ?>"></span>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                            <?php
                        }
                        ?>
                    </td>
                    <?php
                }
                ?>
                <td data-colname="<?php _e('Date', 'more-better-reviews-for-woocommerce'); ?>"><?php echo $review_created; ?></td>
                <td data-colname="<?php _e('Sent', 'more-better-reviews-for-woocommerce'); ?>"><?php echo $review_sent; ?></td>
                <td data-colname="">
                    <?php
                    if ('woocommerce' === $wtsr_email_send_via) {
                        ?>
                        <button type="button" class="button button-primary button-small send-woo-review" data-review="<?php echo $review->id; ?>">
                            <?php _e('Send request', 'more-better-reviews-for-woocommerce'); ?>
                        </button>
                        <?php
                    }
                    ?>

                    <button type="button" class="button button-danger button-small delete-review" data-review-id="<?php echo $review->id; ?>">
                        <?php _e('Delete', 'more-better-reviews-for-woocommerce'); ?>
                    </button>
                </td>
            </tr>
            <?php
        }
    }
    ?>
    </tbody>

    <tfoot>
        <tr>
            <td class="manage-column column-cb check-column">
                <label class="screen-reader-text" for="cb-select-all-2"><?php echo __( 'Select All', 'more-better-reviews-for-woocommerce' ) ?></label>
                <input id="cb-select-all-2" type="checkbox" />
            </td>
            <th class="column-primary"><?php _e('Request #', 'more-better-reviews-for-woocommerce'); ?></th>
            <th><?php _e('Order ID', 'more-better-reviews-for-woocommerce'); ?></th>
            <th><?php _e('Email', 'more-better-reviews-for-woocommerce'); ?></th>
            <th><?php _e('Status', 'more-better-reviews-for-woocommerce'); ?></th>
            <?php
            if ('woocommerce' === $wtsr_email_send_via) {
                ?>
                <th><?php _e('Woo Email Scheduled', 'more-better-reviews-for-woocommerce'); ?></th>
                <?php
            }
            ?>
            <th><?php _e('Created', 'more-better-reviews-for-woocommerce'); ?></th>
            <th><?php _e('Status changed', 'more-better-reviews-for-woocommerce'); ?></th>
            <th></th>
        </tr>
    </tfoot>
</table>
<!-- Reviews list section start -->

<div class="wtsr-page-footer">

</div>
