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

ini_set('memory_limit', '256M');

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Progracqteur\WikipedaleBundle\Entity\Management\Group;

class ReportModeratorTest extends WebTestCase
{
    public static function setUpBeforeClass()
    {
        //static::createKernel();
        //static::$kernel->boot();
    }
    
    public function testModeratorIsPresent()
    {
        $client = static::createClient();
        
        $client->request('GET', '/report/list/bybbox.json?bbox=50.48148610448666%2C4.02755931274414%2C50.42683811382025%2C3.874780687255859');
        $response = json_decode($client->getResponse()->getContent());
        
        $this->assertObjectHasAttribute('moderator', $response->results[0],
            "Check that the moderator key is present");
    }
    
    public function testUpdateModerator()
    {
        //prepare
        $client = static::CreateClient(array(), array(
            'PHP_AUTH_USER' => 'moderator',
            'PHP_AUTH_PW'   => 'moderator',
        ));
        $client->request('GET', '/report/list/bybbox.json?bbox=50.48148610448666%2C4.02755931274414%2C50.42683811382025%2C3.874780687255859');
        
        $response = json_decode($client->getResponse()->getContent(), true);
        //get a random object
        $report = $response['results'][array_rand($response['results'])];
        //get a moderator (not mons)
        $moderatorId = $this->getModerator($report['moderator']['id'])->getId(); 
        
        //act 
        
        //check a new moderator
        $report['moderator']['id'] = $moderatorId;
        $client->request('POST', '/report/change.json', array('entity'
            => json_encode($report)
        ));
        
        //assert
        $this->assertTrue($client->getResponse()->isRedirection(), "the response must be a redirection");
        $client->followRedirect();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($moderatorId, $response['results'][0]['moderator']['id']);
    }
    
    public function testUpdateModeratorWithoutModeratorRight() 
    {
        //prepare
        $client = static::CreateClient(array(), array(
            'PHP_AUTH_USER' => 'manager',
            'PHP_AUTH_PW'   => 'manager',
        ));
        $client->request('GET', '/report/list/bybbox.json?bbox=50.48148610448666%2C4.02755931274414%2C50.42683811382025%2C3.874780687255859');
        $response = json_decode($client->getResponse()->getContent(), true);
        //get a random object
        $report = $response['results'][array_rand($response['results'])];
        //get a moderator (not mons)
        $moderatorId = $this->getModerator($report['moderator']['id'])->getId();
        
        //act
        
        //check a new moderator
        $report['moderator']['id'] = $moderatorId;
        $client->request('POST', '/report/change.json', array('entity'
            => json_encode($report)
        ));
        
        //assert
        $this->assertFalse($client->getResponse()->isRedirection(), "the response must be a redirection");
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
    
    private function getModerator($notThisId)
    {
        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        
        $moderators = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Group')
            ->findBy(array('type' => Group::TYPE_MODERATOR));
        
        $moderator = $moderators[array_rand($moderators)];
        
        if ($moderator->getId() === $notThisId) {
            return $this->getModerator($notThisId);
        } else {
            return $moderator;
        }
    }
}