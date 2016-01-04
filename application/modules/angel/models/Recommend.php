<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Recommend
 *
 * @author vince
 */
class Angel_Model_Recommend extends Angel_Model_AbstractModel {
    protected $_document_class = '\Documents\Recommend';
    
    public function addRecommend($specialId, $userId) {
        $data = array("specialId" => $specialId, "userId" => $userId);
        $result = $this->add($data);
        
        return $result;
    }
    
    public function getRecommend($userId) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('userId')->equals($userId)->sort('created_at', -1);

        $result = $query
                ->getQuery()
                ->execute();
        
        if (count($result) == 0) {
            return false;
        }

        return $result;
    }
}
