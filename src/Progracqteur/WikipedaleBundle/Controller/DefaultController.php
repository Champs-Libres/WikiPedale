<?php

namespace Progracqteur\WikipedaleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Progracqteur\WikipedaleBundle\Entity\Management\Group;

/**
 * Description of DefaultController
 *
 * This controller contains the only webpages of the application (except for the admin part).
 * 
 * @author Julien Fastré <julien arobase fastre point info>
 */
class DefaultController extends Controller
{
    /**
     * Display the 'about' webpage.
     */
    public function aboutAction()
    {
        return $this->render('ProgracqteurWikipedaleBundle:Default:about.html.twig');
    }
    
    /**
     * Display 'homepage' webpage. This is the main page of the application : the webpage is 
     * updated (in function of the user interaction) with JSON request.
     */
    public function homepageAction()
    {
        $id = $this->getRequest()->get('id', null);
        $em = $this->getDoctrine()->getManager();
        
        if ($id != null) {
            $report = $em->getRepository('ProgracqteurWikipedaleBundle:Model\Report')
                ->find($id);
            
            if ($report === null OR $report->isAccepted() == FALSE) {
                throw $this->createNotFoundException('errors.404.report.not_found');
            }
            
            $stringGeo = $this->get('progracqteur.wikipedale.geoservice')->toString($report->getGeom());
            
            $city = $em->createQuery('select c 
                    from ProgracqteurWikipedaleBundle:Management\Zone c
                    where COVERS(c.polygon, :geom) = true and c.type = :type
                ')
                    ->setParameter('geom', $stringGeo)
                    ->setParameter('type', 'city')
                    ->getSingleResult();
            
            $this->getRequest()->getSession()->set('city', $city);
        }

        $cities = $em->createQuery("select c from 
            ProgracqteurWikipedaleBundle:Management\\Zone c  order by c.name")
            ->getResult();
        
        $mainCitiesSlug = $this->get('service_container')->getParameter('cities_in_front_page'); 
        $mainCities = array();
        
        foreach ($cities as $c) {
            if (in_array($c->getSlug(), $mainCitiesSlug)) {
                $mainCities[] = $c;
            }
        }
        
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
        
        $reportTypes = $this->getDoctrine()->getManager()
                ->getRepository('ProgracqteurWikipedaleBundle:Model\Report\ReportType')
                ->findAll();
        //TODO : cachable query
        
        if ($this->getRequest()->getSession()->get('city') !== null) {
            $z = $this->getRequest()->getSession()->get('city');
            $managers = $this->getDoctrine()
                ->getRepository('ProgracqteurWikipedaleBundle:Management\Group')
                ->getGroupsByTypeByCoverage(Group::TYPE_MANAGER, $z->getPolygon());
        } else {
            $managers = array();
        }
        
        $paramsToView = array(
            'mainCities' => $mainCities, 
            'cities' => $cities,
            'categories' => $categories,
            'reportTypes' => $reportTypes,
            'managers' => $managers,
            'terms_allowed' => $terms_allowed_array
        );

        if ($id != null) {
            $paramsToView['goToPlaceId'] = $id;
        }
        
        return $this->render('ProgracqteurWikipedaleBundle:Default:homepage.html.twig', 
            $paramsToView
            );
    }
}
