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
     * Display the 'homepage' webpage. This is the main page of the
     * application : the webpage is updated (in function of the user 
     * interaction) with JSON request.
     * @param $request The request
     * @todo cachable query
     */
    public function homepageAction(Request $request)
    {
        $selectedReportId = $request->get('id', null);
        $selectedReport = null;
        $selectedZone = null;

        $em = $this->getDoctrine()->getManager();
        if ($selectedReportId) {
            $selectedReport = $em->getRepository('ProgracqteurWikipedaleBundle:Model\Report')
                ->find($selectedReportId);
            
            if ($selectedReport === null OR  !$selectedReport->isAccepted()) {
                throw $this->createNotFoundException('errors.404.report.not_found');
            }
            
            $selectedZone = $selectedReport->getModerator()->getZone();
            
            $session = $this->getRequest()->getSession();
            $session->set('selectedZoneId', $selectedZone->getId());

        }
        
        $enableZones = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Zone')->findAllWithModerator();

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
        
        $categoriesQuery = sprintf('SELECT c from
            ProgracqteurWikipedaleBundle:Model\Category c 
            WHERE  c.used = true AND c.parent is null AND c.term IN (%s)
            ORDER BY c.order, c.label', $terms_allowed);
        
        $categories = $em->createQuery($categoriesQuery)->getResult();
        //@todo cachable query
        
        $reportTypes = $em->getRepository('ProgracqteurWikipedaleBundle:Model\Report\ReportType')
            ->findAll();
        //@todo cachable query
        
        if (!$selectedZone && $request->getSession()->get('selectedZoneId') !== null) {
            $selectedZoneId = $request->getSession()->get('selectedZoneId');
            $selectedZone = $em
                ->getRepository('ProgracqteurWikipedaleBundle:Management\Zone')
                ->find($selectedZoneId);

            $managers = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Group')
                ->findBy(array('type' => Group::TYPE_MANAGER, 'zone' => $selectedZone));
        } else {
            $managers = array();
        }

        $moderators = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Group')
            ->findByType(Group::TYPE_MODERATOR);
        
        $paramsToView = array(
            'mainCities' => $enableZones, 
            'categories' => $categories,
            'reportTypes' => $reportTypes,
            'managers' => $managers,
            'moderators' => $moderators,
            'terms_allowed' => $terms_allowed_array
        );

        if ($selectedZone) {
            $paramsToView['selectedZone'] = $selectedZone;
        }

        if ($selectedReportId) {
            $paramsToView['selectedReportId'] = $selectedReportId;
            $paramsToView['selectedReport'] = $selectedReport;
        }
        
        return $this->render(
            'ProgracqteurWikipedaleBundle:Main:homepage.html.twig', 
            $paramsToView);
    }
}
