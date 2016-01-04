<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Contact extends AbstractDocument {
    /** @ODM\String */
    protected $name;

    /** @ODM\String */
    protected $name_en;

    /** @ODM\String */
    protected $sale_tel;

    /** @ODM\String */
    protected $sale_tel_en;

    /** @ODM\String */
    protected $fax;

    /** @ODM\String */
    protected $fax_en;

    /** @ODM\String */
    protected $company_address;

    /** @ODM\String */
    protected $company_address_en;

    /** @ODM\String */
    protected $factory_address_1;

    /** @ODM\String */
    protected $factory_address_1_en;

    /** @ODM\String */
    protected $factory_address_2;

    /** @ODM\String */
    protected $factory_address_2_en;

    /** @ODM\String */
    protected $email;

    /** @ODM\String */
    protected $email_en;


} 