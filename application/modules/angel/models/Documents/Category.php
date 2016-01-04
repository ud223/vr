<?php

namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Category extends AbstractDocument {

    /** @ODM\String */
    protected $name;

    /** @ODM\String */
    protected $name_en;

    /** @ODM\String */
    protected $description = 'nothing';

    /** @ODM\ReferenceOne(targetDocument="\Documents\Category") */
    protected $parent;

    /** @ODM\Int */
    protected $level = 0;

    /** @ODM\Int */
    protected $view = 0;

}
