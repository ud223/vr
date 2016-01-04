<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class City extends AbstractDocument{
    
    /** @ODM\Int */
    protected $cid;

    /** @ODM\String */
    protected $name;
    
    /** @ODM\String */
    protected $zipcode;
    
    /** @ODM\ReferenceOne(targetDocument="\Documents\Province") */
    protected $province;
}
