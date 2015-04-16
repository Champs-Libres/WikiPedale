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
use Progracqteur\WikipedaleBundle\Resources\Container\NormalizedResponse;
use Symfony\Component\HttpFoundation\Response;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

/**
 * Controller of the zone
 *
 * @author Champs-Libres COOP
 */
class ZoneController extends Controller
{
    /**
     * Return the list of the zones (enable or not) for the administration
     *
     * @return Response The response instance
     */
    public function indexAdminAction()
    {
        $em = $this->getDoctrine()->getManager();

        $zones = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Zone')->findAll();
        
        return $this->render(
            'ProgracqteurWikipedaleBundle:Management/Zone:index.html.twig',
            array('zones' => $zones));
    }
    
    /**
     * Return a JSON string that describe all the (moderated) zones.
     * 
     * @return Response The JSON string
     */
    public function getAllAction()
    {
        $em = $this->getDoctrine()->getManager();
        
        $zones = $em
            ->getRepository('ProgracqteurWikipedaleBundle:Management\Zone')
            ->findAllWithModerator();

        $normalizer = $this->get('progracqteurWikipedaleSerializer');
        $rep = new NormalizedResponse($zones);
        $ret = $normalizer->serialize($rep, 'json');
        return new Response($ret);
    }
    
    /**
     * Return a JSON string that describe a given zone
     * 
     * @param strubg $zoneSlug The slug of the asked zone
     * @return Response The JSON string
     */
    public function getAction($zoneSlug)
    {
        $em = $this->getDoctrine()->getManager();
        
        $zone = $em
            ->getRepository('ProgracqteurWikipedaleBundle:Management\Zone')
            ->findOneBy(array('slug' => $zoneSlug));

        $normalizer = $this->get('progracqteurWikipedaleSerializer');
        $rep = new NormalizedResponse($zone);
        $ret = $normalizer->serialize($rep, 'json');
        return new Response($ret);
    }
    
 
    /**
     * Return a JSON string that describe all the zones covering a given
     * point.
     *
     * @param type $lon The longitude of the point
     * @param type $lat The latitude of the point
     * @return Response The JSON string
     */
    public function getCoveringPointAction($lon,$lat)
    {
        $em = $this->getDoctrine()->getManager();

        $point  = new Point($lon, $lat);
        $stringPoint = 'POINT('. $point .')';
        
        $zones = $em
            ->getRepository('ProgracqteurWikipedaleBundle:Management\Zone')
            ->findAllWithModeratorCovering($stringPoint);

        $normalizer = $this->get('progracqteurWikipedaleSerializer');
        $rep = new NormalizedResponse($zones);
        $ret = $normalizer->serialize($rep, 'json');
        return new Response($ret);
    }
    
    /**
     * Set a zone as selected for the user session with 'zoom' as type 
     * of selection.
     * 
     * @param type $zoneSlug The slug of the selected zone
     * @return Response The index page
     * @throws NotFoundHttpException when the variable $zoneSlug does not refer
     * to a known zone
     */
    public function selectZoneForZoomAction($zoneSlug)
    {
        return $this->selectZoneAction($zoneSlug, 'zoom');
    }
    
    /**
     * Set a zone as selected for the user session with 'minisite' as type 
     * of selection.
     * 
     * @param type $zoneSlug The slug of the selected zone
     * @return Response The index page
     * @throws NotFoundHttpException when the variable $zoneSlug does not refer
     * to a known zone
     */
    public function selectZoneForMinisiteAction($zoneSlug)
    {
        return $this->selectZoneAction($zoneSlug, 'minisite');
    }
    
    /**
     * Set a zone as selected for the user session.
     * 
     * Two selection are allowed :
     * - zoom : zooming on the zone 
     * - minisite : display only the selected zone
     * 
     * @param type $zoneSlug The slug of the selected zone
     * @param type $displayType The type of the selection (zoom or minisite)
     * @return Response The index page
     * @throws NotFoundHttpException when the variable $zoneSlug does not refer
     * to a known zone
     */
    public function selectZoneAction($zoneSlug, $displayType)
    {
        $zoneSluged = $this->get('progracqteur.wikipedale.slug')->slug($zoneSlug);

        $em = $this->getDoctrine()->getManager();
        $zones = $em->createQuery("SELECT z
            from ProgracqteurWikipedaleBundle:Management\Zone z
            where z.slug = :slug")
            ->setParameter('slug', $zoneSluged)
            ->getResult();
        
        if(sizeof($zones) == 0 || ! $zones[0]) {
            throw $this->createNotFoundException("La ville demandée '$zone' n'a pas été trouvée");
        }
        
        $selectedZone = $zones[0];
        $session = $this->getRequest()->getSession();
        $session->set('selectedZoneId', $selectedZone->getId());
        $session->set('selectedZoneDisplayType', $displayType);
        
        return $this->redirect($this->generateUrl('wikipedale_homepage'));
    }
    
    /**
     * Reset the zone selected.
     *
     * @return Response The index page.
     */
    public function resetZoneSelectionAction()
    {
        $session = $this->getRequest()->getSession();
        $session->remove('selectedZoneId');
        $url = $this->generateUrl('wikipedale_homepage');
        return $this->redirect($url);
    }
}