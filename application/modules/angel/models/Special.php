<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Special
 *
 * @author vince
 */
class Angel_Model_Special extends Angel_Model_AbstractModel {
    protected $_document_class = '\Documents\Special';
    protected $_author_class = '\Documents\Author';
    
    public function addSpecial($name, $authorId, $photo, $programs, $categoryId) {
        $data = array("name" => $name, "authorId" => $authorId, "photo" => $photo, "program"=>$programs, "categoryId"=>$categoryId);
        
        $result = $this->add($data);
        
        return $result;
    }

    public function saveSpecial($id, $name, $authorId, $photo, $programs, $categoryId) {
        
        $data = array("name" => $name, "authorId" => $authorId, "photo" => $photo, "program"=>$programs, "categoryId"=>$categoryId);
        
        $result = $this->save($id, $data);
        
        return $result;
    }

    public function getRoot() {
        $query = $this->_dm->createQueryBuilder($this->_document_class)->sort('created_at', -1);

        $result = $query
                ->getQuery()
                ->execute();

        return $result;
    }
    
    public function getByCategory($category) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)->field('categoryId')->equals($category)->sort('created_at', -1);

        $result = $query
                ->getQuery()
                ->execute();

        return $result;
    }
    
    public function getLastOne() {
        $query = $this->_dm->createQueryBuilder($this->_document_class)->sort('created_at', -1);

        $result = $query
                ->getQuery()
                ->getSingleResult();

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
        
        $special = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('id')->equals($id)
                ->getQuery()
                ->getSingleResult();

        if (!empty($special)) {
            $result = $special;
        }
        
        return $result;
    }
    
    public function getByIds($id) {     
        $result = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('id')->in($id)->getQuery();

        return $result;
    }
    
    public function getNext($special) {
        //时间比较暂时替代代码
        $specials = $this->getRoot();
        $arrSpecials = array();
        
        foreach ($specials as $p) {
            $arrSpecials[] = $p;
        }
        
        $index = 0;
        
        foreach ($arrSpecials as $p) {
            if ($p->id == $special->id) {
                break;
            }
            
            $index = $index + 1;
        }

        if (count($arrSpecials) == $index)
            return false;

        $result = $arrSpecials[$index + 1];
        
        return $result;
    }
    
    public function getOwnProgramId() {
        $ownProgramsId = null;
        $result = null;
        $result = $this->_dm->createQueryBuilder($this->_document_class)
            ->getQuery()
            ->execute();
        
        foreach ($result as $special) {
            if (strpos($ownProgramsId, $special->id) > -1) {
                continue;
                
                if ($ownProgramsId == null)
                    $ownProgramsId = $ownProgramsId . ",";
                
                $ownProgramsId = $ownProgramsId . $special->id;
            }
        }
        
        return $ownProgramsId;
    }
    
    public function getNotRecommendSpecial($recommendIds) {//$recommendIds, $curSpecialId
        
        $result = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('id')->notIn($recommendIds)->sort('created_at', -1)->getQuery()->getSingleResult();

        if (empty($result))
            return false;
        
        return $result;
    }
    
    public function getLikeNotRecommendSpecial($recommendIds, $categoryId) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('id')->notIn($recommendIds)->field('categoryId')->equals($categoryId)->sort('created_at', -1);

        $result = $query
                ->getQuery()
                ->getSingleResult();

        if (!empty($result))
            return false;
        
        return $result;
    }
    
    public function getSpecialByPhoto($photo_id) {
        $result = false;
        
        if ($photo_id) {
            $query = $this->_dm->createQueryBuilder($this->_document_class)
                    ->field('photo.$id')->equals(new MongoId($photo_id))
                    ->sort('created_at', -1);
            
            $result = $query->getQuery()->getSingleResult();
        }
        
        if ($result)
            return true;
        else
            return false;
    }
    
    public function getSpecialByCategory($category_id) {
        $result = false;
        
        if ($category_id) {
            $query = $this->_dm->createQueryBuilder($this->_document_class)->field('categoryId')->equals($category_id)->sort('created_at', -1);
            
            $result = $query->getQuery()->getSingleResult();
        }
        
        if ($result)
            return true;
        else
            return false;
    }
    
    public function getSpecialByAuthor($author_id) {
        $result = false;
        
        if ($author_id) {
            $query = $this->_dm->createQueryBuilder($this->_document_class)->field('authorId')->equals($author_id)->sort('created_at', -1);
            
            $result = $query->getQuery()->getSingleResult();
        }
        
        if ($result)
            return true;
        else
            return false;
    }
    
    public function getSpecialByProgram($program_id) {
        $result = false;
        
        if ($program_id) {
            $query = $this->_dm->createQueryBuilder($this->_document_class)
                    ->field('program.$id')->equals(new MongoId($program_id))
                    ->sort('created_at', -1);
            
            $result = $query->getQuery()->getSingleResult();
        }
        
        if ($result)
            return true;
        else
            return false;
    }
    
    public function getSpecialByCategoryId($recommend_special_id, $category_id) {
        $result = false;
        
        $query = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('id')->notIn($recommend_special_id)->field('categoryId')->in($category_id)->sort('created_at', -1);
        
        $result = $query->getQuery()->getSingleResult();
        
        if (empty($result))
            return false;

        return $result;
    }
    
    public function getSpecialByNotCategoryId($recommend_special_id, $category_id) {
        $result = false;
        
        $query = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('id')->notIn($recommend_special_id)->field('categoryId')->notIn($category_id)->sort('created_at', -1);//

        $result = $query->getQuery()->getSingleResult();

        if (empty($result))
            return false;

        return $result;
    }
    
    private function getSpecialsByVote($user_id) {
        
    }
}