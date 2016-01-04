<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Follow extends AbstractDocument{
    
    const FOLLOW_TARGET_STARTUP = 'startup';
    const FOLLOW_TARGET_INVESTOR = 'investor';
    const FOLLOW_TARGET_COMPANY = 'company';
    
    /** @ODM\ReferenceOne(targetDocument="\Documents\User") */
    protected $user;

    /** @ODM\String */
    protected $target_type;
    
    /** @ODM\ReferenceOne(targetDocument="\Documents\User") */
    protected $target_user;
    
    /** @ODM\ReferenceOne(targetDocument="\Documents\Company") */
    protected $target_company;
    
    /** @ODM\Boolean */
    protected $active_bln = true;
}
