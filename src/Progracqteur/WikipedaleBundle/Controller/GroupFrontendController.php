<?php
namespace Progracqteur\WikipedaleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Progracqteur\WikipedaleBundle\Resources\Container\NormalizedResponse;
use Progracqteur\WikipedaleBundle\Entity\Management\Group;

/**
 * 
 */
class GroupFrontendController extends Controller
{

    public function getGroupCoveringZoneAction($slugZone, $type, $_format, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $citySlug = $this->get('progracqteur.wikipedale.slug')->slug($slugZone);
        
        $zoneA = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Zone')
                ->findOneBy(array('slug' => $slugZone));
        
        if ($zoneA === null)
        {
            throw $this->createNotFoundException('slugZone does not match any zone');
        }
        
        $groups = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Group')
                ->getGroupsByTypeByCoverage($type, $zoneA->getPolygon());
        
        switch ($_format) 
        {
            case 'json' : 
                $serializer = $this->get('progracqteurWikipedaleSerializer');
        
                $nr = new NormalizedResponse();
                $nr->setResults($groups);
                
                return new Response($serializer->serialize($nr, 'json'));
            default:
                $a =  new Response('format invalid');
                $a->setStatusCode(400);
                return $a;
        }
        
        
    }
    
    public function listByTypeAction($type, $_format = 'json')
    {
        if ($_format !== 'json') {
            $response = new Response("The format $_format is not allowed");
            $response->setStatusCode(406);
            return $response;
        }
        
        if (! in_array(mb_strtoupper($type), array(Group::TYPE_MODERATOR, Group::TYPE_MANAGER, Group::TYPE_NOTATION))) {
            throw $this->createNotFoundException("'$type' not found");
        }
    
        $em = $this->getDoctrine()->getManager();
        $moderators = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Group')
        ->findBy(array('type' => mb_strtoupper($type)));
    
        $normalizer = $this->get('progracqteurWikipedaleSerializer');
        $response = new NormalizedResponse($moderators);
    
        return new Response($normalizer->serialize($response, 'json'));
    }
}


