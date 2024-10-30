<?php
/**
 * Provide a admin area view for the plugin
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/admin/partials
 * @var $is_woocommerce_reviews_disabled_globally
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$review_id = sanitize_text_field( $_GET['review_id'] );
$reviews = ReviewsModel::get( $review_id );
$reviews_with_template = ReviewsModel::get_with_template('DESC', array(), 'id, order_id, email, status, review_created, review_sent');
$current_index = 0;
$first = false;
$last = false;
$next = false;
$prev = false;

foreach ($reviews_with_template as $index => $data) {
    if ($review_id === $data->id) {
        $current_index = $index;
    }
}

if (0 === $current_index) {
    $first = true;
}

$next_index = $current_index + 1;

if (empty($reviews_with_template[$next_index])) {
    $last = true;
}

if (!$first) {
    $prev_index = $current_index - 1;
    $prev = $reviews_with_template[$prev_index]->id;
}

if (!$last) {
    $next = $reviews_with_template[$next_index]->id;
}

$wtsr_email_send_via = TSManager::get_email_send_via();
?>

<h3><?php _e('Review requests preview', 'more-better-reviews-for-woocommerce'); ?></h3>

<div id="top_review_navigation" class="review-navigation">
    <?php
    if ($prev) {
        ?>
        <a class="button button-primary" href="?page=wp2leads-wtsr&tab=reviews&review_id=<?php echo $prev; ?>#wtsr-nav-tab">
            &#171; <?php _e('Previous request', 'more-better-reviews-for-woocommerce'); ?>
        </a>
        <?php
    }

    if ($next) {
        ?>
        <a class="button button-primary" href="?page=wp2leads-wtsr&tab=reviews&review_id=<?php echo $next; ?>#wtsr-nav-tab">
            <?php _e('Next request', 'more-better-reviews-for-woocommerce'); ?> &#187;
        </a>
        <?php
    }
    ?>
</div>

<?php
if (!empty($reviews)) {
    if ('woocommerce' === $wtsr_email_send_via) {
        ?>
        <div style="max-width:800px;margin:auto;">
            <h3><?php _e('Test the Spammyness of your Emails', 'more-better-reviews-for-woocommerce'); ?></h3>
            <p>
                <?php _e('Get your testing email address on <a href="https://www.mail-tester.com/" target="_blank">https://www.mail-tester.com/</a>.', 'more-better-reviews-for-woocommerce'); ?>
                <?php _e('Copy it and paste into the field below and click "Send email" button.', 'more-better-reviews-for-woocommerce'); ?>
                <?php _e('After this go back to previously opened mail-tester page and click "Then check your score" button.', 'more-better-reviews-for-woocommerce'); ?>
            </p>

            <div class="wtsr_input-group_holder">
                <div class="wtsr_input_holder">
                    <input
                        class="form-input"
                        type="text"
                        id="wtsr_email_test_score"
                        <?php echo 'woocommerce' !== $wtsr_email_send_via ? 'disabled' : ''; ?>
                        placeholder="<?php _e('Paste email address', 'more-better-reviews-for-woocommerce'); ?>"
                    >
                </div>

                <div class="wtsr_btn_holder">
                    <button
                        id="wtsr_email_test_score_button"
                        class="button button-primary"
                        data-review="<?php echo $review_id; ?>"
                        data-template="review_email"
                    >
                        <?php _e('Send email', 'more-better-reviews-for-woocommerce'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    foreach ($reviews as $review) {
        $thankyou_template = Wtsr_Template::get_email_thankyou_html_template($review_id, false);
        ?>
        <div id="wtsr_email_html_templates_preview" style="max-width:800px;margin:auto;">
            <?php
            if (!empty($thankyou_template)) {
                ?>
                <div id="wtsr_email_html_templates_preview_control">
                    <div class="nav-tab-wrapper">
                        <a href="#" class="nav-tab nav-tab-active" data-tab="review_email"><?php _e('Review request email', 'more-better-reviews-for-woocommerce'); ?></a>
                        <a href="#" class="nav-tab" data-tab="thankyou_email"><?php _e('Thank you email', 'more-better-reviews-for-woocommerce'); ?></a>
                    </div>
                </div>
                <?php
            }
            ?>

            <div id="wtsr_review_email_template_preview_container" class="wtsr_email_html_templates_preview_item">
                <div id="wtsr_email_template_preview" style="padding:10px;border:1px solid #ddd;border-radius:5px;background-color:#fff;margin-top:35px;margin-bottom:35px;">
                    <?php Wtsr_Template::preview_email_html_template($review); ?>
                </div>
            </div>

            <?php
            if (!empty($thankyou_template)) {
                ?>
                <div id="wtsr_thankyou_email_template_preview_container" class="wtsr_email_html_templates_preview_item" style="display: none;">
                    <p>
                        <?php _e('This template only for previewing and test spammyness, no coupon will be generated.', 'more-better-reviews-for-woocommerce'); ?>
                    </p>
                    <div id="wtsr_thankyouemail_template_preview" style="padding:10px;border:1px solid #ddd;border-radius:5px;background-color:#fff;margin-top:10px;margin-bottom:35px;">
                        <?php echo wpautop($thankyou_template); ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }
}
?>

<div class="review-navigation">
    <?php
    if ($prev) {
        ?>
        <a class="button button-primary" href="?page=wp2leads-wtsr&tab=reviews&review_id=<?php echo $prev; ?>">
            &#171; <?php _e('Previous request', 'more-better-reviews-for-woocommerce'); ?>
        </a>
        <?php
    }

    if ($next) {
        ?>
        <a class="button button-primary" href="?page=wp2leads-wtsr&tab=reviews&review_id=<?php echo $next; ?>">
            <?php _e('Next request', 'more-better-reviews-for-woocommerce'); ?> &#187;
        </a>
        <?php
    }
    ?>
</div>

