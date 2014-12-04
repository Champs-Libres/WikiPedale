<?php

namespace Progracqteur\WikipedaleBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Progracqteur\WikipedaleBundle\Entity\Management\User;
use Progracqteur\WikipedaleBundle\Entity\Management\Group;
use Progracqteur\WikipedaleBundle\Entity\Management\Notation;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
   /**
    *
    * @var Symfony\Component\DependencyInjection\ContainerInterface 
    */
   private $container;
    
   public function getOrder()
   {
      return 300;
   }
    
   public function load(ObjectManager $om)
   {  
      $userManager = $this->container->get('fos_user.user_manager');

      $city = $om->getRepository('ProgracqteurWikipedaleBundle:Management\Zone')
         ->findOneBySlug('mons');

      echo "CREATE 'admin' USER (pwd 'admin')\n";
      $admin = new User(array("label" => "Robert Delieu", "username" => "admin",
         "password" => "admin", "email" => "RANDOM", "enable" => true, "phonenumber" => "RANDOM"));
      $admin->addRole(User::ROLE_ADMIN);
      $userManager->updateUser($admin);

      echo "CREATE 'user' USER (pwd 'user')\n";
      $user = new User(array("label" => "Arnaud Bobo", "username" => "user",
         "password" => "user", "email" => "RANDOM", "enable" => true, "phonenumber" => "RANDOM"));
      $this->addReference('user', $user);
      $userManager->updateUser($user);


      
      echo "CREATE 'Moderator (CeM) Mons' Group\n";
      $cemGroup = new Group('Moderator (CeM) Mons', array());
      $cemGroup
         ->setType(Group::TYPE_MODERATOR)
         ->setNotation(
            $om->getRepository('ProgracqteurWikipedaleBundle:Management\Notation')->find('cem'))
         ->setZone($city);
      $om->persist($cemGroup);
      $this->addReference('cemgroup', $cemGroup);
      
      //we need more than one moderator roup for test working...
      for ($i = 0; $i < 10; $i++) {
          echo "CREATE 'Moderator (CeM) $i' Group\n";
          $cemGroup = new Group('Moderator (CeM) '.$i, array());
          $cemGroup
          ->setType(Group::TYPE_MODERATOR)
          ->setNotation(
              $om->getRepository('ProgracqteurWikipedaleBundle:Management\Notation')->find('cem'))
           ->setZone($this->getUniqueRandomZone())
           ;
          //
          
          $om->persist($cemGroup);
          $this->addReference('cemgroup'.$i, $cemGroup);
      }

      echo "CREATE 'moderator' USER (pwd 'moderator')\n";
      $moderator = new User(array("label" => "Monsieur Vélo Mons", "username" => "moderator",
         "password" => "moderator", "email" => "RANDOM", "enable" => true, "phonenumber" => "RANDOM"));
      $moderator->addGroup($cemGroup);
      $userManager->updateUser($moderator);
      $this->addReference('cem', $moderator);
        
      echo "CREATE 'Gestionnaire de voirie communal Mons' Group\n";
      $manGroup = new Group('Gestionnaire de voirie communal', array());
      $manGroup
         ->setType(Group::TYPE_MANAGER)
         ->setNotation(
            $om->getRepository('ProgracqteurWikipedaleBundle:Management\Notation')->find('cem'))
         ->setZone($city);
      $om->persist($manGroup);
      $this->addReference('manager_mons', $manGroup);

      echo "CREATE 'manager' USER (pwd 'manager')\n";
      $manager = new User(array("label" => "Monsieur Travaux Mons", "username" => "manager",
         "password" => "manager", "email" => "RANDOM", "enable" => true, "phonenumber" => "RANDOM"));
      $manager->addGroup($manGroup);
      $userManager->updateUser($manager);
      $this->addReference('monsieur_velo', $manager);

      echo "CREATE 'Gestionnaire de voirie régional' Group\n";
      $monsspw = $om->getRepository('ProgracqteurWikipedaleBundle:Management\Zone')
         ->findOneBySlug('mons-spw');

      $manGroup = new Group('Gestionnaire de voirie régional', array());
      $manGroup
         ->setType(Group::TYPE_MANAGER)
         ->setNotation(
            $om->getRepository('ProgracqteurWikipedaleBundle:Management\Notation')->find('cem'))
         ->setZone($city);
      $om->persist($manGroup);
      $this->addReference('manager_mons_spw', $manGroup);

      echo "CREATE 'manager_spw' USER (pwd 'manager_spw')\n";
      $manager_spw = new User(array("label" => "Monsieur Travaux Region Mons", "username" => "manager_spw",
         "password" => "manager_spw", "email" => "RANDOM", "enable" => true, "phonenumber" => "RANDOM"));
      $manager_spw->addGroup($manGroup);
      $userManager->updateUser($manager_spw);
      $this->addReference('monsieur_travaux', $manager_spw);

      $om->flush();   
   }
    
   public function setContainer(ContainerInterface $container = null) {
      $this->container = $container;
   }
   
   /**
    * cache zone for the function getUniqueRandomZone
    */
   private $cacheUniqueRandomZone = null;
   
   /**
    * return an unique random zone
    * a zone may be returned only once
    */
   public function getUniqueRandomZone()
   {
       if ($this->cacheUniqueRandomZone === NULL) {
           $this->cacheUniqueRandomZone = $this->container->get('doctrine.orm.entity_manager')
                ->getRepository('ProgracqteurWikipedaleBundle:Management\Zone')
                ->findAll();
           
           //remove mons
           foreach ($this->cacheUniqueRandomZone as $key => $zone) {
               if ($zone->getSlug() === 'mons') {
                   unset($this->cacheUniqueRandomZone[$key]);
               }
           }
       }
       
       $randomKey = array_rand($this->cacheUniqueRandomZone);
       $zone = $this->cacheUniqueRandomZone[$randomKey];
       unset($this->cacheUniqueRandomZone[$randomKey]);
       return $zone;
   }
}