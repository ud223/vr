<?php

class Angel_Model_Vote extends Angel_Model_AbstractModel {
    protected $_document_class = '\Documents\Vote';
    
    public  function addVote($userId, $keywordId, $score) {
        $data = array("userId"=>$userId, "keywordId" => $keywordId, "score" => $score);
        
        $result = $this->add($data);
        
        return $result;
    }
    
    public function saveVote($id, $userId, $keywordId, $score) {
        $data = array("userId"=>$userId, "keywordId" => $keywordId, "score" => $score);
        
        $result = $this->save($id, $data);
        
        return $result;
    }
    
    public function getByKeywordIdAndUid($keyword_id, $user_id) {
        $result = false;
        
        $vote = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('keywordId')->equals($keyword_id)->field('userId')->equals($user_id)
                ->getQuery()
                ->getSingleResult();
        
        if (empty($vote)) {
            return $result;
        }
        
        return $vote;
    }
}
