<?php
/**
 * 消息流 
 */      
class Angel_Model_Feed extends Angel_Model_AbstractModel{
    
    protected $_document_class = '\Documents\Feed';
    
    /**
     * 把关注用户的行为纪录到消息流
     * @param \Documents\User $user - 谁来关注了
     * @param \Documents\User $target_user － 谁被关注了
     * if there is no exception, this function always return true
     */
    public function recordFollowUserFeed(\Documents\User $user, \Documents\User $target_user){
        
        $type = \Documents\Feed::FEED_TYPE_STARTUP_FOLLOW;
        if($target_user->isInvestor()){
            $type = \Documents\Feed::FEED_TYPE_INVESTOR_FOLLOW;
        }
        
        $date = new \DateTime();
        $date->sub(new \DateInterval('PT24H'));
        
        $feed = $this->_dm->createQueryBuilder($this->_document_class)
                          ->field('user.$id')->equals($user->id)
                          ->field('feed_type')->equals($type)
                          ->field('created_at')->gt($date)
                          ->getQuery()
                          ->getSingleResult();
        
        if(!$feed){
            $feed = new $this->_document_class();
            $feed->user = $user;
            $feed->feed_type = $type;
        }
        
        $feed->addFollowedUser($target_user);
        
        $this->_dm->persist($feed);
        $this->_dm->flush();
        
        return true;
    }
}
