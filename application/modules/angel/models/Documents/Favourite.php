<?php

namespace Documents;
/**
 * Description of Favourite
 *
 * @author deanlu
 */
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Favourite  extends AbstractDocument{
     /** @ODM\String */
    protected $user_id;
    
    /** @ODM\ReferenceMany(targetDocument="\Documents\Special") */
    protected $special = array();
    
    public function addSpecial(\Documents\Special $p) {
        $this->special[] = $p;
    }
    
    public function clearSpecial() {
        $this->special = array();
    }
}
