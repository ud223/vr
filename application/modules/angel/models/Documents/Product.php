<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Product extends AbstractDocument{
    /** @ODM\String */
    protected $name;

    /** @ODM\String */
    protected $name_en;

    /** @ODM\String */
    protected $width;

    /** @ODM\String */
    protected $width_en;

    //厚度
    /** @ODM\String */
    protected $thickness;

    /** @ODM\String */
    protected $thickness_en;

    /** @ODM\String */
    protected $height;

    /** @ODM\String */
    protected $height_en;

    /** @ODM\String */
    protected $style;

    /** @ODM\String */
    protected $style_en;

    //上/下钢板厚度厚度
    /** @ODM\String */
    protected $other_thickness;

    //上/下钢板厚度厚度
    /** @ODM\String */
    protected $other_thickness_en;

    //可用芯材
    /** @ODM\String */
    protected $steel_core;

    //可用芯材
    /** @ODM\String */
    protected $steel_core_en;

    //侧边钢带
    /** @ODM\String */
    protected $steel_belt;

    //侧边钢带
    /** @ODM\String */
    protected $steel_belt_en;

    //安装结构
    /** @ODM\String */
    protected $structure;

    //安装结构
    /** @ODM\String */
    protected $structure_en;

    /** @ODM\String */
    protected $description;

    /** @ODM\String */
    protected $description_en;

    /** @ODM\ReferenceMany(targetDocument="\Documents\Photo") */
    protected $photo = array();

    /** @ODM\String */
    protected $category_id;
}