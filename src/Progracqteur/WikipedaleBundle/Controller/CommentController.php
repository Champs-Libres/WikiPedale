<?php
namespace Progracqteur\WikipedaleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Progracqteur\WikipedaleBundle\Resources\Container\NormalizedResponse;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizerSerializerService;
use Progracqteur\WikipedaleBundle\Entity\Management\User;
use Progracqteur\WikipedaleBundle\Entity\Model\Comment;

/**
 * Description of CommentController
 *
 * @author marcducobu [arobase] gmail POINT com 
 * @author julien [arobase] fastre POINT info
 */
class CommentController extends Controller 
{
   const MAX_COMMENTS_BY_REQUEST = 40;
    
   /**
    * Returns the comments for a given report
    * @param string $_format The format of the output (actually only 'json')
    * @param integer $reportId The id of the report
    * @param integer $limit The max comment returned by the request (if null this value is self::MAX_COMMENTS_BY_REQUEST)
    * @param Request $request The Request
    * @return (Output format given by $_format) The comments
    */
   private function getCommentByReportLimit($_format, $reportId, $limit, Request $request)
   {
      $em = $this->getDoctrine()->getManager();

      $report = $em->getRepository("ProgracqteurWikipedaleBundle:Model\\Report")->find($reportId);

      if ($report === null OR $report->isAccepted() === false) { //TODO: i18n
         throw $this->createNotFoundException("Le signalement $reportId n'a pas été trouvée");
      }

      $qstring = "SELECT cm 
         FROM ProgracqteurWikipedaleBundle:Model\\Comment cm 
         WHERE cm.report = :report 
         AND cm.published = true ";

      $q = $em->createQuery()->setParameter('report',$report);

      $countQuery = $em->createQuery()->setParameter('report', $report);

      //create a where clause depending on the user's roles
      $strCommentTypeCondition = '';

      //add default type
      $strCommentTypeCondition .= "cm.type = :public";
      $q->setParameter('public', Comment::TYPE_PUBLIC);
      $countQuery->setParameter('public', Comment::TYPE_PUBLIC);

      //add depending on roles
      if ($this->get('security.context')->isGranted(User::ROLE_COMMENT_MODERATOR_MANAGER)) {
         if ($strCommentTypeCondition !== '') {
             $strCommentTypeCondition .= ' OR ';
         }

         $strCommentTypeCondition .= 'cm.type = :moderator_manager';
         $q->setParameter('moderator_manager', Comment::TYPE_MODERATOR_MANAGER);
         $countQuery->setParameter('moderator_manager', Comment::TYPE_MODERATOR_MANAGER);
      }
  
      $qstring .= " AND (".$strCommentTypeCondition.") ";

      $qstring .= " ORDER BY cm.creationDate DESC ";

      $q->setDql($qstring);

      $limit = $request->query->get('max', null);

      if ($limit !== null) {
         if ($limit > self::MAX_COMMENTS_BY_REQUEST) {
             $limit = self::MAX_COMMENTS_BY_REQUEST;
         }
         $q->setMaxResults($limit);
         
      } else {
         $q->setMaxResults(self::MAX_COMMENTS_BY_REQUEST);
      }

      $first = $request->query->get('first', null);

      if ($first < 0) {
         $response = new Response('le paramètre first ne peut pas être négatif');
         $response->setStatusCode(400);
         return $response;
      }

      if ($first !== null) {
         $q->setFirstResult($first);
      }

      $comments = $q->getResult();
        
      $countQueryDQLString = 'SELECT count(cm.id) 
         FROM ProgracqteurWikipedaleBundle:Model\Comment cm
         WHERE
         cm.report = :report 
         and cm.published = true AND ('.$strCommentTypeCondition.') ';   

      $count = $countQuery->setDql($countQueryDQLString)
         ->getSingleScalarResult();

      switch($_format)
      {
         case 'json':
             $response = new NormalizedResponse();
             $response->setResults($comments);
             $response->setLimit($limit);
             $response->setTotal($count);
             
             $serializer = $this->get('progracqteurWikipedaleSerializer');
             $string = $serializer->serialize($response, $_format);
             
             return new Response($string);
             break;
         default:
             throw new \Exception("le format $_format est inconnu");
      }
   }

   /**
    * Returns the lastest comment for a given report
    * @param string $_format The format of the output (actually only 'json')
    * @param integer $reportId The id of the report
    * @param integer $limit The max comment returned by the request (if null this value is self::MAX_COMMENTS_BY_REQUEST)
    * @param Request $request The Request
    * @return Comment The comment
    */
   public function getLastCommentByReportAction($_format, $reportId, Request $request)
   {
     return $this->getCommentByReportLimit($_format, $reportId, 1, $request);
   }

   /**
    * Returns the lastest comments for a given report. The number returned is given by the
    * constant MAX_COMMENTS_BY_REQUEST
    * @param string $_format The format of the output (actually only 'json')
    * @param integer $reportId The id of the report
    * @param Request $request The Request
    * @return Comment[] The lastest comments
    */
   public function getCommentByReportAction($_format, $reportId,Request $request)
   {
     return $this->getCommentByReportLimit($_format, $reportId, null, $request);
   }

   /**
    * Handle the modify / creation form
    * @param string $_format The format of the output (actually only 'json')
    * @param Request $request The request
    * @return No return : redirection to wikipedale_comment_view
    */
   public function changeAction($_format, Request $request)
   {
      if ($request->getMethod() != 'POST') {
         throw new \Exception("Only post method accepted");
      }

      $em = $this->getDoctrine()->getManager();

      // Identification via APIKEY
      if ($request->get('APIKey') && $request->get('userId') && $request->get('reportId')) {
         $userId = $request->get('userId');
         $reportId = $request->get('reportId');
         $report = $em->getRepository('ProgracqteurWikipedaleBundle:Model\\Report')->find($reportId);

         if (md5($report->getSalt() . $userId) ===  $request->get('APIKey')) {
            $user = $em->getRepository('ProgracqteurWikipedaleBundle:Management\\User')->find($userId);
         } else {
            throw new AccessDeniedException("L'APIKey n'est pas valide");
         }

         $userRoles =  $this->get('security.role_hierarchy')->getReachableRoles($user->getRoles());
         $userStringRoles = array_map("getRole", $userRoles);

         $userHasRole = function ($role) use ($userStringRoles) {
            return in_array($role, $userStringRoles);
         }

      // User must be identified
      } else if ($this->get('security.context')->getToken()->getUser() instanceof User) {
         $user = $this->get('security.context')->getToken()->getUser();

         $userHasRole = function ($role) {
            return $this->get('security.context')->isGranted(User::ROLE_COMMENT_MODERATOR_MANAGER);
         }

      // Otherwise error
      } else {
         throw new AccessDeniedException('Vous devez être un enregistré pour ajouter / modifier un commentaire');
      }

      $serializedJson = $request->get('entity');

      if ($serializedJson === null) {
         $r = new Response("Aucune entitée envoyée"); //TODO: i18n
         $r->setStatusCode(406, 'bad json');
         return $r;
      }

      $serializer = $this->get('progracqteurWikipedaleSerializer');

      $comment = $serializer->deserialize($serializedJson, 
         NormalizerSerializerService::COMMENT_TYPE, $_format);

      $comment->setCreator($user);

      if ($comment->getType() === Comment::TYPE_MODERATOR_MANAGER) {
         if ($userHasRole(User::ROLE_COMMENT_MODERATOR_MANAGER)) {
         //if ($this->get('security.context')->isGranted(User::ROLE_COMMENT_MODERATOR_MANAGER)) {
             //ok, may add a comment
         } else {
             return $this->getNotAuthorizedResponse("security.not_authorized.comment_of_type ".$comment->getType());
         }
      }

      if (! $userHasRole(User::ROLE_NOTATION)) {
      //if (! $this->get('security.context')->isGranted(User::ROLE_NOTATION)) {
         throw new \Exception("Vous n'avez pas le droit d'ajouter un commentaire");
      }

      $errors = $this->get('validator')->validate($comment);

      if ($errors->count() > 0) {  
         if ($_format === 'json') {
            $str = array();
         } else {
            $str = '';
         }
         
         foreach($errors as $error) {
            if ($_format === 'json') {
               $str[] = $error->getMessage(); 
            } else {
               $str .= $error->getMessage().' ';
            }
         }
         
         if ($_format === 'json') {
            $str = json_encode($str);
         }

         $r = new Response($str);
         $r->setStatusCode(400);
         return $r;
      }

      $em->persist($comment);

      $comment->getReport()->getChangeset()->setAuthor($user);

      $em->flush();
        
      return $this->redirect($this->generateUrl('wikipedale_comment_view',
         array('commentId' => $comment->getId(), '_format' => $_format)));
   }
   
   /**
    * Display a comment
    * @param integer $commentId The id of the comment
    * @param string $_format The format of the response (actually only json)
    * @return (Output format given by $_format) The comment
    */
   public function viewAction($commentId, $_format)
   {
      $em = $this->getDoctrine()->getManager();

      $comment = $em->getRepository('ProgracqteurWikipedaleBundle:Model\\Comment')
             ->find($commentId);

      if ($comment === null) {
         throw $this->createNotFoundException("comment with id $commentId not found");
      }

      switch ($comment->getType()) {
         case Comment::TYPE_MODERATOR_MANAGER:
            if ( ! $this->get('security.context')->isGranted(User::ROLE_COMMENT_MODERATOR_MANAGER)) {
               throw new AccessDeniedException('security.comment.must_have_role '.User::ROLE_COMMENT_MODERATOR_MANAGER);
            }
      }

      $serializer = $this->get('progracqteurWikipedaleSerializer');

      $rep = new NormalizedResponse(array($comment));

      $text = $serializer->serialize($rep, $_format);

      switch($_format) 
      {
         case 'json' : 
            return new Response($text);
            break;
         default:
            $r = new Response('format demandé indisponible');
            $r->setStatusCode(400);
            return $r;
      }
   }

   /**
    * Get an error 403 response
    * @return A 403 error
    */
   private function getNotAuthorizedResponse($text = "security.not_allowed") {
      $r = new Response($text);
      $r->setStatusCode(403);
      return $r;
   }
}

