<?php

namespace Angel\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LoadCity extends AngelCommand{
   
    protected function configure()
    {
        $this->setName('load:provincecity')
             ->setDescription('导入省，城市，区或县');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // clear the province collection
        $this->_documentManager->createQueryBuilder('\Documents\Province')
                               ->remove()
                               ->getQuery()
                               ->execute();
        // clear the city collection
        $this->_documentManager->createQueryBuilder('\Documents\City')
                               ->remove()
                               ->getQuery()
                               ->execute();
        // clear the district collection
        $this->_documentManager->createQueryBuilder('\Documents\District')
                               ->remove()
                               ->getQuery()
                               ->execute();
        
        $provinces_xml = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'Provinces.xml';
        
        $dom = new \DOMDocument();
        $dom->load($provinces_xml);
        
        $nodes = $dom->getElementsByTagName('Province');
        foreach($nodes as $node){
            $document = new \Documents\Province();
            $document->pid = intval($node->getAttribute('ID'));
            $document->name = $node->getAttribute('ProvinceName');
            
            $this->_documentManager->persist($document);
        }
        
        $this->_documentManager->flush();
        
        $cities_xml = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'Cities.xml';
        $dom = new \DOMDocument();
        $dom->load($cities_xml);
        
        $location_model = new \Angel_Model_Location($this->_bootstrap);
        
        $nodes = $dom->getElementsByTagName('City');
        foreach($nodes as $node){
            $pid = intval($node->getAttribute('PID'));
            $cid = intval($node->getAttribute('ID'));
            $city_name = $node->getAttribute('CityName');
            $zipcode = $node->getAttribute('ZipCode');
            
            $province = $location_model->getProvinceByPID($pid);
            if($province){
                $document = new \Documents\City();
                $document->cid = $cid;
                $document->name = $city_name;
                $document->zipcode = $zipcode;
                $document->province = $province;
                
                $this->_documentManager->persist($document);
            }
            else{
                $output->writeln($city_name.'('.$cid.') has invalid pid: '.$pid);
                exit;
            }
        }
        
        $this->_documentManager->flush();
        
        $districts_xml = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'Districts.xml';
        $dom = new \DOMDocument();
        $dom->load($districts_xml);
        
        $nodes = $dom->getElementsByTagName('District');
        foreach($nodes as $node){
            $did = intval($node->getAttribute('ID'));
            $cid = intval($node->getAttribute('CID'));
            $district_name = $node->getAttribute('DistrictName');
            
            $city = $location_model->getCityByCID($cid);
            if($city){
                $document = new \Documents\District();
                $document->did = $did;
                $document->name = $district_name;
                $document->city = $city;
                
                $this->_documentManager->persist($document);
            }
            else{
                $output->writeln($district_name.'('.$did.') has invalid cid: '.$cid);
                exit;
            }
        }
        
        $this->_documentManager->flush();
        
        $output->writeln("Done");
    }
}
