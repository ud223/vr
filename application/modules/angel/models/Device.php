<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DeviceInstall
 *
 * @author vince
 */
class Angel_Model_Device  extends Angel_Model_AbstractModel {
   protected $_document_class = '\Documents\Device';
   
   public function addDevice($name) {
        $data = array("name" => $name, "count" => 1);
        
        $result = $this->add($data);
        
        return $result;
    }

    public function saveDevice($id, $name, $count) {
        $data = array("name" => $name, "count" => $count + 1);
        
        $result = $this->save($id, $data);
        
        return $result;
    }
    
    public function getByName($name) {
        $result = false;
        
        $deviceInstall = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('name')->equals($name)
                ->getQuery()
                ->getSingleResult();

        if (!empty($deviceInstall)) {
            $result = $deviceInstall;
        }
        
        return $result;
    }
}
