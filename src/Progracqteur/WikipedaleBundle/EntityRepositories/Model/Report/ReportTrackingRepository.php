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

namespace Progracqteur\WikipedaleBundle\EntityRepositories\Model\Report;

use Doctrine\ORM\EntityRepository;
use Progracqteur\WikipedaleBundle\Entity\Management\Zone;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * Description of ReportTrackingRepository
 *
 * @author Champs-Libres Coop
 */
class ReportTrackingRepository extends EntityRepository
{    
    /**
     * 
     * @param int $first
     * @param int $max
     * @param \Progracqteur\WikipedaleBundle\Entity\Management\Zone $city
     * @param bool $private
     */
    public function getLastEvents($first, $max, Zone $zone, $private = false)
    {    
        $sql = "SELECT placetracking.id as id, 
            placetracking.author_id as author_id, 
            placetracking.place_id as place_id, 
            placetracking.iscreation as iscreation, 
            placetracking.details as details, 
            placetracking.date as date
                FROM placetracking
                JOIN place on placetracking.place_id = place.id
                JOIN group_table on place.moderator_id = group_table.id
                WHERE group_table.zone_id = :zoneId
                and place.accepted = TRUE
                and placetracking.iscreation IS NOT NULL ";
        
        if ($private === false) {
            $sql .= "AND xmlexists('/parent/tree/node[@key=110]' PASSING BY REF details) = false";
        }
        
        $sql.= " ORDER BY date DESC LIMIT :limit OFFSET :offset";
        
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('ProgracqteurWikipedaleBundle:Model\Report\ReportTracking', 'pt');
        
        return $this->getEntityManager()
            ->createNativeQuery($sql, $rsm)
            ->setParameter('zoneId', $zone->getId())
            ->setParameter('limit', $max)
            ->setParameter('offset', $first)
            ->getResult();    
    }
}

