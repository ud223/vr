<?php

namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Author extends AbstractDocument {

    /** @ODM\String */
    protected $name;

    /** @ODM\String */
    protected $description = 'nothing';
    
    /** @ODM\Hash */
    protected $intro = array();                                 // 作者相关信息，例如微博链接，微信号等

    /** @ODM\ReferenceOne(targetDocument="\Documents\Photo") */
    protected $logo;

    /** @ODM\Int */
    protected $view = 0;


}
