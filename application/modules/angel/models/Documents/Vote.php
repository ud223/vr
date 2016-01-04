<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Vote extends AbstractDocument{
    /** @ODM\String */
    protected $userId;
    
    /** @ODM\String */
    protected $keywordId;
    
    /** @ODM\Int */
    protected $score;
}
