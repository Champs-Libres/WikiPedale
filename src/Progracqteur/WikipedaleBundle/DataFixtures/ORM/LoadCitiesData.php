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

      $r = $conn->executeUpdate("INSERT INTO zones 
          (id, name, slug, codeprovince, polygon, center, type, url, description)
          VALUES (1, 'Mons', 'mons', 7000, 
              ST_GeomFromText('POLYGON((3.7 50.55, 4.3 50.55, 4.3 50.4, 3.7 50.4, 3.7 50.55))',4326), 
              ST_GeomFromText('POINT(3.95117 50.45417)',4326), '".Zone::TYPE_CITY."', 'mons.be', 'Description de Mons');");
      
      echo "$r slug updated in the database \n";
   }

   public function setContainer(ContainerInterface $container = null) {
      $this->container = $container;
   }
}

