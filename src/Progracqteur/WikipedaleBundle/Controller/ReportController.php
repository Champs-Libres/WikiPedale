<?php

namespace Progracqteur\WikipedaleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Progracqteur\WikipedaleBundle\Entity\Model\Report;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Progracqteur\WikipedaleBundle\Resources\Geo\BBox;
use Symfony\Component\HttpFoundation\Request;
use Progracqteur\WikipedaleBundle\Resources\Container\NormalizedResponse;
use Progracqteur\WikipedaleBundle\Resources\Container\NormalizedExceptionResponse;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizerSerializerService;
use Progracqteur\WikipedaleBundle\Resources\Security\ChangeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Progracqteur\WikipedaleBundle\Entity\Management\User;
use Progracqteur\WikipedaleBundle\Resources\Security\Authentication\WsseUserToken;
use Progracqteur\WikipedaleBundle\Form\Model\ReportType;
use Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription;

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

        $CategoriesArray = array();

        $CategoriesString = $request->get('categories', null);
        if ($CategoriesString !== null && $CategoriesString !== "") {
            $CategoriesArray = explode (',',$CategoriesString);
        }

        $NotationArray = array();

        $NotationString = $request->get('notations', null);
        if ($NotationString !== null && $NotationString !== "") {
            $NotationArray = explode (',',$NotationString);
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

        $p = $em->createQueryBuilder()
            ->from('ProgracqteurWikipedaleBundle:Model\\Report','p')
            ->select('p')
            ->join('p.category', 'c')
            ->where(('covers(:polygon, p.geom) = true AND p.accepted = true' . $filterCondition));

        if($CategoriesArray) {
            $p = $p->andWhere('c.id IN (:cat)');
        }
        
        $p = $p->orderBy('p.id')
            ->setParameter('polygon', $city->getPolygon());

        if($CategoriesArray) {
            $p = $p->setParameter('cat', $CategoriesArray);
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


        $r = $p->getQuery()->getResult();

        if($NotationArray) {
            $new_r = array();

            for($i = 0; $i < count($r); $i = $i + 1) {
                $cem_notation_row = 0;
                foreach ($r[$i]->getStatuses() as $key)
                { 
                    if($key->getType() == 'cem') {
                        $cem_notation_row = $key->getValue();
                    }
                }
                if(in_array($cem_notation_row, $NotationArray)) {
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
                return new Response('Pas encore implémenté');

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
        
        if ($serializedJson === null)
        {
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
        
        //ajoute l'utilisateur courant comme créateur si connecté
        if ($report->getId() == null && $this->get('security.context')->getToken()->getUser() instanceof User) {
            $u = $this->get('security.context')->getToken()->getUser();
            $report->setCreator($u);
        }
        
        //ajoute l'utilisateur courant au changeset
        if ($report->getChangeset()->isCreation()) { // si création 
            if ($this->get('security.context')->getToken()->getUser() instanceof User) { //si utilisateur connecté
                $report->getChangeset()->setAuthor($this->get('security.context')->getToken()->getUser());
            } else { 
                $user = $report->getCreator();
                
                $report->getChangeset()->setAuthor($user);
            }
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
    public function confirmUserAction(Request $request, $token, $reportId) 
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