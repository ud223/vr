<?php

namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Special extends AbstractDocument {

    /** @ODM\String */
    protected $name;

    /** @ODM\String */
    protected $authorId;

    /** @ODM\ReferenceMany(targetDocument="\Documents\Photo") */
    protected $photo = array();

    /** @ODM\ReferenceMany(targetDocument="\Documents\Program") */
    protected $program = array();

    /** @ODM\String */
    protected $categoryId;
    
    public function addProgram(\Documents\Program $p) {
        $this->program[] = $p;
    }
    
    public function clearProgram() {
        $this->$program = array();
    }
}
