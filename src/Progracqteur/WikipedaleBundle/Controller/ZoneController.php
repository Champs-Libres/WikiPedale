<?php

namespace Progracqteur\WikipedaleBundle\Controller;

use Progracqteur\WikipedaleBundle\Resources\Geo\Point;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Progracqteur\WikipedaleBundle\Resources\Container\NormalizedResponse;
use Symfony\Component\HttpFoundation\Response;

class ZoneController extends Controller
{
    public function getCoveringPointAction($lon,$lat)
    {
        $type = 'city';

        $em = $this->getDoctrine()->getManager();

        $center = new Point($lon, $lat);
        $stringCenter = $this->get('progracqteur.wikipedale.geoservice')->toString($center);

        $zone = $em->createQuery('select z
            from ProgracqteurWikipedaleBundle:Management\Zone z
            where COVERS(z.polygon, :center) = true and z.type = :type')
            ->setParameter('center', $stringCenter)
            ->setParameter('type', $type)
            ->getResult();

        $normalizer = $this->get('progracqteurWikipedaleSerializer');
        $rep = new NormalizedResponse($zone);
        $ret = $normalizer->serialize($rep, 'json');
        return new Response($ret);
    }
}