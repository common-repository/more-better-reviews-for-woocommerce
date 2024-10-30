<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/includes
 */

/**
 * @since      0.0.1
 * @package    Wtsr
 * @subpackage Wtsr/includes/lib
 * @author     Tobias Conrad <tc@santegra.de>
 */
class TSApi {
    /**
     * Trusted Shops ID
     * @var string
     */
    private $ts_id = '';

    /**
     * Trusted Shops Email
     * @var string
     */
    private $ts_email = '';

    /**
     * Trusted Shops Password
     * @var string
     */
    private $ts_password = '';

    /**
     * Trusted Shops URL
     * @var string
     */
    private $ts_protocol = 'http://';

    /**
     * Trusted Shops URL
     * @var string
     */
    private $ts_url = 'api.trustedshops.com/rest';

    /**
     * Trusted Shops Access
     * @var string
     */
    private $ts_access = 'restricted';

    /**
     * Trusted Shops API Version
     * @var string
     */
    private $ts_version = 'v2';

    public function __construct($access = 'restricted', $ts_id = '', $ts_email = '', $ts_password = '' ) {

        if ('restricted' === $access) {
            $this->ts_protocol = 'https://';
        }

        $ts_credentials = TSManager::get_ts_credentials();

        if (!empty($ts_id)) {
            $ts_credentials['ts_id'] = $ts_id;
        }

        if (!empty($ts_email)) {
            $ts_credentials['ts_email'] = $ts_email;
        }

        if (!empty($ts_password)) {
            $ts_credentials['ts_password'] = $ts_password;
        }

        $this->ts_id = $ts_credentials['ts_id'];
        $this->ts_email = $ts_credentials['ts_email'];
        $this->ts_password = $ts_credentials['ts_password'];
        $this->ts_access = $access;
    }

    private function get_request($apiUrl, $headers = array()) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if ('restricted' === $this->ts_access) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->ts_email . ":" . $this->ts_password . '');
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        $output = curl_exec($ch);

        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        // return $contentType;

        curl_close($ch);

        return TSManager::prepare_response($output, $contentType);
    }

    private function post_request($apiUrl, $postData) {
        $ch = curl_init();

        $headers  = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ];

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ('restricted' === $this->ts_access) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->ts_email . ":" . $this->ts_password . '');
        }

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        $output = curl_exec($ch);

        // return $output;

        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        // return $contentType;

        curl_close($ch);

        return TSManager::prepare_response($output, $contentType);
    }

    private function get_api_url() {
        return $this->ts_protocol . $this->ts_url . '/' . $this->ts_access . '/' . $this->ts_version;
    }

    public function get_shop_reviews() {
        $api_url = $this->get_api_url() . '/shops/' . $this->ts_id . '/reviews';

        // return $api_url;

        return $this->get_request($api_url, array( "Accept: application/json" ));
    }

    public function get_products_reviews($size = 100, $page = 0) {
        $is_test_mode = defined( 'WTSR_TEST_MODE' ) && WTSR_TEST_MODE;
        if ($is_test_mode) {
            $response = json_decode(file_get_contents(ABSPATH . 'ts-reviews-dummy.json'), true);
            return $response['response'];
        } else {
            $api_url = $this->get_api_url() . '/shops/' . $this->ts_id . '/products/reviews?size=' . $size . '&page=' . $page;

            return $this->get_request($api_url, array( "Accept: application/json" ));
        }
    }

    public function get_review_request($postData) {
        $api_url = $this->get_api_url() . '/shops/' . $this->ts_id . '/reviews/requests';

        return $this->post_request($api_url, $postData);
    }

    public function get_shop_reviews_restricted() {
        $size = 500;
        $page = 0;
        $more = true;
        $reviews = array();

        while ($more) {
            $api_url = $this->get_api_url() . '/shops/' . $this->ts_id . '/reviews?size=' . $size . '&page=' . $page;
            $response = $this->get_request($api_url, array( "Accept: application/json" ));

            if (is_wp_error($response) || empty($response['data']['shop']['reviews'])) {
                $more = false;
            } else {
                $shopReviews = $response['data']['shop']['reviews'];

                if (count($shopReviews) === $size) {
                    $page++;
                } else {
                    $more = false;
                }

                $reviews = array_merge($reviews, $shopReviews);
            }
        }

        return $reviews;
    }

    public function get_product_reviews_restricted() {
        $size = 500;
        $page = 0;
        $more = true;
        $reviews = array();

        while ($more) {
            $api_url = $this->get_api_url() . '/shops/' . $this->ts_id . '/products/reviews?size=' . $size . '&page=' . $page;
            $response = $this->get_request($api_url, array( "Accept: application/json" ));

            if (is_wp_error($response) || empty($response['data']['shop']['productReviews'])) {
                $more = false;
            } else {
                $productReviews = $response['data']['shop']['productReviews'];

                if (count($productReviews) === $size) {
                    $page++;
                } else {
                    $more = false;
                }

                $reviews = array_merge($reviews, $productReviews);
            }
        }

        return $reviews;
    }

    public function get_product_reviews() {
        $is_test_mode = defined( 'WTSR_TEST_MODE' ) && WTSR_TEST_MODE;

        if ($is_test_mode) {
            $response = file_get_contents(ABSPATH . 'fake.json');
        } else {
            $api_url = 'https://cdn1.api.trustedshops.com/shops/' . $this->ts_id . '/products/public/v1/feed.json';

            //  Initiate curl
            $ch = curl_init();
            // Will return the response, if false it print the response
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // Set the url
            curl_setopt($ch, CURLOPT_URL, $api_url);
            // Execute
            $response = curl_exec($ch);
            // Closing
            curl_close($ch);
        }

        $decoded = json_decode($response);

        if (empty($decoded)) {
            return false;
        }

        return preg_replace('/\s+/', ' ', $response);
    }

    public function show_credentials() {
        var_dump($this->ts_id);
        var_dump($this->ts_email);
        var_dump($this->ts_password);
        var_dump($this->ts_access);
    }
}