<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class News extends AbstractDocument {
    /** @ODM\String */
    protected $title;

    /** @ODM\String */
    protected $title_en;
    /** @ODM\String */
    protected $subtitle;
    /** @ODM\String */
    protected $subtitle_en;

    /** @ODM\String */
    protected $content;

    /** @ODM\String */
    protected $content_en;

    /** @ODM\ReferenceMany(targetDocument="\Documents\Photo") */
    protected $photo = array();
} 