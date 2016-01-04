<?php

namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Recommend  extends AbstractDocument {
    /** @ODM\String */
    protected $specialId;
    /** @ODM\String */
    protected $userId;
}
