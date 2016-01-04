<?php

/**
 * The parent class for all document class
 * Contains the magic method to set/get the value to its properties
 */

namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

abstract class AbstractDocument {

    /** @ODM\Id */
    protected $id;

    /** @ODM\Date */
    protected $created_at;

    /** @ODM\Date */
    protected $updated_at;

    /** @ODM\PrePersist */
    public function updateCreatedAt() {
        $this->created_at = new \DateTime();
    }

    /** @ODM\PreUpdate */
    public function updateUpdatedAt() {
        $this->updated_at = new \DateTime();
    }

    public function __set($name, $value) {
        $class = get_class($this);

        $method = 'set' . strtoupper($name);
        if (method_exists($class, $method)) {
            return call_user_func(array($this, $method), $value);
        } else if (property_exists($class, $name)) {
            $this->$name = $value;
        } else {
            throw new \Exception("Property " . $name . " not exist in " . $class);
        }
    }

    public function __get($name) {
        $class = get_class($this);

        $method = 'get' . strtoupper($name);
        if (method_exists($class, $method)) {
            return call_user_func(array($this, $method));
        } else if (property_exists($class, $name)) {
            return $this->$name;
        } else {
            throw new \Exception("Property " . $name . " not exist in " . $class);
        }
    }

    /**
     * @see https://github.com/zucchi/ZucchiDoctrine/blob/master/src/ZucchiDoctrine/Document/AbstractDocument.php
     * 
     * return public and protected properties as an array
     *
     * @param boolean|integer $deep include nested objects and collections to the specified depth
     * @param boolean $all include private
     * @return array
     */
    public function toArray($deep = true, $all = true, $visited = array()) {
        // disable recursion;
        $hash = spl_object_hash($this);
        if (in_array($hash, $visited)) {
            return '*RECURSION';
        }
        $visited[] = $hash;

        $getpublic = function($obj) {
            return get_object_vars($obj);
        };
        $data = ($all) ? get_object_vars($this) : $getpublic($this);

        foreach ($data as $key => $value) {
            if (strpos($key, '_') === 0) {
                unset($data[$key]);
            }
        }

        if (is_integer($deep)) {
            $deep--;
        }
        foreach ($data AS $key => $val) {
            if (is_object($val)) {
                if ($deep && $val instanceof Collection) {
                    $data[$key] = array();
                    foreach ($val->toArray() as $rel) {
                        $data[$key][] = $rel->toArray($deep, $all, $visited);
                    }
                } else if ($deep && method_exists($val, 'toArray')) {
                    $data[$key] = $val->toArray($deep, $all, $visited);
                } else if (!$deep) {
                    unset($data[$key]);
                }
            }
        }
        $className = strtolower(get_class($this));
        if (strpos($className, '\\') !== false) {
            $parts = explode('\\', $className);
            $className = array_pop($parts);
        }
        //资源自身的名字,区别于引用的resource_name
        $data['my_name'] = $className;
        return $data;
    }
}

?>
