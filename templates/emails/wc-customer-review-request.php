<?php
/**
 * Review request send to customer
 *
 * @var $email_heading
 * @var $order
 * @var $email
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $email_is_live;
$order_id = $order->get_id();
$review = ReviewsModel::get_by_order_id($order_id);

if (empty($review)) {
    return;
}

$order = wc_get_order( $order_id );

if (!$order) {
    return '';
}

$link = $review[0]->review_link;
?>
<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
    <?php printf( __( 'Review request for order #%d.', 'more-better-reviews-for-woocommerce' ), $order->get_order_number() ); ?>
</p>

<?php
echo wpautop($wtsr_email_template_editor = Wtsr_Template::get_review_request_html($order_id, !empty($email_is_live)));
?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>