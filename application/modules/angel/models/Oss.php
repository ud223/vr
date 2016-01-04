<?php

class Angel_Model_Oss extends Angel_Model_AbstractModel {

    protected $_document_class = '\Documents\Oss';

    public function addOss($name, $description, $status, $key, $fsize, $type, $ext, $owner) {
        $data = array(name => $name,
            description => $description,
            status => $status,
            key => $key,
            fsize => $fsize,
            type => $type,
            ext => $ext,
            owner => $owner);
        return $this->add($data);
    }

    public function saveOss($id, $name, $description, $status, $key, $fsize, $type, $ext) {
        $data = array(name => $name,
            description => $description,
            status => $status,
            key => $key,
            fsize => $fsize,
            type => $type,
            ext => $ext);
        return $this->save($id, $data, Angel_Exception_Oss, Angel_Exception_Oss::OSS_NOT_FOUND);
    }

}
