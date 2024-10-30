<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email' ) ) {
    return;
}

class Wtsr_Wc_Review_Request_Email extends WC_Email {
    public function __construct() {
        $this->id = 'wc_customer_review_request';
        $this->title = __( 'Review Request to Customer', 'more-better-reviews-for-woocommerce' );
        $this->description = __( 'An email sent to the customer for review request.', 'more-better-reviews-for-woocommerce' );
        $this->customer_email = true;
        $this->heading     = __( 'Review now!', 'more-better-reviews-for-woocommerce' );
        $this->subject     = sprintf( _x( '[%s] Review Request', 'default email subject for cancelled emails sent to the customer', 'more-better-reviews-for-woocommerce' ), '{blogname}' );

        $this->email_type = $this->get_option( 'email_type', 'multipart' );
        $this->template_html  = 'emails/wc-customer-review-request.php';
        $this->template_plain = 'emails/plain/wc-customer-review-request.php';
        $this->template_base = untrailingslashit( plugin_dir_path( WTSR_PLUGIN_FILE ) ) . '/templates/';

        parent::__construct();
    }

    /**
     * @param $order_id
     */
    public function trigger( $order_id, $email = null, $scheduled = null ) {
        global $email_is_live;
        $email_is_live = true;

        $this->object = wc_get_order( $order_id );

        if (empty($email)) {
            if ( version_compare( '3.0.0', WC()->version, '>' ) ) {
                $order_email = $this->object->billing_email;
            } else {
                $order_email = $this->object->get_billing_email();
            }

            $this->recipient = $order_email;
        } else {
            $this->recipient = $email;
            $email_is_live = false;
        }

        add_filter('wtsr_max_email_template_length', 'wtsr_unlim_email_template_length');
        $send = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        remove_filter('wtsr_max_email_template_length', 'wtsr_unlim_email_template_length');

        if ($send && empty($email)) { // If review request sent and not test mode
            do_action('wtsr_woo_review_request_email_sent', $order_id, $scheduled);
        }
    }

    public function get_content_html() {
        return wc_get_template_html( $this->template_html, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => false,
            'email'			=> $this
        ), '', $this->template_base );
    }

    public function get_content_plain() {
        return wc_get_template_html( $this->template_plain, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => true,
            'email'			=> $this
        ), '', $this->template_base );
    }
}