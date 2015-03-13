<?php

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
     * Display 'homepage' webpage. This is the main page of the application : 
     * the webpage is updated (in function of the user interaction) with JSON
     * request.
     * 
     * @todo Remove the array creation (serialieation, or toArray function)
     * @todo session->get('city') returns a Zone object not an array
     */
    public function homepageAction(Request $request)
    {
        $id = $request->get('id', null);
        $selectedReport = null;

        $em = $this->getDoctrine()->getManager();
        if ($id != null) {
            $selectedReport = $em->getRepository('ProgracqteurWikipedaleBundle:Model\Report')
                ->find($id);
            
            if ($selectedReport === null OR  !$selectedReport->isAccepted()) {
                throw $this->createNotFoundException('errors.404.report.not_found');
            }
            
            $cityObj = $selectedReport->getModerator()->getZone();
            
            $city = [
                'id' => $cityObj->getId(),
                'name' => $cityObj->getName(),
                'center' => [
                    'lon' => $cityObj->getCenter()->getLon(),
                    'lat' => $cityObj->getCenter()->getLat()]];
            
            $request->getSession()->set('city', $city);
        }

        $cities = $em->createQuery("select c from 
            ProgracqteurWikipedaleBundle:Management\\Zone c  order by c.name")
            ->getResult();
        
        $mainCitiesSlug = $this->get('service_container')
            ->getParameter('cities_in_front_page'); 
        
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
        
        $q = sprintf('SELECT c from 
            ProgracqteurWikipedaleBundle:Model\Category c 
            WHERE  c.used = true AND c.parent is null AND c.term IN (%s)
            ORDER BY c.order, c.label', $terms_allowed);
        
        $categories = $em->createQuery($q)->getResult();
        //Todo: cachable query
        
        $reportTypes = $em->getRepository('ProgracqteurWikipedaleBundle:Model\Report\ReportType')
            ->findAll();
        //TODO : cachable query
        
        // @todo why an object is returned not an array ?
        $zone = $request->getSession()->get('city');

        if ($zone) {
            $managers = $em
                ->getRepository('ProgracqteurWikipedaleBundle:Management\Group')
                ->getGroupsByTypeByCoverage(Group::TYPE_MANAGER, $zone->getPolygon());
        } else {
            $managers = array();
        }

        $moderators = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Group')
            ->findByType(Group::TYPE_MODERATOR);
        
        $paramsToView = array(
            'mainCities' => $mainCities, 
            'cities' => $cities,
            'categories' => $categories,
            'reportTypes' => $reportTypes,
            'managers' => $managers,
            'moderators' => $moderators,
            'terms_allowed' => $terms_allowed_array
        );

        if ($id != null) {
            $paramsToView['selectedReportId'] = $id;
            $paramsToView['selectedReport'] = $selectedReport;
        }
        
        return $this->render(
            'ProgracqteurWikipedaleBundle:Main:homepage.html.twig', 
            $paramsToView);
    }
}
