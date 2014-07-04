<?php

namespace Progracqteur\WikipedaleBundle\Tests\Resources\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Description of SlugControllerTest
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class SlugServiceTest extends WebTestCase{
    /**
     *
     * @var Progracqteur\WikipedaleBundle\Resources\Services\SlugService 
     */
    private static $slugService;
   
    public static function setUpBeforeClass()
    {
       $kernel = static::createKernel();
       $kernel->boot();
       static::$slugService = $kernel->getContainer()->get('progracqteur.wikipedale.slug');
    }
    
    public function testSpace()
    {
        $this->general('test test', 'test-test');
    }
    
    public function testAccent()
    {
        $this->general('testétest', 'testetest');
    }
    
    public function testTrim()
    {
        $this->general(' test ', 'test');
    }
    
    public function testA()
    {
        $this->general('àâäá', 'aaaa');
    }
    
    public function testU()
    {
        $this->general('uùüû', 'uuuu');
    }
    
    public function testTiret()
    {
        $this->general('Braine-le-chateau', 'braine-le-chateau');
    }
    
    public function testApostrophe()
    {
        $this->general('Braine l\'alleud', 'braine-lalleud');
    }
    
    private function general($string, $expectedSlug)
    {
        $result = static::$slugService->slug($string);
        $this->assertEquals($expectedSlug, $result);
    }
}