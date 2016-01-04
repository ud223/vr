<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Oss extends AbstractDocument{
    
    /** @ODM\String */
    protected $name;
    
    /** @ODM\String */
    protected $description;
    
    /** @ODM\String */
    protected $status;                          // OSS文件状态：online, offline
    
    /** @ODM\String */
    protected $key;                             // 文件key
        
    /** @ODM\String */
    protected $fsize;                           // 文件大小
    
    /** @ODM\String */
    protected $type = 'video';                  // OSS文件类型：video, audio

    /** @ODM\String */
    protected $ext = '.mp4';                    // OSS文件扩展名
    
    /** @ODM\ReferenceOne(targetDocument="\Documents\User") */
    protected $owner;

}
