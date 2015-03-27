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
     * @param type $zonesNumber The number of zone that must be returned by
     * the getAll action (/zone/list.json).
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
        $this->zoneListCheck(2, "Mons and Namur");
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
        $this->zoneListCheck(3, "Mons, Namur and Charleroi");
        
        $em->remove($charleroiModeratorGroup);
        $em->flush();
        $this->zoneListCheck(2, "Mons and Namur");
    }
    
    public function getCoveringPointAction()
    {
        
    }
}
