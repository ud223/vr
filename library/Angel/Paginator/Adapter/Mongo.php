<?php
/**
 * 
 * @author powerdream5 
 */
class Angel_Paginator_Adapter_Mongo implements Zend_Paginator_Adapter_Interface{
    
    private $_query = null;


    public function __construct($query){
        $this->_query = $query;
    }
    
    public function getItems($offset, $itemCountPerPage) {
        $this->_query->limit($itemCountPerPage)->skip($offset);
        
        return $this->_query->getQuery()->execute();
    }
    
    public function count(){
        return $this->_query->getQuery()->count();
    }
}