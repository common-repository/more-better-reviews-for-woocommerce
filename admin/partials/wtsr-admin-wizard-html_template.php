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
$wtsr_email_template = TSManager::get_default_email_template();
$wtsr_email_template_editor = get_option('wtsr_email_template', TSManager::get_default_email_template_editor());
$wtsr_button_colors = TSManager::get_button_colors();
$wtsr_image_size = TSManager::get_default_image_size();
$wtsr_all_image_sizes = TSManager::get_all_image_sizes();
?>

<div class="settings-container">
    <div class="settings-item">
        <p class="settings-item-label"><?php _e('Email template', 'more-better-reviews-for-woocommerce'); ?></p>

        <div class="settings-item-value">
            <div id="wtsr_email_template_preview_container">
                <h4><?php _e('Template Preview', 'more-better-reviews-for-woocommerce'); ?></h4>

                <div id="wtsr_email_template_preview" style="padding:10px;border:1px solid #ddd;border-radius:5px;background-color:#fff;">
                    <?php echo wpautop( wp_unslash( $wtsr_email_template ) ); ?>
                </div>
            </div>

            <div id="wtsr_email_template_editor" style="display: none;">
                <?php wp_editor( wp_unslash($wtsr_email_template_editor), 'wtsr_email_template' ); ?>
            </div>
        </div>

        <div style="text-align: center;margin-top:15px;">
            <button id="wtsr_email_template_edit" type="button" class="button button-primary button-small">
                <?php echo __('Edit template', 'more-better-reviews-for-woocommerce'); ?>
            </button>
        </div>

        <div class="settings-item-description">
            <?php include dirname(__FILE__) . '/blocks/available-shortcodes-list.php'; ?>
        </div>
    </div>

    <div class="settings-item">
        <p class="settings-item-label"><?php echo __('HTML template colors', 'more-better-reviews-for-woocommerce'); ?></p>

        <div class="settings-item-value">
            <div id="wtsr_color_selector_holder" style="margin-top:20px">
                <div>
                    <div style="display: inline-block;vertical-align: sub;margin-right: 20px;min-width:150px;">
                        <strong><?php echo __('Button BG color', 'more-better-reviews-for-woocommerce'); ?></strong>:
                    </div> <input id="wtsr_button_bg_color" name="wtsr_button_bg_color" class="wtsr-color-picker" type="text" value="<?php echo $wtsr_button_colors['bg_color'] ?>">
                </div>

                <div>
                    <div style="display: inline-block;vertical-align: sub;margin-right: 20px;min-width:150px;">
                        <strong><?php echo __('Button text color', 'more-better-reviews-for-woocommerce'); ?></strong>:
                    </div> <input id="wtsr_button_text_color" name="wtsr_button_text_color" class="wtsr-color-picker" type="text" value="<?php echo $wtsr_button_colors['text_color'] ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="settings-item">

        <div class="settings-item-group">
            <div class="settings-item-group-col">
                <p class="settings-item-label"><?php echo __('Select product image size', 'more-better-reviews-for-woocommerce'); ?></p>
            </div>

            <div class="settings-item-group-col">
                <div class="settings-item-value">
                    <select class="form-input" name="wtsr_image_size" id="wtsr_image_size">
                        <option value="no_image_template"<?php echo 'no_image_template' === $wtsr_image_size ? ' selected' : ''; ?>><?php echo __('No image shown', 'more-better-reviews-for-woocommerce'); ?></option>
                        <option value="full"<?php echo 'full' === $wtsr_image_size ? ' selected' : ''; ?>><?php echo __('Full size', 'more-better-reviews-for-woocommerce'); ?></option>
                        <?php
                        foreach ($wtsr_all_image_sizes as $size => $values) {
                            ?>
                            <option value="<?php echo $size; ?>"<?php echo $size === $wtsr_image_size ? ' selected' : ''; ?>>
                                <?php echo ucfirst(str_replace('_', ' ', $size)); ?>:
                                <?php echo __('width', 'more-better-reviews-for-woocommerce') . ' ' . $values['width'] . 'px, ' . __('height', 'more-better-reviews-for-woocommerce') . ' ' . $values['height'] . 'px'; ?>
                                (<?php echo !empty($values['crop']) ? __('cropped', 'more-better-reviews-for-woocommerce') : __('not cropped', 'more-better-reviews-for-woocommerce') ?>)
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-control">
        <button id="wizard-wtsr_save_template" class="button button-primary" type="button">
            <?php _e('Save changes', 'more-better-reviews-for-woocommerce'); ?>
        </button>
    </div>
</div>
