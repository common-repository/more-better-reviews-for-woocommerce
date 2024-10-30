<?php
/**
 * @var $coupon_id
 * @var $email_heading
 */

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-="  . PHP_EOL;
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo PHP_EOL . "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=" . PHP_EOL  . PHP_EOL;

echo esc_html( wp_strip_all_tags( wpautop($message) ) );

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
    echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
    echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
