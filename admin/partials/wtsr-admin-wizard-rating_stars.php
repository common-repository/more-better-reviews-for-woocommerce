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
$rating_links_options = ReviewsModel::get_rating_links_options();
$default_rating_links = ReviewsModel::get_default_rating_links();
$wtsr_rating_links = get_option('wtsr_rating_links', $default_rating_links);
$is_woocommerce_only_mode = ReviewServiceManager::is_woocommerce_only_mode();
$is_ts_mode = TSManager::is_review_mode_enabled();
$is_woocommerce_only_mode = !$is_ts_mode;
$is_woocommerce_reviews_disabled_globally = TSManager::is_woocommerce_reviews_disabled_globally();

if (!$is_woocommerce_reviews_disabled_globally) {
    $is_woocommerce_reviews_disabled_per_product = TSManager::is_woocommerce_reviews_disabled_per_product();
}

$url = untrailingslashit( plugins_url( '/', WTSR_PLUGIN_FILE ) );
?>

<div class="settings-container">
    <div class="settings-item">
        <div class="wtsr-row">
            <div class="wtsr-col-xs-12 wtsr-col-md-6">
                <h4 style="margin-top: 5px; margin-bottom: 5px;">
                    <label for="wtsr_ts_one_star_link" style="line-height: 20px;display: inline-block;vertical-align: top;">
                        <?php _e('One star link', 'more-better-reviews-for-woocommerce'); ?>
                    </label>
                    <img title="Poor" src="<?php echo $url . '/admin/img/one_star.png'; ?>" alt="">
                </h4>

                <div class="wtsr_rating_link_item">
                    <select class="form-input wtsr_ts_star_link" name="wtsr_ts_one_star_link" id="wtsr_ts_one_star_link">
                        <?php
                        $wtsr_rating_link = $wtsr_rating_links['one_star'];

                        if ($is_woocommerce_only_mode && 'wtsr_ts_review_link' === $wtsr_rating_link) {
                            $wtsr_rating_link = 'wtsr_all_reviews_page';
                        }

                        if ($is_woocommerce_reviews_disabled_globally && 'wtsr_product_url' === $wtsr_rating_link) {
                            $wtsr_rating_link = 'wtsr_custom_link';
                        }

                        foreach ($rating_links_options as $value => $label) {
                            if (
                                (!$is_ts_mode && 'wtsr_ts_review_link' === $value) ||
                                ($is_woocommerce_reviews_disabled_globally && 'wtsr_product_url' === $value)
                            ) {
                                continue;
                            }
                            ?>
                            <option value="<?php echo $value ?>"<?php echo $value === $wtsr_rating_link ? ' selected' : ''; ?>><?php echo $label ?></option>
                            <?php
                        }
                        ?>
                    </select>

                    <div class="wtsr_rating_custom_link_holder" <?php echo 'wtsr_custom_link' === $wtsr_rating_link ? '' : ' style="display:none"'; ?> >
                        <input class="form-input" type="text" id="wtsr_ts_one_star_custom_link" name="wtsr_ts_one_star_custom_link" value="<?php echo $wtsr_rating_links['custom_link']['one_star'] ?>">

                        <p
                                class="warning-text"
                                style="margin-top:5px;<?php echo empty(trim($wtsr_rating_links['custom_link']['one_star'])) ? '' : ' display:none;'; ?>"
                        >
                            <strong><?php _e('Input custom link, please!', 'more-better-reviews-for-woocommerce'); ?></strong>
                        </p>
                    </div>
                </div>
            </div>

            <div class="wtsr-col-xs-12 wtsr-col-md-6">
                <h4 style="margin-top: 5px; margin-bottom: 5px;">
                    <label for="wtsr_ts_two_star_link" style="line-height: 20px;display: inline-block;vertical-align: top;">
                        <?php _e('Two star link', 'more-better-reviews-for-woocommerce'); ?>
                    </label>
                    <img title="Poor" src="<?php echo $url . '/admin/img/two_star.png'; ?>" alt="">
                </h4>

                <div class="wtsr_rating_link_item">
                    <select class="form-input wtsr_ts_star_link" name="wtsr_ts_two_star_link" id="wtsr_ts_two_star_link">
                        <?php
                        $wtsr_rating_link = $wtsr_rating_links['two_star'];

                        if ($is_woocommerce_only_mode && 'wtsr_ts_review_link' === $wtsr_rating_link) {
                            $wtsr_rating_link = 'wtsr_all_reviews_page';
                        }

                        if ($is_woocommerce_reviews_disabled_globally && 'wtsr_product_url' === $wtsr_rating_link) {
                            $wtsr_rating_link = 'wtsr_custom_link';
                        }

                        foreach ($rating_links_options as $value => $label) {
                            if (
                                (!$is_ts_mode && 'wtsr_ts_review_link' === $value) ||
                                ($is_woocommerce_reviews_disabled_globally && 'wtsr_product_url' === $value)
                            ) {
                                continue;
                            }
                            ?>
                            <option value="<?php echo $value ?>"<?php echo $value === $wtsr_rating_link ? ' selected' : ''; ?>><?php echo $label ?></option>
                            <?php
                        }
                        ?>
                    </select>

                    <div class="wtsr_rating_custom_link_holder" <?php echo 'wtsr_custom_link' === $wtsr_rating_link ? '' : ' style="display:none"'; ?>>
                        <input class="form-input" type="text" id="wtsr_ts_two_star_custom_link" name="wtsr_ts_two_star_custom_link" value="<?php echo $wtsr_rating_links['custom_link']['two_star'] ?>">

                        <p class="warning-text" style="margin-top:5px;<?php echo empty(trim($wtsr_rating_links['custom_link']['two_star'])) ? '' : ' display:none;'; ?>">
                            <strong><?php _e('Input custom link, please!', 'more-better-reviews-for-woocommerce'); ?></strong>
                        </p>
                    </div>
                </div>
            </div>

            <div class="wtsr-col-xs-12 wtsr-col-md-6">
                <h4 style="margin-top: 5px; margin-bottom: 5px;">
                    <label for="wtsr_ts_three_star_link" style="line-height: 20px;display: inline-block;vertical-align: top;">
                        <?php _e('Three star link', 'more-better-reviews-for-woocommerce'); ?>
                    </label>
                    <img title="Poor" src="<?php echo $url . '/admin/img/three_star.png'; ?>" alt="">
                </h4>

                <div class="wtsr_rating_link_item">
                    <select class="form-input wtsr_ts_star_link" name="wtsr_ts_three_star_link" id="wtsr_ts_three_star_link">
                        <?php
                        $wtsr_rating_link = $wtsr_rating_links['three_star'];

                        if ($is_woocommerce_only_mode && 'wtsr_ts_review_link' === $wtsr_rating_link) {
                            $wtsr_rating_link = 'wtsr_all_reviews_page';
                        }

                        if ($is_woocommerce_reviews_disabled_globally && 'wtsr_product_url' === $wtsr_rating_link) {
                            $wtsr_rating_link = 'wtsr_custom_link';
                        }

                        foreach ($rating_links_options as $value => $label) {
                            if (
                                (!$is_ts_mode && 'wtsr_ts_review_link' === $value) ||
                                ($is_woocommerce_reviews_disabled_globally && 'wtsr_product_url' === $value)
                            ) {
                                continue;
                            }
                            ?>
                            <option value="<?php echo $value ?>"<?php echo $value === $wtsr_rating_link ? ' selected' : ''; ?>><?php echo $label ?></option>
                            <?php
                        }
                        ?>
                    </select>

                    <div class="wtsr_rating_custom_link_holder" <?php echo 'wtsr_custom_link' === $wtsr_rating_link ? '' : ' style="display:none"'; ?>>
                        <input class="form-input" type="text" id="wtsr_ts_three_star_custom_link" name="wtsr_ts_three_star_custom_link" value="<?php echo $wtsr_rating_links['custom_link']['three_star'] ?>">

                        <p class="warning-text" style="margin-top:5px;<?php echo empty(trim($wtsr_rating_links['custom_link']['three_star'])) ? '' : ' display:none;'; ?>">
                            <strong><?php _e('Input custom link, please!', 'more-better-reviews-for-woocommerce'); ?></strong>
                        </p>
                    </div>
                </div>
            </div>

            <div class="wtsr-col-xs-12 wtsr-col-md-6">
                <h4 style="margin-top: 5px; margin-bottom: 5px;">
                    <label for="wtsr_ts_four_star_link" style="line-height: 20px;display: inline-block;vertical-align: top;">
                        <?php _e('Four star link', 'more-better-reviews-for-woocommerce'); ?>
                    </label>
                    <img title="Poor" src="<?php echo $url . '/admin/img/four_star.png'; ?>" alt="">
                </h4>

                <div class="wtsr_rating_link_item">
                    <select class="form-input wtsr_ts_star_link" name="wtsr_ts_four_star_link" id="wtsr_ts_four_star_link">
                        <?php
                        $wtsr_rating_link = $wtsr_rating_links['four_star'];

                        if ($is_woocommerce_only_mode && 'wtsr_ts_review_link' === $wtsr_rating_link) {
                            $wtsr_rating_link = 'wtsr_all_reviews_page';
                        }

                        if ($is_woocommerce_reviews_disabled_globally && 'wtsr_product_url' === $wtsr_rating_link) {
                            $wtsr_rating_link = 'wtsr_custom_link';
                        }

                        foreach ($rating_links_options as $value => $label) {
                            if (
                                (!$is_ts_mode && 'wtsr_ts_review_link' === $value) ||
                                ($is_woocommerce_reviews_disabled_globally && 'wtsr_product_url' === $value)
                            ) {
                                continue;
                            }
                            ?>
                            <option value="<?php echo $value ?>"<?php echo $value === $wtsr_rating_link ? ' selected' : ''; ?>><?php echo $label ?></option>
                            <?php
                        }
                        ?>
                    </select>

                    <div class="wtsr_rating_custom_link_holder" <?php echo 'wtsr_custom_link' === $wtsr_rating_link ? '' : ' style="display:none"'; ?>>
                        <input class="form-input" type="text" name="wtsr_ts_four_star_custom_link" id="wtsr_ts_four_star_custom_link" value="<?php echo $wtsr_rating_links['custom_link']['four_star'] ?>">

                        <p class="warning-text" style="margin-top:5px;<?php echo empty(trim($wtsr_rating_links['custom_link']['four_star'])) ? '' : ' display:none;'; ?>">
                            <strong><?php _e('Input custom link, please!', 'more-better-reviews-for-woocommerce'); ?></strong>
                        </p>
                    </div>
                </div>
            </div>

            <div class="wtsr-col-xs-12 wtsr-col-md-6">
                <h4 style="margin-top: 5px; margin-bottom: 5px;">
                    <label for="wtsr_ts_five_star_link" style="line-height: 20px;display: inline-block;vertical-align: top;">
                        <?php _e('Five star link', 'more-better-reviews-for-woocommerce'); ?>
                    </label>
                    <img title="Poor" src="<?php echo $url . '/admin/img/five_star.png'; ?>" alt="">
                </h4>

                <div class="wtsr_rating_link_item">
                    <select class="form-input wtsr_ts_star_link" name="wtsr_ts_five_star_link" id="wtsr_ts_five_star_link">
                        <?php
                        $wtsr_rating_link = $wtsr_rating_links['five_star'];

                        if ($is_woocommerce_only_mode && 'wtsr_ts_review_link' === $wtsr_rating_link) {
                            $wtsr_rating_link = 'wtsr_all_reviews_page';
                        }

                        if ($is_woocommerce_reviews_disabled_globally && 'wtsr_product_url' === $wtsr_rating_link) {
                            $wtsr_rating_link = 'wtsr_custom_link';
                        }

                        foreach ($rating_links_options as $value => $label) {
                            if (
                                (!$is_ts_mode && 'wtsr_ts_review_link' === $value) ||
                                ($is_woocommerce_reviews_disabled_globally && 'wtsr_product_url' === $value)
                            ) {
                                continue;
                            }
                            ?>
                            <option value="<?php echo $value ?>"<?php echo $value === $wtsr_rating_link ? ' selected' : ''; ?>><?php echo $label ?></option>
                            <?php
                        }
                        ?>
                    </select>

                    <div class="wtsr_rating_custom_link_holder" <?php echo 'wtsr_custom_link' === $wtsr_rating_link ? '' : ' style="display:none"'; ?>>
                        <input class="form-input" type="text" id="wtsr_ts_five_star_custom_link" name="wtsr_ts_five_star_custom_link" value="<?php echo $wtsr_rating_links['custom_link']['five_star'] ?>">

                        <p class="warning-text" style="margin-top:5px;<?php echo empty(trim($wtsr_rating_links['custom_link']['five_star'])) ? '' : ' display:none;'; ?>">
                            <strong><?php _e('Input custom link, please!', 'more-better-reviews-for-woocommerce'); ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-item">
        <div class="wtsr-row">
            <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-5">
                <h4 style="margin-top: 0;"><?php echo __('Available shortcodes for custom links', 'more-better-reviews-for-woocommerce'); ?></h4>

                <ul>
                    <li><strong>{order_number}</strong> - <?php echo __('order number', 'more-better-reviews-for-woocommerce'); ?></li>
                    <li><strong>{order_number_base64}</strong> - <?php echo __('order number encoded to Base64', 'more-better-reviews-for-woocommerce'); ?></li>
                    <li><strong>{product_id}</strong> - <?php echo __('product id', 'more-better-reviews-for-woocommerce'); ?></li>
                    <li><strong>{product_title}</strong> - <?php echo __('product title', 'more-better-reviews-for-woocommerce'); ?></li>
                    <li><strong>{product_slug}</strong> - <?php echo __('product slug', 'more-better-reviews-for-woocommerce'); ?></li>
                    <li><strong>{customer_fn}</strong> - <?php echo __('customer first name', 'more-better-reviews-for-woocommerce'); ?></li>
                    <li><strong>{customer_ln}</strong> - <?php echo __('customer last name', 'more-better-reviews-for-woocommerce'); ?></li>
                    <li><strong>{customer_email}</strong> - <?php echo __('customer email', 'more-better-reviews-for-woocommerce'); ?></li>
                    <li><strong>{customer_email_base64}</strong> - <?php echo __('customer email encoded to Base64', 'more-better-reviews-for-woocommerce'); ?></li>
                </ul>
            </div>

            <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-7">
                <h4 style="margin-top: 0;"><?php echo __('For our example:', 'more-better-reviews-for-woocommerce'); ?></h4>
                <ul>
                    <li><em>https://your-domain.com?order_number=<strong>{order_number}</strong></em></li>
                    <li><em>https://your-domain.com?product_id=<strong>{product_id}</strong>&product_slug=<strong>{product_slug}</strong>&product_title=<strong>{product_title}</strong></em></li>
                    <li><em>https://your-domain.com?some_param=param_value&first_name=<strong>{customer_fn}</strong>&last_name=<strong>{customer_ln}</strong>&email=<strong>{customer_email}</strong></em></li>
                </ul>
            </div>
        </div>
        <div class="settings-item-description">
        </div>
    </div>

    <div class="settings-control">
        <button id="wizard-wtsr_rating_links_settings" class="button button-primary" type="button">
            <?php _e('Save changes', 'more-better-reviews-for-woocommerce'); ?>
        </button>
    </div>
</div>