<?php

namespace Progracqteur\WikipedaleBundle\Resources\Geo;

/**
 * A polygon that can be stored in DB (Postgresql + PostGIS)
 *
 * @author Champs-Libres Coop
 */
class Polygon
{    
    private $vertex = array();
    
    public static $SRID = '4326';
    
    public function __construct($vertexes = array(), $latLonKey = true)
    {
        foreach ($vertexes as $key => $values)
        {
            if($latLonKey) {
                $this->vertex[] = array('lon' => $values['lon'], 'lat' => $values['lat']);
            } else {
                $this->vertex[] = array('lon' => $values[0], 'lat' => $values[1]); 
            }   
        }
    }
    
    public function __toString()
    {
        $ret = "Polygon[";
        foreach($this->vertex as $v)
        {
            $ret = $ret + "(lon:".$v['lon'].", lat:".$v['lat'].")";
        }
        return $ret."]";
    }
    
    
    /**
     * Convert to WKT (Well-known text)
     * 
     * @return string 
     */
    public function toWKT()
    {
        $retPolygon = "POLYGON((";
        
        $vertexesNbr = sizeof($this->vertex);
            
        for($i = 0; $i < $vertexesNbr; $i++) {
            $retPolygon = $retPolygon.$this->vertex[$i]['lon']." ".$this->vertex[$i]['lat'];
            if($i != $vertexesNbr -1) {
                $retPolygon = $retPolygon.", ";
            }
        }
        
        $retPolygon = $retPolygon.'))';
        
        return 'SRID='.self::$SRID.';'.$retPolygon;
    }
    
    public function toGeoJson()
    {
        $array = $this->toArrayGeoJson();
        return \json_encode($array);
    }
    
    public function toArrayGeoJson()
    {
        $rings = array();
        $ring = array();
        foreach ($this->vertex as $point) {
            $ring[] = array($point['lon'], $point['lat']);
        }
        
        $rings[] = $ring;
        return array("type" => "Polygon", "coordinates" => $rings);
    }
    
    /**
     * Create a polygon form a String containing a geojson description
     * of the polygon.
     * 
     * @param type $geojson The geojson string
     * @return Polygon The polygon 
     */
    public static function fromGeoJson($geojsonString) 
    {
        $geojson = json_decode($geojsonString, true);
        return new Polygon($geojson['coordinates'][0], false);
    }
    
    public static function fromLonLat($lon, $lat)
    {
        return new Polygon;
    }
    
    public static function fromArrayGeoJson($array)
    {
        return new Polygon;
    }
}


