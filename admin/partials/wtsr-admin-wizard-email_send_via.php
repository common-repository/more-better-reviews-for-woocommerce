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
?>

<div id="wtsr_email_send_via_container" class="settings-container">
    <div class="settings-item">
        <p class="settings-item-label"><?php _e('Email will be send via', 'more-better-reviews-for-woocommerce') ?></p>

        <div class="settings-item-value">

            <select class="form-input" id="wtsr_email_send_via">
                <option value="klick-tipp"<?php echo 'klick-tipp' === $wtsr_email_send_via ? ' selected' : ''; ?>><?php _e('Klick Tipp', 'more-better-reviews-for-woocommerce'); ?></option>
                <option value="woocommerce"<?php echo 'woocommerce' === $wtsr_email_send_via ? ' selected' : ''; ?>><?php _e('WooCommerce', 'more-better-reviews-for-woocommerce'); ?></option>
            </select>
        </div>

        <p class="settings-item-description">
            <?php _e('Select how you are going to send review requests to your clients. You can use <strong>Klick Tipp</strong> or <strong>WooCommerce</strong> email options.', 'more-better-reviews-for-woocommerce'); ?>
        </p>

    <?php
    if (!$is_wp2leads_installed) {
        ?>
        <div class="wtsr_email_send_via_klick-tipp_container wtsr_email_send_via_container" style="<?php echo 'klick-tipp' !== $wtsr_email_send_via ? 'display:none;' : ''; ?>">
            <div class="wtsr-processing-holder">
                <div class="required-plugin-container">
                    <p class="settings-item-description">
                        <?php _e('In order to send review requests with <a href="https://klick.santegra-international.com/api/split/1ilwz7uaz1fzkze798" target="_blank">Klick Tipp</a> Wp2Leads plugin must be installed and activated, otherwise select WooCommerce option.', 'more-better-reviews-for-woocommerce'); ?>
                    </p>
                    <div class="required-plugin-holder settings-item-description">
                        <div class="required-plugin-holder-inner">
                            <div class="img-holder">
                                <img src="<?php echo untrailingslashit( plugins_url( '/', WTSR_PLUGIN_FILE ) ) . '/admin/img/icon-'.'wp2leads'.'.png'; ?>" alt="">
                            </div>

                            <div class="info-holder">
                                <h3><a href="<?php echo $required_plugins_wp2leads['link'] ?>" target="_blank"><?php echo $required_plugins_wp2leads['label'] ?></a></h3>
                                <p><?php echo $required_plugins_wp2leads['author'] ?></p>
                            </div>

                            <div class="button-holder">
                                <button id="install-wp2leads" class="wtsr-install-plugin-wp2leads button button-primary" data-plugin="wp2leads">
                                    <?php _e('Install and Activate', 'more-better-reviews-for-woocommerce'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wtsr-spinner-holder">
                    <div class="wtsr-spinner"></div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    </div>

    <div class="settings-item wtsr_email_send_via_woocommerce_container wtsr_email_send_via_container" style="<?php echo 'woocommerce' !== $wtsr_email_send_via ? 'display:none;' : ''; ?>">

        <p class="settings-item-label">
            <?php _e('WooCommerce Email Delay (days)', 'more-better-reviews-for-woocommerce'); ?>
        </p>

        <div class="settings-item-value">
            <input
                    class="form-input"
                    type="text"
                    id="wtsr_email_send_via_woocommerce_delay"
                    value="<?php echo $wtsr_email_send_via_woocommerce_delay; ?>"
                <?php echo 'woocommerce' !== $wtsr_email_send_via ? 'disabled' : ''; ?>
            >

            <p class="settings-item-description">
                <?php _e('Set up delay for sending review requests for Order status set in "Settings".', 'more-better-reviews-for-woocommerce'); ?>
            </p>

            <p class="wtsr_email_send_via_woocommerce_container wtsr_email_send_via_container settings-item-description" style="margin-bottom:5px;<?php echo 'woocommerce' !== $wtsr_email_send_via ? 'display:none;' : ''; ?>">
                <strong><?php _e( 'You can edit email subject, heading, add additional content or change email type <a href="?page=wc-settings&tab=email&section=wtsr_wc_email_review_request" target="_blank">here</a>', 'more-better-reviews-for-woocommerce' ); ?></strong>
            </p>

            <p class="settings-item-description" style="margin-bottom:5px;">
                <em>
                    <?php _e('For our example', 'more-better-reviews-for-woocommerce'); ?>: <?php _e('Order status for generating review request is set to', 'more-better-reviews-for-woocommerce'); ?>
                    <strong>"<?php echo _x( 'Completed', 'Order status', 'woocommerce' ); ?>"</strong>.
                </em>
            </p>

            <ul class="settings-item-description" style="margin-top:0;margin-bottom:5px;">
                <li>
                    <code><?php _e('empty value', 'more-better-reviews-for-woocommerce'); ?></code> - <?php _e('Email will be sent immediately after order Completed', 'more-better-reviews-for-woocommerce'); ?>
                </li>
                <li>
                    <code>3</code> - <?php _e('Email will be sent in 3 days after order Completed', 'more-better-reviews-for-woocommerce'); ?>
                </li>
                <li>
                    <code>0:3</code> - <?php _e('First email will be sent immediately after order Completed. If order not reviewed within 3 days after first review request, email will be sent again.', 'more-better-reviews-for-woocommerce'); ?>
                </li>
                <li>
                    <code>3:7:11</code> - <?php _e('First email will be sent after 3 days after order Completed. If order not reviewed within 7 days after first review request, email will be sent again. If order not reviewed within 11 days after second review request, email will be sent for the last time. ', 'more-better-reviews-for-woocommerce'); ?>
                </li>
                <li>
                    <?php _e('Any of values should not be more than number of days in "Generate request not often than (days)" on "Settings" tab, otherwise review request will be <strong>outdated</strong> (sent but not reviewed) or <strong>canceled</strong> (not sent in timeframe).', 'more-better-reviews-for-woocommerce'); ?>
                </li>
            </ul>
        </div>

    </div>

    <div class="settings-control">
        <button id="wtsr_save_email_service_wizard" class="button<?php echo !$is_wp2leads_installed && 'klick-tipp' === $wtsr_email_send_via ? '' : ' button-primary' ?>" type="button"<?php echo !$is_wp2leads_installed && 'klick-tipp' === $wtsr_email_send_via ? ' disabled' : '' ?>>
            <?php _e('Save settings', 'more-better-reviews-for-woocommerce'); ?>
        </button>
    </div>
</div>