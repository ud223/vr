<?php

class Angel_Model_Contact extends Angel_Model_AbstractModel {
    protected $_document_class = '\Documents\Contact';

    public  function addContact($name, $name_en, $sale_tel, $sale_tel_en, $fax, $fax_en, $company_address, $company_address_en, $factory_address_1, $factory_address_1_en, $factory_address_2, $factory_address_2_en, $email, $email_en) {
        $data = array("name"=>$name, "name_en"=>$name_en, "sale_tel"=>$sale_tel, "sale_tel_en"=>$sale_tel_en, "fax"=>$fax, "fax_en"=>$fax_en, "company_address"=>$company_address, "company_address_en"=>$company_address_en, "factory_address_1"=>$factory_address_1, "factory_address_1_en"=>$factory_address_1_en, "factory_address_2"=>$factory_address_2, "factory_address_2_en"=>$factory_address_2_en, "email"=>$email, "email_en"=>$email_en);

        $result = $this->add($data);

        return $result;
    }

    public function saveContact($id, $name, $name_en, $sale_tel, $sale_tel_en, $fax, $fax_en, $company_address, $company_address_en, $factory_address_1, $factory_address_1_en, $factory_address_2, $factory_address_2_en, $email, $email_en) {
        $data = array("name"=>$name, "name_en"=>$name_en, "sale_tel"=>$sale_tel, "sale_tel_en"=>$sale_tel_en, "fax"=>$fax, "fax_en"=>$fax_en, "company_address"=>$company_address, "company_address_en"=>$company_address_en, "factory_address_1"=>$factory_address_1, "factory_address_1_en"=>$factory_address_1_en, "factory_address_2"=>$factory_address_2, "factory_address_2_en"=>$factory_address_2_en, "email"=>$email, "email_en"=>$email_en);

        $result = $this->save($id, $data);

        return $result;
    }
} 