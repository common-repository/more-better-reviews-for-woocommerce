<?php

$order_id = $order->get_id();
$review = ReviewsModel::get_by_order_id($order_id);

if (empty($review)) {
    return;
}
$order = wc_get_order( $order_id );

if (!$order) {
    return '';
}

$order_data = $order->get_data();

$order_first_name = !empty($order_data['billing']['first_name']) ? $order_data['billing']['first_name'] : '';
$order_last_name = !empty($order_data['billing']['last_name']) ? $order_data['billing']['last_name'] : '';

$link = $review[0]->review_link;
$message = $review[0]->review_message;
$wtsr_email_template_editor = get_option('wtsr_email_template', TSManager::get_default_email_template_editor());
if (false !== strpos($wtsr_email_template_editor, '{order_number}')) {
    $wtsr_email_template_editor = str_replace( '{order_number}', $order_id, $wtsr_email_template_editor );
}

if (false !== strpos($wtsr_email_template_editor, '{customer_fn}')) {
    $wtsr_email_template_editor = str_replace( '{customer_fn}', $order_first_name, $wtsr_email_template_editor );
}

if (false !== strpos($wtsr_email_template_editor, '{customer_ln}')) {
    $wtsr_email_template_editor = str_replace( '{customer_ln}', $order_last_name, $wtsr_email_template_editor );
}

if (false !== strpos($wtsr_email_template_editor, '{order_date}')) {
    $wtsr_email_template_editor = str_replace( '{order_date}', $order_date_created = $order_data['date_created']->date(get_option( 'date_format' )), $wtsr_email_template_editor );
}

if (false !== strpos($wtsr_email_template_editor, '{order_items_table}')) {
    $order_items_table = Wtsr_Template::get_email_plain_template($order_id, $link);

    $wtsr_email_template_editor = str_replace( '{order_items_table}', $order_items_table, $wtsr_email_template_editor );
}

if (false !== strpos($wtsr_email_template_editor, '{order_items_table_one_col}')) {
    $order_items_table = Wtsr_Template::get_email_plain_template($order_id, $link);

    $wtsr_email_template_editor = str_replace( '{order_items_table_one_col}', $order_items_table, $wtsr_email_template_editor );
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-="  . PHP_EOL;
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo PHP_EOL . "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=" . PHP_EOL  . PHP_EOL;
echo esc_html( wp_strip_all_tags( $wtsr_email_template_editor ) );
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
