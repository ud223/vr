<?php

class Angel_HandleController extends Angel_Controller_Action {

    protected $login_not_required = array('error', 'forbidden', 'not-found');

    public function init() {
        parent::init();
        $this->_helper->layout->setLayout('handle');
    }

    public function errorAction() {
        
    }

    public function forbiddenAction() {
        
    }

    public function notFoundAction() {
        
    }

}
