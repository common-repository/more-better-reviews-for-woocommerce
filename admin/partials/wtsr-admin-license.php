<?php
/**
 * Provide a admin area view for the plugin
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$email = '  someemail@gmail.com   ';
$email = sanitize_email($email);

$is_test_mode = defined( 'WTSR_TEST_MODE' ) && WTSR_TEST_MODE;

$order_id = 1972;
$test_emoji = false;
$show_template = false;
$show_request = false;
$show_order = false;
$generate_orders = false;
$generate_orders_domain = 'san-test.com';
// $need_check = get_option('wtsr_to_check_product_reviews');
$generate_product_reviews = false;
$need_check = false;
$reset_statuses = false;
$modules = TSManager::is_wtsr_module_enabled();
$clean_options = false;

//$mailer = WC()->mailer();
//$email = $mailer->emails['Wtsr_WC_Email_Review_Request'];
//$email->trigger( $order_id );

$by_orders = array();

function pk_get_licenses($campaignId, $apiToken, $buyerEmail) {
    $pk_licenses = file_get_contents("https://app.paykickstart.com/api/licenses?campaign_id={$campaignId}&buyer_email={$buyerEmail}&auth_token={$apiToken}");
    // exit(json_encode($pk_licenses));
    $pk_licenses = json_decode($pk_licenses);
    $pk_results = [];
    if (isset($pk_licenses->success) && $pk_licenses->success) {
        if (isset($pk_licenses->data) && count($pk_licenses->data) > 0) {
            $pk_results = $pk_licenses;
        }
    } else {
        // Then if the user is not found in your own campaign we check Mark Thompson's campaign.
        $pk_licenses = file_get_contents("https://staging.magiwebsa.com/donotdelete/getkeys/index.php?buyer_email={$buyerEmail}");
        $pk_licenses = json_decode($pk_licenses);

        if (isset($pk_licenses->success) && $pk_licenses->success) {
            if (isset($pk_licenses->data) && count($pk_licenses->data) > 0) {
                $pk_results = $pk_licenses;
            }
        }
    }

    return $pk_results;
}

// TSManager::maybe_product_page_review_reviewed('18', 'i.synthetica12@wtsr-test.ua');

if ($generate_product_reviews) {
    $product_reviews = Wtsr_License_Fake::get_fake_product_reviews_json();

// var_dump($product_reviews);

    echo "<pre style='padding:10px;border:1px solid #ddd; background-color: #fff;'>";
    foreach ($product_reviews as $sku_data) {
        $product = get_post($sku_data['post_id']);
        ?>
        {
        "sku": "<?php echo $sku_data['meta_value']; ?>",
        "name": "<?php echo $product->post_title; ?>",
        "gtin": "",
        "mpn": "",
        "brand": "",
        "imageUrl": "",
        "productUrl": "<?php echo get_permalink($sku_data['post_id']); ?>",
        "uuid": "f7abd8c5-<?php echo $sku_data['post_id'] ?>-4b1c-97a0-c0577e1d4efd",
        "qualityIndicators": {
        "reviewIndicator": {
        "totalReviewCount": 1,
        "overallMark": 5,
        "overallMarkDescription": "EXCELLENT"
        }
        },
        "productReviews": [
        {
        "creationDate": "2019-08-05T15:51:20+02:00",
        "comment": "Spitzenqualität ",
        "criteria": [
        {
        "mark": "5",
        "type": "Total"
        }
        ],
        "mark": "5",
        "order": {
        "orderDate": "2017-09-02T15:51:20+02:00",
        "orderReference": "599",
        "uuid": "8580fc42-da83-4adf-a139-dce020a92db5"
        },
        "reviewer": {
        "uuid": "ea32bbea406d044b7f15df99ab56992a"
        },
        "uuid": "69c1bd5e-35c1-4fc2-a518-223effff1349"
        }
        ]
        },
        <?php
    }
    echo "</pre>";

}

if ($reset_statuses) {
    $finished = ReviewsModel::get_finished('id');

    foreach ($finished as $id => $data) {
        $data = array(
            'id' => $id,
            'status' => 'transferred',
            'review_sent' => $data['review_created'],
        );

        $id = ReviewsModel::update($data);
    }

    var_dump($finished);
}

if ($generate_orders) {
    $start = time();

    var_dump(TSManager::generate_dummy_orders($generate_orders_domain, true, 'checkout'));
    var_dump(TSManager::generate_dummy_orders($generate_orders_domain, true, 'checkout'));
    var_dump(TSManager::generate_dummy_orders('ebay.com', true, 'checkout'));
    var_dump(TSManager::generate_dummy_orders($generate_orders_domain, true, 'checkout'));
    var_dump(TSManager::generate_dummy_orders('amazon.com', true, 'checkout'));
    var_dump(TSManager::generate_dummy_orders($generate_orders_domain, true, 'checkout'));
    var_dump(TSManager::generate_dummy_orders($generate_orders_domain, true, 'checkout'));
    var_dump(TSManager::generate_dummy_orders('ebay.com', true, 'checkout'));
    var_dump(TSManager::generate_dummy_orders($generate_orders_domain, true, 'checkout'));
    var_dump(TSManager::generate_dummy_orders('amazon.com', true, 'checkout'));

    var_dump(time() - $start);
}
?>

<h3><?php _e('Dev area', 'more-better-reviews-for-woocommerce'); ?></h3>

<?php
//$api = new TSApi('restricted', 'XDCA254DEFA014E61F035DF1412E17FDD', 'api@plugin-test.de', '9DvdMaT6M-!ep!2c');
//$review_request = $api->get_shop_reviews();
?>

<table class="table-settings">
    <thead>
    <tr>
        <th colspan="3"><?php _e('Generate dummy orders', 'more-better-reviews-for-woocommerce'); ?></th>
    </tr>
    </thead>

    <tbody>
    <tr>
        <th style="vertical-align: top">
            <?php _e('Input email domain', 'more-better-reviews-for-woocommerce'); ?>
        </th>
        <td style="vertical-align: top" class="wtsr-processing-holder">
            <input class="form-input" id="wtsr_generate_dummy_orders_domain" type="email" value="" placeholder="@plugin-test.de">
            <button id="wtsr_generate_dummy_orders" class="button button-primary" type="button" style="margin-top: 15px;">
                <?php _e('Generate 10 dummy orders', 'more-better-reviews-for-woocommerce'); ?>
            </button>

            <div class="wtsr-spinner-holder">
                <div class="wtsr-spinner"></div>
            </div>
        </td>
        <td style="vertical-align: top">
            <p style="margin: 7px 0">
                <?php _e('Input email domain', 'more-better-reviews-for-woocommerce'); ?>
            </p>
            <p style="margin: 7px 0">
                <?php _e('Please be patient, it could take upo to a minute, do not reload a page.', 'more-better-reviews-for-woocommerce'); ?>
            </p>
        </td>
    </tr>
    </tbody>
</table>

<?php
if (false) {
    ?>
    <div class="wtsr-row">
        <div class="wtsr-col-xs-12 wtsr-col-sm-12 wtsr-col-md-6" style="background-color: #fff;">
            <?php
            add_filter('wtsr_max_email_template_length', 'wtsr_unlim_email_template_length');
            echo wpautop(Wtsr_Template::get_review_request_html(9959));
            remove_filter('wtsr_max_email_template_length', 'wtsr_unlim_email_template_length');
            ?>
        </div>

        <div class="wtsr-col-xs-12 wtsr-col-sm-12 wtsr-col-md-6" style="background-color: #fff;">
        <pre><?php
            add_filter('wtsr_max_email_template_length', 'wtsr_unlim_email_template_length');
            echo Wtsr_Template::get_email_plain_template($order_id, '');
            remove_filter('wtsr_max_email_template_length', 'wtsr_unlim_email_template_length');
            ?></pre>
        </div>
    </div>
    <?php
}
?>
