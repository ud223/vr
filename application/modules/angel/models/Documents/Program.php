<?php

namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Program extends AbstractDocument {

    /** @ODM\String */
    protected $name;

    /** @ODM\String */
    protected $sub_title;

    /** @ODM\ReferenceOne(targetDocument="\Documents\Oss") */
    protected $oss_video;

    /** @ODM\ReferenceOne(targetDocument="\Documents\Oss") */
    protected $oss_audio;

    /** @ODM\ReferenceOne(targetDocument="\Documents\Author") */
    protected $author;

    /** @ODM\Int */
    protected $duration;

    //** @ODM\ReferenceOne(targetDocument="\Documents\Category") */
   // protected $category;
    /** @ODM\String */
    protected $time;
    
    /** @ODM\ReferenceMany(targetDocument="\Documents\Keyword") */
    protected $keyword = array();

    /** @ODM\String */
    protected $description;

    /** @ODM\ReferenceMany(targetDocument="\Documents\Photo") */
    protected $photo = array();

    /** @ODM\String */
    protected $status;                      // 节目状态：online, offline

    /** @ODM\ReferenceOne(targetDocument="\Documents\User") */
    protected $owner;
    
    /** @ODM\String */
    protected $captions; 

    /**
     * 添加图片
     */
    public function addPhoto(\Documents\Photo $p) {
        $this->photo[] = $p;
    }

    /**
     * 清空图片
     */
    public function clearPhoto() {
        $this->photo = array();
    }
    
    public function addKeyword(\Documents\Keyword $p) {
        $this->keyword[] = $p;
    }
    
    public function clearKeyword() {
        $this->keyword = array();
    }
}
