<?php

namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Subscribe extends AbstractDocument {

    /** @ODM\String */
    protected $email;

}
