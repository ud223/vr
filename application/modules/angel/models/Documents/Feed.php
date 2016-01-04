<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Feed extends AbstractDocument{
    
    const FEED_TYPE_STARTUP_FOLLOW = 'startup_follow';  // 关注创业者的消息流
    const FEED_TYPE_INVESTOR_FOLLOW = 'investor_follow';  // 关注投资人的消息流
    const FEED_TYPE_COMPANY_FOLLOW = 'company_follow';  // 关注公司的消息流
    
    /** @ODM\ReferenceOne(targetDocument="\Documents\User") */
    protected $user;
    
    /** @ODM\String */
    protected $feed_type;
    
    /** @ODM\ReferenceMany(targetDocument="\Documents\User") */
    protected $followed_users = array();
    
    public function addFollowedUser(\Documents\User $user){
        $this->followed_users[] = $user;
    }
}
