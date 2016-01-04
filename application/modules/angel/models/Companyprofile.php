<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/6/30
 * Time: 15:53
 */

class Angel_Model_Companyprofile extends Angel_Model_AbstractModel {
    protected $_document_class = '\Documents\Companyprofile';

    public  function addAbout($title, $title_en, $content, $content_en, $simple_content, $simple_content_en, $photo) {
        $data = array("title"=>$title, "title_en"=>$title_en, "content"=>$content, "content_en"=>$content_en, "simple_content"=>$simple_content, "simple_content_en"=>$simple_content_en, "photo"=>$photo);

        $result = $this->add($data);

        return $result;
    }

    public function saveAbout($id, $title, $title_en, $content, $content_en, $simple_content, $simple_content_en, $photo) {
        $data = array("title"=>$title, "title_en"=>$title_en, "content"=>$content, "content_en"=>$content_en, "simple_content"=>$simple_content, "simple_content_en"=>$simple_content_en, "photo"=>$photo);

        $result = $this->save($id, $data);

        return $result;
    }

    public function getLastByCount($count) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)->sort('created_at', -1)->limit($count)->skip(0);

        $result = $query->getQuery();

        return $result;
    }
} 