<?php

namespace Progracqteur\WikipedaleBundle\Resources\Services\Designator\Finder;

use Progracqteur\WikipedaleBundle\Resources\Services\Designator\ModeratorFinderInterface;
use Progracqteur\WikipedaleBundle\Entity\Model\Report;
use Doctrine\ORM\EntityManagerInterface;
use Progracqteur\WikipedaleBundle\Resources\Services\GeoService;
use Progracqteur\WikipedaleBundle\Entity\Management\Group;
/**
 * return moderators for a 'city' zone, for report within a city zone
 * 
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ModeratorCityFinder implements ModeratorFinderInterface
{
    /** @var EntityManagerInterface */
    private $em;
   
    /** @var GeoService */
    private $geoservice;
   
    /**
     * cache the results for a report
     * use the spl_object_hash to identify reports (instead of the id, which 
     * may be null if the report is not persisted)
     * 
     */
    private $cache = array();
  
    public function __construct(EntityManagerInterface $em, GeoService $geoservice)
    {
        $this->em = $em;
        $this->geoservice = $geoservice;
    }
   
    public function getPossibleModerators(Report $report)
    {
      return $this->findModerators($report);
    }

    public function isResponsible(Report $report)
    {
        return (count($this->findModerators($report)) > 0);
    }
   
    /**
     * find moderators.
     * 
     * cache the moderators into the class
     * 
     * @param \Progracqteur\WikipedaleBundle\Entity\Model\Report $report
     * @return \Progracqteur\WikipedaleBundle\Entity\Management\Group[]
     */
    private function findModerators(Report $report)    
    {
        if (isset($this->cache[spl_object_hash($report)])) {
            return $this->cache[spl_object_hash($report)];
        }
     
        $stringPoint = 'POINT('. $report->getGeom() .')';
      
        $dql = "SELECT g "
            . "FROM ProgracqteurWikipedaleBundle:Management\Group g "
            . "JOIN g.zone z "
            . "WHERE ST_COVERS(z.polygon, :point) = true "
            . "AND z.type = 'city'"
            . "AND g.type = :type";
        
          
        $groups = $this->em->createQuery($dql)
            ->setParameter('point', $stringPoint)
            ->setParameter('type', Group::TYPE_MODERATOR)
            ->getResult();
      
        $this->cache[spl_object_hash($report)] = $groups;
        return $groups;
    }
}
