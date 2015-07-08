<?php

/*
 *  Uello is a reporting tool. This file is part of Uello.
 * 
 *  Copyright (C) 2015, Champs-Libres Cooperative SCRLFS,
 *  <http://www.champs-libres.coop>, <info@champs-libres.coop>
 * 
 *  Uello is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Uello is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Uello.  If not, see <http://www.gnu.org/licenses/>.
 */

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