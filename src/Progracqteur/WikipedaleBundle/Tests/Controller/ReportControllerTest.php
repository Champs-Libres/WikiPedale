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

use Progracqteur\WikipedaleBundle\Entity\Management\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * Tests the Report controller class
 *
 * @author Champs-Libres coop
 */
class ReportControllerTest extends WebTestCase
{
    const MODERATOR_STATUS_MOD = 1;

    /**
     * Create the JSON string to send to the serveur for modifying a report
     *
     * @param Integer $reportId The id of the report to modify
     * @param const $param The constant identifying the parameter to change
     * @param mixed $newValue The new value of the selected parameter
     * @return String The JSON string to send to the serveur for modifying the report
     */
    private function jsonModificationReport($reportId, $param, $newValue)
    {
        $report = [
            'entity' => 'report',
            'id' => $reportId,
        ];

        if($param === ReportControllerTest::MODERATOR_STATUS_MOD) {
            $report['statuses'] = [
                [
                    't' => 'cem',
                    'v' => $newValue
                ]
            ];
        }

        return json_encode($report);
    }

    /**
     * Modify with the API a report and test if the modification is effective
     *
     * @param $client The client
     * @param Integer $reportId The id of the report to modify
     * @param const $param The constant identifying the parameter to change
     * @param mixed $newValue The new value of the selected parameter
     */
    private function modifyReportAndCheckIt($client, $reportId, $param, $newValue)
    {      
        $client->request(
            'POST', '/report/change.json',
            array(
                'entity' => 
                    $this->jsonModificationReport($reportId, $param, $newValue)
            )
        );
        $this->modifyReport($client, $reportId, $param, $newValue);
        $this->assertTrue($client->getResponse()->isRedirection(), "the response must be a redirection");
        $client->followRedirect();
        $response = json_decode($client->getResponse()->getContent(), true);

        if($param === ReportControllerTest::MODERATOR_STATUS_MOD) {
            $this->assertEquals($newValue, $response['results'][0]['statuses'][0]['v']);
        }
    }

    /**
     * Modify the moderator status and check if the modification is effective
     */
    public function testChangeModeratorStatus()
    {
        $client = static::CreateClient(array(), array(
            'PHP_AUTH_USER' => 'moderator',
            'PHP_AUTH_PW'   => 'moderator',
        ));

        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');    
        $monsZone = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Zone')
            ->findOneBy(array('slug' => 'mons'));
        $monsModeratorGroup = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Group')
            ->findOneBy(['zone' => $monsZone, 'type' => Group::TYPE_MODERATOR]);
        $reports = $em->getRepository('ProgracqteurWikipedaleBundle:Model\Report')
            ->findBy(['moderator' => $monsModeratorGroup]);
        $aRandomReport = $reports[array_rand($reports)];
        $moderatorStatus = $aRandomReport->getStatusByType('cem', '0');
        $newStatus = $moderatorStatus + 1;
        if($newStatus > 3) {
            $newStatus --;
        }

        $this->modifyReportAndCheckIt(
            $client, $aRandomReport->getId(), 
            ReportControllerTest::MODERATOR_STATUS_MOD, $newStatus);
    }
}