<?php

class Angel_Model_Hot extends Angel_Model_AbstractModel {
    protected $_document_class = '\Documents\Hot';

    public function addHot($category_id, $specials) {
        $hot = new $this->_document_class();
        
        foreach ($specials as $p) {
            $hot->addSpecial($p);
        }
 
        $hot->categoryId = $category_id;

        try {
            $this->_dm->persist($hot);
            $this->_dm->flush();

            $result = $hot->id;
        } catch (Exception $e) {
            $this->_logger->info(__CLASS__, __FUNCTION__, $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new Angel_Exception_Program(Angel_Exception_Program::ADD_PROGRAM_FAIL);
        }
        
        return $result;
    }

    public function saveHot($id, $category_id, $specials) {     
        $hot = new $this->_document_class();
        
        $hot->clearSpecial();
        
        foreach ($specials as $p) {
            $hot->addSpecial($p);
        }
        
        $hot->id = $id;
        $hot->categoryId = $category_id;

        try {
            $this->_dm->persist($hot);
            $this->_dm->flush();

            $result = $hot->id;
        } catch (Exception $e) {
            $this->_logger->info(__CLASS__, __FUNCTION__, $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new Angel_Exception_Program(Angel_Exception_Program::SAVE_PROGRAM_FAIL);
        }

        return $result;
    }
    
    public function getByCategoryId($id) {
        $result = false;
        
        $hot = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('categoryId')->equals($id)
                ->getQuery()
                ->getSingleResult();

        if (!empty($hot)) {
            $result = $hot;
        }

        return $result;
    }
    
    public function getAll() {
        $query = $this->_dm->createQueryBuilder($this->_document_class)->sort('created_at', -1);

        $result = $query
                ->getQuery()
                ->execute();
        
        return $result;
    }
    
    public function getLikeNotRecommendHot($categorys_id) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('categoryId')->in($categorys_id)->sort('created_at', -1);
        
        $result = $query
                ->getQuery();
               // ->getSingleResult();
//        echo count($result); exit;
        if (empty($result))
            return false;
        
        return $result;
    }
    
    public function getNotRecommendHot($categorys_id) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('categoryId')->notIn($categorys_id)->sort('created_at', -1);

        $result = $query
                ->getQuery();
               // ->getSingleResult();
        
        if (empty($result))
            return false;
        
        return $result;
    }
}
