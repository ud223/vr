<?php

namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Hot extends AbstractDocument{
    /** @ODM\String */
    protected $categoryId;
    
    /** @ODM\ReferenceMany(targetDocument="\Documents\Special") */
    protected $special = array();
    
    public function addSpecial(\Documents\Special $p) {
        $this->special[] = $p;
    }
    
    public function clearSpecial() {
        $this->special = array();
    }
}
