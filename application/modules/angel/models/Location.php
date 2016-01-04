<?php
      
class Angel_Model_Location extends Angel_Model_AbstractModel{
    
    protected $_province_class = '\Documents\Province';
    protected $_city_class = '\Documents\City';
    
    /**
     * 根据pid，返回省份
     * @param int $pid
     * @return \Documents\Province
     */
    public function getProvinceByPID($pid){
        $province = $this->_dm->createQueryBuilder($this->_province_class)
                           ->field('pid')->equals($pid)
                           ->getQuery()
                           ->getSingleResult();
        
        return $province;
    }
    
    /**
     * 根据cid，返回城市
     * @param int $cid
     * @return \Documents\City
     */
    public function getCityByCID($cid){
        $city = $this->_dm->createQueryBuilder($this->_city_class)
                          ->field('cid')->equals($cid)
                          ->getQuery()
                          ->getSingleResult();
        
        return $city;
    }
}
