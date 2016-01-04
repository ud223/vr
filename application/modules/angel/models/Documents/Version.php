<?php

namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Version  extends AbstractDocument{
    /** @ODM\String */
    protected $name;
    
    /** @ODM\String */
    protected $sys;
    
    /** @ODM\String */
    protected $fix;
    
    /** @ODM\String */
    protected $update;
    
    /** @ODM\String */
    protected $url;
}
