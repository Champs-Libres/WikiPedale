<?php

namespace Progracqteur\WikipedaleBundle\Resources\Normalizer;

use \Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use CrEOF\Spatial\PHP\Types\Geography\Polygon;

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

/**
 * Normalize a polygon in GeoJSON
 *
 * @author Champs-LIbres Coop
 */
class GeographyPolygonGeoJSONNormalizer implements NormalizerInterface
{
    public function normalize($polugon, $format = null, array $context = array())
    {
        $ret = array(
            "type" => "Feature",
            "geometry" => array (
                "type" => "Polygon",
                "coordinates" => $polugon->toArray()),
            "properties" => array()
        );
        
        return $ret;   
    }

    public function supportsNormalization($data, $format = null)
    {
        return ($data instanceof Polygon);
    }
}
