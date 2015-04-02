<?php

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
}