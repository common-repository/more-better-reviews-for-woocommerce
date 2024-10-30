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

$wtsr_thankyou_settings = Wtsr_Settings::get_thankyou_settings();
?>

<div class="settings-container">
    <form  id="thankyou-settings_form" method="post">
        <div class="wtsr-row">
            <div class="wtsr-col-xs-12 wtsr-col-md-6">
                <h4><?php _e('Enable Thank you email', 'more-better-reviews-for-woocommerce') ?></h4>
            </div>

            <div class="wtsr-col-xs-12 wtsr-col-md-6">
                <p>
                    <select name="thankyou_enabled" id="wtsr_thankyou_enabled" class="form-input select wtsr_dependency_control" data-enabled="yes" data-dependency="wtsr_thankyou_enabled_dependency">
                        <option value="no"<?php echo $wtsr_thankyou_settings['thankyou_enabled'] === 'no' ? ' selected' : ''; ?>><?php echo __( 'Disabled', 'woocommerce' ); ?></option>
                        <option value="yes"<?php echo $wtsr_thankyou_settings['thankyou_enabled'] === 'yes' ? ' selected' : ''; ?>><?php echo __( 'Enabled', 'woocommerce' ); ?></option>
                    </select>
                </p>
            </div>

            <div class="wtsr-col-xs-12">
                <p>
                    <?php _e('In this section you can set up sending Thank you email after customers review is published.', 'more-better-reviews-for-woocommerce') ?>
                </p>
            </div>
        </div>

        <div class="wtsr-row wtsr_thankyou_enabled_dependency"<?php echo $wtsr_thankyou_settings['thankyou_enabled'] === 'no' ? ' style="display:none;"' : ''; ?>>
            <div class="wtsr-col-xs-12">
                <h4><?php _e('Thank you email template', 'more-better-reviews-for-woocommerce'); ?></h4>

                <?php wp_editor( wp_unslash($wtsr_thankyou_settings['thankyou_template']), 'thankyou_template', array(
                    'textarea_rows' => 8,
                    'wpautop'       => 1,
                ) ); ?>

                <h4><?php echo __('Available shortcodes', 'more-better-reviews-for-woocommerce'); ?></h4>

                <ul>
                    <li><strong>{coupon_description}</strong> - <?php echo __('coupon description (you need to set up settings for coupon)', 'more-better-reviews-for-woocommerce'); ?></li>
                </ul>
            </div>
        </div>

        <div id="wtsr_coupon_settings" class="wtsr_thankyou_enabled_dependency <?php echo $wtsr_thankyou_settings['discount_type'] === 'none' ? 'setting_disabled' : ''; ?>"<?php echo $wtsr_thankyou_settings['thankyou_enabled'] === 'no' ? ' style="display:none;"' : ''; ?>>
            <div>
                <div class="wtsr-row">
                    <div class="wtsr-col-xs-12">
                        <h3 style="text-align: center;"><?php echo __( 'Coupon settings', 'woocommerce' ); ?></h3>
                    </div>
                </div>

                <div class="wtsr-row">
                    <div class="wtsr-col-xs-12 wtsr-col-md-4">
                        <h4 style="margin-top: 8px; margin-bottom: 10px;">
                            <?php echo __( 'Discount type', 'woocommerce' ); ?>
                        </h4>

                        <select id="wtsr_discount_type" name="discount_type" class="form-input select">
                            <option value="none"<?php echo $wtsr_thankyou_settings['discount_type'] === 'none' ? ' selected' : ''; ?>><?php echo __( '-- Select discount type --', 'more-better-reviews-for-woocommerce' ); ?></option>
                            <option value="fixed_cart"<?php echo $wtsr_thankyou_settings['discount_type'] === 'fixed_cart' ? ' selected' : ''; ?>><?php echo __( 'Fixed cart discount', 'woocommerce' ); ?></option>
                            <option value="percent"<?php echo $wtsr_thankyou_settings['discount_type'] === 'percent' ? ' selected' : ''; ?>><?php echo __( 'Percentage discount', 'woocommerce' ); ?></option>
                        </select>
                    </div>

                    <div class="wtsr-col-xs-12 wtsr-col-md-4 need-enabled">
                        <h4 style="margin-top: 8px; margin-bottom: 10px;">
                            <?php echo __( 'Coupon amount', 'woocommerce' ); ?>
                        </h4>

                        <input class="form-input" type="number" id="wtsr_coupon_amount" name="coupon_amount" value="<?php echo $wtsr_thankyou_settings['coupon_amount']; ?>">
                    </div>

                    <div class="wtsr-col-xs-12 wtsr-col-md-4 need-enabled">
                        <h4 style="margin-top: 8px; margin-bottom: 10px;">
                            <?php echo __( 'Coupon expiration', 'more-better-reviews-for-woocommerce' ); ?> (<?php _e('hours', 'more-better-reviews-for-woocommerce'); ?>)
                        </h4>

                        <input class="form-input" type="number" id="wtsr_coupon_expiration" name="coupon_expiration" value="<?php echo $wtsr_thankyou_settings['coupon_expiration']; ?>">
                    </div>
                </div>

                <div class="wtsr-row need-enabled" style="margin-top: 25px;">
                    <div class="wtsr-col-xs-12 wtsr-col-md-4">
                        <h4 style="margin-top: 8px; margin-bottom: 10px;">
                            <?php echo __( 'Coupon description', 'more-better-reviews-for-woocommerce' ); ?>
                        </h4>
                    </div>

                    <div class="wtsr-col-xs-12 wtsr-col-md-8">
                        <textarea class="form-input" name="coupon_description" id="wtsr_coupon_description" rows="5"><?php echo $wtsr_thankyou_settings['coupon_description']; ?></textarea>
                    </div>
                </div>
            </div>

            <div class="wtsr-row need-enabled">
                <div class="wtsr-col-xs-12">
                    <h4><?php echo __('Available shortcodes for coupon description', 'more-better-reviews-for-woocommerce'); ?></h4>

                    <ul>
                        <li><strong>{coupon_url}</strong> - <?php echo __('coupon URL', 'more-better-reviews-for-woocommerce'); ?></li>
                        <li><strong>{coupon_code}</strong> - <?php echo __('coupon code', 'more-better-reviews-for-woocommerce'); ?></li>
                        <li><strong>{coupon_hours}</strong> - <?php echo __('hours until coupon will expire (you need to set Coupon expiration hours field)', 'more-better-reviews-for-woocommerce'); ?></li>
                        <li><strong>{coupon_date_time_expires}</strong> - <?php echo __('Date and Time until coupon is valid (you need to set Coupon expiration hours field)', 'more-better-reviews-for-woocommerce'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="wtsr-row need-enabled">
                <div class="wtsr-col-xs-12">
                    <div class="wtsr-row">
                        <div class="wtsr-col-xs-12 wtsr-col-md-6">
                            <h4><?php _e('Coupon countdown', 'more-better-reviews-for-woocommerce'); ?></h4>

                            <p>
                                <select
                                        name="coupon_countdown"
                                        id="wtsr_coupon_countdown"
                                        class="form-input select wtsr_dependency_control"
                                        data-enabled="yes"
                                        data-dependency="wtsr_coupon_countdown_enabled_dependency"
                                >
                                    <option value="no"<?php echo $wtsr_thankyou_settings['coupon_countdown'] === 'no' ? ' selected' : ''; ?>><?php _e('Disabled', 'more-better-reviews-for-woocommerce'); ?></option>
                                    <option value="yes"<?php echo $wtsr_thankyou_settings['coupon_countdown'] === 'yes' ? ' selected' : ''; ?>><?php _e('Enabled', 'more-better-reviews-for-woocommerce'); ?></option>
                                </select>
                            </p>
                        </div>

                        <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr_coupon_countdown_enabled_dependency"<?php echo $wtsr_thankyou_settings['coupon_countdown'] === 'no' ? ' style="display:none;"' : ''; ?>>
                            <h4><?php _e('Coupon countdown period (hours)', 'more-better-reviews-for-woocommerce'); ?></h4>

                            <p>
                                <input class="form-input" type="number" id="wtsr_coupon_countdown_period" name="coupon_countdown_period" value="<?php echo $wtsr_thankyou_settings['coupon_countdown_period']; ?>">
                            </p>
                        </div>
                    </div>

                    <div class="wtsr-row wtsr_coupon_countdown_enabled_dependency"<?php echo $wtsr_thankyou_settings['coupon_countdown'] === 'no' ? ' style="display:none;"' : ''; ?>>
                        <div class="wtsr-col-xs-12 wtsr-col-md-6">
                            <h4><?php _e('Coupon countdown reset (hours)', 'more-better-reviews-for-woocommerce'); ?></h4>

                            <p>
                                <input class="form-input" type="number" id="wtsr_coupon_countdown_period_reset" name="coupon_countdown_period_reset" value="<?php echo $wtsr_thankyou_settings['coupon_countdown_period_reset']; ?>">
                            </p>

                            <p>
                                <?php _e('This setting allow your customer to have another chance to get coupon for review. F.e. if you send review request email more then once.', 'more-better-reviews-for-woocommerce'); ?>
                            </p>
                        </div>

                        <div class="wtsr-col-xs-12 wtsr-col-md-6">
                            <h4><?php _e('Coupon countdown description', 'more-better-reviews-for-woocommerce'); ?></h4>

                            <textarea class="form-input" name="coupon_countdown_description" id="wtsr_coupon_countdown_description" rows="5"><?php echo wp_unslash($wtsr_thankyou_settings['coupon_countdown_description']); ?></textarea>

                            <p><?php _e('You can use this description in your review request template with placeholder <strong>{coupon_countdown_description}</strong>', 'more-better-reviews-for-woocommerce'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="wtsr-col-xs-12 wtsr_coupon_countdown_enabled_dependency"<?php echo $wtsr_thankyou_settings['coupon_countdown'] === 'no' ? ' style="display:none;"' : ''; ?>>
                    <h4><?php echo __('Available shortcodes for coupon description', 'more-better-reviews-for-woocommerce'); ?></h4>

                    <ul>
                        <li><strong>{coupon_countdown_period}</strong></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="wtsr-row">
            <div class="wtsr-col-xs-12" style="margin-top: 15px;">
                <button id="wtsr_save_thankyou_settings" class="button<?php echo !$is_wp2leads_installed && 'klick-tipp' === $wtsr_email_send_via ? '' : ' button-primary' ?>" type="button"<?php echo !$is_wp2leads_installed && 'klick-tipp' === $wtsr_email_send_via ? ' disabled' : '' ?>>
                    <?php _e('Save changes', 'more-better-reviews-for-woocommerce'); ?>
                </button>
            </div>
        </div>
    </form>
</div>