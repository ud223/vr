<?php

/**
 * @author powerdream5
 * 所有model的父类 
 */
abstract class Angel_Model_AbstractModel {

    protected $_bootstrap;
    protected $_angel_bootstrap;
    protected $_bootstrap_options;
    protected $_dm;
    protected $_container;
    protected $_logger;
    protected $models = array();

    public function __construct($bootstrap) {
        $this->_bootstrap = $bootstrap;
        $this->_angel_bootstrap = $this->_bootstrap->getResource('modules')->offsetGet('angel');
        $this->_bootstrap_options = $this->_bootstrap->getOptions();
        $this->_container = $this->_bootstrap->getResource('serviceContainer');
        $this->_dm = $this->_angel_bootstrap->getResource('mongoDocumentManager');
        $this->_logger = $this->_bootstrap->getResource('logger');
    }

    public function getAll($return_as_paginator = true) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)
                ->sort('created_at', -1);
        $result = null;
        if ($return_as_paginator) {
            $result = $this->paginator($query);
        } else {
            $result = $query->getQuery()->execute();
        }
        return $result;
    }

    public function add($data) {
        $result = false;
        if ($data) {
            $object = new $this->_document_class();
            foreach ($data as $key => $val) {
                $object->$key = $val;
            }
            $this->_dm->persist($object);
            $this->_dm->flush();
            $result = true;
        }
        return $result;
    }

    public function save($id, $data, $notFoundException = Exception, $exceptionMessage = "") {
        $result = false;
        if ($data) {
            
            $target = $this->getById($id);
            
            if (!$target) {
                throw new $notFoundException($exceptionMessage);
            }

            foreach ($data as $key => $val) {
                $target->$key = $val;
            }
            $this->_dm->persist($target);
            $this->_dm->flush();
            $result = true;
        }
        return $result;
    }

    public function remove($id) {
        $result = false;
        try {
            // remove document
            $result = $this->_dm->createQueryBuilder($this->_document_class)
                    ->remove()
                    ->field('id')
                    ->equals($id)
                    ->getQuery()
                    ->execute();
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

//    public function getByUser($user_id, $return_as_paginator = true, $condition = false) {
//        $query = $this->_dm->createQueryBuilder($this->_document_class)
//                        ->field('owner.$id')->equals(new MongoId($user_id));
//        if (is_array($condition)) {
//            foreach ($condition as $key => $val) {
//                $query = $query->field(key)->equals($val);
//            }
//        }
//        $query = $query->sort('created_at', -1);
//        $result = null;
//        if ($return_as_paginator) {
//            $result = $this->paginator($query);
//        } else {
//            $result = $query->getQuery()->execute();
//        }
//        return $result;
//    }

    public function getByUser($user_id, $return_as_paginator = true, $condition = false) {
        $new_condition = array('owner.$id' => new MongoId($user_id));
        if($condition) {
            $new_condition = array_merge($new_condition, $condition);
        }
        return $this->getBy($return_as_paginator, $new_condition);
    }

    public function getById($id) {
        $result = false;
        $obj = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('id')->equals($id)
                ->getQuery()
                ->getSingleResult();

        if (!empty($obj)) {
            $result = $obj;
        }

        return $result;
    }

    public function getBy($return_as_paginator = true, $condition = false) {
        $query = $this->_dm->createQueryBuilder($this->_document_class);
        if (is_array($condition)) {
            foreach ($condition as $key => $val) {
                $query = $query->field($key)->equals($val);
            }
        }
        $query = $query->sort('created_at', -1);
        $result = null;
        if ($return_as_paginator) {
            $result = $this->paginator($query);
        } else {
            $result = $query->getQuery()->execute();
        }
        return $result;
    }
    
    public function getOneBy($condition = false) {
        $query = $this->_dm->createQueryBuilder($this->_document_class);
        if (is_array($condition)) {
            foreach ($condition as $key => $val) {
                $query = $query->field($key)->equals($val);
            }
        }
        $result = $query->getQuery()->getSingleResult();
        return $result;
    }

    public function getDocumentClass() {
        return $this->_document_class;
    }

    public function paginator($query) {
        $adapter = new Angel_Paginator_Adapter_Mongo($query);
        return new Zend_Paginator($adapter);
    }

    public function getModel($modelName) {
        $modelName = 'Angel_Model_' . ucwords($modelName);
        if (!isset($models[$modelName])) {
            $models[$modelName] = new $modelName($this->bootstrap);
        }

        return $models[$modelName];
    }

}
