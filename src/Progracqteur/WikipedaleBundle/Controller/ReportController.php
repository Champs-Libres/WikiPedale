<?php

namespace Progracqteur\WikipedaleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Progracqteur\WikipedaleBundle\Resources\Geo\BBox;
use Symfony\Component\HttpFoundation\Request;
use Progracqteur\WikipedaleBundle\Resources\Container\NormalizedResponse;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizerSerializerService;
use Progracqteur\WikipedaleBundle\Resources\Security\ChangeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Progracqteur\WikipedaleBundle\Entity\Management\User;
use Progracqteur\WikipedaleBundle\Resources\Security\Authentication\WsseUserToken;
use Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription;

use Progracqteur\WikipedaleBundle\Resources\Normalizer\LightReportArrayNormalizer;

/**
 * Description of ReportController
 *
 * @author Julien Fastré
 */
class ReportController extends Controller
{
    /**
     * Return a given report
     *
     * @param int $id The id of the asked report
     *
     * @return Symfony\Component\HttpFoundation\Response A json that contains the details of the report of id $id.
     */
    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $report = $em->getRepository('ProgracqteurWikipedaleBundle:Model\\Report')->find($id);
        
        if ($report === null) {
            throw $this->createNotFoundException("L'endroit n'a pas été trouvé dans la base de donnée");
        }
        
        if ($report->isAccepted() === false 
                && ! $this->get('security.context')->isGranted(User::ROLE_SEE_UNACCEPTED)) {
            $hash = $this->getRequest()->query->get('checkcode');
            $code = $report->getCreator()->getCheckCode();

            if ($hash !== hash('sha512', $code)) { /* sha512 -> is the used hashing algorithm */
                throw new \Exception('code does not match '.$code.' '.$hash);
                throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
            }      
        }
        
        $normalizer = $this->get('progracqteurWikipedaleSerializer');
        $rep = new NormalizedResponse($report);
        $ret = $normalizer->serialize($rep, 'json');

        return new Response($ret);
    }

    public function lightListAction()
    {
        $defaultHiddenTerms = $this->get('service_container')->getParameter('default_hidden_report_terms');
        $defaultHiddenStatus = $this->get('service_container')->getParameter('default_hidden_report_status');
        //var_dump($defaultHiddenTerms);
        //var_dump($defaultHiddenStatus);

        $em = $this->getDoctrine()->getManager();

        $req = $em->createQueryBuilder()
            ->from('ProgracqteurWikipedaleBundle:Model\\Report','r')
            ->select('r')
            ->join('r.category', 'c')
            ->where('r.accepted = TRUE');
            

        foreach ($defaultHiddenTerms as $hiddenTerm) {
            $req = $req->andWhere("c.term != '" .$hiddenTerm . "'");
        }

        $allReportsArrayWithoutStatusSelection = $req->getQuery()->getResult();

        $allReports = array();
        foreach ($allReportsArrayWithoutStatusSelection as $report) {
            $displayReport = true;
            $status = $report->getStatuses();

            foreach($defaultHiddenStatus as $s) {
                foreach ($status as $reportStatus) {
                    if($reportStatus->getType() == $s["label"] && $reportStatus->getValue() == $s["value"]) {
                        $displayReport = false;
                    }
                }
            }

            if($displayReport) {
                $allReports[] = $report;
            }
        }

        $normalizer = new LightReportArrayNormalizer();
        $encoder = new JsonEncoder();

        $serializer = new Serializer(array($normalizer), array($encoder));
        $jsonContent = $serializer->serialize($allReports, 'json');

        $geojsonContent = '{"type": "FeatureCollection", "features": ' . $jsonContent  . '}';
        return new Response($geojsonContent);
    }


    /**
     * Return all the reports contained in a Polygon
     *
     * @param $polygon The polygon
     * @param String $_format the format of the output (csv or json)
     * @param Symfony\Component\HttpFoundation\Request $request the request
     *
     * @return Symfony\Component\HttpFoundation\Response A csv or json file that contains an array of all the reports contained in a polygon
     */
    public function listContainedInPolygonAction($polygon, $_format, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $categoriesArray = array();
        $categoriesString = $request->get('categories');
        if ($categoriesString) {
            $categoriesArray = explode (',',$categoriesString);
        }

        $moderatorStatusArray = array();
        $moderatorStatusString = $request->get('moderator_status');
        if ($moderatorStatusString) {
            $moderatorStatusArray = explode (',',$moderatorStatusString);
        }

        $filterCondition = "";

        $timestampBeginString = $request->get('timestamp_begin');
        if($timestampBeginString) {
            $filterCondition = $filterCondition . " AND p.createDate >= :timestampB";
        }

        $timestampEndString = $request->get('timestamp_end');
        if($timestampEndString) {
            $filterCondition = $filterCondition . " AND p.createDate <= :timestampE";
        }

        $managersString = $request->get('managers');
        if($managersString) {
            $managersArray = explode (',', $managersString);
            $filterCondition = $filterCondition . " AND (";
            if (in_array('-1', $managersArray)) {
                $filterCondition = $filterCondition . " p.manager IS NULL OR";
            }
            $filterCondition = $filterCondition . " p.manager IN (:managers))";
        }

        $p = $em->createQueryBuilder()
            ->from('ProgracqteurWikipedaleBundle:Model\\Report','p')
            ->select('p');

        if($categoriesArray) {
            $p = $p->join('p.category', 'c');
        }
            
        $p = $p->where(('covers(:polygon, p.geom) = true AND p.accepted = true' . $filterCondition));

        if($categoriesArray) {
            $p = $p->andWhere('c.id IN (:categories)');
        }
        
        $p = $p->orderBy('p.id')
            ->setParameter('polygon', $polygon);

        if($categoriesArray) {
            $p = $p->setParameter('categories', $categoriesArray);
        }          

        if($timestampBeginString) {
            $dateB = new \DateTime();
            $dateB->setTimestamp(intval($timestampBeginString));
            $p = $p->setParameter('timestampB', $dateB);
        }

        if($timestampEndString) {
            $dateE = new \DateTime();
            $dateE->setTimestamp(intval($timestampEndString));
            $p = $p->setParameter('timestampE', $dateE);
        }

        if($managersString) {
            $p = $p->setParameter('managers', $managersArray);
        }

        $r = $p->getQuery()->getResult();

        if($moderatorStatusArray) {
            $new_r = array();

            for($i = 0; $i < count($r); $i = $i + 1) {
                $cem_notation_row = 0;
                foreach ($r[$i]->getStatuses() as $key) { 
                    if($key->getType() == 'cem') {
                        $cem_notation_row = $key->getValue();
                    }
                }
                if(in_array($cem_notation_row, $moderatorStatusArray)) {
                    array_push($new_r, $r[$i]);
                }
            }
            $r = $new_r;
        }

        switch($_format) {
            case 'json':
                $normalizer = $this->get('progracqteurWikipedaleSerializer');
                $rep = new NormalizedResponse($r);
                $ret = $normalizer->serialize($rep, $_format);
                return new Response($ret);

            case 'html':
                return $this->render('ProgracqteurWikipedaleBundle:Report:list_printable.html.twig', 
                    array('reports' => $r));

            case 'csv' :
                $response = $this->render('ProgracqteurWikipedaleBundle:Report:list.csv.twig', 
                    array('reports' => $r));
                
                $response->setStatusCode(200);
                $response->headers->set('Content-Type', 'text/csv');
                $response->headers->set('Content-Description', 'List of reports');
                $response->headers->set('Content-Disposition', 'attachment; filename=list.csv');
                $response->headers->set('Content-Transfer-Encoding', 'binary');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');

                return $response;    
        }
    }
    

    /**
     * Return all the reports contained in a BBox
     *
     * @param String $_format the format of the output (csv or json)
     * @param Symfony\Component\HttpFoundation\Request $request the request that must contain a bbox string
     *
     * @return Symfony\Component\HttpFoundation\Response A csv or json file that contains an array of all the reports contained in the BBox
     */
    public function listByBBoxAction($_format, Request $request)
    {
        $BboxStr = $request->get('bbox', null);
        if ($BboxStr === null) {
            throw new \Exception('Fournissez un bbox');
        }
        $BboxArr = explode(',', $BboxStr, 4);
        foreach($BboxArr as $value){
            if (!is_numeric($value)) {
                throw new \Exception("Le Bbox n'est pas valide : $BboxStr");
            }
        }
        
        $bbox = BBox::fromCoord($BboxArr[0], $BboxArr[1], $BboxArr[2], $BboxArr[3]);
        $polygon = $bbox->toWKT();

        return $this->listContainedInPolygonAction($polygon, $_format, $request);
    }

    /**
     * Return all the reports for a given city
     *
     * @param String $_format the format of the output (csv or json)
     * @param Symfony\Component\HttpFoundation\Request $request the request that must contain a city param
     *
     * @return Symfony\Component\HttpFoundation\Response A csv or json file that contains an array of all the report of the city
     */
    public function listByCityAction($_format, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $citySlug = $request->get('city', null);
        $citySlug = $this->get('progracqteur.wikipedale.slug')->slug($citySlug);
        
        if ($citySlug === null) {
            throw new \Exception('Renseigner une ville dans une variable \'city\' ');
        }
        
        $city = $em->getRepository('ProgracqteurWikipedaleBundle:Management\\Zone')
                ->findOneBy(array('slug' => $citySlug));
        
        if ($city === null) {
            throw $this->createNotFoundException("Aucune ville correspondant à $citySlug n'a pu être trouvée");
        }

        $polygon = $city->getPolygon();

        return $this->listContainedInPolygonAction($polygon, $_format, $request);
    }

    /**
     * Update a given report (post method and json objec)
     *
     * @param Symfony\Component\HttpFoundation\Request $request The request containing the changes
     *
     * @return Symfony\Component\HttpFoundation\Response An error (400, 401, 403 or 404) if there is a problem. If not redirect to the viewAction : the JSON of the updated report
     */    
    public function changeAction(Request $request)
    {
        $logger = $this->get('logger');

        if ($request->getMethod() != 'POST') {
            throw new \Exception("Only post method accepted");
        }
        
        //check tokens
        $token = $request->attributes->get('token', null);
        if ($token === null) {
            //if WSSE authentication, does not need token
            if(! 
                    (
                        $this->get('security.context')->getToken() instanceof WsseUserToken
                        AND 
                        $this->get('security.context')->getToken()->isFullyAuthenticated()
                    )
              ) {
                /*TODO: when the token will be enabled into javascript, if there
                 * is no token, the script must reject request without tokens
                 */
                $logger->warn('Wikipedale:ReportController:ChangeAction change report without token');
                
                //TODO: remove debug code below :
                if ($this->get('security.context')->getToken() instanceof WsseUserToken) {
                    if (!$this->get('security.context')->getToken()->isFullyAuthenticated()) {
                        $logger->debug('Wikipedale:ReportController:ChangeAction connected with WSSE but not fully');
                    }
                }
            }
        } else {
            if (false === $this->get('progracqteur.wikipedale.token_provider')->isCsrfTokenValid($token)) {
                $logger->warn('Wikipedale:ReportController:ChangeAction use of invalid token');
                $response = new Response('invalid token provided');
                $response->setStatusCode(400);

                return $response;
            }
        }
        
        $serializedJson = $request->get('entity', null);
        
        if ($serializedJson === null) {
            throw new \Exception("Aucune entitée envoyée");
        }
        
        $serializer = $this->get('progracqteurWikipedaleSerializer');
        $report = $serializer->deserialize($serializedJson, NormalizerSerializerService::REPORT_TYPE, 'json');

        //SECURITE: refuse la modification d'une report par un utilisateur anonyme
        if (
                ($this->get('security.context')->getToken()->getUser() instanceof User) == false 
                && 
                $report->getChangeset()->isCreation() == false
            ) {
            $r = new Response("Il faut être enregistré pour modifier un signalement");
            $r->setStatusCode(403);

            return $r;
        }
        
        // Modification du report
        //ajoute l'utilisateur courant comme créateur si connecté
        if ($report->getId() == null && $this->get('security.context')->getToken()->getUser() instanceof User) {
            $u = $this->get('security.context')->getToken()->getUser();
            $report->setCreator($u);
        }
        
        // Modification du changeset
        
        if ($report->getChangeset()->isCreation()) { // si création 
           //ajoute l'utilisateur courant au changeset
            if ($this->get('security.context')->getToken()->getUser() instanceof User) { //si utilisateur connecté
                $report->getChangeset()->setAuthor($this->get('security.context')->getToken()->getUser());
            } else { 
                $user = $report->getCreator();
                
                $report->getChangeset()->setAuthor($user);
            }
            //add a default moderator
            $moderatorDesignator = $this->get('progracqteur.wikipedale.'
                  . 'moderator_designator');
            $report->setModerator($moderatorDesignator->getModerator($report));
        } else { //si modification d'un signalement
            //les vérifications de sécurité ayant eu lieu, il suffit d'ajouter l'utilisateur courant
            $report->getChangeset()->setAuthor($this->get('security.context')->getToken()->getUser());
        }
        
        $waitingForConfirmation = false;
        //if user = unregistered and creation, prepare the user for checking
        //and set the report as not accepted, and send an email to the user
        if ($report->getChangeset()->isCreation() === true 
                && $report->getCreator()->isRegistered() === false) {
            $report->setAccepted(false);
            $checkCode = $report->getCreator()->getCheckCode();
            $report->getChangeset()->setCreation(null);
            //register the report to the EntityManager, for getting the Id
            $this->getDoctrine()->getManager()->persist($report);
            
            $t = $this->get('translator');
            $message = \Swift_Message::newInstance()
                    ->setSubject($t->trans('email_confirmation_message.subject'))
                    ->setFrom('no-reply@uello.be') //TODO insert into parameters.yml
                    ->setTo($report->getCreator()->getEmail())
                    ->setBody(
                        $this->renderView('ProgracqteurWikipedaleBundle:Emails:confirmation.txt.twig',
                            array(
                                'code' => $checkCode,
                                'user' => $report->getCreator(),
                                'report' => $report
                            )), 'text/plain'
                    );

            $this->get('mailer')->send($message);
            $waitingForConfirmation = true;
        }
        
        /**
         * @var Progracqteur\WikipedaleBundle\Resources\Security\ChangeService 
         */
        $securityController = $this->get('progracqteurWikipedaleSecurityControl');
        
        try {
        //TODO implémenter une réponse avec code d'erreur en JSON
            $return = $securityController->checkChangesAreAllowed($report);
        } catch (ChangeException $exc) {
            $r = new Response($exc->getMessage());
            $r->setStatusCode(403);

            return $r;
        }
        
        if ($return == false) {
            $r = new Response("Vous n'avez pas de droits suffisants pour effectuer cette modification");
            $r->setStatusCode(403);

            return $r;
        }
        
        $validator = $this->get('validator');
        
        if ($report->getId() === null)
            $arrayValidation = array('Default', 'creation');
        else
            $arrayValidation = array('Default');
        
        $errors = $validator->validate($report, $arrayValidation);
        
        if ($errors->count() > 0) {
            $stringErrors = ''; $k = 0;
            foreach ($errors as $error) {
                $stringErrors .= $k.'. '.$error->getMessage();
            }
            
            throw new HttpException(403, 'Erreurs de validation : '.$stringErrors);
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($report);
        
        //If the change is a creation, suscribe the creator to notification
        //only for registered users - a notificaiton will be suscribe at email confirmation for unregistered
        if ($report->getChangeset()->isCreation() === true
            && $report->getCreator()->isRegistered() === true) {
            $notification = new NotificationSubscription();
                  
            $notification->setOwner($report->getCreator())
                    ->setKind(NotificationSubscription::KIND_PUBLIC_REPORT)
                    ->setReport($report)
                    ->setTransporter(NotificationSubscription::TRANSPORTER_MAIL);
            
            $em->persist($notification);
        }
        
        $em->flush();
        
        $params = array(
            'id' => $report->getId(), 
            '_format' => 'json',
            'return' => $return,
            'addUserInfo' => $request->get('addUserInfo', false));
        
        if ($waitingForConfirmation === true) {
            $hashCheckCode = hash('sha512', $report->getCreator()->getCheckCode());
            $params['checkcode'] = $hashCheckCode;
        }     

        return $this->redirect(
            $this->generateUrl('wikipedale_report_view', $params));
    }
    
    /**
     * Validate a report for an unregister user (When an unregister user add a report, the added
     * report is not displayed on the map and an email
     * is send to user with a confimation code. This controller check this confirmation code.
     * If the confirmation code is correct the report is validate and shown on the map).
     *
     * @param Symfony\Component\HttpFoundation\Request $request the request
     * @param string $token The confirmation code
     * @param int $reportId The id of the related report
     *
     * @return Symfony\Component\HttpFoundation\Response Error 401 if problems. If not a confrmation page.
     */
    public function confirmUserAction($token, $reportId)
    {
        $report = $this->getDoctrine()->getManager()
            ->getRepository('ProgracqteurWikipedaleBundle:Model\Report')
            ->find($reportId);
        
        if ($report === null) {
            throw $this->createNotFoundException('Report not found');
        }
        
        if ($report->getCreator() instanceof \Progracqteur\WikipedaleBundle\Entity\Management\UnregisteredUser
                && $report->getCreator()->checkCode($token)) {
            $creator = $report->getCreator();
            
            //if the creator is already confirmed, stop the script
            if ($creator->isChecked()) {
                $r = new Response('Report already confirmed');
                $r->setStatusCode(401);

                return $r;
            }
            
            $creator->setChecked(true);
            $report->setConfirmedCreator($creator);
            $this->getDoctrine()->getManager()->flush($report);
            
            return $this->render('ProgracqteurWikipedaleBundle:Report:confirmed.html.twig',
                array(
                    'report' => $report
                ));
        } else {
            $r = new Response('check code does not match');
            $r->setStatusCode(401);

            return $r;
        }
    }
}