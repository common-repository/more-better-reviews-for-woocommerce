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

$wtsr_all_reviews_page = get_option('wtsr_all_reviews_page', false);
$wtsr_all_reviews_page_exists = false;

if ($wtsr_all_reviews_page) {
    $page_object = get_post($wtsr_all_reviews_page);
    if (!empty($page_object)) {
        $wtsr_all_reviews_page_exists = true;
    }
}

$current_user = wp_get_current_user();
$current_user_email = $current_user->user_email;
$wtsr_hover_colors = TSManager::get_hover_colors();
$wtsr_aiop_button_colors = TSManager::get_aiop_button_colors();
$wtsr_uploaded_image = get_option('wtsr_uploaded_image', '');
$wtsr_all_reviews_page_footer_template_editor = get_option('wtsr_all_reviews_page_footer_template_editor', '');
$wtsr_all_reviews_page_description = get_option('wtsr_all_reviews_page_description', false);
$wtsr_all_reviews_page_reviews_title = get_option('wtsr_all_reviews_page_reviews_title');

if ( false === $wtsr_all_reviews_page_reviews_title ) {
    $wtsr_all_reviews_page_reviews_title = __( 'Review now!', 'more-better-reviews-for-woocommerce' );
}

$wtsr_all_reviews_page_comment_placeholder = get_option('wtsr_all_reviews_page_comment_placeholder', false);

if (false === $wtsr_all_reviews_page_comment_placeholder) {
    ob_start();
    ?><?php echo __( 'Describe your experiences with the product here', 'more-better-reviews-for-woocommerce' );
    echo PHP_EOL . PHP_EOL . __( 'Why did you choose this product?', 'more-better-reviews-for-woocommerce' );
    echo PHP_EOL . __( 'What did you like in particular?', 'more-better-reviews-for-woocommerce' );
    echo PHP_EOL . __( 'Would you recommend this product?', 'more-better-reviews-for-woocommerce' ); ?><?php
    $wtsr_all_reviews_page_comment_placeholder = ob_get_clean();
}

if (empty($wtsr_all_reviews_page_description)) {
    $wtsr_all_reviews_page_description = 'yes';
}

$wtsr_all_reviews_page_product_link = get_option('wtsr_all_reviews_page_product_link', 'no');
$textarea_min_length = get_option('wtsr_all_reviews_page_reviews_min');

if (false === $textarea_min_length) {
    $textarea_min_length = 50;
}
?>

<div class="settings-container">
    <div class="settings-item">
        <?php
        if ($wtsr_all_reviews_page && $wtsr_all_reviews_page_exists) {
            $page_sample_url = get_permalink($wtsr_all_reviews_page) . '?sample=' . md5($current_user_email);
            $aior_page_title = get_the_title( $wtsr_all_reviews_page );

            if (empty(trim($aior_page_title))) {
                $aior_page_title = '[' . __('No title', 'more-better-reviews-for-woocommerce') . ']';
            }
            ?>
            <div class="settings-item-label">
                <h3>
                    <span style="font-weight:100;"><?php echo __('Page title', 'more-better-reviews-for-woocommerce'); ?>:</span>
                    <?php echo $aior_page_title; ?>
                </h3>
            </div>

            <div class="settings-control">
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
            <div class="settings-item-value" style="margin-bottom:10px;">
                <input
                        class="form-input"
                        type="text"
                        id="wtsr_all_reviews_page_title"
                        value=""
                        placeholder="<?php echo __('Input page title', 'more-better-reviews-for-woocommerce'); ?>"
                >
            </div>

            <p class="settings-item-description">
                <?php echo __('You can create custom page, where customers can review all products from order at once.', 'more-better-reviews-for-woocommerce'); ?>
                <br>
                <?php echo __('Just input a title you want to use and we will generate this page for you.', 'more-better-reviews-for-woocommerce'); ?>
                <?php echo __('After page will be created, you can adit and add any type of content to be shown for your customers in wordpress editor.', 'more-better-reviews-for-woocommerce'); ?>
                <br><br>
                <?php echo __('Otherwise your client will be redirected to default all in one reviews page.', 'more-better-reviews-for-woocommerce'); ?>
            </p>

            <div class="settings-control">
                <button id="wtsr_all_reviews_page_create" type="button" class="button button-primary">
                    <?php echo __('Create page', 'more-better-reviews-for-woocommerce'); ?>
                </button>

                <a href="<?php echo home_url( '/shop-review-page' ) . '?sample=' . md5($current_user_email); ?>" target="_blank" class="button button-primary">
                    <?php echo __('View default page sample', 'more-better-reviews-for-woocommerce'); ?>
                </a>
            </div>
            <?php
        }
        ?>
    </div>

    <div class="settings-item">
        <div class="settings-item-group">
            <div class="settings-item-group-col">
                <p class="settings-item-label">
                    <?php echo __('Min comment length', 'more-better-reviews-for-woocommerce'); ?>
                    (<?php echo __('characters', 'more-better-reviews-for-woocommerce'); ?>)
                </p>
            </div>

            <div class="settings-item-group-col">
                <div class="settings-item-value">
                    <input class="form-input" name="wtsr_all_reviews_page_reviews_min" id="wtsr_all_reviews_page_reviews_min" type="number" value="<?php echo $textarea_min_length ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="settings-item">
        <div class="settings-item-group">
            <div class="settings-item-group-col">
                <p class="settings-item-label">
                    <?php echo __('Comment field placeholder', 'more-better-reviews-for-woocommerce'); ?>
                </p>
            </div>

            <div class="settings-item-group-col">
                <div class="settings-item-value">
                    <textarea id="wtsr_all_reviews_page_comment_placeholder" name="wtsr_all_reviews_page_comment_placeholder" class="form-input" cols="30" rows="6"
                    ><?php echo $wtsr_all_reviews_page_comment_placeholder; ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-item">
        <h3 style="text-align:center;"><?php _e('All-in-one review page design', 'more-better-reviews-for-woocommerce'); ?></h3>

        <div class="settings-item-label">
            <?php _e('All-in-one reviews page colors', 'more-better-reviews-for-woocommerce'); ?>
        </div>

        <div class="settings-item-value" style="margin-top:20px">
            <div id="wtsr_page_color_selector_holder">
                <div>
                    <div style="display: inline-block;vertical-align: sub;margin-right: 20px;min-width:150px;">
                        <strong><?php echo __('Normal title color', 'more-better-reviews-for-woocommerce'); ?></strong>:
                    </div> <input id="wtsr_normal_title_color" class="wtsr-color-picker" type="text" value="<?php echo $wtsr_hover_colors['normal'] ?>">
                </div>

                <div>
                    <div style="display: inline-block;vertical-align: sub;margin-right: 20px;min-width:150px;">
                        <strong><?php echo __('Hover title color', 'more-better-reviews-for-woocommerce'); ?></strong>:
                    </div> <input id="wtsr_hover_title_color" class="wtsr-color-picker" type="text" value="<?php echo $wtsr_hover_colors['hover'] ?>">
                </div>
            </div>

            <div id="wtsr_page_color_selector_holder" style="margin-top:5px">
                <div>
                    <div style="display: inline-block;vertical-align: sub;margin-right: 20px;min-width:150px;">
                        <strong><?php echo __('Norma button bg color', 'more-better-reviews-for-woocommerce'); ?></strong>:
                    </div> <input id="wtsr_normal_button_bg_color" class="wtsr-color-picker" type="text" value="<?php echo $wtsr_aiop_button_colors['normal_bg'] ?>">
                </div>

                <div>
                    <div style="display: inline-block;vertical-align: sub;margin-right: 20px;min-width:150px;">
                        <strong><?php echo __('Normal button text color', 'more-better-reviews-for-woocommerce'); ?></strong>:
                    </div> <input id="wtsr_normal_button_txt_color" class="wtsr-color-picker" type="text" value="<?php echo $wtsr_aiop_button_colors['normal_txt'] ?>">
                </div>
            </div>

            <div id="wtsr_page_color_selector_holder" style="margin-top:5px">
                <div>
                    <div style="display: inline-block;vertical-align: sub;margin-right: 20px;min-width:150px;">
                        <strong><?php echo __('Hover button bg color', 'more-better-reviews-for-woocommerce'); ?></strong>:
                    </div> <input id="wtsr_hover_button_bg_color" class="wtsr-color-picker" type="text" value="<?php echo $wtsr_aiop_button_colors['hover_bg'] ?>">
                </div>

                <div>
                    <div style="display: inline-block;vertical-align: sub;margin-right: 20px;min-width:150px;">
                        <strong><?php echo __('Normal button text color', 'more-better-reviews-for-woocommerce'); ?></strong>:
                    </div> <input id="wtsr_hover_button_txt_color" class="wtsr-color-picker" type="text" value="<?php echo $wtsr_aiop_button_colors['hover_txt'] ?>">
                </div>
            </div>
        </div>

        <div class="settings-item-label" style="margin-top:20px">
            <?php _e('All-in-one reviews page header logo', 'more-better-reviews-for-woocommerce'); ?>
        </div>

        <div class="settings-item-value">
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

            <button id="wtsr_upload_image_btn" class="button button-primary button-small" type="button"<?php echo !empty($wtsr_uploaded_image) ? ' style="display:none;"' : '' ?>>
                <?php _e('Upload logo image', 'more-better-reviews-for-woocommerce'); ?>
            </button>

            <button id="wtsr_delete_image_btn" class="button button-primary button-small" type="button"<?php echo empty($wtsr_uploaded_image) ? ' style="display:none;"' : '' ?>>
                <?php _e('Delete logo image', 'more-better-reviews-for-woocommerce'); ?>
            </button>

            <p>
                <?php _e('Recommended image size not more than 390x90 px', 'more-better-reviews-for-woocommerce'); ?>
            </p>
        </div>

        <div class="settings-item-label" style="margin-top:20px">
            <?php _e('Review block title', 'more-better-reviews-for-woocommerce'); ?>
        </div>

        <div class="settings-item-value">
            <input
                    class="form-input"
                    type="text"
                    id="wtsr_all_reviews_page_reviews_title"
                    value="<?php echo $wtsr_all_reviews_page_reviews_title; ?>"
                    placeholder="<?php echo __('Input reviews block title', 'more-better-reviews-for-woocommerce'); ?>"
            >
        </div>

        <div class="settings-item-label" style="margin-top:20px">
            <?php _e('All-in-one reviews page footer content', 'more-better-reviews-for-woocommerce'); ?>
        </div>

        <div class="settings-item-value">
            <?php wp_editor( $wtsr_all_reviews_page_footer_template_editor, 'wtsr_all_reviews_page_footer_template_editor', array(
                'textarea_rows' => 12,
                'wpautop'       => 1,
            ) ); ?>
        </div>

        <div class="settings-item-value">
            <div id="wtsr_page_color_selector_holder" style="margin-top:15px;">
                <div style="margin-bottom:10px;">
                    <strong><?php _e('Show product description', 'more-better-reviews-for-woocommerce'); ?></strong> <input id="wtsr_all_reviews_page_description" type="checkbox" value="1"<?php echo 'yes' === $wtsr_all_reviews_page_description ? ' checked' : ''; ?>>
                </div>

                <div style="margin-bottom:10px;">
                    <strong><?php _e('Add link to product page', 'more-better-reviews-for-woocommerce'); ?></strong> <input id="wtsr_all_reviews_page_product_link" type="checkbox" value="1"<?php echo 'yes' === $wtsr_all_reviews_page_product_link ? ' checked' : ''; ?>>
                </div>
            </div>
        </div>

        <div class="settings-control" style="margin-top:20px">
            <button id="wtsr_all_reviews_page_colors_save" type="button" class="button button-primary">
                <?php echo __('Save All-in-one reviews page settings', 'more-better-reviews-for-woocommerce'); ?>
            </button>
        </div>
    </div>
</div>