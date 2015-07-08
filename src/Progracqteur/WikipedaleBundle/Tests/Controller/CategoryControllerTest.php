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

class CategoryControllerTest extends WebTestCase
{
    public function testTODO()
    {
        $this->markTestIncomplete('No test for CategroyController');
    }
    
    /*
    public function testCompleteScenario()
    {
        // Create a new client to browse the application
        $client = static::createClient();

        // Create a new entry in the database
        $crawler = $client->request('GET', '/admin/category/');
        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
        $crawler = $client->click($crawler->selectLink('Create a new entry')->link());

        // Fill in the form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'category[field_name]'  => 'Test',
            // ... other fields to fill
        ));

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check data in the show view
        $this->assertTrue($crawler->filter('td:contains("Test")')->count() > 0);

        // Edit the entity
        $crawler = $client->click($crawler->selectLink('Edit')->link());

        $form = $crawler->selectButton('Edit')->form(array(
            'category[field_name]'  => 'Foo',
            // ... other fields to fill
        ));

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check the element contains an attribute with value equals "Foo"
        $this->assertTrue($crawler->filter('[value="Foo"]')->count() > 0);

        // Delete the entity
        $client->submit($crawler->selectButton('Delete')->form());
        $crawler = $client->followRedirect();

        // Check the entity has been delete on the list
        $this->assertNotRegExp('/Foo/', $client->getResponse()->getContent());
    }
    */
}