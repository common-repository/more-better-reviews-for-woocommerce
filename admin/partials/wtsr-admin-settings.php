<?php
/**
 * Provide a admin area view for the plugin
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/admin/partials
 * @var $is_woocommerce_reviews_disabled_globally
 * @var $is_woocommerce_only_mode
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$order_statuses = wc_get_order_statuses();

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

$license_info = Wtsr_License::get_lecense_info();
$activation_in_progress = get_transient( 'wtsr_activation_in_progress' );
$check_ts_credentials = get_transient('wtsr_check_ts_credentials');

$selected_order_status = get_option('wtsr_order_status', 'wc-completed');
$selected_review_period = get_option('wtsr_review_period', '30');
$selected_review_ask = get_option('wtsr_review_ask', 'no');
$selected_review_variations = get_option('wtsr_review_variations', 'no');
$review_ask_template_editor = get_option('wtsr_review_ask_template_editor', TSManager::get_default_review_ask_template_editor());
$selected_filter_email_domain = get_option('wtsr_filter_email_domain', '');
$wtsr_review_approved = get_option('wtsr_review_approved', 'yes');

if (!empty($selected_filter_email_domain) && is_array($selected_filter_email_domain)) {
    $selected_filter_email_domain = implode("\r\n", $selected_filter_email_domain);
}
?>

<div class="wtsr-page-header with-btn-holder">
    <h3><?php _e('Settings', 'more-better-reviews-for-woocommerce'); ?></h3>

    <div class="buttons-holder">
        <span style="display:inline-block;line-height:28px;margin:0 5px 0 0;">
            <strong><?php _e( 'Next step', 'more-better-reviews-for-woocommerce' ) ?>:</strong>
        </span>

        <a href="?page=wp2leads-wtsr&tab=email" class="button button-success">
            <?php _e('Email template settings', 'more-better-reviews-for-woocommerce') ?>
        </a>
    </div>
</div>

<?php
$show_license_info = true;

if (function_exists('mbrfw_fs') && mbrfw_fs()->is_paying__fs__()) {
    $show_license_info = false;
}
if ($show_license_info) {
    ?>
    <form>
        <input type="hidden" name="wtsr_settings_license" value="1">

        <table class="table-settings">
            <thead>
            <tr>
                <th colspan="3">
                    <?php _e('Digistore24 license', 'more-better-reviews-for-woocommerce') ?>
                    <?php _e('(optional)', 'more-better-reviews-for-woocommerce') ?>
                </th>
            </tr>
            </thead>

            <tbody>
            <tr>
                <th><?php _e('Email', 'more-better-reviews-for-woocommerce'); ?></th>
                <td>
                    <input class="form-input" name="wtsr_license_email" id="wtsr_license_email" type="email" value="<?php echo $license_info['email']; ?>">
                </td>
                <td></td>
            </tr>

            <tr>
                <th><?php _e('License key', 'more-better-reviews-for-woocommerce'); ?></th>
                <td>
                    <input class="form-input" name="wtsr_license_key" id="wtsr_license_key" type="text" value="<?php echo $activation_in_progress ? $license_info['key'] : $license_info['secured_key']; ?>">
                </td>
                <td></td>
            </tr>

            <tr>
                <th></th>
                <td colspan="3">
                    <button data-license-action="activate" class="button button-primary license-action-btn" type="button">
                        <?php _e('Activate', 'more-better-reviews-for-woocommerce'); ?>
                    </button>

                    <button data-license-action="deactivate" class="button button-primary license-action-btn" type="button">
                        <?php _e('Deactivate', 'more-better-reviews-for-woocommerce'); ?>
                    </button>

                    <?php
                    if ($activation_in_progress) {
                        ?>
                        <button data-license-action="close-license" class="button button-danger license-action-btn" type="button">
                            <?php _e('Close manage licenses', 'more-better-reviews-for-woocommerce'); ?>
                        </button>
                        <?php
                    }
                    ?>
                </td>
            </tr>

            <?php
            if ($activation_in_progress) {
                $site_list = Wtsr_License::get_license_list();

                if ($site_list) {
                    ?>
                    <tr>
                        <th><?php _e('List of sites', 'more-better-reviews-for-woocommerce'); ?></th>
                        <td>
                            <?php
                            if (is_array($site_list)) {
                                ?>
                                <table>
                                    <tr>
                                        <td colspan="3">
                                            <p><?php echo __('Total licenses', 'wp2leads') ?>: <strong><?php echo  Wtsr_License::count_licenses(); ?></strong></p>
                                            <p><?php echo __('Available licenses', 'wp2leads') ?>: <strong><?php echo  Wtsr_License::count_licenses(false); ?></strong></p>
                                        </td>
                                    </tr>
                                    <?php
                                    foreach ($site_list as $site) {
                                        if ($site['site_url'] === Wtsr_License::get_current_site()) {
                                            $status = __('Current', 'wp2leads');
                                            $current = true;
                                        } else {
                                            $status = $site['status'] === '1' ? __('Active', 'wp2leads') : __('Disabled', 'wp2leads');
                                            $current = false;
                                        }
                                        ?>
                                        <tr>
                                            <td style="padding: 5px;"><strong><?php echo $site['site_url'] ?></strong></td>
                                            <td style="padding: 5px;"><?php echo $status ?></td>
                                            <td style="padding: 5px;">
                                                <?php
                                                if (!$current) {
                                                    ?>
                                                    <button class="button wtsr_remove_license button-small button-danger" data-site="<?php echo $site['site_url'] ?>" type="button"><?php _e( 'Remove', 'wp2leads' ) ?></button>
                                                    <?php
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </table>
                                <?php
                            } else {
                                echo $site_list;
                            }
                            ?>
                        </td>
                        <td></td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
    </form>
    <?php
}
?>

<form id="wtsr_save_credential_form">
    <div class="wtsr-settings-group">
        <div class="wtsr-settings-group-header">
            <h3><?php _e('Reviews services integration', 'more-better-reviews-for-woocommerce'); ?></h3>

            <p style="margin-bottom: 0;">
                <?php _e('By default <strong>WooCommerce only mode</strong> is enabled to get reviews from your customers. If you want more from your customers reviews <a href="?page=wp2leads-wtsr&tab=overview" target="_blank">click here for the overview!</a>', 'more-better-reviews-for-woocommerce'); ?>
            </p>
        </div>

        <div class="wtsr-settings-group-body compact">
            <div class="wtsr-row">
                <div class="wtsr-col-xs-12 wtsr-col-sm-12">
                    <div class="wtsr-row">
                        <div class="wtsr-col-xs-12">
                            <h3>
                                <?php _e('Trusted Shops credentials', 'more-better-reviews-for-woocommerce') ?>
                                <?php _e('(optional)', 'more-better-reviews-for-woocommerce') ?>
                            </h3>

                            <p style="margin-bottom: 15px;">
                                <?php _e('You can get reviews from <strong>Trusted Shops</strong> - The European trust brand in e-commerce using their API.', 'more-better-reviews-for-woocommerce'); ?>
                                <?php _e('If you already have your Trusted Shops ID and API credential, please input them in the form below.', 'more-better-reviews-for-woocommerce'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="wtsr-row">
                        <div class="wtsr-col-xs-12 wtsr-col-sm-12 wtsr-col-md-6 wtsr-col-lg-4">
                            <h4>
                                <label for="wtsr_ts_id"><?php _e('ID', 'more-better-reviews-for-woocommerce'); ?></label>
                            </h4>
                        </div>
                        <div class="wtsr-col-xs-12 wtsr-col-sm-12 wtsr-col-md-6 wtsr-col-lg-8">
                            <p>
                                <input class="form-input" name="wtsr_ts_id" id="wtsr_ts_id" type="text" value="<?php echo $ts_id; ?>">
                            </p>
                        </div>
                    </div>

                    <div class="wtsr-row">
                        <div class="wtsr-col-xs-12 wtsr-col-sm-12 wtsr-col-md-6 wtsr-col-lg-4">
                            <h4>
                                <label for="wtsr_ts_email"><?php _e('Email', 'more-better-reviews-for-woocommerce'); ?></label>
                            </h4>
                        </div>
                        <div class="wtsr-col-xs-12 wtsr-col-sm-12 wtsr-col-md-6 wtsr-col-lg-8">
                            <p>
                                <input class="form-input" name="wtsr_ts_email" id="wtsr_ts_email" type="email" value="<?php echo $ts_email; ?>">
                            </p>
                        </div>
                    </div>

                    <div class="wtsr-row">
                        <div class="wtsr-col-xs-12 wtsr-col-sm-12 wtsr-col-md-6 wtsr-col-lg-4">
                            <h4>
                                <label for="wtsr_ts_password"><?php _e('Password', 'more-better-reviews-for-woocommerce'); ?></label>
                            </h4>
                        </div>
                        <div class="wtsr-col-xs-12 wtsr-col-sm-12 wtsr-col-md-6 wtsr-col-lg-8">
                            <p>
                                <input class="form-input" name="wtsr_ts_password" id="wtsr_ts_password" type="password" value="<?php echo $ts_password; ?>">
                            </p>
                        </div>
                    </div>

                    <div class="wtsr-row">
                        <div class="wtsr-col-xs-12 wtsr-col-sm-12 wtsr-col-md-6 wtsr-col-lg-4">
                            <h4>
                                <label for="wtsr_ts_mode_enabled"><?php _e('Enable review request', 'more-better-reviews-for-woocommerce'); ?></label>
                            </h4>
                        </div>
                        <div class="wtsr-col-xs-12 wtsr-col-sm-12 wtsr-col-md-6 wtsr-col-lg-8">
                            <p>
                                <?php
                                $checked = !empty($ts_mode_enabled) ? ' checked="checked"' : ''
                                ?>
                                <input name="wtsr_ts_mode_enabled" id="wtsr_ts_mode_enabled" type="checkbox"<?php echo $checked; ?>>
                            </p>
                        </div>
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

        <div class="wtsr-settings-group-footer">
            <div class="wtsr-row">
                <div class="wtsr-col-xs-12">

                    <p style="text-align: center;">
                        <button id="wtsr_save_credential" class="button button-primary" type="button">
                            <?php _e('Save credentials', 'more-better-reviews-for-woocommerce'); ?>
                        </button>

                        <?php
                        if (empty($is_woocommerce_only_mode)) {
                            ?>
                            <button id="wtsr_woocommerce_only_mode_enable" class="button button-primary" type="button">
                                <?php _e('Enable WooCommerce only mode', 'more-better-reviews-for-woocommerce'); ?>
                            </button>
                            <?php
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>


<form method="post">
    <input type="hidden" name="wtsr_settings_save" value="1">
    <input type="hidden" name="wtsr_settings_general" value="1">

    <?php

    if (false) {
        ?>
        <table class="table-settings">
            <thead>
            <tr>
                <th colspan="3">
                    <?php _e('Trusted Shops credentials', 'more-better-reviews-for-woocommerce') ?>
                    <?php _e('(optional)', 'more-better-reviews-for-woocommerce') ?>
                </th>
            </tr>
            </thead>

            <tbody>
            <tr>
                <th><?php _e('ID', 'more-better-reviews-for-woocommerce'); ?></th>
                <td>
                    <input class="form-input" name="wtsr_ts_id" id="wtsr_ts_id" type="text" value="<?php echo $ts_id; ?>">
                </td>
                <td></td>
            </tr>

            <tr>
                <th><?php _e('Email', 'more-better-reviews-for-woocommerce'); ?></th>
                <td>
                    <input class="form-input" name="wtsr_ts_email" id="wtsr_ts_email" type="email" value="<?php echo $ts_email; ?>">
                </td>
                <td></td>
            </tr>

            <tr>
                <th><?php _e('Password', 'more-better-reviews-for-woocommerce'); ?></th>
                <td>
                    <input class="form-input" name="wtsr_ts_password" id="wtsr_ts_password" type="password" value="<?php echo $ts_password; ?>">
                </td>
                <td></td>
            </tr>

            <?php
            if (empty($ts_id) || empty($ts_email) || empty($ts_password)) {
                ?>
                <tr>
                    <th></th>
                    <td colspan="3">
                        <strong class="warning-text">
                            <?php _e('Please, fill in all Trusted Shops credential fields', 'more-better-reviews-for-woocommerce'); ?>
                        </strong>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <th></th>
                <td colspan="3">
                    <button id="wtsr_save_credential" class="button button-primary" type="submit">
                        <?php _e('Enable Trusted Shops mode', 'more-better-reviews-for-woocommerce'); ?>
                    </button>

                    <?php
                    if (empty($is_woocommerce_only_mode)) {
                        ?>
                        <button id="wtsr_woocommerce_only_mode_enable" class="button button-primary" type="button">
                            <?php _e('Enable WooCommerce only mode', 'more-better-reviews-for-woocommerce'); ?>
                        </button>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    ?>

    <table id="general-settings-table" class="table-settings">
        <thead>
            <tr>
                <th colspan="3"><?php _e('General settings', 'more-better-reviews-for-woocommerce') ?></th>
            </tr>
        </thead>

        <tbody>
        <?php
        if (!empty($is_woocommerce_only_mode)) {
            ?>
            <tr>
                <th><?php _e('Ask customer for review', 'more-better-reviews-for-woocommerce'); ?></th>
                <td>
                    <select class="form-input" name="wtsr_review_ask" id="wtsr_review_ask" style="max-width:120px;">
                        <option value="no"<?php echo $selected_review_ask === 'no' ? ' selected' : ''; ?>><?php _e('No', 'more-better-reviews-for-woocommerce'); ?></option>
                        <option value="yes"<?php echo $selected_review_ask === 'yes' ? ' selected' : ''; ?>><?php _e('Yes', 'more-better-reviews-for-woocommerce'); ?></option>
                    </select>
                </td>
                <td>
                    <?php include_once dirname(__FILE__) . '/wtsr-admin-description-review-ask.php'; ?>
                </td>
            </tr>

            <tr>
                <th></th>
                <td>
                    <div id="wtsr_email_template_editor">
                        <?php wp_editor( $review_ask_template_editor, 'wtsr_review_ask_template_editor', array(
                            'textarea_rows' => 10,
                            'wpautop'       => 1,
                        ) ); ?>
                    </div>
                </td>
                <td></td>
            </tr>
            <?php
        }
        ?>

            <tr>
                <th valign="top"><?php _e('Filter email domain', 'more-better-reviews-for-woocommerce'); ?></th>
                <td>
                    <textarea class="form-input" name="wtsr_filter_email_domain" id="wtsr_filter_email_domain" cols="30" rows="5"><?php echo $selected_filter_email_domain; ?></textarea>
                    <p>
                        <?php _e('For secure reason we excluding orders that contain <strong><em>amazon</em></strong> and <strong><em>ebay</em></strong> in billing email.', 'more-better-reviews-for-woocommerce'); ?>
                        <?php _e('Emails to these addresses can cause a blocking accounts at the portals!', 'more-better-reviews-for-woocommerce'); ?>
                        <?php _e('Also we generate reviews request only for orders that was done via checkout on your shop.', 'more-better-reviews-for-woocommerce'); ?>
                    </p>
                </td>
                <td valign="top">
                    <p>
                        <?php _e('If you want to add more filters for billing email, please add values in form field above.', 'more-better-reviews-for-woocommerce'); ?><br>
                        <?php echo __('Put one item per line.', 'more-better-reviews-for-woocommerce'); ?>
                    </p>

                    <p>
                        <em>
                            <?php echo __('For our example:', 'more-better-reviews-for-woocommerce'); ?><br>
                            amazon <br>
                            ebay
                        </em>
                    </p>
                </td>
            </tr>

            <tr>
                <th valign="top"><?php _e('Generate request not often than (days)', 'more-better-reviews-for-woocommerce'); ?></th>
                <td valign="top">
                    <input class="form-input" name="wtsr_review_period" id="wtsr_review_period" type="number" value="<?php echo $selected_review_period; ?>">
                </td>
                <td valign="top">
                    <?php echo __('If you often have recurring customers, you can limit the number of valuation requests in a given period of time.', 'more-better-reviews-for-woocommerce'); ?>
                    <br>
                    <?php echo __('When this period will end review request status will become <strong>outdated</strong> and you can generate new review request for this customer.', 'more-better-reviews-for-woocommerce'); ?>
                    <br>
                    <?php echo __('Order outdated status is calculating not from created date but from status changed. So every time we send email date will be updated and we have 30 days for review again.', 'more-better-reviews-for-woocommerce'); ?>
                </td>
            </tr>

            <tr>
                <th><?php _e('Order status generating reviews requests', 'more-better-reviews-for-woocommerce'); ?></th>
                <td>
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
                </td>
                <td></td>
            </tr>

            <tr>
                <th><?php _e('Product variations', 'more-better-reviews-for-woocommerce'); ?></th>
                <td>
                    <select class="form-input" name="wtsr_review_variations" id="wtsr_review_variations">
                        <option value="no"<?php echo $selected_review_variations === 'no' ? ' selected' : ''; ?>><?php _e('Only parent products', 'more-better-reviews-for-woocommerce'); ?></option>
                        <option value="yes"<?php echo $selected_review_variations === 'yes' ? ' selected' : ''; ?>><?php _e('Each variation separately', 'more-better-reviews-for-woocommerce'); ?></option>
                    </select>
                </td>
                <td>
                    <?php echo __('Goal: gathering all reviews from all variants of a product under the parent product sku.', 'more-better-reviews-for-woocommerce'); ?>
                </td>
            </tr>

            <tr>
                <th><?php _e('Transfer only approved reviews', 'more-better-reviews-for-woocommerce'); ?></th>
                <td>
                    <input type="checkbox" name="wtsr_review_approved" id="wtsr_review_approved"<?php echo 'yes' === $wtsr_review_approved ? ' checked' : '' ?>>
                </td>
                <td>
                    <?php _e('If this settings is checked only approved reviews from product page will be transferred to Klick Tipp. If disabled it will be transfered at once after user click "Submit" button', 'more-better-reviews-for-woocommerce'); ?>
                </td>
            </tr>

            <tr>
                <th></th>
                <td colspan="3">
                    <button id="wtsr_save_settings" class="button button-primary" type="submit">
                        <?php _e('Save settings', 'more-better-reviews-for-woocommerce'); ?>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</form>

<div class="wtsr-page-footer">
    <div class="buttons-holder">
        <span style="display:inline-block;line-height:28px;margin:0 5px 0 0;">
            <strong><?php _e( 'Next step', 'more-better-reviews-for-woocommerce' ) ?>:</strong>
        </span>

        <a href="?page=wp2leads-wtsr&tab=email" class="button button-success">
            <?php _e('Email template settings', 'more-better-reviews-for-woocommerce') ?>
        </a>
    </div>
</div>