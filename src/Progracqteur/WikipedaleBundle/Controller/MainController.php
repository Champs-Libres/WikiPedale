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

namespace Progracqteur\WikipedaleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Progracqteur\WikipedaleBundle\Entity\Management\Group;
use Symfony\Component\HttpFoundation\Request;

/**
 * This is the main controller that contains the only webpages of the 
 * application (except for the admin part).
 * 
 * @author Champs-Libres COOP
 */
class MainController extends Controller
{
    /**
     * Display the 'about' webpage.
     */
    public function aboutAction()
    {
        return $this->render('ProgracqteurWikipedaleBundle:Main:about.html.twig');
    }
    
    /**
     * Display the 'homepage' webpage. This is the main page of the
     * application : the webpage is updated (in function of the user 
     * interaction) with JSON request.
     * @param $request The request
     * @todo cachable query
     */
    public function homepageAction(Request $request)
    {
        $selectedReportId = $request->get('id', null);
        $selectedReport = null;
        $selectedZone = null;
        $session = $this->getRequest()->getSession();

        $em = $this->getDoctrine()->getManager();
        if ($selectedReportId) {
            $selectedReport = $em->getRepository('ProgracqteurWikipedaleBundle:Model\Report')
                ->find($selectedReportId);
            
            if ($selectedReport === null ||  !$selectedReport->isAccepted()) {
                throw $this->createNotFoundException('errors.404.report.not_found');
            }
            
            $selectedZone = $selectedReport->getModerator()->getZone();
            $session->set('selectedZoneId', $selectedZone->getId());
        }
        
        $enableZones = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Zone')->findAllWithModerator();

        //retrieve categories depending on user's right
        $terms_allowed = ' ';
        $terms_allowed_array = array();
        $iTerm = 0;
        foreach ($this->get('service_container')->getParameter('report_types') 
                as $target => $array) {
            //TODO extendds to other transports
            if ($target === 'bike') {
                foreach ($array["terms"] as $term) {
                    if ($this->get('security.context')->isGranted(
                            $term['mayAddToReport'])){
                        if ($iTerm > 0) {
                            $terms_allowed .= ', ';
                        }
                        $terms_allowed .= "'".$term['key']."'";
                        $terms_allowed_array[] = $term['key'];
                        $iTerm ++;
                    }   
                }
            }
        }
        
        $terms_allowed .= ' ';
        
        $categoriesQuery = sprintf('SELECT c from
            ProgracqteurWikipedaleBundle:Model\Category c 
            WHERE  c.used = true AND c.parent is null AND c.term IN (%s)
            ORDER BY c.order, c.label', $terms_allowed);
        
        $categories = $em->createQuery($categoriesQuery)->getResult();
        //@todo cachable query
        
        $reportTypes = $em->getRepository('ProgracqteurWikipedaleBundle:Model\Report\ReportType')
            ->findAll();
        //@todo cachable query
        
        if (!$selectedZone && $session->get('selectedZoneId') !== null) {
            $selectedZoneId = $session->get('selectedZoneId');
            $selectedZone = $em
                ->getRepository('ProgracqteurWikipedaleBundle:Management\Zone')
                ->find($selectedZoneId);

            $managers = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Group')
                ->findBy(array('type' => Group::TYPE_MANAGER, 'zone' => $selectedZone));
        } else {
            $managers = array();
        }

        $moderators = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Group')
            ->findByType(Group::TYPE_MODERATOR);
        
        $paramsToView = array(
            'mainCities' => $enableZones, 
            'categories' => $categories,
            'reportTypes' => $reportTypes,
            'managers' => $managers,
            'moderators' => $moderators,
            'terms_allowed' => $terms_allowed_array
        );

        if ($selectedZone) {
            $paramsToView['selectedZone'] = $selectedZone;
            $paramsToView['selectedZoneDisplayType'] = $session->get('selectedZoneDisplayType');
        }

        if ($selectedReportId) {
            $paramsToView['selectedReportId'] = $selectedReportId;
            $paramsToView['selectedReport'] = $selectedReport;
        }
        
        return $this->render(
            'ProgracqteurWikipedaleBundle:Main:homepage.html.twig', 
            $paramsToView);
    }
}
