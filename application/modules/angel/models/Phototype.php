<?php

class Angel_Model_Phototype extends Angel_Model_AbstractModel {

    protected $_document_class = '\Documents\Phototype';

    public function addPhototype($name, $description, $owner) {
        $data = array("name" => $name, "description" => $description, "owner" => $owner);
        $result = $this->add($data);
        return $result;
    }

    public function savePhototype($id, $name, $description) {
        $data = array("name" => $name, "description" => $description);
        $result = $this->save($id, $data, Angel_Exception_Phototype, Angel_Exception_Phototype::PHOTOTYPE_NOT_FOUND);
        return $result;
    }

}
