<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\EmbeddedDocument */
class CompanyInvestor extends AbstractDocument{
    
    /** @ODM\ReferenceOne(targetDocument="\Documents\User") */
    protected $user;

    /** @ODM\Int */
    protected $percent;
    
    /** @ODM\Int */
    protected $amount;
}

?>