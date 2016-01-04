<?php

class Angel_Model_Show extends Angel_Model_AbstractModel {
    protected $_document_class = '\Documents\Show';

    public  function addShow($remark, $remark_en, $photo) {
        $data = array("remark"=>$remark, "remark_en"=>$remark_en, "photo" => $photo);

        $result = $this->add($data);

        return $result;
    }

    public function saveShow($id, $remark, $remark_en, $photo) {
        $data = array("remark"=>$remark, "remark_en"=>$remark_en, "photo" => $photo);

        $result = $this->save($id, $data);

        return $result;
    }
} 