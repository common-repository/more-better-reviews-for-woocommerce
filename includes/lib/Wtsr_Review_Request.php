<?php


class Wtsr_Review_Request {
    private $_review_id = null;
    private $_review = null;
    private $_email = null;
    private $_order_id = null;

    public function __construct($review_id) {
        $this->_review_id = $review_id;

        $this->set_review();
        $this->set_email();
        $this->set_order_id();
    }

    private function set_review() {
        $this->_review = ReviewsModel::get($this->_review_id)[0];
    }

    private function set_email() {
        $this->_email = $this->_review->email;
    }

    private function set_order_id() {
        $this->_order_id = $this->_review->order_id;
    }

    public function get_id() {
        return $this->_review->id;
    }

    public function get_email() {
        return $this->_review->email;
    }

    public function get_order_id() {
        return $this->_review->order_id;
    }
}