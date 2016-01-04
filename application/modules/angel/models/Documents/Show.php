<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Show extends AbstractDocument {
    /** @ODM\String */
    protected $remark;

    /** @ODM\String */
    protected $remark_en;

    /** @ODM\ReferenceMany(targetDocument="\Documents\Photo") */
    protected $photo = array();
} 