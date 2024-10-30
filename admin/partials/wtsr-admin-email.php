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
 * @var $is_ts_mode
 * @var $required_plugins_wp2leads
 * @var $is_wp2leads_installed
 * @var $default_send_via
 * @var $wtsr_email_send_via
 */

if ( ! defined( 'ABSPATH' ) ) exit;
$rating_links_options = ReviewsModel::get_rating_links_options();
$default_rating_links = ReviewsModel::get_default_rating_links();
$wtsr_all_reviews_page = get_option('wtsr_all_reviews_page', false);
$wtsr_rating_links = get_option('wtsr_rating_links', $default_rating_links);
$wtsr_email_template = TSManager::get_default_email_template();
$wtsr_email_template_editor = get_option('wtsr_email_template', TSManager::get_default_email_template_editor());
$wtsr_all_reviews_page_footer_template_editor = get_option('wtsr_all_reviews_page_footer_template_editor', '');
$wtsr_uploaded_image = get_option('wtsr_uploaded_image', '');
$wtsr_button_colors = TSManager::get_button_colors();
$wtsr_hover_colors = TSManager::get_hover_colors();
$wtsr_aiop_button_colors = TSManager::get_aiop_button_colors();
$wtsr_image_size = TSManager::get_default_image_size();
$wtsr_all_image_sizes = TSManager::get_all_image_sizes();
$wtsr_all_reviews_page_product_link = get_option('wtsr_all_reviews_page_product_link', 'no');
$wtsr_all_reviews_page_description = get_option('wtsr_all_reviews_page_description', false);
$wtsr_all_reviews_page_reviews_title = get_option('wtsr_all_reviews_page_reviews_title');

if ( false === $wtsr_all_reviews_page_reviews_title ) $wtsr_all_reviews_page_reviews_title = __( 'Review now!', 'more-better-reviews-for-woocommerce' );

$wtsr_all_reviews_page_comment_placeholder = get_option('wtsr_all_reviews_page_comment_placeholder', false);

if (false === $wtsr_all_reviews_page_comment_placeholder) {
    ob_start();
    ?><?php echo __( 'Describe your experiences with the product here', 'more-better-reviews-for-woocommerce' );
    echo PHP_EOL . __( 'Why did you choose this product?', 'more-better-reviews-for-woocommerce' );
    echo PHP_EOL . __( 'What did you like in particular?', 'more-better-reviews-for-woocommerce' );
    echo PHP_EOL . __( 'Would you recommend this product?', 'more-better-reviews-for-woocommerce' ); ?><?php
    $wtsr_all_reviews_page_comment_placeholder = ob_get_clean();
}

if (!$wtsr_all_reviews_page_description) $wtsr_all_reviews_page_description = 'yes';

$textarea_min_length = get_option('wtsr_all_reviews_page_reviews_min');

if (false === $textarea_min_length) $textarea_min_length = 50;
?>

<div class="wtsr-page-header with-btn-holder">
    <h3><?php _e('Email', 'more-better-reviews-for-woocommerce'); ?></h3>

    <div class="buttons-holder">
        <span style="display:inline-block;line-height:28px;margin:0 5px 0 0;">
            <strong><?php _e( 'Next step', 'more-better-reviews-for-woocommerce' ) ?>:</strong>
        </span>

        <a href="?page=wp2leads-wtsr&tab=generate" class="button button-success">
            <?php _e('Generate customer reviews requests', 'more-better-reviews-for-woocommerce') ?>
        </a>
    </div>
</div>

<div class="wtsr-settings-group">
    <div class="wtsr-settings-group-header">
        <h3><?php _e('Email will be send via', 'more-better-reviews-for-woocommerce') ?></h3>
    </div>

    <div class="wtsr-settings-group-body">
        <div class="wtsr-row">
            <div class="wtsr-col-xs-12 wtsr-col-md-4 wtsr-col-lg-2">
                <h4><?php _e('Select service for sending review requests', 'more-better-reviews-for-woocommerce'); ?></h4>
            </div>

            <div class="wtsr-col-xs-12 wtsr-col-md-8 wtsr-col-lg-10">
                <div class="wtsr-row">
                    <div id="wtsr_email_send_via_container" class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-7">
                        <p>
                            <select class="form-input" id="wtsr_email_send_via">
                                <option value="klick-tipp"<?php echo 'klick-tipp' === $wtsr_email_send_via ? ' selected' : ''; ?>><?php _e('Klick Tipp', 'more-better-reviews-for-woocommerce'); ?></option>
                                <option value="woocommerce"<?php echo 'woocommerce' === $wtsr_email_send_via ? ' selected' : ''; ?>><?php _e('WooCommerce', 'more-better-reviews-for-woocommerce'); ?></option>
                            </select>
                        </p>

                        <p class=" settings-item-description wtsr_email_send_via_woocommerce_container wtsr_email_send_via_container" style="margin-bottom:5px;<?php echo 'woocommerce' !== $wtsr_email_send_via ? 'display:none;' : ''; ?>">
                            <strong><?php _e( 'You can edit email subject, heading, add additional content or change email type <a href="?page=wc-settings&tab=email&section=wtsr_wc_email_review_request" target="_blank">here</a>', 'more-better-reviews-for-woocommerce' ); ?></strong>
                        </p>

                        <?php
                        if (!$is_wp2leads_installed) {
                            ?>
                            <div class="wtsr_email_send_via_klick-tipp_container wtsr_email_send_via_container" style="margin-top:10px;<?php echo 'klick-tipp' !== $wtsr_email_send_via ? 'display:none;' : ''; ?>">
                                <div class="wtsr-processing-holder">
                                    <div class="required-plugin-container">
                                        <p>
                                            <?php _e('In order to send review requests with <a href="https://www.klick-tipp.com/15194" target="_blank">Klick Tipp</a> Wp2Leads plugin must be installed and activated, otherwise select WooCommerce option.', 'more-better-reviews-for-woocommerce'); ?>
                                        </p>
                                        <div class="required-plugin-holder">
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

                    <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-5">
                        <p>
                            <?php _e('Select how you are going to send review requests to your clients. You can use <strong>Klick Tipp</strong> or <strong>WooCommerce</strong> email options.', 'more-better-reviews-for-woocommerce'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="wtsr-row wtsr_email_send_via_woocommerce_container wtsr_email_send_via_container" <?php echo 'woocommerce' !== $wtsr_email_send_via ? 'style="display:none;"' : ''; ?>>
            <div class="wtsr-col-xs-12 wtsr-col-md-4 wtsr-col-lg-2">
                <h4><?php _e('WooCommerce Email Delay (days)', 'more-better-reviews-for-woocommerce'); ?></h4>
            </div>

            <div class="wtsr-col-xs-12 wtsr-col-md-8 wtsr-col-lg-10">
                <div class="wtsr-row">
                    <div class="wtsr-col-xs-12">
                        <div class="wtsr-row">
                            <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-7">
                                <p>
                                    <input
                                            class="form-input"
                                            type="text"
                                            id="wtsr_email_send_via_woocommerce_delay"
                                            value="<?php echo $wtsr_email_send_via_woocommerce_delay; ?>"
                                        <?php echo 'woocommerce' !== $wtsr_email_send_via ? 'disabled' : ''; ?>
                                    >
                                </p>
                            </div>

                            <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-5">
                                <p>
                                    <?php _e('Set up delay for sending review requests for Order status set in "Settings".', 'more-better-reviews-for-woocommerce'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="wtsr-row">
                            <div class="wtsr-col-xs-12">

                                <p style="<?php echo 'woocommerce' !== $wtsr_email_send_via ? 'display:none;' : ''; ?>" class="wtsr_email_send_via_woocommerce_container wtsr_email_send_via_container">
                                    <?php _e('Empty value means that email with review request would be send immediately.', 'more-better-reviews-for-woocommerce'); ?>
                                    <br>
                                    <?php _e('If you want to send more then one review request for the same order put multiple values devided by ":".', 'more-better-reviews-for-woocommerce'); ?>
                                </p>

                                <p class="wtsr_email_send_via_woocommerce_container wtsr_email_send_via_container" style="margin-bottom:5px;<?php echo 'woocommerce' !== $wtsr_email_send_via ? 'display:none;' : ''; ?>">
                                    <em>
                                        <?php _e('For our example', 'more-better-reviews-for-woocommerce'); ?>: <?php _e('Order status for generating review request is set to', 'more-better-reviews-for-woocommerce'); ?>
                                        <strong>"<?php echo _x( 'Completed', 'Order status', 'woocommerce' ); ?>"</strong>.
                                    </em>
                                </p>

                                <ul class="wtsr_email_send_via_woocommerce_container wtsr_email_send_via_container" style="margin-top:0;margin-bottom:5px;<?php echo 'woocommerce' !== $wtsr_email_send_via ? 'display:none;' : ''; ?>">
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
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="wtsr-settings-group-footer">
        <div class="wtsr-row">
            <div class="wtsr-col-xs-12 wtsr-col-md-4 wtsr-col-lg-2">

            </div>

            <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-5">
                <button id="wtsr_save_email_service" class="button<?php echo !$is_wp2leads_installed && 'klick-tipp' === $wtsr_email_send_via ? '' : ' button-primary' ?>" type="button"<?php echo !$is_wp2leads_installed && 'klick-tipp' === $wtsr_email_send_via ? ' disabled' : '' ?>>
                    <?php _e('Save changes', 'more-better-reviews-for-woocommerce'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<form id="email-settings_form" method="post">
    <input type="hidden" name="wtsr_settings_save" value="1">
    <input type="hidden" name="wtsr_settings_email" value="1">

    <div class="wtsr-settings-group">
        <div class="wtsr-settings-group-header">
            <h3><?php _e('Email template settings', 'more-better-reviews-for-woocommerce') ?></h3>
        </div>

        <div class="wtsr-settings-group-body">
            <div class="wtsr-row">
                <div class="wtsr-col-xs-12 wtsr-col-md-4 wtsr-col-lg-2">
                    <h4><?php _e('Email template', 'more-better-reviews-for-woocommerce'); ?></h4>
                </div>

                <div class="wtsr-col-xs-12 wtsr-col-md-8 wtsr-col-lg-10">
                    <div class="wtsr-row">
                        <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-7">
                            <div id="wtsr_email_template_preview_container">
                                <h4><?php _e('Template Preview', 'more-better-reviews-for-woocommerce'); ?></h4>

                                <div id="wtsr_email_template_preview" style="padding:10px;border:1px solid #ddd;border-radius:5px;background-color:#fff;">
                                    <?php echo wpautop($wtsr_email_template); ?>
                                </div>
                            </div>

                            <div id="wtsr_email_template_editor" style="display: none;">
                                <?php wp_editor( $wtsr_email_template_editor, 'wtsr_email_template' ); ?>
                            </div>
                        </div>

                        <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-5">
                            <?php include dirname(__FILE__) . '/blocks/available-shortcodes-list.php'; ?>
                        </div>
                    </div>

                    <div class="wtsr-row">
                        <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-7">
                            <div class="wtsr-row">
                                <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-4">
                                    <p><strong><?php echo __('Button BG color', 'more-better-reviews-for-woocommerce'); ?></strong>:</p>

                                    <input name="wtsr_button_bg_color" class="wtsr-color-picker" type="text" value="<?php echo $wtsr_button_colors['bg_color'] ?>">
                                </div>

                                <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-4">
                                    <p><strong><?php echo __('Button text color', 'more-better-reviews-for-woocommerce'); ?></strong>:</p>

                                    <input name="wtsr_button_text_color" class="wtsr-color-picker" type="text" value="<?php echo $wtsr_button_colors['text_color'] ?>">
                                </div>

                                <div class="wtsr-col-xs-12 wtsr-col-md-12 wtsr-col-lg-4">
                                    <p>
                                        <label for="wtsr_image_size"><strong><?php echo __('Select product image size', 'more-better-reviews-for-woocommerce'); ?></strong></label>
                                    </p>

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
                </div>
            </div>
        </div>

        <div class="wtsr-settings-group-footer">
            <div class="wtsr-row">
                <div class="wtsr-col-xs-12 wtsr-col-md-4 wtsr-col-lg-2">
                </div>

                <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-5">
                    <button id="wtsr_email_template_edit" type="button" class="button button-primary">
                        <?php echo __('Edit template', 'more-better-reviews-for-woocommerce'); ?>
                    </button>

                    <button id="wtsr_save_template" class="button button-primary" type="submit">
                        <?php _e('Save changes', 'more-better-reviews-for-woocommerce'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (!defined('WTSR_PRESENTATION') || !WTSR_PRESENTATION) {
        $url = untrailingslashit( plugins_url( '/', WTSR_PLUGIN_FILE ) );
        ?>
        <div class="wtsr-settings-group">
            <div class="wtsr-settings-group-header">
                <h3><?php _e('Rating stars link settings', 'more-better-reviews-for-woocommerce') ?></h3>
            </div>

            <div class="wtsr-settings-group-body">
                <div class="wtsr-row">
                    <div class="wtsr-col-xs-12 wtsr-col-md-4 wtsr-col-lg-2">
                        <h4><?php _e('Star rating links', 'more-better-reviews-for-woocommerce'); ?></h4>
                    </div>

                    <div class="wtsr-col-xs-12 wtsr-col-md-8 wtsr-col-lg-10">
                        <div class="wtsr-row">
                            <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-8">
                                <div class="wtsr-row">
                                    <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-4">
                                        <div class="wtsr_rating_link_item">
                                            <h4>
                                                <label for="wtsr_ts_one_star_link" style="line-height: 20px;display: inline-block;vertical-align: top;">
                                                    <?php _e('One star link', 'more-better-reviews-for-woocommerce'); ?>
                                                </label>
                                                <img title="Poor" src="<?php echo $url . '/admin/img/one_star.png'; ?>" alt="">
                                            </h4>

                                            <select class="form-input wtsr_ts_star_link" name="wtsr_ts_one_star_link" id="wtsr_ts_one_star_link">
                                                <?php
                                                $wtsr_rating_link = $wtsr_rating_links['one_star'];

                                                if ($is_woocommerce_only_mode && 'wtsr_ts_review_link' === $wtsr_rating_link) {
                                                    if ($wtsr_all_reviews_page) {
                                                        $wtsr_rating_link = 'wtsr_all_reviews_page';
                                                    }
                                                }

                                                if ($is_woocommerce_reviews_disabled_globally && ('wtsr_product_url' === $wtsr_rating_link || 'wtsr_all_reviews_page' === $wtsr_rating_link)) {
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

                                            <div
                                                    class="wtsr_rating_custom_link_holder"
                                                <?php echo 'wtsr_custom_link' === $wtsr_rating_link ? '' : ' style="display:none"'; ?>
                                            >
                                                <input class="form-input" type="text" name="wtsr_ts_one_star_custom_link" id="wtsr_ts_one_star_custom_link" value="<?php echo $wtsr_rating_links['custom_link']['one_star'] ?>">

                                                <p
                                                        class="warning-text"
                                                        style="margin-top:5px;<?php echo empty(trim($wtsr_rating_links['custom_link']['one_star'])) ? '' : ' display:none;'; ?>"
                                                >
                                                    <strong><?php _e('Input custom link, please!', 'more-better-reviews-for-woocommerce'); ?></strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-4">
                                        <div class="wtsr_rating_link_item">
                                            <h4>
                                                <label for="wtsr_ts_two_star_link" style="line-height: 20px;display: inline-block;vertical-align: top;">
                                                    <?php _e('Two star link', 'more-better-reviews-for-woocommerce'); ?>
                                                </label>
                                                <img title="Poor" src="<?php echo $url . '/admin/img/two_star.png'; ?>" alt="">
                                            </h4>

                                            <select class="form-input wtsr_ts_star_link" name="wtsr_ts_two_star_link" id="wtsr_ts_two_star_link">
                                                <?php
                                                $wtsr_rating_link = $wtsr_rating_links['two_star'];

                                                if ($is_woocommerce_only_mode && 'wtsr_ts_review_link' === $wtsr_rating_link) {
                                                    if ($wtsr_all_reviews_page) {
                                                        $wtsr_rating_link = 'wtsr_all_reviews_page';
                                                    }
                                                }

                                                if ($is_woocommerce_reviews_disabled_globally && ('wtsr_product_url' === $wtsr_rating_link || 'wtsr_all_reviews_page' === $wtsr_rating_link)) {
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

                                            <div
                                                    class="wtsr_rating_custom_link_holder"
                                                <?php echo 'wtsr_custom_link' === $wtsr_rating_link ? '' : ' style="display:none"'; ?>
                                            >
                                                <input class="form-input" type="text" name="wtsr_ts_two_star_custom_link" id="wtsr_ts_two_star_custom_link" value="<?php echo $wtsr_rating_links['custom_link']['two_star'] ?>">

                                                <p
                                                        class="warning-text"
                                                        style="margin-top:5px;<?php echo empty(trim($wtsr_rating_links['custom_link']['two_star'])) ? '' : ' display:none;'; ?>"
                                                >
                                                    <strong><?php _e('Input custom link, please!', 'more-better-reviews-for-woocommerce'); ?></strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-4">
                                        <div class="wtsr_rating_link_item">
                                            <h4>
                                                <label for="wtsr_ts_three_star_link" style="line-height: 20px;display: inline-block;vertical-align: top;">
                                                    <?php _e('Three star link', 'more-better-reviews-for-woocommerce'); ?>
                                                </label>
                                                <img title="Poor" src="<?php echo $url . '/admin/img/three_star.png'; ?>" alt="">
                                            </h4>

                                            <select class="form-input wtsr_ts_star_link" name="wtsr_ts_three_star_link" id="wtsr_ts_three_star_link">
                                                <?php
                                                $wtsr_rating_link = $wtsr_rating_links['three_star'];

                                                if ($is_woocommerce_only_mode && 'wtsr_ts_review_link' === $wtsr_rating_link) {
                                                    if ($wtsr_all_reviews_page) {
                                                        $wtsr_rating_link = 'wtsr_all_reviews_page';
                                                    }
                                                }

                                                if ($is_woocommerce_reviews_disabled_globally && ('wtsr_product_url' === $wtsr_rating_link || 'wtsr_all_reviews_page' === $wtsr_rating_link)) {
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

                                            <div
                                                    class="wtsr_rating_custom_link_holder"
                                                <?php echo 'wtsr_custom_link' === $wtsr_rating_link ? '' : ' style="display:none"'; ?>
                                            >
                                                <input class="form-input" type="text" name="wtsr_ts_three_star_custom_link" id="wtsr_ts_three_star_custom_link" value="<?php echo $wtsr_rating_links['custom_link']['three_star'] ?>">

                                                <p
                                                        class="warning-text"
                                                        style="margin-top:5px;<?php echo empty(trim($wtsr_rating_links['custom_link']['three_star'])) ? '' : ' display:none;'; ?>"
                                                >
                                                    <strong><?php _e('Input custom link, please!', 'more-better-reviews-for-woocommerce'); ?></strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-4">
                                        <div class="wtsr_rating_link_item">
                                            <h4>
                                                <label for="wtsr_ts_four_star_link" style="line-height: 20px;display: inline-block;vertical-align: top;">
                                                    <?php _e('Four star link', 'more-better-reviews-for-woocommerce'); ?>
                                                </label>
                                                <img title="Poor" src="<?php echo $url . '/admin/img/four_star.png'; ?>" alt="">
                                            </h4>

                                            <select class="form-input wtsr_ts_star_link" name="wtsr_ts_four_star_link" id="wtsr_ts_four_star_link">
                                                <?php
                                                $wtsr_rating_link = $wtsr_rating_links['four_star'];

                                                if ($is_woocommerce_only_mode && 'wtsr_ts_review_link' === $wtsr_rating_link) {
                                                    if ($wtsr_all_reviews_page) {
                                                        $wtsr_rating_link = 'wtsr_all_reviews_page';
                                                    }
                                                }

                                                if ($is_woocommerce_reviews_disabled_globally && ('wtsr_product_url' === $wtsr_rating_link || 'wtsr_all_reviews_page' === $wtsr_rating_link)) {
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

                                            <div
                                                    class="wtsr_rating_custom_link_holder"
                                                <?php echo 'wtsr_custom_link' === $wtsr_rating_link ? '' : ' style="display:none"'; ?>
                                            >
                                                <input class="form-input" type="text" name="wtsr_ts_four_star_custom_link" id="wtsr_ts_four_star_custom_link" value="<?php echo $wtsr_rating_links['custom_link']['four_star'] ?>">

                                                <p
                                                        class="warning-text"
                                                        style="margin-top:5px;<?php echo empty(trim($wtsr_rating_links['custom_link']['four_star'])) ? '' : ' display:none;'; ?>"
                                                >
                                                    <strong><?php _e('Input custom link, please!', 'more-better-reviews-for-woocommerce'); ?></strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-4">
                                        <div class="wtsr_rating_link_item">
                                            <h4>
                                                <label for="wtsr_ts_five_star_link" style="line-height: 20px;display: inline-block;vertical-align: top;">
                                                    <?php _e('Five star link', 'more-better-reviews-for-woocommerce'); ?>
                                                </label>
                                                <img title="Poor" src="<?php echo $url . '/admin/img/five_star.png'; ?>" alt="">
                                            </h4>

                                            <select class="form-input wtsr_ts_star_link select" name="wtsr_ts_five_star_link" id="wtsr_ts_five_star_link">
                                                <?php
                                                $wtsr_rating_link = $wtsr_rating_links['five_star'];

                                                if ($is_woocommerce_only_mode && 'wtsr_ts_review_link' === $wtsr_rating_link) {
                                                    if ($wtsr_all_reviews_page) {
                                                        $wtsr_rating_link = 'wtsr_all_reviews_page';
                                                    }
                                                }

                                                if ($is_woocommerce_reviews_disabled_globally && ('wtsr_product_url' === $wtsr_rating_link || 'wtsr_all_reviews_page' === $wtsr_rating_link)) {
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

                                            <div
                                                    class="wtsr_rating_custom_link_holder"
                                                <?php echo 'wtsr_custom_link' === $wtsr_rating_link ? '' : ' style="display:none"'; ?>
                                            >
                                                <input class="form-input" type="text" name="wtsr_ts_five_star_custom_link" id="wtsr_ts_five_star_custom_link" value="<?php echo $wtsr_rating_links['custom_link']['five_star'] ?>">

                                                <p
                                                        class="warning-text"
                                                        style="margin-top:5px;<?php echo empty(trim($wtsr_rating_links['custom_link']['five_star'])) ? '' : ' display:none;'; ?>"
                                                >
                                                    <strong><?php _e('Input custom link, please!', 'more-better-reviews-for-woocommerce'); ?></strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-4">
                                <h4><?php echo __('Available shortcodes for custom links', 'more-better-reviews-for-woocommerce'); ?></h4>

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
                        </div>

                        <div class="wtsr-row">
                            <div class="wtsr-col-xs-12 wtsr-col-lg-8">
                                <p>
                                    <em>
                                        <?php echo __('For our example:', 'more-better-reviews-for-woocommerce'); ?><br>
                                        https://your-domain.com?order_number=<strong>{order_number}</strong><br>
                                        https://your-domain.com?product_id=<strong>{product_id}</strong>&product_slug=<strong>{product_slug}</strong>&product_title=<strong>{product_title}</strong><br>
                                        https://your-domain.com?some_param=param_value&first_name=<strong>{customer_fn}</strong>&last_name=<strong>{customer_ln}</strong>&email=<strong>{customer_email}</strong>
                                    </em>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wtsr-settings-group-footer">
                <div class="wtsr-row">
                    <div class="wtsr-col-xs-12 wtsr-col-md-4 wtsr-col-lg-2">

                    </div>

                    <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-10">
                        <button id="wtsr_rating_links" class="button button-primary" type="submit">
                            <?php _e('Save changes', 'more-better-reviews-for-woocommerce'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="wtsr-settings-group">
            <div class="wtsr-settings-group-header">
                <h3><?php _e('All-in-one reviews page settings', 'more-better-reviews-for-woocommerce') ?></h3>
            </div>

            <div class="wtsr-settings-group-body">
                <div class="wtsr-row">
                    <div class="wtsr-col-xs-12 wtsr-col-md-4 wtsr-col-lg-2">
                        <h4><?php _e('All-in-one reviews page', 'more-better-reviews-for-woocommerce'); ?></h4>
                    </div>

                    <div class="wtsr-col-xs-12 wtsr-col-md-8 wtsr-col-lg-10">
                        <div class="wtsr-row">
                            <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-5">
                                <?php
                                $wtsr_all_reviews_page_exists = false;

                                if ($wtsr_all_reviews_page) {
                                    $page_object = get_post($wtsr_all_reviews_page);
                                    if (!empty($page_object)) {
                                        $wtsr_all_reviews_page_exists = true;
                                    }
                                }

                                $current_user = wp_get_current_user();
                                $current_user_email = $current_user->user_email;

                                if ($wtsr_all_reviews_page && $wtsr_all_reviews_page_exists) {
                                    $page_sample_url = get_permalink($wtsr_all_reviews_page) . '?sample=' . md5($current_user_email);
                                    $aior_page_title = get_the_title( $wtsr_all_reviews_page );

                                    if (empty(trim($aior_page_title))) {
                                        $aior_page_title = '[' . __('No title', 'more-better-reviews-for-woocommerce') . ']';
                                    }
                                    ?>
                                    <div>
                                        <h3>
                                            <span style="font-weight:100;"><?php echo __('Page title', 'more-better-reviews-for-woocommerce'); ?>:</span>
                                            <?php echo $aior_page_title; ?>
                                        </h3>
                                    </div>

                                    <div style="margin-top:15px;">
                                        <a href="post.php?post=<?php echo $wtsr_all_reviews_page; ?>&action=edit" target="_blank" class="button button-primary">
                                            <?php echo __('Edit page', 'more-better-reviews-for-woocommerce'); ?>
                                        </a>

                                        <a href="<?php echo $page_sample_url; ?>" target="_blank" class="button button-primary">
                                            <?php echo __('View page sample', 'more-better-reviews-for-woocommerce'); ?>
                                        </a>
                                    </div>
                                    <?php
                                } else {
                                    ?>
                                    <div class="p">
                                        <input
                                                class="form-input"
                                                type="text"
                                                id="wtsr_all_reviews_page_title"
                                                value=""
                                                placeholder="<?php echo __('Input page title', 'more-better-reviews-for-woocommerce'); ?>"
                                        >
                                    </div>

                                    <div style="margin-top:15px;">
                                        <button id="wtsr_all_reviews_page_create" type="button" class="button button-primary">
                                            <?php echo __('Create page', 'more-better-reviews-for-woocommerce'); ?>
                                        </button>

                                        <a href="<?php echo home_url( '/all-in-one-woo-review-page' ) . '?sample=' . md5($current_user_email); ?>" target="_blank" class="button button-primary">
                                            <?php echo __('View default page sample', 'more-better-reviews-for-woocommerce'); ?>
                                        </a>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>

                            <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-7">
                                <p>
                                    <?php echo __('You can create custom page, where customers can review all products from order at once.', 'more-better-reviews-for-woocommerce'); ?>
                                    <br>
                                    <?php echo __('Just input a title you want to use and we will generate this page for you.', 'more-better-reviews-for-woocommerce'); ?>
                                    <?php echo __('After page will be created, you can adit and add any type of content to be shown for your customers in wordpress editor.', 'more-better-reviews-for-woocommerce'); ?>
                                </p>
                                <p>
                                    <?php echo __('Otherwise your client will be redirected to default all in one reviews page.', 'more-better-reviews-for-woocommerce'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wtsr-row">
                    <div class="wtsr-col-xs-12 wtsr-col-md-4 wtsr-col-lg-2">
                        <h4><?php _e('Comment field settings', 'more-better-reviews-for-woocommerce'); ?></h4>
                    </div>

                    <div class="wtsr-col-xs-12 wtsr-col-md-8 wtsr-col-lg-10">
                        <div class="wtsr-row">
                            <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-5">
                                <h4>
                                    <?php echo __('Min comment length', 'more-better-reviews-for-woocommerce'); ?>
                                    (<?php echo __('characters', 'more-better-reviews-for-woocommerce'); ?>)
                                </h4>

                                <p>
                                    <input class="form-input" name="wtsr_all_reviews_page_reviews_min" id="wtsr_all_reviews_page_reviews_min" type="number" value="<?php echo $textarea_min_length ?>">
                                </p>
                            </div>

                            <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-7">
                                <h4>
                                    <?php echo __('Comment field placeholder', 'more-better-reviews-for-woocommerce'); ?>
                                </h4>

                                <p>
                                    <textarea id="wtsr_all_reviews_page_comment_placeholder" name="wtsr_all_reviews_page_comment_placeholder" class="form-input" cols="30" rows="6"
                                    ><?php echo $wtsr_all_reviews_page_comment_placeholder; ?></textarea>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wtsr-row">
                    <div class="wtsr-col-xs-12 wtsr-col-md-4 wtsr-col-lg-2">
                        <h4><?php _e('All-in-one review page design', 'more-better-reviews-for-woocommerce'); ?></h4>
                    </div>

                    <div class="wtsr-col-xs-12 wtsr-col-md-8 wtsr-col-lg-10">
                        <div class="wtsr-row">
                            <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-5">
                                <h4>
                                    <?php _e('All-in-one reviews page header logo', 'more-better-reviews-for-woocommerce'); ?>
                                </h4>

                                <p>
                                    <?php _e('Recommended image size not more than 390x90 px', 'more-better-reviews-for-woocommerce'); ?>
                                </p>

                                <div>
                                    <div id="wtsr_uploaded_image_holder">
                                        <?php
                                        if (!empty($wtsr_uploaded_image)) {
                                            $image_attributes = wp_get_attachment_image_src( $wtsr_uploaded_image, 'full' );
                                            ?>
                                            <img src="<?php echo $image_attributes[0] ?>" alt="">
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <input id="wtsr_uploaded_image" type="hidden" value="<?php echo $wtsr_uploaded_image ?>"><br>

                                    <button id="wtsr_upload_image_btn" class="button button-primary" type="button"<?php echo !empty($wtsr_uploaded_image) ? ' style="display:none;"' : '' ?>>
                                        <?php _e('Upload logo image', 'more-better-reviews-for-woocommerce'); ?>
                                    </button>

                                    <button id="wtsr_delete_image_btn" class="button button-primary" type="button"<?php echo empty($wtsr_uploaded_image) ? ' style="display:none;"' : '' ?>>
                                        <?php _e('Delete logo image', 'more-better-reviews-for-woocommerce'); ?>
                                    </button>
                                </div>
                            </div>

                            <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-7">
                                <h4>
                                    <?php _e('All-in-one reviews page colors', 'more-better-reviews-for-woocommerce'); ?>
                                </h4>

                                <div class="wtsr-row">
                                    <div class="wtsr-col-xs-12 wtsr-col-sm-6 wtsr-col-md-12 wtsr-col-lg-6">
                                        <p><strong><?php echo __('Normal title color', 'more-better-reviews-for-woocommerce'); ?></strong>:</p>

                                        <p><input id="wtsr_normal_title_color" class="wtsr-color-picker" type="text" value="<?php echo $wtsr_hover_colors['normal'] ?>"></p>
                                    </div>
                                    <div class="wtsr-col-xs-12 wtsr-col-sm-6 wtsr-col-md-12 wtsr-col-lg-6">
                                        <p><strong><?php echo __('Hover title color', 'more-better-reviews-for-woocommerce'); ?></strong>:</p>

                                        <p><input id="wtsr_hover_title_color" class="wtsr-color-picker" type="text" value="<?php echo $wtsr_hover_colors['hover'] ?>"></p>
                                    </div>
                                </div>

                                <div class="wtsr-row">
                                    <div class="wtsr-col-xs-12 wtsr-col-sm-6 wtsr-col-md-12 wtsr-col-lg-6">
                                        <p><strong><?php echo __('Normal button bg color', 'more-better-reviews-for-woocommerce'); ?></strong>:</p>

                                        <p><input id="wtsr_normal_button_bg_color" class="wtsr-color-picker" type="text" value="<?php echo $wtsr_aiop_button_colors['normal_bg'] ?>"></p>
                                    </div>
                                    <div class="wtsr-col-xs-12 wtsr-col-sm-6 wtsr-col-md-12 wtsr-col-lg-6">
                                        <p><strong><?php echo __('Normal button text color', 'more-better-reviews-for-woocommerce'); ?></strong>:</p>

                                        <p><input id="wtsr_normal_button_txt_color" class="wtsr-color-picker" type="text" value="<?php echo $wtsr_aiop_button_colors['normal_txt'] ?>"></p>
                                    </div>
                                </div>

                                <div class="wtsr-row">
                                    <div class="wtsr-col-xs-12 wtsr-col-sm-6 wtsr-col-md-12 wtsr-col-lg-6">
                                        <p><strong><?php echo __('Hover button bg color', 'more-better-reviews-for-woocommerce'); ?></strong>:</p>

                                        <p><input id="wtsr_hover_button_bg_color" class="wtsr-color-picker" type="text" value="<?php echo $wtsr_aiop_button_colors['hover_bg'] ?>"></p>
                                    </div>
                                    <div class="wtsr-col-xs-12 wtsr-col-sm-6 wtsr-col-md-12 wtsr-col-lg-6">
                                        <p><strong><?php echo __('Hover button text color', 'more-better-reviews-for-woocommerce'); ?></strong>:</p>

                                        <p><input id="wtsr_hover_button_txt_color" class="wtsr-color-picker" type="text" value="<?php echo $wtsr_aiop_button_colors['hover_txt'] ?>"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wtsr-row">
                    <div class="wtsr-col-xs-12 wtsr-col-md-4 wtsr-col-lg-2">
                        <h4>
                            <?php _e('Review block title', 'more-better-reviews-for-woocommerce'); ?>
                        </h4>
                    </div>

                    <div class="wtsr-col-xs-12 wtsr-col-md-8 wtsr-col-lg-10">
                        <div class="wtsr-row">
                            <div class="wtsr-col-xs-12 wtsr-col-md-12 wtsr-col-lg-6">
                                <p>
                                    <input
                                            class="form-input"
                                            type="text"
                                            id="wtsr_all_reviews_page_reviews_title"
                                            value="<?php echo $wtsr_all_reviews_page_reviews_title; ?>"
                                            placeholder="<?php echo __('Input reviews block title', 'more-better-reviews-for-woocommerce'); ?>"
                                    >
                                </p>
                            </div>

                            <div class="wtsr-col-xs-12 wtsr-col-sm-6 wtsr-col-lg-3">
                                <h4>
                                    <strong><?php _e('Show product description', 'more-better-reviews-for-woocommerce'); ?></strong>
                                    <input id="wtsr_all_reviews_page_description" type="checkbox" value="1"<?php echo 'yes' === $wtsr_all_reviews_page_description ? ' checked' : ''; ?>>
                                </h4>
                            </div>

                            <div class="wtsr-col-xs-12 wtsr-col-sm-6 wtsr-col-lg-3">
                                <h4>
                                    <strong><?php _e('Add link to product page', 'more-better-reviews-for-woocommerce'); ?></strong>
                                    <input id="wtsr_all_reviews_page_product_link" type="checkbox" value="1"<?php echo 'yes' === $wtsr_all_reviews_page_product_link ? ' checked' : ''; ?>>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wtsr-row">
                    <div class="wtsr-col-xs-12 wtsr-col-md-4 wtsr-col-lg-2">
                        <h4><?php _e('All-in-one reviews page footer content', 'more-better-reviews-for-woocommerce'); ?></h4>
                    </div>

                    <div class="wtsr-col-xs-12 wtsr-col-md-8 wtsr-col-lg-6">
                        <?php wp_editor( $wtsr_all_reviews_page_footer_template_editor, 'wtsr_all_reviews_page_footer_template_editor', array(
                            'textarea_rows' => 5,
                            'wpautop'       => 1,
                        ) ); ?>
                    </div>
                </div>
            </div>

            <div class="wtsr-settings-group-footer">
                <div class="wtsr-row">
                    <div class="wtsr-col-xs-12 wtsr-col-md-4 wtsr-col-lg-2">

                    </div>

                    <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-5">
                        <button id="wtsr_all_reviews_page_colors_save" type="button" class="button button-primary">
                            <?php echo __('Save All-in-one reviews page settings', 'more-better-reviews-for-woocommerce'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>

</form>

<?php

$wtsr_thankyou_settings = Wtsr_Settings::get_thankyou_settings();

?>
<form  id="thankyou-settings_form" method="post">
    <div class="wtsr-settings-group">
        <div class="wtsr-settings-group-header">
            <h3><?php _e('Thank you email settings', 'more-better-reviews-for-woocommerce') ?></h3>
        </div>

        <div class="wtsr-settings-group-body">
            <!-- Thank you settings section - Start -->
            <div class="wtsr-row">
                <div class="wtsr-col-xs-12 wtsr-col-md-4 wtsr-col-lg-2">
                    <h4><?php _e('General settings', 'more-better-reviews-for-woocommerce'); ?></h4>
                </div>

                <div class="wtsr-col-xs-12 wtsr-col-md-8 wtsr-col-lg-10">
                    <div class="wtsr-row">
                        <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-8">
                            <div class="wtsr-row">
                                <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-4">
                                    <h4><?php _e('Enable Thank you email', 'more-better-reviews-for-woocommerce') ?></h4>
                                </div>

                                <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-8">
                                    <p>
                                        <select name="thankyou_enabled" id="wtsr_thankyou_enabled" class="form-input select wtsr_dependency_control" data-enabled="yes" data-dependency="wtsr_thankyou_enabled_dependency">
                                            <option value="no"<?php echo $wtsr_thankyou_settings['thankyou_enabled'] === 'no' ? ' selected' : ''; ?>><?php echo __( 'Disabled', 'woocommerce' ); ?></option>
                                            <option value="yes"<?php echo $wtsr_thankyou_settings['thankyou_enabled'] === 'yes' ? ' selected' : ''; ?>><?php echo __( 'Enabled', 'woocommerce' ); ?></option>
                                        </select>
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
                        </div>

                        <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-4">
                            <p>
                                <?php _e('In this section you can set up sending Thank you email after customers review is published.', 'more-better-reviews-for-woocommerce') ?>
                            </p>

                            <p>
                                <?php
                                $url = admin_url( 'admin.php?page=wc-settings&tab=email&section=wtsr_wc_email_customer_coupon' );
                                $string = __( 'You can <strong><a href="%s" target="_blank">edit coupon email here</a></strong>', 'more-better-reviews-for-woocommerce' );
                                echo sprintf($string , $url );
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Thank you settings section - End -->

            <!-- Coupon settings section - Start -->

            <div id="wtsr_coupon_settings" class="wtsr-row wtsr_thankyou_enabled_dependency<?php echo $wtsr_thankyou_settings['discount_type'] === 'none' ? ' setting_disabled' : ''; ?>"<?php echo $wtsr_thankyou_settings['thankyou_enabled'] === 'no' ? ' style="display:none;"' : ''; ?>>
                <div class="wtsr-col-xs-12 wtsr-col-md-4 wtsr-col-lg-2">
                    <h4><?php _e('Coupon settings', 'more-better-reviews-for-woocommerce'); ?></h4>
                </div>

                <div class="wtsr-col-xs-12 wtsr-col-md-8 wtsr-col-lg-10">
                    <div class="wtsr-row">
                        <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-8">
                                <div class="wtsr-row">
                                    <div class="wtsr-col-xs-12 wtsr-col-lg-6">
                                        <div class="wtsr-row">
                                            <div class="wtsr-col-xs-12 wtsr-col-md-6">
                                                <h4 style="margin-top: 8px; margin-bottom: 10px;">
                                                    <?php echo __( 'Discount type', 'woocommerce' ); ?>
                                                </h4>

                                                <select id="wtsr_discount_type" name="discount_type" class="form-input select">
                                                    <option value="none"<?php echo $wtsr_thankyou_settings['discount_type'] === 'none' ? ' selected' : ''; ?>><?php echo __( '-- Select discount type --', 'more-better-reviews-for-woocommerce' ); ?></option>
                                                    <option value="fixed_cart"<?php echo $wtsr_thankyou_settings['discount_type'] === 'fixed_cart' ? ' selected' : ''; ?>><?php echo __( 'Fixed cart discount', 'woocommerce' ); ?></option>
                                                    <option value="percent"<?php echo $wtsr_thankyou_settings['discount_type'] === 'percent' ? ' selected' : ''; ?>><?php echo __( 'Percentage discount', 'woocommerce' ); ?></option>
                                                </select>
                                            </div>

                                            <div class="wtsr-col-xs-12 wtsr-col-md-6 need-enabled">
                                                <h4 style="margin-top: 8px; margin-bottom: 10px;">
                                                    <?php echo __( 'Coupon amount', 'woocommerce' ); ?>
                                                </h4>

                                                <input class="form-input" type="number" id="wtsr_coupon_amount" name="coupon_amount" value="<?php echo $wtsr_thankyou_settings['coupon_amount']; ?>">
                                            </div>
                                        </div>

                                        <div class="wtsr-row need-enabled" style="margin-top: 25px;">
                                            <div class="wtsr-col-xs-12 wtsr-col-md-6">
                                                <h4 style="margin-top: 8px; margin-bottom: 10px;">
                                                    <?php echo __( 'Coupon expiration', 'more-better-reviews-for-woocommerce' ); ?> (<?php _e('hours', 'more-better-reviews-for-woocommerce'); ?>)
                                                </h4>
                                            </div>

                                            <div class="wtsr-col-xs-12 wtsr-col-md-6">
                                                <input class="form-input" type="number" id="wtsr_coupon_expiration" name="coupon_expiration" value="<?php echo $wtsr_thankyou_settings['coupon_expiration']; ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="wtsr-col-xs-12 wtsr-col-lg-6 need-enabled">
                                        <h4 style="margin-top: 8px; margin-bottom: 10px;">
                                            <?php echo __( 'Description', 'more-better-reviews-for-woocommerce' ); ?>
                                        </h4>

                                        <textarea class="form-input" name="coupon_description" id="wtsr_coupon_description" rows="5"><?php echo $wtsr_thankyou_settings['coupon_description']; ?></textarea>
                                    </div>
                                </div>
                        </div>

                        <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-4 need-enabled">
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
                        <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-8">
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

                        <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-4 wtsr_coupon_countdown_enabled_dependency"<?php echo $wtsr_thankyou_settings['coupon_countdown'] === 'no' ? ' style="display:none;"' : ''; ?>>
                            <h4><?php echo __('Available shortcodes for coupon description', 'more-better-reviews-for-woocommerce'); ?></h4>

                            <ul>
                                <li><strong>{coupon_countdown_period}</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Coupon settings section - End -->
        </div>

        <div class="wtsr-settings-group-footer">
            <div class="wtsr-row">
                <div class="wtsr-col-xs-12 wtsr-col-md-4 wtsr-col-lg-2">

                </div>

                <div class="wtsr-col-xs-12 wtsr-col-md-6 wtsr-col-lg-5">
                    <button id="wtsr_save_thankyou_settings" class="button<?php echo !$is_wp2leads_installed && 'klick-tipp' === $wtsr_email_send_via ? '' : ' button-primary' ?>" type="button"<?php echo !$is_wp2leads_installed && 'klick-tipp' === $wtsr_email_send_via ? ' disabled' : '' ?>>
                        <?php _e('Save changes', 'more-better-reviews-for-woocommerce'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>



<div class="wtsr-page-footer">
    <div class="buttons-holder">
        <span style="display:inline-block;line-height:28px;margin:0 5px 0 0;">
            <strong><?php _e( 'Next step', 'more-better-reviews-for-woocommerce' ) ?>:</strong>
        </span>

        <a href="?page=wp2leads-wtsr&tab=generate" class="button button-success">
            <?php _e('Generate customer reviews requests', 'more-better-reviews-for-woocommerce') ?>
        </a>
    </div>
</div>
