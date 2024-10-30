<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Wtsr_Background_Review_Check_Process extends Wtsr_Background_Process {
    use Wtsr_Logger;

    /**
     * Initiate new background process.
     */
    public function __construct() {
        $this->action = 'wtsr_review_check';

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
        $product_reviews = TSManager::check_if_product_reviews_reviewed();

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