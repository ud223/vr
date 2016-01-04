<?php

class Angel_Model_Author extends Angel_Model_AbstractModel {

    protected $_document_class = '\Documents\Author';
    protected $_document_user_class = '\Documents\User';

    public function saveAuthor($id, $name, $description, $intro, $logo) {
        $data = array("name" => $name, "description" => $description, "intro" => $intro, "logo" => $logo);
        $result = $this->save($id, $data, Angel_Exception_Author, Angel_Exception_Author::AUTHOR_NOT_FOUND);
        return $result;
    }

    public function addAuthor($name, $description, $intro, $logo) {
        $data = array("name" => $name, "description" => $description, "intro" => $intro, "logo" => $logo);
        $result = $this->add($data);
        return $result;
    }

    public function getAuthorById($author_id) { 
        if ($author_id == "")
            return "";
        
        $query = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('id')->equals($author_id)->sort('created_at', -1);
        
        $result = $query->getQuery()->getSingleResult();
        
        return $result;
    }
    
    public function getAuthorByPhoto($photo_id) {
        $result = false;
        
        if ($photo_id) {
            $query = $this->_dm->createQueryBuilder($this->_document_class)
                    ->field('logo.$id')->equals(new MongoId($photo_id))
                    ->sort('created_at', -1);
            
            $result = $query->getQuery()->getSingleResult();
        }
        
        return $result;
    }
}
