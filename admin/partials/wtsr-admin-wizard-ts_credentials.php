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

$is_woocommerce_only_mode = ReviewServiceManager::is_woocommerce_only_mode();
$is_ts_mode = TSManager::is_review_mode_enabled();
$is_woocommerce_only_mode = !$is_ts_mode;

/**
 * @var $ts_id
 * @var $ts_email
 * @var $ts_password
 * @var $ts_mode_enabled
 */
$ts_credentials = TSManager::get_ts_credentials();
extract($ts_credentials);

$ts_error_message = '';
$ts_cred_error = get_option('wtsr_check_ts_credentials');

if (!empty($ts_cred_error)) {
    $ts_error_message = $ts_cred_error;
}

$tutorial_link = '';
?>

<form id="wtsr_save_wizard_credential_form">
    <div class="wtsr-row">
        <div class="wtsr-col-xs-12">
            <p>
                <?php _e('By default <strong>WooCommerce only mode</strong> is enabled to get reviews from your customers. If you want more from your customers reviews <a href="?page=wp2leads-wtsr&tab=overview" target="_blank">click here for the overview!</a>', 'more-better-reviews-for-woocommerce'); ?>
                <?php _e('You can get reviews from <strong>Trusted Shops</strong> - The European trust brand in e-commerce using their API.', 'more-better-reviews-for-woocommerce'); ?>
                <?php _e('If you already have your Trusted Shops ID and API credential, please input them in the form below.', 'more-better-reviews-for-woocommerce'); ?>
            </p>

            <p>
                <?php echo sprintf(__('Watch our tutorials <a href="%s">here</a> if you want to get to know how get your own <strong>Trusted Shops ID</strong> and <strong>API credentials</strong>.', 'more-better-reviews-for-woocommerce') , $tutorial_link ); ?>
            </p>
        </div>
    </div>

    <div class="compact">
        <div class="wtsr-row">
            <div class="wtsr-col-xs-12 wtsr-col-sm-12">
                <!-- Trusted Shops pilot -->
                <div class="wtsr-row">
                    <div class="wtsr-col-xs-12">
                        <h3>
                            <?php echo __('Trusted Shops credentials', 'more-better-reviews-for-woocommerce'); ?>
                        </h3>
                    </div>
                </div>

                <div class="wtsr-row">
                    <div class="wtsr-col-xs-12 wtsr-col-sm-4 wtsr-col-md-5 wtsr-col-lg-4">
                        <h4>
                            <label for="wtsr_ts_id"><?php _e('ID', 'more-better-reviews-for-woocommerce'); ?></label>
                        </h4>
                    </div>

                    <div class="wtsr-col-xs-12 wtsr-col-sm-8 wtsr-col-md-7 wtsr-col-lg-8">
                        <p>
                            <input class="form-input" name="wtsr_ts_id" id="wtsr_ts_id" type="text" value="<?php echo $ts_id; ?>">
                        </p>
                    </div>
                </div>

                <div class="wtsr-row">
                    <div class="wtsr-col-xs-12 wtsr-col-sm-4 wtsr-col-md-5 wtsr-col-lg-4">
                        <h4>
                            <label for="wtsr_ts_email"><?php _e('Email', 'more-better-reviews-for-woocommerce'); ?></label>
                        </h4>
                    </div>

                    <div class="wtsr-col-xs-12 wtsr-col-sm-8 wtsr-col-md-7 wtsr-col-lg-8">
                        <p>
                            <input class="form-input" name="wtsr_ts_email" id="wtsr_ts_email" type="email" value="<?php echo $ts_email; ?>">
                        </p>
                    </div>
                </div>

                <div class="wtsr-row">
                    <div class="wtsr-col-xs-12 wtsr-col-sm-4 wtsr-col-md-5 wtsr-col-lg-4">
                        <h4>
                            <label for="wtsr_ts_password"><?php _e('Password', 'more-better-reviews-for-woocommerce'); ?></label>
                        </h4>
                    </div>

                    <div class="wtsr-col-xs-12 wtsr-col-sm-8 wtsr-col-md-7 wtsr-col-lg-8">
                        <p>
                            <input class="form-input" name="wtsr_ts_password" id="wtsr_ts_password" type="password" value="<?php echo $ts_password; ?>">
                        </p>
                    </div>
                </div>

                <div class="wtsr-row">
                    <div class="wtsr-col-xs-12 wtsr-col-sm-4 wtsr-col-md-5 wtsr-col-lg-4">
                        <h4>
                            <label for="wtsr_ts_mode_enabled"><?php _e('Enable review request', 'more-better-reviews-for-woocommerce'); ?></label>
                        </h4>
                    </div>

                    <div class="wtsr-col-xs-12 wtsr-col-sm-8 wtsr-col-md-7 wtsr-col-lg-8">
                        <p>
                            <?php
                            $checked = !empty($ts_mode_enabled) ? ' checked="checked"' : ''
                            ?>
                            <input name="wtsr_ts_mode_enabled" id="wtsr_ts_mode_enabled" type="checkbox"<?php echo $checked; ?>>
                        </p>
                    </div>

                    <?php
                    if (!empty($ts_error_message)) {
                        ?>
                        <div class="wtsr-row">
                            <div class="wtsr-col-xs-12 wtsr-col-sm-12 wtsr-col-md-6 wtsr-col-lg-4"></div>
                            <div class="wtsr-col-xs-12 wtsr-col-sm-12 wtsr-col-md-6 wtsr-col-lg-8">
                                <p class="warning-text"><strong><?php echo $ts_error_message; ?></strong></p>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Buttons -->
    <div class="wtsr-row">
        <div class="wtsr-col-xs-12">
            <p style="text-align: center;">
                <button id="wizard-wtsr_save_credential" class="button button-primary" type="button">
                    <?php _e('Save credentials', 'more-better-reviews-for-woocommerce'); ?>
                </button>

                <?php
                if (!$is_woocommerce_only_mode) {
                    ?>
                    <button id="wizard-wtsr_enable_woocommerce_only" class="button button-primary" type="button"
                            data-confirm-message="<?php _e('Are you sure? Review requests with third party services would be disabled.', 'more-better-reviews-for-woocommerce'); ?>"
                    >
                        <?php _e('Enable WooCommerce only mode', 'more-better-reviews-for-woocommerce'); ?>
                    </button>
                    <?php
                }
                ?>
            </p>
        </div>
    </div>
</form>