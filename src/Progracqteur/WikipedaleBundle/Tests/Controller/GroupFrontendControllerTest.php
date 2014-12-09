<?php
namespace Progracqteur\WikipedaleBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GroupFrontendControllerTest extends WebTestCase
{
    public function testListByTypeActionModerators()
    {
        $client = static::createClient();
        
        $crawler = $client->request('GET', '/groups/bytype/moderator.json');
        
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        
        //decode the response
        $response = json_decode($client->getResponse()->getContent(), true);
        $moderators = $response['results'];
        
        //test response
        $this->assertGreaterThan(0, count($moderators));
        
    }
    
    public function testListByTypeActionManagers()
    {
        $client = static::createClient();
        
        $crawler = $client->request('GET', '/groups/bytype/manager.json');
        
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        
        //decode the response
        $response = json_decode($client->getResponse()->getContent(), true);
        $moderators = $response['results'];
        
        //test response
        $this->assertGreaterThan(0, count($moderators));
    }
    
    public function testListByTypeActionNotation()
    {
        $client = static::createClient();
        
        $crawler = $client->request('GET', '/groups/bytype/notation.json');
        
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        
        //decode the response
        $response = json_decode($client->getResponse()->getContent(), true);
        $moderators = $response['results'];
        
        //test response 
        //$this->assertGreaterThan(0, count($moderators)); TODO disable until we have notation with fixtures
    }
    
    public function testListByTypeActionUnknowType()
    {
        $client = static::createClient();
        
        $crawler = $client->request('GET', '/groups/bytype/foo.json');
        
        $this->assertTrue($client->getResponse()->isNotFound());
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }
    
    public function testListByTypeActionWrongType()
    {
        $client = static::createClient();
    
        $crawler = $client->request('GET', '/groups/bytype/moderator.html');
    
        $this->assertEquals(
            406,
            $client->getResponse()->getStatusCode()
        );
        
    }
    
}