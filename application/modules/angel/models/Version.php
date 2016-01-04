<?php


class Angel_Model_Version extends Angel_Model_AbstractModel {
    protected $_document_class = '\Documents\Version';
    
    public function addVersion($name, $sys, $fix, $update, $url) {
        $data = array("name" => $name, "sys" => $sys, "fix" => $fix, "update"=>$update, "url"=> $url);
        
        $result = $this->add($data);
        
        return $result;
    }

    public function saveVersion($id, $name, $sys, $fix, $update, $url) {
        
        $data = array("name" => $name, "sys" => $sys, "fix" => $fix, "update"=>$update, "url"=> $url);
        
        $result = $this->save($id, $data);
        
        return $result;
    }
    
    public function getById($id) {
        $result = false;
        
        $version = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('id')->equals($id)
                ->getQuery()
                ->getSingleResult();

        if (!empty($version)) {
            $result = $version;
        }
        
        return $result;
    }
    
    public function getNewVersion($sys) {
        $result = false;

        $version = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('sys')->equals($sys)->sort('created_at', -1)
                ->getQuery()->getSingleResult();

        if ($version) {
            $result = $version;
        }

        return $result;
    }
}
