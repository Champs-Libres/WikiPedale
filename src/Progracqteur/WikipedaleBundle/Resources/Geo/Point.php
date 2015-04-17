<?php

namespace Progracqteur\WikipedaleBundle\Resources\Geo;

/**
 * Description of Point
 *
 * @author user
 */
class Point
{
    private $lat;
    private $lon;
    public static $SRID = '4326';
    
    public function __construct($lon, $lat)
    {
        $this->lat = $lat;
        $this->lon = $lon;
    }
    
    public function toGeoJson()
    {
        $array = $this->toArrayGeoJson();
        return \json_encode($array);
    }
    
    public function toArrayGeoJson()
    {
        return array("type" => "Point", "coordinates" => array ($this->lon, $this->lat));
    }
    
    /**
     * Convert to WKT (Well-known text)
     * 
     * @return string 
     */
    public function toWKT()
    {
        return 'SRID='.self::$SRID.';POINT('.$this->lon.' '.$this->lat.')';
    }
    
    /**
     *
     * @param type $geojson
     * @return Point 
     */
    public static function fromGeoJson($geojson) 
    {
        $a = json_decode($geojson);
        //check if the geojson string is correct
        if ($a == null || !isset($a->type) || !isset($a->coordinates)){
            throw PointException::badJsonString($geojson);
        }
        
        if ($a->type != "Point"){
            throw PointException::badGeoType();
        } else {
            $lat = $a->coordinates[1];
            $lon = $a->coordinates[0];
            return Point::fromLonLat($lon, $lat);
        }   
    }
    
    public static function fromLonLat($lon, $lat)
    {
        //TODO : les tests devraient être réalisés dans le constructeur
        if (($lon > -180 && $lon < 180) && ($lat > -90 && $lat < 90)) {
            return new Point($lon, $lat);
        } else {
            throw PointException::badCoordinates($lon, $lat);
        }
    }
    
    public static function fromArrayGeoJson($array)
    {
        if ($array['type'] == 'Point' &&
                isset($array['coordinates'])) {
            return self::fromLonLat($array['coordinates'][0], $array['coordinates'][1]);
        }
    }
    
    public function getLat()
    {
        return $this->lat;
    }
    
    public function getLon()
    {
        return $this->lon;
    }

    /**
     * Returns a random point in the Mons region (lat in [50.4500, 50.4570]; lon in  [3.9400, 3.9620])
     * @return \Progracqteur\WikipedaleBundle\Resources\Geo\Point A random point in the Mons region
     */
    public static function randomGenerate()
    {
        return self::fromLonLat((rand(39400, 39620)/10000), (rand(504500, 504570)/10000));
    }
}