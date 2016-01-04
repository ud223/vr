<?php

class Angel_Model_Category extends Angel_Model_AbstractModel {
    protected $_document_class = '\Documents\Category';

    public function addCategory($name, $description, $parent_id, $name_en) {
        $parent = null;
        $level = 0;
        if ($parent_id) {
            $parent = $this->getById($parent_id);
            if (!$parent) {
                throw Angel_Exception_Category(Angel_Exception_Category::CATEGORY_NOT_FOUND);
            }
            $level = $parent->level + 1;
        }
        $data = array("name" => $name, "description" => $description, "parent" => $parent, "level" => $level, "name_en"=>$name_en);
        $result = $this->add($data);
        return $result;
    }

    public function saveCategory($id, $name, $description, $parent_id, $name_en) {
        $parent = null;
        $level = 0;
        if ($parent_id) {
            $parent = $this->getById($parent_id);
            if (!$parent) {
                throw Angel_Exception_Category(Angel_Exception_Category::CATEGORY_NOT_FOUND);
            }
            if ($id == $parent_id) {
                throw Angel_Exception_Category(Angel_Exception_Category::CATEGORY_CANT_BE_SELF);
            }
            if ($parent->level > 0) {
                throw Angel_Exception_Category(Angel_Exception_Category::CATEGORY_CANT_BE_PARENT);
            }
            $level = $parent->level + 1;
        }
        $data = array("name" => $name, "description" => $description, "parent" => $parent, "level" => $level, "name_en"=>$name_en);
        $result = $this->save($id, $data, Angel_Exception_Category, Angel_Exception_Category::CATEGORY_NOT_FOUND);
        return $result;
    }

    public function getRoot() {
        $query = $this->_dm->createQueryBuilder($this->_document_class)
                ->sort('created_at', -1);
        $result = null;
        $result = $query->field('level')
                ->equals(0)
                ->getQuery()
                ->execute();

        return $result;
    }

    public function getByParent($parent_id) {
        $result = null;
        if ($parent_id) {
            $result = $this->_dm->createQueryBuilder($this->_document_class)
                    ->field('parent.$id')
                    ->equals(new MongoId($parent_id))
                    ->getQuery()
                    ->execute();
        }
        return $result;
    }
    
    public function getByIds($categorys_id) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('id')->in($categorys_id)->sort('created_at', -1);
        
        $result = null;
        $result = $query->getQuery();
        
        if (empty($result))
            return false;

        return $result;
    }

    public function getNotLikeCategory($categorys_id) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('id')->equals($categorys_id)->sort('created_at', -1);
        $result = null;
        $result = $query->getQuery()->execute();
        
        if (empty($result))
            return false;

        return $result;
    }
}
