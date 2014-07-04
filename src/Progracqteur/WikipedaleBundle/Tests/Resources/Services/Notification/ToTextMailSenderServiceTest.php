<?php

namespace Progracqteur\WikipedaleBundle\Tests\Resources\Services\Notification;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Progracqteur\WikipedaleBundle\Entity\Management\Notification\PendingNotification;
use Progracqteur\WikipedaleBundle\Entity\Management\User;

/**
 * Unit test for Services\Notification\ToTextMailSenderService
 */
class ToTextMailSenderServiceTest extends WebTestCase{
   private static $container;
   
   public static function setUpBeforeClass()
   {
      $kernel = static::createKernel();
      $kernel->boot();
      static::$container = $kernel->getContainer();
   }

   /**
    *
    */
   public function testTransformToText()
   {
      // Tester les owner suivantes 
      // creation utilisateur Manager (le créer dans fixtures : manager, mdp manager) 
      // creation utilisateur Virtuel  (le créer dans fixtures : virtuel, mdp virtuel) 
      // creation utiilisateur normal (le créer )
      // creation Moderateur (peut être identique au Manager)



      // setup before class ( get les users  )
      // setuf before (executer a chaque fois -> supprimer les notifs de la table - a voir si 
      // on veut utiliser les même notifs pour différents users  )
      // setud add depends ABC (qui  execute ABC avant le tester voulu )

      // vider les notifications ? (supprimer dans la table)

      // creer un report dans la zone (automatique) (TEST CREATION)
      // le sauvegarder (pour avoir son id)
      // puis regarder les pendingnotif (report -> changeset -> pendingnotif) 

      // vide les notifications 

      // modifier le  report dans la zone (automatique) (TEST MODIFICATION)
      // le sauvegarder (pour avoir son id)
      // puis regarder les pendingnotif (report -> changeset -> pendingnotif) 

      // vide les notifications 

      /*
      $n1 = new PendingNotification();
      $n2 = new PendingNotification();
      $u = new User();

      $ttss = $this->getContainer()->get('progracqteur.wikipedale.notification.to_text.mail');
      $em = $this->getContainer()->get('doctrine.orm.entity_manager');
      $admin = $em->getRepository('ProgracqteurWikipedaleBundle:Management\\User')->findOneByUsername('admin');

      echo($ttss->transformToText(array($n1,$n2), $admin));
      */
   }
}