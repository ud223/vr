<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Keyword
 *
 * @author vince
 */
class Angel_Model_Keyword extends Angel_Model_AbstractModel {
    protected $_document_class = '\Documents\Keyword';

    public function addKeyWord($name) {
        $data = array("name" => $name);
        $result = $this->add($data);
        
        return $result;
    }

    public function saveKeyWord($id, $name) {
        $data = array("name" => $name);
        $result = $this->save($id, $data);
        
        return $result;
    }

    /**
     * 根据id获取Keyword document
     * 
     * @param string $id
     * @return mix - when the user found, return the user document
     */
    public function getById($id) {
        $result = false;
        $keyWord = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('id')->equals($id)
                ->getQuery()
                ->getSingleResult();
        
        if (!empty($keyWord)) {
            $result = $keyWord;
        }

        return $result;
    }
}
