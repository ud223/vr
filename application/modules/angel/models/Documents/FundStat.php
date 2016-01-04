<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class FundStat extends AbstractDocument{
    
    /** @ODM\ReferenceOne(targetDocument="\Documents\User") */
    protected $user;
    
    /** @ODM\ReferenceOne(targetDocument="\Documents\Company") */
    protected $company;
    
    /** @ODM\Int */
    protected $percent;
            
    /** @ODM\Int */
    protected $amount;
    
    /** @ODM\String */
    protected $contract_address;
    
    /** @ODM\String */
    protected $contract_receiver;
    
    /** @ODM\String */
    protected $contract_phone;
}

?>