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

$review_id = !empty($_GET['review_id']) ? sanitize_text_field($_GET['review_id']) : false;
$license_version = Wtsr_License::get_license_version();
$activation_in_progress = get_transient( 'wtsr_activation_in_progress' );

if ('free' === $license_version && empty($review_id)) {
    $reviews_all_with_template = ReviewsModel::get_with_template('DESC', array(), 'id, order_id, email, status, review_created, review_sent');

    if (!empty($reviews_all_with_template)) {
        $review_id = $reviews_all_with_template[0]->id;
    }
}

if ($review_id) {
    $review = ReviewsModel::get( $review_id );

    if (empty($review)) {
        $reviews = ReviewsModel::get_all();
        $filter_email = !empty($_GET['filter_email']) ? trim($_GET['filter_email']) : '';
        $filter_status = !empty($_GET['filter_status']) ? trim($_GET['filter_status']) : '';
    } else {
        $reviews_with_template = ReviewsModel::get_with_template('DESC', array(), 'id, order_id, email, status, review_created, review_sent');
        $current_index = 0;
        $first = false;
        $last = false;
        $next = false;
        $prev = false;

        foreach ($reviews_with_template as $index => $data) {
            if ($review_id === $data->id) {
                $current_index = $index;
            }
        }

        if (0 === $current_index) {
            $first = true;
        }

        $next_index = $current_index + 1;

        if (empty($reviews_with_template[$next_index])) {
            $last = true;
        }

        if (!$first) {
            $prev_index = $current_index - 1;
            $prev = $reviews_with_template[$prev_index]->id;
        }

        if (!$last) {
            $next = $reviews_with_template[$next_index]->id;
        }
    }
} else {
    $reviews = ReviewsModel::get_all();
    $filter_email = !empty($_GET['filter_email']) ? trim($_GET['filter_email']) : '';
    $filter_status = !empty($_GET['filter_status']) ? trim($_GET['filter_status']) : '';
}

if (!empty($review)) {

    if ('free' === $license_version || !empty($activation_in_progress)) {
        require_once dirname(__FILE__) . '/wtsr-admin-wizard-block-license_warning.php';
        require_once dirname(__FILE__) . '/wtsr-admin-wizard-block-license_info.php';
    }
    ?>

    <div id="top_review_navigation" class="review-navigation">
        <?php
        if ($prev) {
            ?>
            <a class="button button-primary button-small" href="?page=wp2leads-wtsr&tab=wizard&wizard_step=reviews_list&review_id=<?php echo $prev; ?>">
                &#171; <?php _e('Previous request', 'more-better-reviews-for-woocommerce'); ?>
            </a>
            <?php
        }

        if ($next) {
            ?>
            <a class="button button-primary button-small" href="?page=wp2leads-wtsr&tab=wizard&wizard_step=reviews_list&review_id=<?php echo $next; ?>">
                <?php _e('Next request', 'more-better-reviews-for-woocommerce'); ?> &#187;
            </a>
            <?php
        }

        if ('free' !== $license_version) {
            ?>
            <a class="button button-primary button-small" href="?page=wp2leads-wtsr&tab=wizard&wizard_step=reviews_list">
                <?php _e('All Reviews', 'more-better-reviews-for-woocommerce'); ?>
            </a>
            <?php
        }
        ?>
    </div>

    <?php
    if (!empty($review)) {

        if ('woocommerce' === $wtsr_email_send_via) {
            ?>
            <div style="max-width:800px;margin:auto;">
                <h3><?php _e('Test the Spammyness of your Emails', 'more-better-reviews-for-woocommerce'); ?></h3>
                <p>
                    <?php _e('Get your testing email address on <a href="https://www.mail-tester.com/" target="_blank">https://www.mail-tester.com/</a>.', 'more-better-reviews-for-woocommerce'); ?>
                    <?php _e('Copy it and paste into the field below and click "Send email" button.', 'more-better-reviews-for-woocommerce'); ?>
                    <?php _e('After this go back to previously opened mail-tester page and click "Then check your score" button.', 'more-better-reviews-for-woocommerce'); ?>
                </p>

                <div class="wtsr_input-group_holder">
                    <div class="wtsr_input_holder">
                        <input
                                class="form-input"
                                type="text"
                                id="wtsr_email_test_score"
                            <?php echo 'woocommerce' !== $wtsr_email_send_via ? 'disabled' : ''; ?>
                                placeholder="<?php _e('Paste email address', 'more-better-reviews-for-woocommerce'); ?>"
                        >
                    </div>

                    <div class="wtsr_btn_holder">
                        <button
                                id="wtsr_email_test_score_button"
                                class="button button-primary"
                                data-review="<?php echo $review_id; ?>"
                                data-template="review_email"
                        >
                            <?php _e('Send email', 'more-better-reviews-for-woocommerce'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <?php
        }
        foreach ($review as $review_item) {
            $thankyou_template = Wtsr_Template::get_email_thankyou_html_template($review_id, false);
            ?>
            <div id="wtsr_email_html_templates_preview" style="max-width:800px;margin:auto;">
                <?php
                if (!empty($thankyou_template)) {
                    ?>
                    <div id="wtsr_email_html_templates_preview_control">
                        <div class="nav-tab-wrapper">
                            <a href="#" class="nav-tab nav-tab-active" data-tab="review_email"><?php _e('Review request email', 'more-better-reviews-for-woocommerce'); ?></a>
                            <a href="#" class="nav-tab" data-tab="thankyou_email"><?php _e('Thank you email', 'more-better-reviews-for-woocommerce'); ?></a>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <div id="wtsr_review_email_template_preview_container" class="wtsr_email_html_templates_preview_item">
                    <div id="wtsr_email_template_preview" style="padding:10px;border:1px solid #ddd;border-radius:5px;background-color:#fff;max-width:800px;margin:auto;margin-top:15px;margin-bottom:15px;">
                        <?php echo wpautop($review_item->review_message); ?>
                    </div>
                </div>
                <?php
                if (!empty($thankyou_template)) {
                    ?>
                    <div id="wtsr_thankyou_email_template_preview_container" class="wtsr_email_html_templates_preview_item" style="display: none;">
                        <p>
                            <?php _e('This template only for previewing and test spammyness, no coupon will be generated.', 'more-better-reviews-for-woocommerce'); ?>
                        </p>
                        <div id="wtsr_thankyouemail_template_preview" style="padding:10px;border:1px solid #ddd;border-radius:5px;background-color:#fff;margin-top:10px;margin-bottom:35px;">
                            <?php echo wpautop($thankyou_template); ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }
    }
    ?>

    <div class="review-navigation">
        <?php
        if ($prev) {
            ?>
            <a class="button button-primary button-small" href="?page=wp2leads-wtsr&tab=wizard&wizard_step=reviews_list&review_id=<?php echo $prev; ?>">
                &#171; <?php _e('Previous request', 'more-better-reviews-for-woocommerce'); ?>
            </a>
            <?php
        }

        if ($next) {
            ?>
            <a class="button button-primary button-small" href="?page=wp2leads-wtsr&tab=wizard&wizard_step=reviews_list&review_id=<?php echo $next; ?>">
                <?php _e('Next request', 'more-better-reviews-for-woocommerce'); ?> &#187;
            </a>
            <?php
        }

        if ('free' !== $license_version) {
            ?>
            <a class="button button-primary button-small" href="?page=wp2leads-wtsr&tab=wizard&wizard_step=reviews_list">
                <?php _e('All Reviews', 'more-better-reviews-for-woocommerce'); ?>
            </a>
            <?php
        }
        ?>
    </div>
    <?php
} else {

    if (!empty($activation_in_progress)) {
        require_once dirname(__FILE__) . '/wtsr-admin-wizard-block-license_info.php';
    }
    ?>
    <div id="reviews_actions" style="margin-bottom:15px;">
        <form method="get">
            <div id="filter_actions" style="margin-bottom:10px;">
                <input type="hidden" name="page" value="wp2leads-wtsr">
                <input type="hidden" name="tab" value="reviews">
                <?php _e('Status', 'more-better-reviews-for-woocommerce'); ?>:
                <select name="filter_status" id="filter_status" class="form-input" style="max-width:160px;display:inline-block;margin-right:20px;">
                    <option value="">-- <?php _e('select status', 'more-better-reviews-for-woocommerce'); ?> --</option>
                    <option value="pending"<?php echo 'pending' === $filter_status ? ' selected' : '' ?>>pending</option>
                    <option value="ready"<?php echo 'ready' === $filter_status ? ' selected' : '' ?>>ready</option>
                    <option value="transferred"<?php echo 'transferred' === $filter_status ? ' selected' : '' ?>>transferred</option>
                    <option value="reviewed"<?php echo 'reviewed' === $filter_status ? ' selected' : '' ?>>reviewed</option>
                    <option value="outdated"<?php echo 'outdated' === $filter_status ? ' selected' : '' ?>>outdated</option>
                    <option value="cancelled"<?php echo 'cancelled' === $filter_status ? ' selected' : '' ?>>cancelled</option>
                </select>

                <?php _e('Email', 'more-better-reviews-for-woocommerce'); ?>:
                <input type="text" name="filter_email" id="filter_email" value="<?php echo $filter_email ?>" class="form-input" style="max-width:250px;display:inline-block;margin-right:10px;">

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

    <p>
        <strong><?php echo __('Next', 'more-better-reviews-for-woocommerce') ?>:</strong>
        <?php _e('Click <strong>"View"</strong> to see generated review requests', 'more-better-reviews-for-woocommerce'); ?>
    </p>

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
            $wtsr_send_woo_email_schedule_all = get_option(Wtsr_Cron::$schedule_option, array());

            foreach ($reviews as $review) {
                if (!empty($filter_email)) {
                    if (false === strpos($review->email, $filter_email)) {
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

                // __('prefix + %s sent to KT', 'more-better-reviews-for-woocommerce') , $count )

                if (in_array($review_status, array('outdated', 'reviewed'))) {
                    $tag_label = ' (' . sprintf(__('prefix + %s sent to KT', 'more-better-reviews-for-woocommerce') , $review_status ) . ')';
                } elseif ('transferred' === $review_status) {
                    $tag_label = ' (' . sprintf(__('prefix + %s sent to KT', 'more-better-reviews-for-woocommerce') , 'ready' ) . ')';
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
                                echo $review->id;
                            } else {
                                ?>
                                <a href="?page=wp2leads-wtsr&tab=wizard&wizard_step=reviews_list&review_id=<?php echo $review->id; ?>"><?php echo $review->id; ?></a>

                                <a class="button button-small button-success" href="?page=wp2leads-wtsr&tab=wizard&wizard_step=reviews_list&review_id=<?php echo $review->id; ?>">
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
                        <button type="button" class="button button-danger button-small delete-review" data-review-id="<?php echo $review->id; ?>"><?php _e('Delete', 'more-better-reviews-for-woocommerce'); ?></button>
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
                <label class="screen-reader-text" for="cb-select-all-2"><?php echo __( 'Select All' ) ?></label>
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
    <?php
}
?>

