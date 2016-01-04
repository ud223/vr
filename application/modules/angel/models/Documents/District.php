<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class District extends AbstractDocument{
    
    /** @ODM\Int */
    protected $did;

    /** @ODM\String */
    protected $name;
    
    /** @ODM\ReferenceOne(targetDocument="\Documents\City") */
    protected $city;
}
