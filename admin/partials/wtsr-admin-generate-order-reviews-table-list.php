<?php
/**
 * @var $orders
 * @var $orders_limit
 * @var $orders_count
 * @var $mysql_orders
 * @var $emails_array
 * @var $license_version
 * @var $user_left
 * @var $selected_review_ask
 * @var $reviews_not_allowed
 * @var $total_orders_ids
 */
foreach ($mysql_orders as $order_id => $order) {
    $email = !empty($order['_billing_email']) ? $order['_billing_email'] : false;

    if (!$email) {
        $blocked_emails[] = array( 'ID' => $order_id, 'email' => $email, 'reason' => 'empty');
        continue;
    }

    $buyer = '';

    if ( !empty($order['_billing_first_name']) || !empty($order['_billing_last_name']) ) {
        if (!empty($order['_billing_first_name'])) $buyer .= trim($order['_billing_first_name']);

        if (!empty($order['_billing_last_name'])) {
            if (!empty($buyer)) $buyer .= ' ';
            $buyer .= $order['_billing_last_name'];
        }
    } elseif ( $order['_billing_company'] ) {
        $buyer = trim( $order['_billing_company'] );
    } elseif ( $order['_billing_email'] ) {
        $buyer = explode('@', $order['_billing_email'])[0];
    }

    if (in_array($email, $emails_array)) {
        $blocked_emails[] = array( 'ID' => $order_id, 'email' => $email, 'reason' => 'twice');
        continue;
    } else {
        $emails_array[] = $email;
        $existed_email = ReviewsModel::get_last_by_email($email, OBJECT, 'id, order_id, status, review_created, review_sent');

        if (!empty($existed_email)) {
            $review_id = $existed_email->id;
            $outdated = TSManager::maybe_review_outdated($existed_email);

            if (!$outdated) {
                $blocked_emails[] = array( 'ID' => $order_id, 'email' => $email, 'reason' => 'not outdated');
                continue;
            }
        }
    }

    $existed_review = !empty($reviews[$order_id]) ? $reviews[$order_id] : false;
    $order_created_via = TSManager::get_array_created_via($order);
    $is_amazon = strpos($order_created_via, 'amazon');

    if ($is_amazon) {
        $blocked_emails[] = array( 'ID' => $order_id, 'email' => $email, 'reason' => 'amazon');
        continue;
    }

    $is_ebay = strpos($order_created_via, 'ebay');

    if ($is_ebay) {
        $blocked_emails[] = array( 'ID' => $order_id, 'email' => $email, 'reason' => 'ebay');
        continue;
    }

    $is_on_checkout = $order_created_via === 'checkout';

    if ($is_on_checkout && empty($existed_review)) {
        $is_review_allowed = !empty(get_post_meta( $order_id, 'Trusted Shops Review', true )) || 'no' === $selected_review_ask;
        if (!$is_review_allowed) $reviews_not_allowed++;

        if ($orders_count < $orders_limit) {
            ?>
            <tr>
                <th class="check-column">
                    <?php
                    if ('free' === $license_version && 0 === $user_left) {

                    } else {
                        ?>
                        <input id="cb-select-<?php echo $order_id; ?>" type="checkbox" value="<?php echo $order_id; ?>">
                        <?php
                    }
                    ?>
                </th>

                <td class="column-primary has-row-actions">
                    <strong>#<?php echo $order_id . ' ' . $buyer; ?></strong>

                    <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                </td>
                <td data-colname="<?php _e('Status', 'more-better-reviews-for-woocommerce'); ?>"><?php echo str_replace('wc-', '', $order['post_status']); ?></td>
                <td data-colname="<?php _e('Email', 'more-better-reviews-for-woocommerce'); ?>"><?php echo $email; ?></td>
                <td data-colname="<?php _e('Created', 'more-better-reviews-for-woocommerce'); ?>"><?php echo date('Y-m-d', strtotime($order['post_date'])); ?></td>
                <?php
                if (!empty($reviews_not_allowed)) {
                    ?><td data-colname="<?php _e('Want to give review', 'more-better-reviews-for-woocommerce'); ?>">
                    <?php
                    if (!$is_review_allowed) {
                        $selected_review_ask_link = '<a href="?page=wp2leads-wtsr&tab=wizard&wizard_step=general_settings" target="_blank">'.__( 'here', 'more-better-reviews-for-woocommerce' ).'</a>';

                        $string = __( 'Customer did not check "I want to give review" on checkout. You can deactivate checkbox %s.', 'more-better-reviews-for-woocommerce' );
                        echo sprintf($string, $selected_review_ask_link);
                    }
                    ?>
                    </td><?php
                }
                ?>
                <td data-colname="">
                    <?php
                    if ('free' === $license_version && 0 === $user_left) {
                        ?>
                        <button
                                type="button"
                                class="button button-primary button-small generate-review"
                                data-order-id="<?php echo $order_id; ?>"
                                disabled
                        >
                            <?php _e('Generate request', 'more-better-reviews-for-woocommerce'); ?>
                        </button>
                        <?php
                    } else {
                        ?>
                        <button
                                type="button"
                                class="button button-primary button-small generate-review"
                                data-order-id="<?php echo $order_id; ?>"
                            <?php echo !empty($product_review_request_bg) ? ' disabled' : ''; ?>
                        >
                            <?php _e('Generate request', 'more-better-reviews-for-woocommerce'); ?>
                        </button>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
        }

        $orders_count++;

        if ('free' === $license_version) {
            if (count($total_orders_ids) < $user_left) {
                $total_orders_ids[] = $order_id;
            }
        } else {
            $total_orders_ids[] = $order_id;
        }

    } else {
        $blocked_emails[] = array( 'ID' => $order_id, 'email' => $email, 'reason' => 'another');
    }
}