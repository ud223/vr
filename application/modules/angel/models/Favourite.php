<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Favourite
 *
 * @author deanlu
 */
class Angel_Model_Favourite extends Angel_Model_AbstractModel {

    protected $_document_class = '\Documents\Favourite';

    public function addFavourite($user_id, $special) {
        $favourite = new $this->_document_class();

        $favourite->addSpecial($special);

        $favourite->user_id = $user_id;

        try {
            $this->_dm->persist($favourite);
            $this->_dm->flush();

            $result = $favourite->id;
        } catch (Exception $e) {
            $this->_logger->info(__CLASS__, __FUNCTION__, $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new Angel_Exception_Program(Angel_Exception_Program::ADD_PROGRAM_FAIL);
        }

        return $result;
    }

    public function saveFavourite($favourite, $special) {
        $favourite->addSpecial($special);

        try {
            $this->_dm->persist($favourite);
            $this->_dm->flush();

            $result = $favourite->id;
        } catch (Exception $e) {
            $this->_logger->info(__CLASS__, __FUNCTION__, $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new Angel_Exception_Program(Angel_Exception_Program::SAVE_PROGRAM_FAIL);
        }

        return $result;
    }

    public function getFavouriteByUserId($user_id) {
        $result = false;

        $favourite = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('user_id')->equals($user_id)
                ->getQuery()
                ->getSingleResult();

        if (empty($favourite)) {
            return $result;
        }

        return $favourite;
    }

    public function RemoveFavouriteByUserId($favourite, $special_id) {
        $specials = array();

        foreach ($favourite->special as $p) {
            if ($p->id == $special_id)
                continue;

            $specials[] = $p;
        }

        $favourite->clearSpecial();

        foreach ($specials as $p) {
            $favourite->addSpecial($p);
        }

        try {
            $this->_dm->persist($favourite);
            $this->_dm->flush();

            $result = $favourite->id;
        } catch (Exception $e) {
            $this->_logger->info(__CLASS__, __FUNCTION__, $e->getMessage() . "\n" . $e->getTraceAsString());
            throw new Angel_Exception_Program(Angel_Exception_Program::SAVE_PROGRAM_FAIL);
        }
    }

    public function isUserFavourite($user_id, $special_id) {

        $favourite = $this->_dm->createQueryBuilder($this->_document_class)
                ->field('user_id')->equals($user_id)
                ->field('special.$id')->equals(new MongoId($special_id))
                ->getQuery()
                ->getSingleResult();
        if (empty($favourite)) {
            return false;
        }
        if ($favourite) {
            return true;
        } else {
            return false;
        }
    }

}
