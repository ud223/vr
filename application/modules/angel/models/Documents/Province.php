<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Province extends AbstractDocument{
    
    /** @ODM\Int */
    protected $pid;

    /** @ODM\String */
    protected $name;
    
}
