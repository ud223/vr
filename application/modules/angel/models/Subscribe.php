<?php

class Angel_Model_Subscribe extends Angel_Model_AbstractModel {

    protected $_document_class = '\Documents\Subscribe';

    public function addSubscribe($email) {
        $data = array("email" => $email);
        $result = $this->add($data);
        return $result;
    }

}
