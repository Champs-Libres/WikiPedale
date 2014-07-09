<?php

namespace Progracqteur\WikipedaleBundle\Tests\Resources\Services\Notification;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Progracqteur\WikipedaleBundle\Entity\Management\Notification\PendingNotification;
use Progracqteur\WikipedaleBundle\Entity\Management\User;
use Progracqteur\WikipedaleBundle\Entity\Model\Report;

/**
 * Unit test for Services\Notification\ToTextMailSenderService
 */
class ToTextMailSenderServiceTest extends WebTestCase{
   private static $container;
   private static $ttss; //progracqteur.wikipedale.notification.to_text.mail Service
   private static $em; // doctrine.orm.entity_manager Service
   
   public static function setUpBeforeClass()
   {
      $kernel = static::createKernel();
      $kernel->boot();
      static::$container = $kernel->getContainer();
      static::$ttss = ToTextMailSenderServiceTest::$container->get('progracqteur.wikipedale.notification.to_text.mail');
      static::$em = ToTextMailSenderServiceTest::$container->get('doctrine.orm.entity_manager');
   }

   /**
    * Test if the generation of the description the following information :
    *  - $report->getDescription()
    *  - $report->getLabel()
    *  - $report->getCreator()->getLabel()
    *  - $report->getCategory()->getLabel()
    *  - $report->getCreateDate()->format(static::$container->getParameter('date_format'))
    *  - $report->getId()
    */
   public function testAddReportPresentation()
   {
      $report = Report::randomGenerate(array('category' => 'RANDOM'));
      static::$em->persist($report); //to get an id and a creation date

      $reportPresentation = static::$ttss->addReportPresentation($report); // the description of the report

      $this->assertTrue(strpos($reportPresentation, $report->getDescription()) !== False);
      $this->assertTrue(strpos($reportPresentation, $report->getLabel()) !== False);
      $this->assertTrue(strpos($reportPresentation, $report->getCreator()->getLabel()) !== False);
      $this->assertTrue(strpos($reportPresentation, $report->getCategory()->getLabel()) !== False);
      $this->assertTrue(strpos($reportPresentation, $report->getCreateDate()->format(static::$container->getParameter('date_format'))) !== False);
      $this->assertTrue(strpos($reportPresentation, ((string)$report->getId())) !== False);

      static::$em->remove($report);
   }


   public function __testCreateReportText($userNameArray)
   {
      $category = static::$em->getRepository("ProgracqteurWikipedaleBundle:Model\\Category")->findOneByTerm('short');
      echo $category->getLabel();

      $report = Report::randomGenerate(array('category' => $category));
      static::$em->persist($report); //to get an id and a creation date
      static::$em->persist($report->getCreator());
      static::$em->flush();
      $reportId = $report->getId();

      $reportTrackingCreationArray = static::$em->getRepository("ProgracqteurWikipedaleBundle:Model\\Report\\ReportTracking")->findByReport($report);
      $this->assertTrue(sizeof($reportTrackingCreationArray) === 1);
      $reportTrackingCreation = $reportTrackingCreationArray[0];

      $pendingNotificationCreationArray = static::$em->getRepository("ProgracqteurWikipedaleBundle:Management\\Notification\\PendingNotification")->findByReportTracking($reportTrackingCreation);
   
      foreach ($userNameArray as $userName) {
         echo $userName;
         $user = static::$em->getRepository("ProgracqteurWikipedaleBundle:Management\\User")->findOneByUsername($userName);
         echo $user;
         echo '----------\n';
         echo static::$ttss->transformToText($pendingNotificationCreationArray, $user); 
      }
   }


   /**
    *
    */
   public function testTransformToText()
   {
      $this->
      __testCreateReportText(array('manager','moderateur','user'));
      // Tester les owner suivantes 
      // creation utilisateur Manager (le créer dans fixtures : manager, mdp manager) 
      // creation utilisateur Virtuel  (le créer dans fixtures : virtuel, mdp virtuel) 
      // creation utilisateur normal (le créer )
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