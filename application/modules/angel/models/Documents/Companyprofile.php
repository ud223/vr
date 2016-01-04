<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Companyprofile extends AbstractDocument {
    /** @ODM\String */
    protected $title;

    /** @ODM\String */
    protected $title_en;

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
} 