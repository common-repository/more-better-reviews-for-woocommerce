<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly more-better-reviews-for-woocommerce
}

if ( ! class_exists( 'WC_Email' ) ) {
    return;
}

class Wtsr_WC_Email_Customer_Coupon extends WC_Email {
    public function __construct() {
        $this->id = 'wc_customer_coupon_request';
        $this->customer_email = true;
        $this->title = __( 'Thank you for review email', 'more-better-reviews-for-woocommerce' );
        $this->description = __( 'An thank you email sent to the customer after review approved.', 'more-better-reviews-for-woocommerce' );
        $this->template_html  = 'emails/wc-customer-review-thankyou.php';
        $this->template_plain = 'emails/plain/wc-customer-review-thankyou.php';
        $this->template_base = untrailingslashit( plugin_dir_path( WTSR_PLUGIN_FILE ) ) . '/templates/';


        $this->heading     = __( 'Thank you for your review', 'more-better-reviews-for-woocommerce' );
        $this->subject     = sprintf( _x( '[%s] - Thank you for your review', 'default email subject for emails sent to the customer', 'more-better-reviews-for-woocommerce' ), '{blogname}' );

        $this->email_type = $this->get_option( 'email_type', 'multipart' );

        parent::__construct();
    }

    /**
     * @param $coupon_id
     * @param $email
     *
     * @return bool
     */
    public function trigger( $review_id, $email = false ) {
        $review = ReviewsModel::get_by_id($review_id);

        if (empty($review)) {
            return false;
        }

        if ($email) {
            $message =  Wtsr_Template::get_email_thankyou_html_template($review_id, false);
        } else {
            $message = ReviewsModel::get_meta($review_id, 'thankyou_email_template', true);
            $review = ReviewsModel::get_by_id($review_id);
            $email = $review['email'];
        }

        if (empty($message)) {
            return false;
        }

        $this->setup_locale();

        $this->object = $message;

        $this->recipient = $email;

        $send = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

        if ($send) {
            do_action('wooaiocoupon_thankyou_email_sent', $review_id);
        }

        $this->restore_locale();

        return $send;
    }

    public function get_content_html() {
        return wc_get_template_html( $this->template_html, array(
            'message'         => $this->object,
            'email_heading' => $this->get_heading(),
            'additional_content' => $this->get_additional_content(),
            'sent_to_admin' => false,
            'plain_text'    => false,
            'email'			=> $this
        ), '', $this->template_base );
    }

    public function get_content_plain() {
        return wc_get_template_html( $this->template_plain, array(
            'message'         => $this->object,
            'email_heading' => $this->get_heading(),
            'additional_content' => $this->get_additional_content(),
            'sent_to_admin' => false,
            'plain_text'    => true,
            'email'			=> $this
        ), '', $this->template_base );
    }
}
