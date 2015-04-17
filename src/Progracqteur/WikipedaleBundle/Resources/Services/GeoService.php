<?php

/*
 *  Uello is a reporting tool. This file is part of Uello.
 * 
 *  Copyright (C) 2015, Champs-Libres Cooperative SCRLFS,
 *  <http://www.champs-libres.coop>, <info@champs-libres.coop>
 * 
 *  Uello is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Uello is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Uello.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Progracqteur\WikipedaleBundle\Resources\Services;

use Doctrine\ORM\EntityManager;
use CrEOF\Spatial\PHP\Types\Geography\Point;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * This service is a tool for geographic operation where the database is
 * needed.
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class GeoService {
    
    /**
     *
     * @var Doctrine\ORM\EntityManager 
     */
    private $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    /**
     * This function return the postgis'string of a Geographic Point.
     * 
     * It use a postgis database request to convert the point to the postgis string
     * 
     * @deprecated
     * 
     * @param \Progracqteur\WikipedaleBundle\Resources\Geo\Point $geog
     * @return string
     */
    public function toString($geog)
    {
        if ($geog instanceof Point)
        {
            $wkt = $geog->toWKT();
            
            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('string', 'string');
            
            $q = $this->em->createNativeQuery('SELECT ST_GeographyFromText(:wkt) as string ', $rsm)
                ->setParameter('wkt', $wkt);
            $r = $q->getResult();
            
            return $r[0]['string'];
        } else {
            throw new \Exception('object not supported');
        }
    }
    
    /**
     * return whether the point is covered be (= inside a) polygon
     * 
     * @deprecated
     * 
     * @param string $polygon the postgis representation of the polygon
     * @param \Progracqteur\WikipedaleBundle\Resources\Geo\Point $point the postgis representation of the point
     * @return boolean
     */
    public function covers($polygon, Point $point)
    {
        $rsm = new ResultSetMapping;
        $rsm->addScalarResult('covered', 'covered', 'boolean');
        
        $r = $this->em->createNativeQuery('SELECT ST_COVERS(:polygon, ST_GeographyFromText(:point)) as covered;', $rsm)
                ->setParameter('polygon', $polygon)
                ->setParameter('point', $point->toWKT())
                ->getSingleScalarResult();
        
        return $r;
    }
    
    /**
     * Return a point instance for a GeoJSON stirng
     * 
     * @param string $geojson The GeoJSON description of the point
     * @return Point The point
     * @throws Exception if the GeoJSON is not well formated
     */
    public function pointFromGeoJSON($geojson)
    {
        return $this->pointFromGeoJSONArray(json_decode($geojson));
    }
    
    /**
     * Return a point instance for a GeoJSON stirng
     * 
     * @param string $array The GeoJSON array description of the point
     * @return Point The point
     * @throws Exception if the GeoJSON is not well formated
     */
    public function pointGeoJSONArray($array)
    {
        if ($array == null || !array_key_exists('type', $array)
            || !array_key_exists('coordinates', $array)
            || $array['type'] != "Point"){
            throw new Exception('Bad GeoJSON for point : ' . $array);
        } else {
            return new Point($array['coordinates']);
        }
    }
}

