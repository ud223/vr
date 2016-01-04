<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/6/30
 * Time: 15:53
 */

class Angel_Model_News extends Angel_Model_AbstractModel {
    protected $_document_class = '\Documents\News';

    public  function addNews($title, $title_en, $content, $content_en, $photo, $subtitle, $subtitle_en) {
        $data = array("title"=>$title, "title_en"=>$title_en, "content"=>$content, "content_en"=>$content_en, "photo"=>$photo, "subtitle"=>$subtitle, "subtitle_en"=>$subtitle_en);

        $result = $this->add($data);

        return $result;
    }

    public function saveNews($id, $title, $title_en, $content, $content_en, $photo, $subtitle, $subtitle_en) {
        $data = array("title"=>$title, "title_en"=>$title_en, "content"=>$content, "content_en"=>$content_en, "photo"=>$photo, "subtitle"=>$subtitle, "subtitle_en"=>$subtitle_en);

        $result = $this->save($id, $data);

        return $result;
    }

    public function getLastByCount($count) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)->sort('created_at', -1)->limit($count)->skip(0);

        $result = $query->getQuery();

        return $result;
    }
} 