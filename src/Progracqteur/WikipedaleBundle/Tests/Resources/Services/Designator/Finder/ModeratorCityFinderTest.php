<?php

namespace Progracqteur\WikipedaleBundle\Tests\Resources\Services\Designator\Finder;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Progracqteur\WikipedaleBundle\Entity\Model\Report;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ModeratorCityFinderTest extends WebTestCase
{
   /**
    *
    * @var \Progracqteur\WikipedaleBundle\Resources\Services\Designator\ModeratorDesignator 
    */
   private static $moderatorFinder;
   
   private static $container;
   
   public static function setUpBeforeClass()
   {
      $kernel = static::createKernel();
      $kernel->boot();
      static::$container = $kernel->getContainer();
      static::$moderatorFinder = static::$container
            ->get('progracqteur.wikipedale.moderator_designator');
   }
   
   public function testMons()
   {
      $report = Report::randomGenerate(array('category' => 'RANDOM', 
         'creator' => 'RANDOM_UNREGISTERED'));
      
      
      $moderator = self::$moderatorFinder->getModerator($report);

      $this->assertTrue($moderator->getZone()->getSlug() === 'mons');
   }
}
