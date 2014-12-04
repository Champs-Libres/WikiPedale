<?php

namespace Progracqteur\WikipedaleBundle\Tests\Resources\Services\Designator;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Progracqteur\WikipedaleBundle\Entity\Model\Report;
use Progracqteur\WikipedaleBundle\Entity\Management\Group;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ModeratorDesignatorTest extends WebTestCase
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
   
   public function testDesignator()
   {
      $report = Report::randomGenerate(array('category' => 'RANDOM', 
         'creator' => 'RANDOM_UNREGISTERED'));
      $groups = self::$moderatorFinder->getPossibleModerators($report);
      
      $this->assertTrue(is_array($groups));
      $this->assertGreaterThanOrEqual(1, count($groups));
      
      $moderator = self::$moderatorFinder->getModerator($report);
      
      $this->assertTrue($moderator instanceof Group);
      
      return array($report, $moderator);
   }
   
   /**
    * 
    * @param array $args
    * @depends testDesignator
    */
   public function testAddModeratorToClass($args) {
      $report    = $args[0];
      $moderator = $args[1];
      
      $report->setModerator($moderator);
      
      $this->assertEquals($report->getModerator(), $moderator);
      $this->assertContains($report, $moderator->getReportsAsModerator());
   }
}
