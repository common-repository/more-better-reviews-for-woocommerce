<?php


class Wtsr_Cron {
    // private $default_interval = 1 * 120;
    private $default_interval = 1 * 60 * 60;
    private $two_hours_interval = 2 * 60 * 60;
    private $one_min_interval = 1 * 60;
    public static $schedule_option = 'wtsr_send_woo_email_schedule';

    public function __construct() {
        $this->addFilter();
        $this->addAction();
    }

    /**
     * Add Cron Filter
     *
     * @access private
     * @return void
     */
    private function addFilter() {
        add_filter('cron_schedules', array($this, 'cron_time_intervals'));
    }

    /**
     * Add the WP Cron Action
     *
     * @access private
     * @return void
     */
    private function addAction() {
        add_action('wtsr_send_woo_email', array($this, 'send_woo_email'));
        add_action('wtsr_check_reviewed', array($this, 'check_reviewed'));
    }

    /**
     * Set the schedule hooks
     *
     * @access public
     * @return void
     */
    public function setScheduleHook() {
        if (!wp_next_scheduled('wtsr_send_woo_email')) {
            wp_schedule_event(time(), 'one_hour', 'wtsr_send_woo_email');
        }

        if (!wp_next_scheduled('wtsr_check_reviewed')) {
            wp_schedule_event(time(), 'two_hours', 'wtsr_check_reviewed');
        }
    }

    /**
     * Add new schedule for wordpress cron
     *
     * @access public
     * @return array
     */
    public function cron_time_intervals( $schedules ) {
        $schedules['one_hour'] = array(
            'interval'=> $this->default_interval,
            'display'=> __( 'Every 1 hour', 'wp2leads' )
        );
        $schedules['two_hours'] = array(
            'interval'=> $this->two_hours_interval,
            'display'=> __( 'Every 2 hours', 'wp2leads' )
        );

        return $schedules;
    }

    public function check_reviewed() {
        ReviewServiceManager::check_product_reviews();
    }

    /**
     * Execute plugin cron jobs
     *
     * Debug cron with the following url
     * http://[URL]/wp-cron.php?doing_cron
     *
     * @return void
     */
    public function send_woo_email() {
        $wtsr_send_woo_email_schedule_all = get_option(self::$schedule_option, array());
        $now = time();

        if (!empty($wtsr_send_woo_email_schedule_all)) {
            foreach ($wtsr_send_woo_email_schedule_all as $review_id => $scheduled_item) {
                if (empty(ReviewsModel::get_by_id($review_id))) {
                    unset($wtsr_send_woo_email_schedule_all[$review_id]);
                } elseif (empty($scheduled_item['schedule'])) {
                    unset($wtsr_send_woo_email_schedule_all[$review_id]);
                }

                $new_scheduled_array = array();
                $outdated_schedule = null;

                foreach ($scheduled_item['schedule'] as $scheduled) {
                    if ($scheduled > $now) {
                        $new_scheduled_array[] = $scheduled;
                    } else {
                        if (empty($outdated_schedule)) {
                            $outdated_schedule = $scheduled;
                        } elseif ($scheduled > $outdated_schedule) {
                            $outdated_schedule = $scheduled;
                        }
                    }
                }

                if (!empty($outdated_schedule)) {
                    $new_scheduled_array[] = $outdated_schedule;
                }

                $wtsr_send_woo_email_schedule_all[$review_id]['schedule'] = $new_scheduled_array;

                update_option(self::$schedule_option, $wtsr_send_woo_email_schedule_all);
            }

            $result = Wtsr_Background_Woo_Email_Review::bg_process($wtsr_send_woo_email_schedule_all);
        }
    }

    public static function add_woo_email_schedule($review_id) {
        $wtsr_email_send_via_woocommerce_delay = get_option('wtsr_email_send_via_woocommerce_delay', '');
        $delay_array = explode(':', $wtsr_email_send_via_woocommerce_delay);
        $now = time();

        if (count($delay_array)) {
            $review = ReviewsModel::get_by_id( $review_id );

            if (!empty($review)) {
                $wtsr_send_woo_email_schedule = array(
                    'email' => $review['email'],
                    'schedule' => array()
                );

                foreach ($delay_array as $delay_days) {
                    if (is_numeric($delay_days) && !empty($delay_days)) {
                        $delay = (int)$delay_days * 24 * 60 * 60;
                        $wtsr_send_woo_email_schedule['schedule'][] = $now + $delay;
                    }
                }

                if (!empty($wtsr_send_woo_email_schedule['schedule'])) {
                    $wtsr_send_woo_email_schedule_all = get_option(self::$schedule_option, array());
                    $wtsr_send_woo_email_schedule_all[$review_id] = $wtsr_send_woo_email_schedule;

                    return update_option(self::$schedule_option, $wtsr_send_woo_email_schedule_all);
                }
            }
        }

        return false;
    }

    public static function remove_woo_email_schedule($review_id, $time) {
        $wtsr_send_woo_email_schedule_all = get_option(self::$schedule_option, array());

        if (!empty($wtsr_send_woo_email_schedule_all[$review_id]['schedule'])) {
            $schedule = $wtsr_send_woo_email_schedule_all[$review_id]['schedule'];

            foreach ($schedule as $i => $stime) {
                if ($time == $stime) {
                    unset($schedule[$i]);
                }
            }

            $wtsr_send_woo_email_schedule_all[$review_id]['schedule'] = $schedule;

            return update_option(self::$schedule_option, $wtsr_send_woo_email_schedule_all);
        }

        return true;
    }
}