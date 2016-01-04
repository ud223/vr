<?php

namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Device  extends AbstractDocument {
   /** @ODM\String */
    protected $name;

    /** @ODM\int */
    protected $count; 
}
