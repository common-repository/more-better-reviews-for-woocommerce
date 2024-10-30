<?php
$checkout_url = get_permalink( wc_get_page_id( 'checkout' ) );

$checkout_link = '<a href="'.$checkout_url.'" target="_blank">' . __('your checkout', 'more-better-reviews-for-woocommerce') . '</a>';
?>

<?php echo __('If you select <strong>"Yes"</strong> only customer who checked "I want to give review" on checkout page will get review request.', 'more-better-reviews-for-woocommerce'); ?>
<?php echo __('If you select <strong>"No"</strong> all customers will get review request.', 'more-better-reviews-for-woocommerce'); ?>
    <br>
<?php
$test_string = __('As the default Woocommerce integration of the "I want to review" checkbox depends on your used theme, custom CSS, payment plugins, we can not guarantee how and where it is viewed. If you select "Yes", please test integration by visiting %s.', 'more-better-reviews-for-woocommerce');
echo sprintf($test_string, $checkout_link);
?>