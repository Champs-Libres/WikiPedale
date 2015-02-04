<?php

namespace Progracqteur\WikipedaleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizerSerializerService;
use Progracqteur\WikipedaleBundle\Entity\Management\UnregisteredUser;
use Progracqteur\WikipedaleBundle\Resources\Container\NormalizedResponse;

/**
 * Controller that manages the user session. This session contains information
 * about the selected city (or not) and the current logged user (or not)
 *
 * @author Champs-Libres COOP
 */
class ManagerController extends Controller {
    
    /**
     * Select a city and set this information into the session.
     *
     * @param string $citySlug
     * @param Request $request
     * @return Symfony\Component\HttpFoundation\Response The index page
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * The exception is thrown if the variable $citySlug does not refer to a
     * known city
     */
    public function toCityAction($citySlug, Request $request)
    {
        $citySlug = $this->get('progracqteur.wikipedale.slug')->slug($citySlug);

        $em = $this->getDoctrine()->getManager();
        $cities = $em->createQuery("SELECT  c from ProgracqteurWikipedaleBundle:Management\\Zone c 
                 where c.slug = :slug")
                ->setParameter('slug', $citySlug)
                ->getResult();
        $city = $cities[0];
        
        if ($city === null) {
            throw $this->createNotFoundException("La ville demandée '$city' n'a pas été trouvée");
        }
        
        $session = $this->getRequest()->getSession();
        $session->set('city', $city);
        
        $url = $request->get('url', null);
        
        if ($url === null) {
            $url = $this->generateUrl('wikipedale_homepage');
        }
        
        return $this->redirect($url);
    }

    /**
     * Remove the session information about the selected city.
     *
     * @return Symfony\Component\HttpFoundation\Response The index page.
     */
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
    /**
     * Returns a json data containing the current user. If the user is not logged, 
     * an empty UnregisteredUser is returned.
     *
     * @param string $_format The format. For the moment only JSON is supported.
     * @return Response The json data containing the actual user.
     * @throws \Exception If the format is not JSON
     */
    public function httpBasicAuthenticateAction($_format) {
        if ($_format != NormalizerSerializerService::JSON_FORMAT) {
            throw new \Exception("Le format demandé n'est pas disponible");
        }
        
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = new UnregisteredUser();
        } else {
            $user = $this->get('security.context')->getToken()->getUser();
        }
        
        $normalizedResponse = new NormalizedResponse();
        $normalizedResponse->setResults(array($user));
        
        $serializer = $this->get('progracqteurWikipedaleSerializer');
         
        $serializer->getUserNormalizer()->addGroupsToNormalization(true);
        
        return new Response($serializer->serialize($normalizedResponse, NormalizerSerializerService::JSON_FORMAT)); 
    }
}

