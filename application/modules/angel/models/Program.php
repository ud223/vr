<?php

class Angel_Model_Program extends Angel_Model_AbstractModel {

    protected $_document_class = '\Documents\Program';
    protected $_keyword_class = '\Documents\Keyword';
    /**
     * 添加节目
     * 
     * @param string $name
     * @param string $sub_title
     * @param \Documents\Oss $oss_video
     * @param \Documents\Oss $oss_audio
     * @param \Documents\Author $author
     * @param int $duration
     * @param string $description
     * @param array $photo
     * @param string $status
     * @param \Documents\Category $category
     * @param \Documents\User $owner
     * @return mix - when user registration success, return the user id, otherwise, boolean false
     * @throws Angel_Exception_Program
     */
    public function addProgram($name, $sub_title, $oss_video, $oss_audio, $author, $duration, $description, $photo, $status, $owner, $keywords, $time, $captions) {
        $result = false;

        $program = new $this->_document_class();
        
        if (is_array($photo)) {
            foreach ($photo as $p) {
                $program->addPhoto($p);
            }
        }
        
        foreach ($keywords as $p) {
            $program->addKeyword($p);
        }
    //    echo $program->keyword[0]->id; exit;
        $program->name = $name;
        $program->sub_title = $sub_title;
        $program->oss_video = $oss_video;
        $program->oss_audio = $oss_audio;
        $program->author = $author;
        $program->duration = $duration;
        $program->description = $description;
        $program->status = $status;
        $program->owner = $owner;
        $program->time = $time;
        $program->captions = $captions;
         
        try {
            $this->_dm->persist($program);
            $this->_dm->flush();

            $result = $program->id;
        } catch (Exception $e) {
            $this->_logger->info(__CLASS__, __FUNCTION__, $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new Angel_Exception_Program(Angel_Exception_Program::ADD_PROGRAM_FAIL);
        }

        return $result;
    }

    /**
     * 编辑节目
     * @param string $id
     * @param string $name
     * @param string $sub_title
     * @param \Documents\Oss $oss_video
     * @param \Documents\Oss $oss_audio
     * @param \Documents\Author $author
     * @param int $duration
     * @param string $description
     * @param array $photo
     * @param string $status
     * @param \Documents\Category $category
     * @return mix - when user registration success, return the user id, otherwise, boolean false
     * @throws Angel_Exception_Program
     */
    public function saveProgram($id, $name, $sub_title, $oss_video, $oss_audio, $author, $duration, $description, $photo, $status, $keywords, $time, $captions) {
        $result = false;

        $program = $this->getById($id);
        if (!$program) {
            throw new Angel_Exception_Program(Angel_Exception_Program::PROGRAM_NOT_FOUND);
        }
        // 清空图片
        $program->clearPhoto();
        // 重新添加图片并保存
        if (is_array($photo)) {
            foreach ($photo as $p) {
                $program->addPhoto($p);
            }
        }
        
        $program->name = $name;
        $program->sub_title = $sub_title;
        $program->oss_video = $oss_video;
        $program->oss_audio = $oss_audio;
        $program->author = $author;
        $program->duration = $duration;
        $program->description = $description;
        $program->status = $status;
        $program->time = $time;
        $program->captions = $captions;

        $program->clearKeyword();

        foreach ($keywords as $p) {
            $program->addKeyword($p);
        }

        try {
            $this->_dm->persist($program);
            $this->_dm->flush();

            $result = $program->id;
        } catch (Exception $e) {
            $this->_logger->info(__CLASS__, __FUNCTION__, $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new Angel_Exception_Program(Angel_Exception_Program::SAVE_PROGRAM_FAIL);
        }

        return $result;
    }

    public function getProgramByOss($oss_id, $type, $return_as_paginator = true) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)
                ->field(sprintf('oss_%s.$id', $type))->equals(new MongoId($oss_id))
                ->sort('created_at', -1);
        $result = null;
        if ($return_as_paginator) {
            $result = $this->paginator($query);
        } else {
            $result = $query->getQuery()->execute();
        }
        return $result;
    }
    
    public function getProgramNotOwn($programs_id) { 
        $query = null;
        
        if (count($programs_id) < 1 || $programs_id[0] == "") {
            $query = $this->_dm->createQueryBuilder($this->_document_class)->find()->sort('created_at', -1);
        }
        else {
            $query = $this->_dm->createQueryBuilder($this->_document_class)->field('id')->notIn($programs_id)->sort('created_at', -1);
        }

        $result = $query
                ->getQuery();
        
        return $result;
    }
    
    public function getProgramOwn($programs_id) { 
        $query = null;
        
        if (count($programs_id) < 1 || $programs_id[0] == "") {
            $query = $this->_dm->createQueryBuilder($this->_document_class)->find()->sort('created_at', -1);
        }
        else {
            $query = $this->_dm->createQueryBuilder($this->_document_class)->field('id')->in($programs_id)->sort('created_at', -1);
        }

        $result = $query
                ->getQuery();
        
        return $result;
    }
    
//    public function getAll() { 
//        $query = $this->_dm->createQueryBuilder($this->_document_class)->sort('created_at', -1);
//
//        $result = $query
//                ->getQuery()
//                ->execute();
//
//        return $result;
//    }
    
    public function getProgramBySpecialId($programs_id) {
        if (count($programs_id) < 1 || $programs_id[0] == "") {
            return null;
        }
        
        $query = $this->_dm->createQueryBuilder($this->_document_class)->field('id')->in($programs_id)->sort('created_at', -1);

        $result = $query
                ->getQuery();

        return $result;
    }
    
    public function getProgramByKeyword($keyword_id) {
        $result = false;
        
        if ($keyword_id) {
            $query = $this->_dm->createQueryBuilder($this->_document_class)
                    ->field('keyword.$id')->equals(new MongoId($keyword_id))
                    ->sort('created_at', -1);
            
            $result = $query->getQuery()->getSingleResult();
        }
        
        if ($result)
            return true;
        else
            return false;
    }
}
