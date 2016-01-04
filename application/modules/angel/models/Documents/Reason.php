<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\EmbeddedDocument */
class Reason extends AbstractDocument{
    
    /** @ODM\String */
    protected $content;
    
}
