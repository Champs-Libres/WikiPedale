<?php

namespace Progracqteur\WikipedaleBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Progracqteur\WikipedaleBundle\Entity\Model\Report;

//require Kernel
require_once __DIR__.'/../../../../../app/AppKernel.php';

/**
 * Description of ReportControllerTest
 *
 * @author julien
 */
class ReportControllerTest extends WebTestCase
{
    private $_kernel;  

    /**
     * Tests if a report well generated pass the validation test.
     */
    public function testValidationCorrect() 
    {
        $r = Report::randomGenerate();
        
        $validator = $this->getValidator();
        
        $errors = $validator->validate($r);
        
        $this->assertEquals(0, $errors->count());
    }

    /**
     * Tests if a report not-well generated fails the validation test.
     */
    public function testValidationUser()
    {
        $r = Report::randomGenerate(array('noUser' => true));
        
        $validator = $this->getValidator();
        
        $errors = $validator->validate($r);
        
        $this->assertEquals(1, $errors->count());
    }
    
    /**
     * Tests if a report with a very long description fails the validation test.
     */
    public function testValidationDescriptionMoreThan10000()
    {
        $r = Report::randomGenerate();
        
        //ce texte contient 463 caractères
        $string = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent volutpat consectetur ligula, venenatis tincidunt dui commodo et. Curabitur eleifend justo dolor. Maecenas vel ipsum sit amet odio vehicula commodo eget sit amet sem. Curabitur sagittis pulvinar mauris. Fusce ut augue vitae nulla semper malesuada eu vel massa. Suspendisse vel justo mauris. Sed mattis ipsum sed mi dapibus vestibulum. Cras vitae lorem eget tortor fringilla ornare ut vel sapien. ";
        
        $s = $string;
        for ($a = 0; $a < 10000; $a = $a+463) {
            $s .= $string;
        }
        
        $r->setDescription($s);
        
        $validator = $this->getValidator();
        
        $errors = $validator->validate($r);
        
        $this->assertEquals(1, $errors->count());
    }
    
    public function getValidator()
    {
        if ($this->_kernel === null) {
            $this->_kernel = new \AppKernel('dev', true);
            $this->_kernel->boot(); 
        }
        
        return $this->_kernel->getContainer()->get('validator');        

    }
}

