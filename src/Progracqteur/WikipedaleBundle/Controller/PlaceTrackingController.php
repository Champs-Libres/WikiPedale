<?php

namespace Progracqteur\WikipedaleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class PlaceTrackingController extends Controller {
    
    public function byCityAction($citySlug, $_format, Request $request)
    {
         $em = $this->getDoctrine()->getEntityManager();
        
        $citySlug = $this->get('progracqteur.wikipedale.slug')->slug($citySlug);
        
        if ($citySlug === null)
        {
            throw new \Exception('Renseigner une ville dans une variable \'city\' ');
        }
        
         $city = $em->getRepository('ProgracqteurWikipedaleBundle:Management\\City')
                ->findOneBy(array('slug' => $citySlug));
        
        if ($city === null)
        {
            throw $this->createNotFoundException("Aucune ville correspondant à $citySlug n'a pu être trouvée");
        }
        
        $max = $request->get('max', 20);
        if ($max > 100)
        {
            $response = new Response('limite du nombre de réponse dépassée. Maximum = 100');
            $response->setStatusCode(413);
            return $response;
        }
        
        $first = $request->get('first', 0);
        if ($first < 0)
        {
            $response = new Response('le paramètre first ne peut aps être négatif');
            $response->setStatusCode(400);
            return $response;
        }
        
        
        
        $tracks = $em->createQuery('SELECT p 
            from ProgracqteurWikipedaleBundle:Model\\Place\\PlaceTracking p 
               JOIN p.place pl 
            where covers(:polygon, pl.geom ) = true 
               and pl.accepted = true
            order by p.date DESC
            ')
                ->setParameter('polygon', $city->getPolygon())
                ->setFirstResult($first)
                ->setMaxResults($max)
                ->getResult();
        
        return new Response(count($tracks).$tracks[0]->getId());
    }
    
}
