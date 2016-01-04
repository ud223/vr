<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Classiccase extends AbstractDocument {
    /** @ODM\String */
    protected $name;

    /** @ODM\String */
    protected $name_en;

    /** @ODM\String */
    protected $simple_content;

    /** @ODM\String */
    protected$simple_content_en;

    /** @ODM\String */
    protected $content;

    /** @ODM\String */
    protected $content_en;

    /** @ODM\ReferenceMany(targetDocument="\Documents\Photo") */
    protected $photo = array();

    /** @ODM\ReferenceMany(targetDocument="\Documents\Product") */
    protected $product = array();
}
