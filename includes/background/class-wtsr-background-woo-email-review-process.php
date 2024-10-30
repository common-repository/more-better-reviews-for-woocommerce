<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Wtsr_Background_Woo_Email_Review_Process extends Wtsr_Background_Process {
    use Wtsr_Logger;

    /**
     * Initiate new background process.
     */
    public function __construct() {
        $this->action = 'wtsr_woo_email_review';

        // This is needed to prevent timeouts due to threading. See https://core.trac.wordpress.org/ticket/36534.
        @putenv( 'MAGICK_THREAD_LIMIT=1' ); // @codingStandardsIgnoreLine.

        parent::__construct();
    }

    public function set_map_id( $map_id ) {
        $this->map_id = $map_id;
    }

    /**
     * Handle.
     */
    protected function handle() {
        $this->lock_process();

        do {
            $batch = $this->get_batch();
            $count = count($batch->data);

            ob_start();

            foreach ( $batch->data as $key => $value ) {
                $bg_task = $this->bg_task($value);

                $task = $this->task( $value );

                if ( false !== $task ) {
                    $batch->data[ $key ] = $task;
                } else {
                    unset( $batch->data[ $key ] );
                }

                if ( $this->batch_limit_exceeded() ) {
                    // Batch limits reached.
                    break;
                }
            }

            // Update or delete current batch.
            if ( ! empty( $batch->data ) ) {
                $this->update( $batch->key, $batch->data );
            } else {
                $this->delete( $batch->key );
            }
        } while ( ! $this->batch_limit_exceeded() && ! $this->is_queue_empty() );

        $this->unlock_process();

        // Start next batch or complete process.
        if ( ! $this->is_queue_empty() ) {
            $this->dispatch();
        } else {
            $this->complete();
        }
    }

    protected function bg_task($value) {
        sleep(1);
        $now = time();
        $need_send = array();

        foreach ($value[1]['schedule'] as $schedule) {
            if ($now > (int)$schedule) {
                $need_send[] = $schedule;
            }
        }

        if (!empty($need_send)) {
            $review = ReviewsModel::get_by_id($value[0]);

            if (!empty($review)) {
                // error_log(serialize($review));
                $mailer = WC()->mailer();
                $email  = $mailer->emails['Wtsr_WC_Email_Review_Request'];
                $email->trigger($review['order_id'], null, true);

                if (!empty($email->sending)) { // email sent

                    foreach ($value[1]['schedule'] as $key => $schedule) {
                        if (in_array($schedule, $need_send)) {
                            unset($value[1]['schedule'][$key]);
                        }
                    }

                    $wtsr_send_woo_email_schedule_all = get_option(Wtsr_Cron::$schedule_option, array());

                    if (empty($value[1]['schedule'])) {
                        if (!empty($wtsr_send_woo_email_schedule_all[$value[0]])) {
                            unset($wtsr_send_woo_email_schedule_all[$value[0]]);
                        }
                    } else {
                        $wtsr_send_woo_email_schedule_all[$value[0]] = array(
                            'email' => $value[1]['email'],
                            'schedule' => $value[1]['schedule'],
                        );
                    }

                    update_option(Wtsr_Cron::$schedule_option, $wtsr_send_woo_email_schedule_all);
                }
            }
        }

        return true;
    }

    /**
     * Task
     */
    protected function task( $item ) {
        return false;
    }

    /**
     * Limit each task ran per batch to 1 for image regen.
     */
    protected function batch_limit_exceeded() {
        return true;
    }

    /**
     * Save queue
     */
    public function save() {
        $unique  = md5( microtime() . rand() );
        $prepend = $this->identifier . '_batch_';
        $key = substr( $prepend . $unique, 0, 64 );

        if ( ! empty( $this->data ) ) {
            update_site_option( $key, $this->data );
        }

        $this->data = array();

        return $this;
    }

    protected function complete() {
        parent::complete();
    }
}