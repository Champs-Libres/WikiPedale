<?php

namespace Progracqteur\WikipedaleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Progracqteur\WikipedaleBundle\Entity\Model\Report;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Progracqteur\WikipedaleBundle\Resources\Geo\Point;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizerSerializerService;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\UserNormalizer;

/**
 * Description of ManagerController
 *
 * @author Julien Fastré
 */
class ManagerController extends Controller {
    
    public function toCityAction($citySlug, Request $request)
    {
        $session = $this->getRequest()->getSession();
        
        $em = $this->getDoctrine()->getManager();
        
        $citySlug = $this->get('progracqteur.wikipedale.slug')->slug($citySlug);
        
        $cities = $em->createQuery("SELECT  c from ProgracqteurWikipedaleBundle:Management\\Zone c 
                 where c.slug = :slug")
                ->setParameter('slug', $citySlug)
                ->getResult();
        
        $city = $cities[0];
        
        if ($city === null) {
            throw $this->createNotFoundException("La ville demandée '$city' n'a pas été trouvée");
        }
        
        
        $session->set('city', $city);
        
        $url = $request->get('url', null);
        
        if ($url === null) {
            $url = $this->generateUrl('wikipedale_homepage');
        }
        
        return $this->redirect($url);
        
    }
     
    public function resetCityAction()
    {
        $session = $this->getRequest()->getSession();
        $session->set('city', null);
         
        $url = $this->getRequest()->get('url', null);
        
        if ($url === null) {
            $url = $this->generateUrl('wikipedale_homepage');
        }
        
        return $this->redirect($url);
    }
     
    public function wsseAuthenticateAction($_format)
    {
        if ($_format != NormalizerSerializerService::JSON_FORMAT) {
            throw new \Exception("Le format demandé n'est pas disponible");
        }
         
        $u = $this->get('security.context')->getToken()->getUser();
         
        $r = new \Progracqteur\WikipedaleBundle\Resources\Container\NormalizedResponse();
        $r->setResults(array($u));
         
        $serializer = $this->get('progracqteurWikipedaleSerializer');
         
        $serializer->getUserNormalizer()->addGroupsToNormalization(true);
         
        $t = $serializer->serialize($r, NormalizerSerializerService::JSON_FORMAT);
         
        return new Response($t); 
    }
    
}

