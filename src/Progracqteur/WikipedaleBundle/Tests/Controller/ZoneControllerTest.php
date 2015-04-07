<?php

namespace Progracqteur\WikipedaleBundle\Tests\Controller;

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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Progracqteur\WikipedaleBundle\Entity\Management\Group;

/**
 * Test for the ZoneController
 */
class ZoneControllerTest extends WebTestCase
{    
    /**
     * Test if the getAll action (/zone/list.json) return a good number of zone.
     * 
     * @param integer $zonesNumber The number of zone that must be returned by
     * the getAll action (/zone/list.json).
     * @param String $message The message that explain the test expected number
     */
    private function zoneListCheck($zonesNumber, $message)
    {
        $client = static::createClient();
        $client->request('GET', '/zone/list.json');
        $jsonResponse = $client->getResponse()->getContent();
        
        $arrayResponse = json_decode($jsonResponse, true);
        $this->assertFalse($arrayResponse['query']['error'], 'The response '
            . 'must not contains error.');
        
        $this->assertTrue($arrayResponse['query']['nb'] == $zonesNumber,
            "The number of zone returned must be $zonesNumber : $message");
    }
    
    /**
     * Test the reponse of the getAll action (/zone/list.json)
     * after loading fixtures.
     */
    public function testBasicGetAllAction()
    {
        $this->zoneListCheck(3, "Mons, Mons-ring and Namur");
    }
    
    /**
     * Test the reponse of the getAll action (/zone/list.json)
     * after adding a moderator group to a zone not moderated.
     */
    public function testAfterAddingAModeratorGroupGetAllAction()
    {
        $client = static::createClient();
        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $charleroiZone = $em
            ->getRepository('ProgracqteurWikipedaleBundle:Management\Zone')
            ->FindOneBy(array('name' => 'Charleroi'));
        $cemNotation = $em
            ->getRepository('ProgracqteurWikipedaleBundle:Management\Notation')
            ->Find('cem');
        $charleroiModeratorGroup = (new Group('Charleroi Moderator Group', []))
            ->setType(Group::TYPE_MODERATOR)
            ->setZone($charleroiZone)
            ->setNotation($cemNotation);
        
        $em->persist($charleroiModeratorGroup);
        $em->flush();
        $this->zoneListCheck(4, "Mons, Mons-ring Namur and Charleroi");
        
        $em->remove($charleroiModeratorGroup);
        $em->flush();
        $this->zoneListCheck(3, "Mons, Mons-ring and Namur");
    }
    
    /**
     * Test the reponse of the getCoveringPointAction action returns a good
     * number of zone for a point in Mons and a point with no zone.
     */
    public function testGetCoveringPointAction()
    {
        $this->zoneNumberCoveringPointCheck(0,0,0,'no zone at (0,0)');
        $this->zoneNumberCoveringPointCheck(3.94, 50.44, 1,
            'mons at (3.94, 50.44)');
    }
    
    /**
     * Test if the getCoveringPointAction returns a good number of zone for a
     * given point.
     * 
     * @param float $lon The longitude of the point
     * @param float $lat The latitude of the point
     * @param integer $expectedZonesNumber The expected number of zones
     * @param String $message The message that explain the expected zones number
     */
    private function zoneNumberCoveringPointCheck(
        $lon,$lat, $expectedZonesNumber, $message)
    {
        $client = static::createClient();
        $client->request('GET', "/zone/covers/point/$lon/$lat/json");
        $jsonResponse = $client->getResponse()->getContent();
        
        $arrayResponse = json_decode($jsonResponse, true);
        $this->assertFalse($arrayResponse['query']['error'], 'The response '
            . 'must not contains error.');

        $this->assertTrue($arrayResponse['query']['nb'] == $expectedZonesNumber,
            "The number of zone returned must be $expectedZonesNumber : "
            . "$message");
    }
}
