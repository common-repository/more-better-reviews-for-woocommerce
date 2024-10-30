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
$selected_review_ask = get_option('wtsr_review_ask', 'no');
$review_ask_template_editor = get_option('wtsr_review_ask_template_editor', TSManager::get_default_review_ask_template_editor());
$selected_filter_email_domain = get_option('wtsr_filter_email_domain', '');
$selected_review_period = get_option('wtsr_review_period', '30');
$order_statuses = wc_get_order_statuses();
$selected_order_status = get_option('wtsr_order_status', 'wc-completed');
$selected_review_variations = get_option('wtsr_review_variations', 'no');
$wtsr_review_approved = get_option('wtsr_review_approved', 'yes');
$selected_filter_email_domain = get_option('wtsr_filter_email_domain', '');

if (!empty($selected_filter_email_domain) && is_array($selected_filter_email_domain)) {
    $selected_filter_email_domain = implode("\r\n", $selected_filter_email_domain);
}

$is_woocommerce_only_mode = ReviewServiceManager::is_woocommerce_only_mode();
?>

<div class="settings-container">
    <?php
    if (!empty($is_woocommerce_only_mode)) {
        ?>
        <div class="settings-item">
            <div class="settings-item-group">
                <div class="settings-item-group-col">
                    <p class="settings-item-label">
                        <?php _e('Ask customer for review', 'more-better-reviews-for-woocommerce'); ?>
                    </p>
                </div>
                <div class="settings-item-group-col">
                    <div class="settings-item-value">
                        <select class="form-input" name="wtsr_review_ask" id="wtsr_review_ask" style="max-width:120px;">
                            <option value="no"<?php echo $selected_review_ask === 'no' ? ' selected' : ''; ?>><?php _e('No', 'more-better-reviews-for-woocommerce'); ?></option>
                            <option value="yes"<?php echo $selected_review_ask === 'yes' ? ' selected' : ''; ?>><?php _e('Yes', 'more-better-reviews-for-woocommerce'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <p class="settings-item-description">
                <?php include_once dirname(__FILE__) . '/wtsr-admin-description-review-ask.php'; ?>
            </p>
        </div>

        <div class="settings-item">
            <p class="settings-item-label">
                <?php _e('Ask customer for review HTML template', 'more-better-reviews-for-woocommerce'); ?>
            </p>

            <div class="settings-item-value">
                <div id="wtsr_email_template_editor">
                    <?php wp_editor( $review_ask_template_editor, 'wtsr_review_ask_template_editor', array(
                        'textarea_rows' => 10,
                        'wpautop'       => 1,
                    ) ); ?>
                </div>
            </div>
        </div>
        <?php
    }
    ?>

    <div class="settings-item">
        <p class="settings-item-label">
            <?php _e('Filter email domain', 'more-better-reviews-for-woocommerce'); ?>
        </p>

        <div class="settings-item-group">
            <div class="settings-item-group-col">
                <div class="settings-item-value">
                    <textarea class="form-input" name="wtsr_filter_email_domain" id="wtsr_filter_email_domain" cols="30" rows="5"><?php echo $selected_filter_email_domain; ?></textarea>
                </div>
            </div>
            <div class="settings-item-group-col">
                <p class="settings-item-description">
                    <?php echo __('Put one item per line.', 'more-better-reviews-for-woocommerce'); ?>

                    <?php echo __('For our example:', 'more-better-reviews-for-woocommerce'); ?><br>
                    <em>
                        domain1 <br>
                        domain2
                    </em>
                </p>
            </div>
        </div>

        <p class="settings-item-description">
            <?php _e('For secure reason we excluding orders that contain <strong><em>amazon</em></strong> and <strong><em>ebay</em></strong> in billing email.', 'more-better-reviews-for-woocommerce'); ?>
            <?php _e('Emails to these addresses can cause a blocking accounts at the portals!', 'more-better-reviews-for-woocommerce'); ?>
            <?php _e('Also we generate reviews request only for orders that was done via checkout on your shop.', 'more-better-reviews-for-woocommerce'); ?>
        </p>

        <p class="settings-item-description">
            <?php _e('If you want to add more filters for billing email, please add values in form field above.', 'more-better-reviews-for-woocommerce'); ?>
        </p>
    </div>

    <div class="settings-item">
        <div class="settings-item-group">
            <div class="settings-item-group-col">
                <p class="settings-item-label">
                    <?php _e('Generate request not often than (days)', 'more-better-reviews-for-woocommerce'); ?>:
                </p>
            </div>
            <div class="settings-item-group-col">
                <div class="settings-item-value">
                    <input class="form-input" name="wtsr_review_period" id="wtsr_review_period" type="number" value="<?php echo $selected_review_period; ?>">
                </div>
            </div>
        </div>

        <div class="settings-item-description">
            <?php echo __('If you often have recurring customers, you can limit the number of valuation requests in a given period of time.', 'more-better-reviews-for-woocommerce'); ?>
            <br>
            <?php echo __('When this period will end review request status will become <strong>outdated</strong> and you can generate new review request for this customer.', 'more-better-reviews-for-woocommerce'); ?>
            <br>
            <?php echo __('Order outdated status is calculating not from created date but from status changed. So every time we send email date will be updated and we have 30 days for review again.', 'more-better-reviews-for-woocommerce'); ?>
        </div>
    </div>

    <div class="settings-item">
        <div class="settings-item-group">
            <div class="settings-item-group-col">
                <p class="settings-item-label">
                    <?php _e('Order status generating reviews requests', 'more-better-reviews-for-woocommerce'); ?>:
                </p>
            </div>

            <div class="settings-item-group-col">
                <div class="settings-item-value">
                    <?php
                    if (!empty($order_statuses)) {
                        ?>
                        <select class="form-input" name="wtsr_order_status" id="wtsr_order_status">
                            <option value="wc-order-created"<?php echo 'wc-order-created' === $selected_order_status ? ' selected' : ''; ?>><?php _e('Order created', 'more-better-reviews-for-woocommerce'); ?></option>
                            <?php
                            foreach ($order_statuses as $slug => $label) {
                                ?>
                                <option value="<?php echo $slug ?>"<?php echo $slug === $selected_order_status ? ' selected' : ''; ?>><?php echo $label ?></option>
                                <?php
                            }
                            ?>
                        </select>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-item">
        <div class="settings-item-group">
            <div class="settings-item-group-col">
                <p class="settings-item-label">
                    <?php _e('Product variations', 'more-better-reviews-for-woocommerce'); ?>:
                </p>
            </div>

            <div class="settings-item-group-col">
                <div class="settings-item-value">
                    <select class="form-input" name="wtsr_review_variations" id="wtsr_review_variations">
                        <option value="no"<?php echo $selected_review_variations === 'no' ? ' selected' : ''; ?>><?php _e('Only parent products', 'more-better-reviews-for-woocommerce'); ?></option>
                        <option value="yes"<?php echo $selected_review_variations === 'yes' ? ' selected' : ''; ?>><?php _e('Each variation separately', 'more-better-reviews-for-woocommerce'); ?></option>
                    </select>
                </div>
            </div>
        </div>

        <p class="settings-item-description">
            <?php echo __('Goal: gathering all reviews from all variants of a product under the parent product sku.', 'more-better-reviews-for-woocommerce'); ?>
        </p>
    </div>

    <div class="settings-item">
        <div class="settings-item-group">
            <div class="settings-item-group-col">
                <p class="settings-item-label">
                    <?php _e('Transfer only approved reviews', 'more-better-reviews-for-woocommerce'); ?>
                </p>
            </div>

            <div class="settings-item-group-col">
                <div class="settings-item-value" style="padding-top:10px;">
                    <input type="checkbox" name="wtsr_review_approved" id="wtsr_review_approved"<?php echo 'yes' === $wtsr_review_approved ? ' checked' : '' ?>>
                </div>
            </div>
        </div>

        <p class="settings-item-description">
            <?php _e('If this settings is checked only approved reviews from product page will be transferred to Klick Tipp. If disabled it will be transfered at once after user click "Submit" button', 'more-better-reviews-for-woocommerce'); ?>
        </p>
    </div>

    <div class="settings-control">
        <button id="wizard-wtsr_save_general_settings" class="button button-primary" type="button">
            <?php _e('Save settings', 'more-better-reviews-for-woocommerce'); ?>
        </button>
    </div>
</div>
