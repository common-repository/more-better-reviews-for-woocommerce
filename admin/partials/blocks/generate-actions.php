<?php
/**
 * @var $order_statuses
 * @var $selected_order_status
 * @var $start_order_date
 * @var $end_order_date
 */
?>
<?php _e('Order status', 'more-better-reviews-for-woocommerce'); ?>:
<select name="wtsr_filter_status" id="wtsr_filter_status" class="form-input" style="max-width:160px;display:inline-block;margin-right:20px;">
    <?php
    foreach ($order_statuses as $slug => $label) {
        ?>
        <option value="<?php echo $slug ?>"<?php echo $slug === $selected_order_status ? ' selected' : ''; ?>><?php echo $label ?></option>
        <?php
    }
    ?>
</select>

<?php _e('Start / End dates', 'more-better-reviews-for-woocommerce'); ?>:
<input id="wtsr_start_order_date" name="wtsr_start_order_date" type="text"
       value="<?php echo $start_order_date; ?>"
       class="wtsr-datepicker form-input" style="max-width:120px;display:inline-block;" readonly> -

<input id="wtsr_end_order_date" name="wtsr_end_order_date" type="text"
       value="<?php echo $end_order_date; ?>"
       class="wtsr-datepicker form-input" style="max-width:120px;display:inline-block;margin-right:20px;" readonly>

<?php _e('Show orders', 'more-better-reviews-for-woocommerce'); ?>:
<select name="wtsr_filter_number" id="wtsr_filter_number" class="form-input" style="max-width:60px;display:inline-block;margin-right:20px;">
    <option value="50" <?php echo '50' === $filter_number ? ' selected' : ''; ?>>50</option>
    <option value="100" <?php echo '100' === $filter_number ? ' selected' : ''; ?>>100</option>
    <option value="200" <?php echo '200' === $filter_number ? ' selected' : ''; ?>>200</option>
    <option value="500" <?php echo '500' === $filter_number ? ' selected' : ''; ?>>500</option>
    <option value="all" <?php echo 'all' === $filter_number ? ' selected' : ''; ?>>All</option>
</select>

<button type="submit" class="button button-primary" style="height:34px;line-height:32px;">
    <?php _e('Filter', 'more-better-reviews-for-woocommerce'); ?>
</button>
