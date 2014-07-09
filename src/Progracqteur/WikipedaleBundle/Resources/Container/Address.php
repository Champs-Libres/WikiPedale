<?php

namespace Progracqteur\WikipedaleBundle\Resources\Container;

use Progracqteur\WikipedaleBundle\Resources\Geo\Point;

/**
 * Description of Address
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class Address {
    private $city = '';
    private $administrative = '';
    private $county = '';
    private $state_district = ''; 
    private $state = '';
    private $country = '';
    private $country_code = '';
    private $road = '';
    private $public_building = '';
    
    const ROAD_DECLARATION = 'road';
    const PUBLIC_BUILDING_DECLARATION = 'public_building';
    const CITY_DECLARATION = 'city';
    const ADMINISTRATIVE_DECLARATION = 'administrative';
    const COUNTY_DECLARATION = 'county';
    const STATE_DISTRICT_DECLARATION = 'state_district_declaration';
    const STATE_DECLARATION = 'state';
    const COUNTRY_DECLARATION = 'country';
    const COUNTRY_CODE_DECLARATION = 'country_code';     
    
    public function setCity($city) {
        $this->city = trim($city);
    }
    
    public function setAdministrative($administrative) {
        $this->administrative = trim($administrative);
    }
    
    public function setStateDistrict($state_strict)
    {
        $this->state_district = trim($state_strict);
    }
    
    public function setCounty($county) {
        $this->county = trim($county);
    }
    
    public function setState($state) {
        $this->state = trim($state);
    }
    
    public function setCountry($country) {
        $this->country = trim($country);
    }
    
    public function setCountryCode($country_code) {
        $this->country_code = trim($country_code);
    }
    
    public function setRoad($road)
    {
        $this->road = trim($road);
    }
    
    public function setPublicBuilding($public_building)
    {
        $this->public_building = trim($public_building);
    }
    
    public function toArray(){
        return array(
          //self::CITY_DECLARATION => $this->city,
          //self::ADMINISTRATIVE_DECLARATION => $this->administrative,
          //self::COUNTRY_CODE_DECLARATION => $this->country,
          //self::COUNTY_DECLARATION => $this->county,
          //self::STATE_DECLARATION => $this->state,
          //self::STATE_DISTRICT_DECLARATION => $this->state_district,
          self::ROAD_DECLARATION => $this->road,
          //self::PUBLIC_BUILDING_DECLARATION => $this->public_building  
        );
    }
    
    public function getCity()
    {
        return $this->city;
    }
    
    public function equals(Address $address)
    {
        return $this->toArray() == $address->toArray();
    }
    
    public function getRoad()
    {
        return $this->road;
    }
    
    public static function fromArray($array)
    {
        $a = new self;
        
        foreach ($array as $key => $v)
        {
                switch ($key) {
                    case self::CITY_DECLARATION :
                        $a->setCity($v);
                        break;
                    case self::ADMINISTRATIVE_DECLARATION :
                        $a->setAdministrative($v);
                        break;
                    case self::COUNTY_DECLARATION :
                        $a->setCounty($v);
                        break;
                    case self::STATE_DISTRICT_DECLARATION :
                        $a->setStateDistrict($v);
                        break;
                    case self::STATE_DECLARATION :
                        $a->setState($v);
                        break;
                    case self::COUNTRY_DECLARATION :
                        $a->setCountry($v);
                        break;
                    case self::COUNTRY_CODE_DECLARATION :
                        $a->setCountryCode($v);
                        break;
                    case self::ROAD_DECLARATION : 
                        $a->setRoad($v);
                            break;
                    case self::PUBLIC_BUILDING_DECLARATION :
                        $a->setPublicBuilding($v);
                        break;
                }
        }
              
        return $a;
    }

    /**
     * Generate the adress from a point via mapquestapi
     * @param Point the point
     * @return The addresse
     */
    public static function maquestGenerateFromPoint(Point $point)
    {
        $a = new self();

        $dom = new \DOMDocument();
        $lat = $point->getLat();
        $lon = $point->getLon();

        $url = "http://open.mapquestapi.com/nominatim/v1/reverse?format=xml&lat=$lat&lon=$lon";

        $dom->load($url);
        $docs = $dom->getElementsByTagName('addressparts');
        
        $doc = $docs->item(0);

        if ($dom->hasChildNodes()) {
            foreach ($doc->childNodes as $node) {
                $v = $node->nodeValue;
                
                switch ($node->nodeName) {
                    case self::CITY_DECLARATION :
                        $a->setCity($v);
                        break;
                    case self::ADMINISTRATIVE_DECLARATION :
                        $a->setAdministrative($v);
                        break;
                    case self::COUNTY_DECLARATION :
                        $a->setCounty($v);
                        break;
                    case self::STATE_DISTRICT_DECLARATION :
                        $a->setStateDistrict($v);
                        break;
                    case self::STATE_DECLARATION :
                        $a->setState($v);
                        break;
                    case self::COUNTRY_DECLARATION :
                        $a->setCountry($v);
                        break;
                    case self::COUNTRY_CODE_DECLARATION :
                        $a->setCountryCode($v);
                        break;
                    case self::ROAD_DECLARATION : 
                        $a->setRoad($v);
                            break;
                    case self::PUBLIC_BUILDING_DECLARATION :
                        $a->setPublicBuilding($v);
                        break;
                }
            }

        }   
        return $a;
    }
}

