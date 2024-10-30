<?php
/**
 * @var $orders_list
 * @var $orders_list
 */
?>

<table class="wp-list-table widefat fixed striped pages">
    <thead>
    <tr>
        <td class="manage-column column-cb check-column">
            <label class="screen-reader-text" for="cb-select-all-1"><?php echo __( 'Select All', 'more-better-reviews-for-woocommerce' ) ?></label>
            <input id="cb-select-all-1" type="checkbox" />
        </td>
        <th class="column-primary"><?php _e('Order', 'more-better-reviews-for-woocommerce'); ?></th>
        <th><?php _e('Status', 'more-better-reviews-for-woocommerce'); ?></th>
        <th><?php _e('Email', 'more-better-reviews-for-woocommerce'); ?></th>
        <th><?php _e('Created', 'more-better-reviews-for-woocommerce'); ?></th>
        <?php
        if (!empty($reviews_not_allowed)) {
            ?><th><?php _e('Want to give review', 'more-better-reviews-for-woocommerce'); ?></th><?php
        }
        ?>
        <th></th>
    </tr>
    </thead>

    <tbody id="the-list">
    <?php
    if (!empty($orders_count)) {
        echo $orders_list;
    } else {
        $colspan = !empty($reviews_not_allowed) ? 7 : 6;
        ?>
        <tr>
            <td colspan="<?php echo $colspan ?>">
                <p style="text-align: center;margin: 0;">
                    <?php _e('You do not have any orders for generating reviews', 'more-better-reviews-for-woocommerce'); ?>
                </p>
            </td>
        </tr>
        <?php
    }
    ?>
    </tbody>

    <tfoot>
    <tr>
        <td class="manage-column column-cb check-column">
            <label class="screen-reader-text" for="cb-select-all-2"><?php echo __( 'Select All', 'more-better-reviews-for-woocommerce' ) ?></label>
            <input id="cb-select-all-2" type="checkbox" />
        </td>
        <th class="column-primary"><?php _e('Order', 'more-better-reviews-for-woocommerce'); ?></th>
        <th><?php _e('Status', 'more-better-reviews-for-woocommerce'); ?></th>
        <th><?php _e('Email', 'more-better-reviews-for-woocommerce'); ?></th>
        <th><?php _e('Created', 'more-better-reviews-for-woocommerce'); ?></th>
        <?php
        if (!empty($reviews_not_allowed)) {
            ?><th><?php _e('Want to give review', 'more-better-reviews-for-woocommerce'); ?></th><?php
        }
        ?>
        <th></th>
    </tr>
    </tfoot>
</table>