<?php

namespace Progracqteur\WikipedaleBundle\Resources\Services\Designator;

use Progracqteur\WikipedaleBundle\Entity\Model\Report;
use Progracqteur\WikipedaleBundle\Resources\Services\Designator\ModeratorFinderInterface;

/**
 * This class receive all services tagged as `moderatorFinder`
 * (which are instance of `ModeratorFinderInterface`.
 * 
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ModeratorDesignator
{
   /**
    * associative array as $priority => $finder
    * 
    * @var ModeratorFinderInterface[]
    */
   private $finders = array();
   
   public function getModerator(Report $report)
   {
      foreach ($this->finders as $finder) {
         if ($finder->isResponsible($report)) {
            $moderators =  $finder->getPossibleModerators($report);
            if (count($moderators) > 1) {
               //TODO : log 
            }
            return $moderators[0];
         }
      }
   }
   
   public function getPossibleModerators(Report $report)
   {
      $moderators = array();
      foreach($this->finders as $priority => $finder) {
         if ($finder->isResponsible($report)) {
            $moderators = array_merge($moderators, $finder
                  ->getPossibleModerators($report));
         }
      }
      return $moderators;
   }
   
   public function addModeratorFinder(ModeratorFinderInterface $finder, $priority)
   {
      $this->finders[$priority] = $finder;
   }
   
   public function sortFinders()
   {
      ksort($this->finders);
   }
}
