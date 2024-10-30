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

$wtsr_map_id = 195;
$is_ts_review_request_enabled = get_option('wtsr_ts_review_request_enabled');

$is_wtsr_module_enabled = TSManager::is_wtsr_module_enabled();
$is_wtsr_map_exists = TSManager::is_wtsr_map_exists();

//TSManager::is_limit_allowed();
// TSManager::increment_reviews_limit_counter();

$license_info = Wtsr_License::get_lecense_info();
$activation_in_progress = get_transient( 'wtsr_activation_in_progress' );
$license_version = Wtsr_License::get_license_version();
?>
<div class="settings-container">

    <?php
    if ('free' === $license_version || !empty($activation_in_progress)) {
        require_once dirname(__FILE__) . '/wtsr-admin-wizard-block-license_warning.php';
        require_once dirname(__FILE__) . '/wtsr-admin-wizard-block-license_info.php';
    }
    ?>

    <h4 style="text-align: center"><?php _e("Almost done!." , 'more-better-reviews-for-woocommerce') ?></h4>

    <?php
    if ('woocommerce' !== $wtsr_email_send_via) {
        if ($is_wtsr_map_exists) {
            if (!empty($is_wtsr_module_enabled)) {
                ?>
                <p class="settings-item-label"><?php _e("Map installed and instant module is active. You can start transferring!" , 'more-better-reviews-for-woocommerce') ?></p>
                <?php
            } else {
                ?>
                <p class="settings-item-label"><?php _e("Map installed but instant module is not active. Please, activate module!" , 'more-better-reviews-for-woocommerce') ?></p>
                <?php
            }
        } else {
            ?>
            <p class="settings-item-label"><?php _e("For transferring review requests to Klick Tipp, please Import this map and Activate transferring module." , 'more-better-reviews-for-woocommerce') ?></p>
            <?php
        }

        if ($is_wtsr_map_exists) {
            ?>
            <div class="settings-item-label">
                <h3>
                    <span style="font-weight:400;"><?php _e("Map title" , 'more-better-reviews-for-woocommerce') ?>:</span>
                    <span style="display: inline-block;margin-right: 15px;"><?php echo $is_wtsr_map_exists[0]["name"]; ?></span>
                    <?php
                    if (empty($is_wtsr_module_enabled)) {
                        ?>
                        <button id="wizard-wtsr_activate_map" class="button button-primary button-small" style="font-weight:400;" data-map-id="<?php echo $is_wtsr_map_exists[0]["id"] ?>"><?php _e('Activate', 'more-better-reviews-for-woocommerce'); ?></button>
                        <?php
                    }
                    ?>
                </h3>
            </div>
            <?php
        } else {
            if (class_exists('MapBuilderManager')) {
                $maps_from_server = MapBuilderManager::get_available_maps_from_server();
            }

            if ($maps_from_server) {
                foreach ($maps_from_server as $map_from_server) {
                    if ($wtsr_map_id == $map_from_server['id']) {
                        ?>
                        <div class="settings-item-label">
                            <h3>
                                <span style="font-weight:400;"><?php _e("Map title" , 'more-better-reviews-for-woocommerce') ?>:</span>
                                <span style="display: inline-block;margin-right: 15px;"><?php echo $map_from_server['name']; ?> (id <?php echo $map_from_server['id'] ?>)</span>
                                <button id="wizard-wtsr_import_activate_map" class="button button-primary button-small" style="font-weight:400;"><?php _e('Import and Activate', 'more-better-reviews-for-woocommerce'); ?></button>
                            </h3>
                        </div>
                        <?php
                    }
                }
            }
        }
    }

    ?>

    <div class="settings-item" style="margin-top:20px;">
        <div class="settings-item-group">
            <div class="settings-item-group-col">
                <?php
                if ('woocommerce' !== $wtsr_email_send_via) {
                    ?>
                    <p class="settings-item-label"><?php _e("Enable auto generate and transfer review requests" , 'more-better-reviews-for-woocommerce') ?>:</p>
                    <?php
                } else {
                    ?>
                    <p class="settings-item-label"><?php _e("Enable auto generate and email review requests" , 'more-better-reviews-for-woocommerce') ?>:</p>
                    <?php
                }
                ?>

            </div>

            <div class="settings-item-group-col">
                <div class="settings-item-value">
                    <select name="wtsr_ts_enable_review_request" id="wtsr_ts_enable_review_request" class="form-input">
                        <option value="yes"<?php echo $is_ts_review_request_enabled ? ' selected' : ''; ?>><?php _e('Enabled', 'more-better-reviews-for-woocommerce'); ?></option>
                        <option value="no"<?php echo !$is_ts_review_request_enabled ? ' selected' : ''; ?>><?php _e('Disabled', 'more-better-reviews-for-woocommerce'); ?></option>
                    </select>
                </div>

                <div class="settings-control" style="text-align:right;margin-top:5px;padding:0 10px;">
                    <button id="wtsr_ts_enable_review_request_btn" type="button" class="button button-primary button-small">
                        <?php _e('Save', 'more-better-reviews-for-woocommerce'); ?>
                    </button>
                </div>
            </div>
        </div>


    </div>
</div>