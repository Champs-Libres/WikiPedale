<?php

namespace Progracqteur\WikipedaleBundle\Resources\Services\Designator;

use Progracqteur\WikipedaleBundle\Entity\Model\Report;

/**
 * This interface must be implemented on classes'services 
 * tagged as `moderatorFinder`.
 * 
 * 
 * 
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
interface ModeratorFinderInterface
{
   /**
    * 
    * @param \Progracqteur\WikipedaleBundle\Entity\Model\Report $report
    * @return boolean If the service may have a Moderator for this report
    */
   public function isResponsible(Report $report);
   
   /**
    * return a list of possible moderators for this report
    * 
    * @param \Progracqteur\WikipedaleBundle\Entity\Model\Report $report
    * @return \Progracqteur\WikipedaleBundle\Entity\Management\Group[] possible moderators
    */
   public function getPossibleModerators(Report $report);
}
