<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Progracqteur\WikipedaleBundle\Entity\Management;

use Doctrine\ORM\EntityRepository;

/**
 * Description of ZoneRepository
 *
 * @author Champs Libres Coop
 */
class ZoneRepository extends EntityRepository
{
    /*
     * Return the sql for getting all the zone having a moderator.
     * 
     * @param $whereParameters More where condition. Must start with ' and '
     */
    private function getZoneWithModeratorQuery($whereParameters = '')
    {
        return 'SELECT z FROM ProgracqteurWikipedaleBundle:Management\Zone z'
            . ' INNER JOIN ProgracqteurWikipedaleBundle:Management\Group g'
            . ' WHERE z.id = g.zone'
            . ' '.$whereParameters
            . ' AND g.type=\'MODERATOR\''
            . ' GROUP BY z.id'
            . ' HAVING COUNT(z) >= 1'
            . ' ORDER BY z.name';
    }
    
    /*
     * Returns all zone having a moderator (group) assigned
     */
    public function findAllWithModerator()
    {
        return $this->getEntityManager()
            ->createQuery($this->getZoneWithModeratorQuery())
            ->getResult();
    }
    
    /*
     * Returns all zone having a moderator (group) assigned and covering
     * a given position
     */
    public function findAllWithModeratorCovering($position) {
        return $this->getEntityManager()
            ->createQuery(
                $this->getZoneWithModeratorQuery(
                    " AND ST_COVERS(z.polygon, '$position') = true "))
            ->getResult();

    }        
}


