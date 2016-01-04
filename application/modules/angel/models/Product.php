<?php

class Angel_Model_Product extends Angel_Model_AbstractModel {
    protected $_document_class = '\Documents\Product';

    public  function addProduct($name, $name_en, $width, $width_en, $thickness, $thickness_en, $height, $height_en, $style, $style_en, $other_thickness, $other_thickness_en, $steel_core, $steel_core_en, $steel_belt, $steel_belt_en, $structure, $structure_en, $description, $description_en, $photo, $category_id) {
        $data = array("name"=>$name, "name_en"=>$name_en, "width"=>$width, "width_en"=>$width_en, "thickness"=>$thickness, "thickness_en"=>$thickness_en, "height"=>$height, "height_en"=>$height_en, "style"=>$style, "style_en"=>$style_en, "other_thickness"=>$other_thickness, "other_thickness_en"=>$other_thickness_en, "steel_core"=>$steel_core, "steel_core_en"=>$steel_core_en, "steel_belt"=>$steel_belt, "steel_belt_en"=>$steel_belt_en, "structure"=>$structure, "structure_en"=>$structure_en, "description"=>$description, "description_en"=>$description_en, "photo" => $photo, "category_id"=>$category_id);

        $result = $this->add($data);

        return $result;
    }

    public function saveProduct($id, $name, $name_en, $width, $width_en, $thickness, $thickness_en, $height, $height_en, $style, $style_en, $other_thickness, $other_thickness_en, $steel_core, $steel_core_en, $steel_belt, $steel_belt_en, $structure, $structure_en, $description, $description_en, $photo, $category_id) {
        $data = array("name"=>$name, "name_en"=>$name_en, "width"=>$width, "width_en"=>$width_en, "thickness"=>$thickness, "thickness_en"=>$thickness_en, "height"=>$height, "height_en"=>$height_en, "style"=>$style, "style_en"=>$style_en, "other_thickness"=>$other_thickness, "other_thickness_en"=>$other_thickness_en, "steel_core"=>$steel_core, "steel_core_en"=>$steel_core_en, "steel_belt"=>$steel_belt, "steel_belt_en"=>$steel_belt_en, "structure"=>$structure, "structure_en"=>$structure_en, "description"=>$description, "description_en"=>$description_en, "photo" => $photo, "category_id"=>$category_id);

        $result = $this->save($id, $data);

        return $result;
    }

    public function getProductByCategory($category_id) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)->field('category_id')->equals($category_id)->sort('created_at', -1);

        $result = $query->getQuery();

        return $result;
    }

    public function getLastByCount($count) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)->sort('created_at', -1)->limit($count)->skip(0);

        $result = $query->getQuery();

        return $result;
    }

    public function getProductByIds($product_id) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)->field('id')->in($product_id)->sort('created_at', -1);

        $result = $query->getQuery();

        return $result;
    }
}
