<?php

namespace Progracqteur\WikipedaleBundle\Tests\Services\Notification;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Progracqteur\WikipedaleBundle\Entity\Model\Report;
use Progracqteur\WikipedaleBundle\Tests\Controller\ReportControllerTest;

/**
 * Unit test for Services\Notification\ToTextMailSenderService
 */
class ToTextMailSenderServiceTest extends WebTestCase{

   /**
    *
    */
   public function testTransformToText() {
      $r = Report::randomGenerate();

      //TODO
   }
}