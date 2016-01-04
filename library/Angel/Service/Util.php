<?php

class Angel_Service_Util{
    
    protected $_bootstrap_options;
    
    public function __construct($bootstrap_options){
        $this->_bootstrap_options = $bootstrap_options;
    }
    
    public function getTmpDirectory(){
        return $this->_bootstrap_options['tmpDirectory'];
    }
    
    /*
     * dynamic generate a unique filename
     */
    public function generateFilename($extension = ''){
//        $filename = uniqid('md5(mt_rand())', true);
        $filename = uniqid('', true);
        
        if(!empty($extension)){
            if(strpos($extension, '.') !== 0){
                $extension = '.'.$extension;
            }
            $filename .= $extension;
        }
        
        return $filename;
    }
    
    public function getFilename($filepath){
        $filename = $filepath;
        
        $pos = strrpos($filepath, DIRECTORY_SEPARATOR);
        if($pos !== false){
            $filename = substr($filepath, $pos+1);
        }
        
        return $filename;
    }
    
    /**
     * convert the time to the time on specific timezone
     * @param \Datetime or String $date
     * @param String timezone 
     * @return return a datetime object, when the date is not be able to be converted, 
     */
    public function localDate($date, $timezone=null){
        $default_timezone = date_default_timezone_get();
        
        date_default_timezone_set('UTC');
        
        $result = null;
        
        if(is_string($date)){
           $date = new \DateTime($date); 
        }
        
        if($date instanceof \DateTime){
            $timezone = empty($timezone) ? $this->_bootstrap_options['localTimezone'] : $timezone;
            $result = $date;
            $result->setTimezone(new \DateTimeZone($timezone));
        }
        
        date_default_timezone_set($default_timezone);
        
        return $result;
    }
}
