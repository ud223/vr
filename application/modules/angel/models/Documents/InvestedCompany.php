<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\EmbeddedDocument */
class InvestedCompany extends AbstractDocument{
    
    /** @ODM\ReferenceOne(targetDocument="\Documents\Company") */
    protected $company;

    /** @ODM\Int */
    protected $percent;
    
    /** @ODM\Int */
    protected $amount;

    public function getRequiredFields(){
        return array();
    }
    
    public function setAmount($value){
        $this->amount = $value * 100;
    }
    
    public function getAmount(){
        return $this->amount/100;
    }
    
    public function getPercent(){
        return $this->percent/100;
    }
}

?>