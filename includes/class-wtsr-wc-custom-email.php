<?php
class Wtsr_Wc_Custom_Email {
    public function __construct() {
        add_action( 'woocommerce_email_classes', array( $this, 'register_email' ), 90, 1 );
    }

    public function register_email( $emails ) {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wtsr-wc-review-request-email.php';

        $emails['Wtsr_WC_Email_Review_Request'] = new Wtsr_Wc_Review_Request_Email();

        return $emails;
    }
}

new Wtsr_Wc_Custom_Email();


class Wtsr_Coupon_Custome_Email {
    public function __construct() {
        add_action( 'woocommerce_email_classes', array( $this, 'register_email' ), 90, 1 );
    }

    public function register_email( $emails ) {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/Wtsr_WC_Email_Customer_Coupon.php';

        $emails['Wtsr_WC_Email_Customer_Coupon'] = new Wtsr_WC_Email_Customer_Coupon();

        return $emails;
    }
}

new Wtsr_Coupon_Custome_Email();