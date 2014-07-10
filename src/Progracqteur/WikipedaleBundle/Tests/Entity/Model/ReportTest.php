<?php

namespace Progracqteur\WikipedaleBundle\Tests\Entity\Model;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Progracqteur\WikipedaleBundle\Entity\Model\Report;

/**
 * Test for Report
 *
 * @author julien
 */
class ReportTest extends WebTestCase
{
   private static $container;

   public static function setUpBeforeClass()
   {
      $kernel = static::createKernel();
      $kernel->boot();
      static::$container = $kernel->getContainer();
   }

   /**
    * Tests if a report well generated pass the validation test.
    */
   public function testValidationCorrect() 
   {
      $r = Report::randomGenerate(array('category' => 'RANDOM', 'creator' => 'RANDOM_UNREGISTERED'));
      $validator =  static::$container->get('validator');
      $errors = $validator->validate($r);
      $this->assertEquals(0, $errors->count());
   }

   /**
    * Tests if a report not-well generated fails the validation test (no user).
    */
   public function testValidationNoUser()
   {
      $r = Report::randomGenerate(array('category' => 'RANDOM'));
      $validator = static::$container->get('validator');
      $errors = $validator->validate($r);
      $this->assertEquals(1, $errors->count());
   }

   /**
    * Tests if a report with a very long description fails the validation test.
    */
   public function testValidationDescriptionMoreThan10000()
   {
      $r = Report::randomGenerate(array('category' => 'RANDOM', 'creator' => 'RANDOM_UNREGISTERED'));
      
      //ce texte contient 463 caractères
      $string = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent volutpat consectetur ligula, venenatis tincidunt dui commodo et. Curabitur eleifend justo dolor. Maecenas vel ipsum sit amet odio vehicula commodo eget sit amet sem. Curabitur sagittis pulvinar mauris. Fusce ut augue vitae nulla semper malesuada eu vel massa. Suspendisse vel justo mauris. Sed mattis ipsum sed mi dapibus vestibulum. Cras vitae lorem eget tortor fringilla ornare ut vel sapien. ";
       
      $s = $string;
      for ($a = 0; $a < 10000; $a = $a+463) {
         $s .= $string;
      }
      
      $r->setDescription($s);
      
      $validator = static::$container->get('validator');
      $errors = $validator->validate($r);
      $this->assertEquals(1, $errors->count());
   }
}