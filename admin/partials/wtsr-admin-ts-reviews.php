<?php
/**
 * Provide a admin area view for the plugin
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/admin/partials
 * @var $is_woocommerce_reviews_disabled_globally
 * @var $required_plugins_wp2leads
 * @var $is_wp2leads_installed
 * @var $default_send_via
 * @var $wtsr_email_send_via
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wtsr_is_dev_env = defined( 'WTSR_DEV_ENV' ) && WTSR_DEV_ENV;

$import_product_reviews = get_option('wtsr_ts_product_reviews_import', array());
$published_reviews_status = Wtsr_Wc_Review::get_ts_reviews_uuid_comments_array();
$published_reviews = array_keys($published_reviews_status);

$wc_products = wc_get_products(array(
    'status'  => array( 'private', 'publish' ),
    'limit'   => "-1",
    'orderby' => array(
        'menu_order' => 'ASC',
        'ID'         => 'DESC',
    ),
));

$wc_products_by_id = array();

foreach ($wc_products as $wc_product) {
    if (!empty($wc_product->get_title())) {
        $wc_product_label = $wc_product->get_title();

        if (!empty($wc_product->get_sku())) {
            $wc_product_label .= ' (' . __('SKU', 'more-better-reviews-for-woocommerce') . ': ' . $wc_product->get_sku() . ')';
        }

        $wc_products_by_id[$wc_product->get_ID()] = $wc_product_label;
    }
}

?>
<div class="wtsr-page-header">
    <h3><?php _e('Reviews', 'more-better-reviews-for-woocommerce'); ?></h3>
</div>

<!-- Reviews filter section start -->
<div id="reviews_actions" style="margin-bottom:15px;">
</div>

<div class="wtsr-settings-group">
    <div class="wtsr-settings-group-header">
        <h3><?php _e('Import Reviews from Trusted Shops', 'more-better-reviews-for-woocommerce'); ?></h3>
    </div>

    <div class="wtsr-settings-group-body">
        <?php
        if (empty($import_product_reviews['products'])) {
            ?>
            <div class="wtsr-row">
                <div class="wtsr-col-xs-12 wtsr-col-sm-12 wtsr-col-md-6 wtsr-col-lg-4">
                    <h4>
                        <?php _e('Get reviews from Trusted Shops', 'more-better-reviews-for-woocommerce'); ?>
                    </h4>
                </div>

                <div class="wtsr-col-xs-12 wtsr-col-sm-12 wtsr-col-md-6 wtsr-col-lg-8 wtsr-processing-holder">
                    <p style="text-align: center;">
                        <button class="button button-success button-large" id="wtsr_get_ts_reviews">
                            <?php _e('Get reviews from Trusted Shops', 'more-better-reviews-for-woocommerce'); ?>
                        </button>
                    </p>

                    <div id="wtsr_get_ts_reviews-processing" class="wtsr-spinner-holder">
                        <div class="wtsr-spinner"></div>
                    </div>
                </div>
            </div>
            <?php
        } else {
            ob_start();
            $is_import_available = false;
            $is_reviews_for_import = false;
            $is_mapped = false;

            foreach ($import_product_reviews['products'] as $uuid => $product) {
                ?>
                <tr>
                    <th class="check-column">
                        <!--                                        <input id="cb-select---><?php //echo $uuid; ?><!--" type="checkbox" value="--><?php //echo $uuid; ?><!--">-->
                    </th>

                    <td class="column-primary has-row-actions">
                        <strong><?php echo $product['name']; ?>
                            <?php
                            if (!empty($product['sku'])) {
                                ?>(<?php _e('SKU', 'more-better-reviews-for-woocommerce'); ?>: <?php echo $product['sku']; ?>)<?php
                            }
                            ?>

                        </strong><br>
                        <?php
                        if ($uuid === 'shop_review') {
                            _e('Not connected to any product, but for shop in general. You still can map it to any product, and publish on product page.', 'more-better-reviews-for-woocommerce');
                        }
                        ?>

                        <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                    </td>

                    <td data-colname="<?php _e('Reviews', 'more-better-reviews-for-woocommerce'); ?>">
                        <?php
                        if (!empty($product["productReviews"])) {
                        $count = 0;
                        ob_start();

                        foreach ($product["productReviews"] as $product_review) {
                            if (!empty($product_review["reviewer"]["email"]) && !in_array($product_review["uuid"], $published_reviews)) {
                                ?>
                                <div class="wtsr-row" style="margin-top:5px; margin-bottom:5px;">
                                    <div class="wtsr-col-xs-12 wtsr-col-sm-7 wtsr-col-md-8 wtsr-col-lg-8">
                                        <?php echo $product_review["comment"]; ?><br>
                                        <?php echo $product_review["creationDate"]; ?><br>
                                        <?php echo strtotime($product_review["creationDate"]); ?><br>
                                        <?php //echo $product_review["uuid"]; ?>
                                        <?php //var_dump($product_review["reviewer"]); ?>
                                    </div>
                                    <div class="wtsr-col-xs-12 wtsr-col-sm-5 wtsr-col-md-4 wtsr-col-lg-4">
                                        <strong><?php echo $product_review["mark"]; ?>*</strong>
                                    </div>
                                </div>
                                <?php
                                $count++;
                            }
                        }

                        $reviews_list = ob_get_clean();
                        ?>
                        <?php
                        if (empty($count)) {
                            ?>
                            <h4 style="margin-top: 0;">
                                <small><?php _e('No reviews for import', 'more-better-reviews-for-woocommerce'); ?></small>
                            <?php
                        } else {
                            $is_reviews_for_import = true;
                            ?>
                            <h4 style="margin-top: 0;" class="ts-reviews-accordeon closed">
                                <small><?php _e('Total reviews', 'more-better-reviews-for-woocommerce'); ?>:</small> <?php echo $count ?>
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                                <span class="dashicons dashicons-arrow-up-alt2"></span>
                                <?php
                        }
                        ?>
                        </h4>
                        <?php
                        if (!empty($count)) {
                            ?>
                            <div class="ts-reviews-accordeon-body closed">
                                <?php echo $reviews_list; ?>
                            </div>
                            <?php
                            }
                        }
                        ?>
                    </td>

                    <td data-colname="<?php _e('Woocommerce product title', 'more-better-reviews-for-woocommerce'); ?>">
                        <?php
                        if (!empty($wc_products_by_id)) {
                            $mapped = !empty($import_product_reviews["mapping"][$uuid]) ? $import_product_reviews["mapping"][$uuid] : '';
                            ?>
                            <p style="margin-top: 0;">
                                <select
                                        data-mapped="<?php echo $mapped; ?>"
                                        class="review_map_to_wc"
                                        name="review_map_to__<?php echo $uuid; ?>"
                                        id="review_map_to__<?php echo $uuid; ?>"
                                        style="width: 100%"
                                >
                                    <option value="">---- <?php _e('Select WC product', 'more-better-reviews-for-woocommerce'); ?> ----</option>
                                    <?php
                                    foreach ($wc_products_by_id as $product_map_to_id => $product_map_to) {
                                        $is_item_mapped = false;

                                        if ((int)$product_map_to_id ===  (int)$mapped) {
                                            $is_item_mapped = true;
                                            $is_mapped = true;
                                        }
                                        ?>
                                        <option value="<?php echo $product_map_to_id; ?>"<?php echo $is_item_mapped ? ' selected' : ''; ?>><?php echo $product_map_to; ?></option>
                                        <?php

                                        if (!empty($is_item_mapped) && !empty($count)) {
                                            $is_import_available = true;
                                        }
                                    }
                                    ?>
                                </select>
                            </p>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
            $table_list = ob_get_clean();
            ?>
            <div class="wtsr-row">
                <div class="wtsr-col-xs-12 wtsr-col-sm-12 wtsr-col-md-6 wtsr-col-lg-4">
                    <h4>
                        <?php _e('Reviews from Trusted Shops', 'more-better-reviews-for-woocommerce'); ?>
                    </h4>
                    <p>
                        <?php _e('Last updated', 'more-better-reviews-for-woocommerce'); ?>:
                        <strong><?php echo $import_product_reviews['last_update'] ?></strong>
                    </p>
                </div>

                <div class="wtsr-col-xs-12 wtsr-col-sm-12 wtsr-col-md-6 wtsr-col-lg-8 wtsr-processing-holder">
                    <?php
                    if (!empty($_GET['import'])) {
                        ?>
                        <div class="notice notice-success inline" style="text-align: center;padding: 10px">
                            <?php
                            echo sprintf(
                                __('Reviews imported successfully. You can manage them %shere%s.', 'more-better-reviews-for-woocommerce') ,
                                '<a href="'.get_admin_url(null, 'edit-comments.php?comment_status=moderated').'" target="_blank">',
                                '</a>'
                            );
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                    <p style="text-align: center;">
                        <button class="button" id="wtsr_get_ts_reviews" type="button">
                            <?php _e('Get updates from Trusted Shops', 'more-better-reviews-for-woocommerce'); ?>
                        </button>

                        <button class="button wtsr_map_reviews" id="wtsr_map_ts_reviews" type="button" disabled>
                            <?php _e('Save product mapping', 'more-better-reviews-for-woocommerce'); ?>
                        </button>

                        <?php
                        if ($is_import_available) {
                            ?>
                            <button
                                    class="button button-success"
                                    id="wtsr_import_ts_reviews"
                                    type="button"
                                    data-confirm="<?php _e('Are you sure you want to import all reviews from Trusted Shops to woocommerce products?', 'more-better-reviews-for-woocommerce'); ?>"
                            >
                                <?php _e('Import reviews', 'more-better-reviews-for-woocommerce'); ?>
                            </button>
                            <?php
                        }
                        ?>
                    </p>

                    <?php
                    if (!$is_import_available) {
                        ?>
                        <p style="text-align: center;">
                            <?php
                            if (!$is_mapped) {
                                _e('In order to import Trusted Shops reviews, you need to connect at least one of Trusted Shops Products with Woocommerce products and click "Save product mapping" button.', 'more-better-reviews-for-woocommerce');
                            } elseif (!$is_reviews_for_import) {
                                _e('There is no reviews for import at the moment. Click "Get updates from Trusted Shops" to get new reviews.', 'more-better-reviews-for-woocommerce');
                            } else {
                                _e('Some reviews can not be imported as far as they do not connected to any of Woocommerce product on your site. ', 'more-better-reviews-for-woocommerce');
                            }
                            ?>
                        </p>
                        <?php
                    }



                    if ($wtsr_is_dev_env) {
                        ?>
                        <p style="text-align: center;">
                            <button
                                    class="button button-danger button-small"
                                    id="wtsr_delete_ts_reviews"
                                    type="button"
                                    data-confirm="<?php _e('Are you sure you want to delete all Trusted Shops reviews from to woocommerce products?', 'more-better-reviews-for-woocommerce'); ?>"
                            >
                                <?php _e('Clear data and delete all reviews', 'more-better-reviews-for-woocommerce'); ?>
                            </button>
                        </p>
                        <?php
                    }
                    ?>

                    <div id="wtsr_get_ts_reviews-processing" class="wtsr-spinner-holder">
                        <div class="wtsr-spinner"></div>
                    </div>
                </div>
            </div>

            <div class="wtsr-row">
                <div class="wtsr-col-xs-12">
                    <form id="wtsr_ts_to_woo_connection_form" method="post">
                        <table id="reviews-list" class="wp-list-table widefat fixed striped pages">
                            <thead>
                            <tr>
                                <td class="manage-column column-cb check-column">
<!--                                    <label class="screen-reader-text" for="cb-select-all-1">--><?php //echo __( 'Select All' ) ?><!--</label>-->
<!--                                    <input id="cb-select-all-1" type="checkbox" />-->
                                </td>
                                <th class="column-primary"><?php _e('Trusted Shops product title', 'more-better-reviews-for-woocommerce'); ?></th>
                                <th><?php _e('Reviews', 'more-better-reviews-for-woocommerce'); ?></th>
                                <th><?php _e('Woocommerce product title', 'more-better-reviews-for-woocommerce'); ?></th>
                            </tr>
                            </thead>

                            <tbody id="the-list">
                            <?php
                            echo $table_list;
                            ?>
                            </tbody>

                            <tfoot>
                            <tr>
                                <td class="manage-column column-cb check-column">
<!--                                    <label class="screen-reader-text" for="cb-select-all-1">--><?php //echo __( 'Select All' ) ?><!--</label>-->
<!--                                    <input id="cb-select-all-1" type="checkbox" />-->
                                </td>
                                <th class="column-primary"><?php _e('Trusted Shops product title', 'more-better-reviews-for-woocommerce'); ?></th>
                                <th><?php _e('Reviews', 'more-better-reviews-for-woocommerce'); ?></th>
                                <th><?php _e('Woocommerce product title', 'more-better-reviews-for-woocommerce'); ?></th>
                            </tr>
                            </tfoot>
                        </table>
                    </form>
                </div>
            </div>
            <?php
        }
        ?>
    </div>

    <div class="wtsr-settings-group-footer">

    </div>
</div>

<div class="wtsr-page-footer">

</div>
