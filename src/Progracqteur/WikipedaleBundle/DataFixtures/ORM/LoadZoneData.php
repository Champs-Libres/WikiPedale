<?php

namespace Progracqteur\WikipedaleBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Progracqteur\WikipedaleBundle\Entity\Management\Zone;

/**
 * Description of LoadCitiesData
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class LoadCitiesData extends AbstractFixture implements ContainerAwareInterface, 
        OrderedFixtureInterface 
{

   /**
    *
    * @var Symfony\Component\DependencyInjection\ContainerInterface 
    */
   private $container;

   public function getOrder() {
     return 100;
   }

   public function load(ObjectManager $manager) {
      /**
      * @var \Doctrine\ORM\EntityManager
      */
      $em = $this->container->get('doctrine.orm.entity_manager');
         
      $conn = $em->getConnection();
      
      foreach ($this->getCities() as $i => $data) {
          $r = $conn->executeUpdate("INSERT INTO zones
          (id, name, slug, codeprovince, polygon, center, type, url, description)
          VALUES ('$i', '".$data['name']."', '".$data['slug']."', 7000,
              ST_GeomFromText('POLYGON((".$data['east']." ".$data['north'].", ".$data['west']." ".$data['north'].", ".$data['west']." ".$data['south'].", ".$data['east']." ".$data['south'].", ".$data['east']." ".$data['north']."))',4326),
              ST_GeomFromText('POINT(".($data['east'] + $data['west'])/ 2 ." ".($data['north'] + $data['south'])/2 .")',4326), '".Zone::TYPE_CITY."', 'mons.be', 'Description de Mons');");
          
          echo "$r slug updated in the database \n";
      }

      
   }
   
   private function getCities()
   {
       return array(
           array(  'name' => 'Mons', 'slug' => 'mons', 
               'east' => 3.7, 'north' => 50.55, 'west' => 4.3, 'south' => 50.4,
                'url' => 'mons.be', 'desc' => 'Description de Mons'
           ),
           array(  'name' => 'Lima', 'slug' => 'lima',
               'east' => -5, 'north' => -10, 'west' => -4, 'south' => -12,
               'url' => 'lima.chili', 'desc' => 'Description de Lima'
           ),
           array(  'name' => 'Honolulu', 'slug' => 'honolulu',
               'east' => -25, 'north' => 50, 'west' => -18, 'south' => 49,
               'url' => 'mons.be', 'desc' => 'Description de Mons'
           ),
           array( 'name' => 'Hawaii', 'slug' => 'hawaii',
               'east' => -30, 'north' => 60, 'west' => -29, 'south' => 59,
               'url' => 'mons.be', 'desc' => 'Description de Mons'
           ),
           array(  'name' => 'Paris', 'slug' => 'paris',
               'east' => 4, 'north' => 48, 'west' => 5, 'south' => 47,
               'url' => 'paris.be', 'desc' => 'Description de Paris'
           ),
           array(  'name' => 'Anvers', 'slug' => 'anvers',
               'east' => 5, 'north' => 51, 'west' => 5.5, 'south' => 50.8,
               'url' => 'Anvers.be', 'desc' => 'Description de Anvers'
           ),
           array(  'name' => 'Moscou', 'slug' => 'moscou',
               'east' => 15, 'north' => 60, 'west' => 16, 'south' => 59,
               'url' => 'moscou.ru', 'desc' => 'Description de Moscou'
           ),
           array(  'name' => 'Beijing', 'slug' => 'beijing',
               'east' => 45, 'north' => 48, 'west' => 46, 'south' => 47,
               'url' => 'beijing.cn', 'desc' => 'Description de beijing'
           ),
           array(  'name' => 'Conakry', 'slug' => 'paris',
               'east' => 4, 'north' => -20, 'west' => 5, 'south' => -21,
               'url' => 'mons.be', 'desc' => 'Description de Mons'
           ),
           array(  'name' => 'Portsmouth', 'slug' => 'portshmouth',
               'east' => 50, 'north' => -2, 'west' => 51, 'south' => -1,
               'url' => 'mons.be', 'desc' => 'Description de Mons'
           ),
           array(  'name' => 'Moddsles;cs', 'slug' => 'moddslescs',
               'east' => 22.1, 'north' => 22.1, 'west' => 22.0, 'south' => 22.0,
               'url' => 'mdfqns.be', 'desc' => 'Description de Mfdqons'
           ),
       );
   }

   public function setContainer(ContainerInterface $container = null) {
      $this->container = $container;
   }
}

