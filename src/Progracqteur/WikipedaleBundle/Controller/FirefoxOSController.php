<?php

namespace Progracqteur\WikipedaleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Progracqteur\WikipedaleBundle\Entity\Management\Group;

class FirefoxOsController extends Controller
{
	public function chooseCityAction() {
		$em = $this->getDoctrine()->getManager();
		$cities = $em
        	->createQuery("select c from ProgracqteurWikipedaleBundle:Management\\Zone c  order by c.name")
           	->getResult();   

		$mainCitiesSlug = $this->get('service_container')->getParameter('cities_in_front_page'); 
        $mainCities = array();
        
        foreach ($cities as $c)
        {
            if (in_array($c->getSlug(), $mainCitiesSlug))
            {
                $mainCities[] = $c;
            }
        }

        $paramsToView = array(
            'mainCities' => $mainCities, 
        );

		return $this->render('ProgracqteurWikipedaleBundle:FirefoxOS:city_choice.html.twig',
			$paramsToView
		);

	}

	public function homepageAction() {
		$session = $this->getRequest()->getSession();

		 if ($this->getRequest()->getSession()->get('city') == null)
        {	
        	return $this->redirect($this->generateUrl('wikipedale_firefoxos_choose_city'));
        }

        return $this->render('ProgracqteurWikipedaleBundle:FirefoxOS:menu.html.twig');
	}

    public function viewAction() {
        $terms_allowed = ' ';
        $terms_allowed_array = array();
        $iTerm = 0;
        foreach ($this->get('service_container')->getParameter('place_types') 
                as $target => $array) {
            //TODO extendds to other transports
                    if ($target === 'bike') {
                        foreach ($array["terms"] as $term) {
                            if ($this->get('security.context')->isGranted(
                                    $term['mayAddToPlace'])){
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
        
        $q = sprintf('SELECT c from 
            ProgracqteurWikipedaleBundle:Model\Category c 
            WHERE  c.used = true AND c.parent is null AND c.term IN (%s)
            ORDER BY c.order, c.label', $terms_allowed);
        
        $categories = $this->getDoctrine()->getManager()
                ->createQuery($q)
                ->getResult();
        //Todo: cachable query

        $placeTypes = $this->getDoctrine()->getManager()
                ->getRepository('ProgracqteurWikipedaleBundle:Model\Place\PlaceType')
                ->findAll();

        if ($this->getRequest()->getSession()->get('city') !== null)
        {
            $z = $this->getRequest()->getSession()->get('city');
            $managers = $this->getDoctrine()
                    ->getRepository('ProgracqteurWikipedaleBundle:Management\Group')
                    ->getGroupsByTypeByCoverage(Group::TYPE_MANAGER, $z->getPolygon());
        } else {
            $managers = array();
        }

        $paramsToView = array(
            'categories' => $categories,
            'terms_allowed' => $terms_allowed_array,
            'placeTypes' => $placeTypes,
            'managers' => $managers
        );

        return $this->render('ProgracqteurWikipedaleBundle:FirefoxOS:view.html.twig', $paramsToView);
    }

    public function signalAction() {
        $terms_allowed = ' ';
        $terms_allowed_array = array();
        $iTerm = 0;
        foreach ($this->get('service_container')->getParameter('place_types') 
                as $target => $array) {
            //TODO extendds to other transports
                    if ($target === 'bike') {
                        foreach ($array["terms"] as $term) {
                            if ($this->get('security.context')->isGranted(
                                    $term['mayAddToPlace'])){
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
        
        $q = sprintf('SELECT c from 
            ProgracqteurWikipedaleBundle:Model\Category c 
            WHERE  c.used = true AND c.parent is null AND c.term IN (%s)
            ORDER BY c.order, c.label', $terms_allowed);
        
        $categories = $this->getDoctrine()->getManager()
                ->createQuery($q)
                ->getResult();
        //Todo: cachable query

        $paramsToView = array(
            'categories' => $categories,
            'terms_allowed' => $terms_allowed_array
        );

        return $this->render('ProgracqteurWikipedaleBundle:FirefoxOS:signal.html.twig', $paramsToView);
    }

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
        
        if ($city === null)
        {
            throw $this->createNotFoundException("La ville demandée '$city' n'a pas été trouvée");
        }
        
        $session->set('city', $city);
        
        $url = $request->get('url', null);
        
        if ($url === null)
        {
            $url = $this->generateUrl('wikipedale_firefoxos_index');
        }
        
        return $this->redirect($url);
     }
}
?>