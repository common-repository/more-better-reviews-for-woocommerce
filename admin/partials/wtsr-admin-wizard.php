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
 *
 * @var $required_plugins_wp2leads
 * @var $is_wp2leads_installed
 * @var $default_send_via
 * @var $wtsr_email_send_via
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wizard_step = !empty($_GET['wizard_step']) ? $_GET['wizard_step'] : 'intro';
$wizard_body_class = '';

if ('generate_reviews' === $wizard_step || 'reviews_list' === $wizard_step) {
    $wizard_body_class = ' class="full-width-container"';
}

$required_plugins = Wtsr_Required_Plugins::required_plugins();

if (!empty($required_plugins)) {
    $wizard_step = 'required_plugin';
} else {
    if ($wizard_step === 'required_plugin') {
        $wizard_step = 'intro';
    }
}

$wizard_steps = Wtsr_Wizard::get_wizard_steps();
$wizard_steps_order = array();

while(key($wizard_steps) !== $wizard_step) next($wizard_steps);

$prev_val = prev($wizard_steps);
$prev_step = key($wizard_steps);

reset($wizard_steps);

while(key($wizard_steps) !== $wizard_step) next($wizard_steps);

$next_val = next($wizard_steps);
$next_step = key($wizard_steps);

$is_woocommerce_only_mode = ReviewServiceManager::is_woocommerce_only_mode();

if ('reviews_list' === $wizard_step) {
    $review_id = !empty($_GET['review_id']) ? sanitize_text_field($_GET['review_id']) : false;
    $license_version = Wtsr_License::get_license_version();

    if ('free' === $license_version && empty($review_id)) {
        $reviews_all_with_template = ReviewsModel::get_with_template('DESC', array(), 'id, order_id, email, status, review_created, review_sent');

        if (!empty($reviews_all_with_template)) {
            $review_id = $reviews_all_with_template[0]->id;
        }
    }

    if ($review_id) {
        $review = ReviewsModel::get( $review_id );
    }

    if (!empty($review)) {
        $wizard_body_class = '';
        $step_title = __('Review requests preview', 'more-better-reviews-for-woocommerce');
    }
}
?>

<div class="wizard-wrap">
    <div id="wizard-header">
        <h1>
            <?php _e('Better Reviews for WOO', 'more-better-reviews-for-woocommerce'); ?>
            (<?php echo Wtsr_License::get_license_version_label(); ?>)
            <?php
            if ('woocommerce' === $wtsr_email_send_via) {
                ?>- <?php _e('Woo', 'more-better-reviews-for-woocommerce'); ?><?php
            } else {
                ?>- <?php _e('Klick Tipp', 'more-better-reviews-for-woocommerce'); ?><?php
            }
            ?>
            <?php
            if (!$is_woocommerce_only_mode) {
                ?>- <?php _e('Trusted Shops', 'more-better-reviews-for-woocommerce'); ?><?php
            }
            ?>
        </h1>
    </div>

    <div id="wizard-body"<?php echo $wizard_body_class; ?>>
        <div class="inner-container">
            <?php if (!empty($wizard_steps[$wizard_step]['title'])) {
                if (!empty($step_title)) {
                    ?><h2><?php echo $step_title; ?></h2><?php
                } else {
                    ?><h2><?php echo $wizard_steps[$wizard_step]['title']; ?></h2><?php
                }
            }

            require_once dirname(__FILE__) . '/wtsr-admin-wizard-'.$wizard_step.'.php';

            if ('intro' === $wizard_step) {
                if (!empty($next_step)) {
                    ?>
                    <p style="text-align:center;margin-top:25px;">
                        <button class="button button-success button-large go-to-step" data-step="<?php echo $next_step; ?>" type="button">
                            <?php _e("Let's Start", 'more-better-reviews-for-woocommerce'); ?>
                        </button>
                    </p>
                    <?php
                }
            }
            ?>
        </div>
    </div>

    <?php
    if ('required_plugin' !== $wizard_step) {
        ?>
        <div id="wizard-footer">
            <div class="navigation-wizard-holder">
                <p id="navigation-wizard-warning" class="warning-text" style="margin-bottom:10px;margin-top:0;display:none;">
                    <strong>
                        <?php _e('Do not forget to save your settings before going to next step.', 'more-better-reviews-for-woocommerce'); ?>
                    </strong>
                </p>
                <?php
                if ('intro' !== $wizard_step) {
                    if (!empty($prev_step) && 'intro' !== $prev_step) {
                        ?>
                        <button class="button go-to-step" data-step="<?php echo $prev_step; ?>" type="button">
                            &#171; <?php echo __('Back to', 'more-better-reviews-for-woocommerce') . ': ' . $prev_val["title"]; ?>
                        </button>
                        <?php
                    }

                    if (!empty($next_step)) {
                        ?>
                        <button class="button button-success go-to-step" data-step="<?php echo $next_step; ?>" type="button">
                            <?php echo __('Next', 'more-better-reviews-for-woocommerce') . ': ' . $next_val["title"]; ?> &#187;
                        </button>
                        <?php
                    }
                }
                ?>
            </div>

            <?php
            if (empty($wizard_steps[$wizard_step]['required']) && !empty($next_step)) {
                ?>
                <div class="skip-wizard-holder" style="text-align: center;margin-top:25px;">
                    <button id="skip-wizard" class="button button-transparent button go-to-step" data-step="complete_wizard">
                        <?php _e('Skip installation wizard.', 'more-better-reviews-for-woocommerce'); ?>
                    </button>
                </div>
                <?php
            } elseif (empty($next_step)) {
                ?>
                <div class="skip-wizard-holder" style="text-align: center;margin-top:25px;">
                    <button class="button button-success button-large go-to-step" data-step="complete_wizard" type="button">
                        <?php echo __('Finish installation', 'more-better-reviews-for-woocommerce'); ?>
                    </button>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }
    ?>
</div>
