<?php

namespace Progracqteur\WikipedaleBundle\Resources\Services\Notification;

use Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription;
use Symfony\Component\Translation\Translator;
use Progracqteur\WikipedaleBundle\Entity\Management\User;
use Progracqteur\WikipedaleBundle\Entity\Model\Report;
use Progracqteur\WikipedaleBundle\Resources\Security\ChangeService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\RouterInterface;
use Progracqteur\WikipedaleBundle\Entity\Management\Notification\PendingNotification;

/**
 * A service which transform a Notification to a text
 * This service is adapted for email.
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class ToTextMailSenderService {
   
   /**
    * the translator, stored by the constructor
    * @var Symfony\Component\Translation\Translator 
   */
   private $t;

   private $array;

   private $date_format;

   /**
   *
   * @var Doctrine\Common\Persistence\ObjectManager 
   */
   private $om;

   /**
   *
   * @var Symfony\Component\Routing\RouterInterface 
   */
   private $router;

   private $exceptions = array();



   const DOMAIN = 'notifications';

   public function __construct(Translator $translator, array $moderatorArray, array $managerArray, 
      $date_format, ObjectManager $om, RouterInterface $router)
   {
      $this->t = $translator;
      $this->array[NotificationSubscription::KIND_MODERATOR] = $moderatorArray;
      $this->array[NotificationSubscription::KIND_MANAGER] = $managerArray;
      $this->array[NotificationSubscription::KIND_PUBLIC_REPORT] = NotificationFilterBySubscriptionPublicReport::$authorizedChangesToBeNotified;
      $this->date_format = $date_format;
      $this->om = $om;
      $this->router = $router;
   }

   /**
   * 
   * @return \Progracqteur\WikipedaleBundle\Resources\Services\Notification\SendPendingNotificationException[]
   */
   public function getExceptionsAndReset()
   {
      return $this->exceptions;
   }

   /**
   * 
   * @param Progracqteur\WikipedaleBundle\Entity\Management\Notification\PendingNotification[] $notifications
   * @param \Progracqteur\WikipedaleBundle\Entity\Management\User $owner
   * @return string
   */
   public function transformToText($notifications, User $owner)
   {
      echo "TOTEXTMAIL : écriture du mail pour l'utilisateur ".$owner->getLabel()."\n";
     
     //prepare intro text
     //
     //get notification subscription
     /*
      * array to prevent for adding a subscription twice
      */
      $previousNotificationSubscriptionIds = array();

      $notificationSubscriptions = array();

      foreach ($notifications as $notification) {
         if (!in_array($notification->getSubscription()->getId(),
            $previousNotificationSubscriptionIds)) {
            $notificationSubscriptions[] = $notification->getSubscription();
            $previousNotificationSubscriptionIds[] = $notification->getSubscription()->getId();
         }
      }
     
      //prepare the list of notifications inside the email:
      $subText = '';
      foreach ($notificationSubscriptions as $notificationSubscription) {
         $subText .= '- ';
         
         if ($notificationSubscription->getGroup() !== null) {
             
            $subText .= $this->t->trans('mail.subscriptions.group',
               array('%groupe%' => $notificationSubscription->getGroup()->getName()),
               self::DOMAIN
            );
         } else {
            if ($notificationSubscription->getZone() !== null) {
               $subText .= $this->t->trans('mail.subscriptions.zone',
                  array('%zone%' => $notificationSubscription->getZone()->getName()),
                  self::DOMAIN
               );
            } else {
               if ($notificationSubscription->getReport() !== null) {
                  $subText .= $this->t->trans('mail.subscriptions.report',
                     array('%report%' => $notificationSubscription->getReport()->getLabel()),
                     self::DOMAIN
                  );
               }
            }
         }
      }
     
     $subText .= "\n";
     
      if ($owner->isVirtual() == false ) {
         $t = $this->t->trans('mail.intro_text_registered', array(
            '%dest%' => $owner->getLabel(),
            '%subscriptions%' => $subText,
            '%updateNotificationUrl%' =>
               $this->router->generate('wikipedale_notification_subscriptions_list', array(), true)),
            self::DOMAIN
         );
      } else {
         $t = $this->t->trans('mail.intro_text_virtual', array(
            '%dest%' => $owner->getLabel(),
            '%subscriptions%' => $subText,
            '%updateNotificationUrl%' => 
               $this->router->generate('wikipedale_notification_subscriptions_list', array(), true)),
            self::DOMAIN
         );
     }
     $t.= "\n \n";
     
     //sort by report
     
     /**
      * array to sort reporttrackings by report and date
      */
     $a = array();
     
     foreach ($notifications as $notification) {
         $u = (int) $notification->getReportTracking()->getDate()->format('U');
         $a[$notification->getReportTracking()->getReport()->getId()][$u] = $notification;
      }
     
      //prefix for changes items :
      $prefix = '- ';
     
      //create a string for each report
      foreach ($a as $reportId => $notifications_) {
         echo "TOTEXTMAIL : traitement du signalement ".$reportId."\n";
         
         $headerShow = false;
         foreach($notifications_ as $timestamp => $notification) {
            try {
               $reporttracking = $notification->getReportTracking();
                
               //show the header if this is the first element of the report
               if ($headerShow === false) {
                  $t .= "**".$this->t->trans('mail.report.header', 
                     array('%label%' => $reporttracking->getReport()->getLabel()), 
                     self::DOMAIN
                     )."** \n \n";

                     $headerShow = true;
               }

               echo "TOTEXTMAIL : traitement de la reporttracking ".$reporttracking->getId()."\n";
               $args = array(
                  '%author%' => $reporttracking->getAuthor()->getLabel(),
                  '%label%' => $reporttracking->getReport()->getLabel(),
                  '%date%' => $reporttracking->getDate()->format($this->date_format)
               );

               if ($reporttracking->isCreation())
               {
                  $t .= $prefix . $this->t->trans('mail.report.creation', 
                          $args,
                          self::DOMAIN
                          );
                  $t .= "\n";
                  continue; //go to next event
               }

               $keyChanges = array();
               foreach ($reporttracking as $change) {
                  $keyChanges[$change->getType()] = $change;
               }
                    
               //if the change is add a photo (do not consider other changes)
               if (isset($keyChanges[ChangeService::REPORT_ADD_PHOTO])) {
                  $t .= $prefix . $this->t->trans('mail.report.add_photo', $args, self::DOMAIN);
                  $t .= "\n";
                  continue;
               }

               //if the change concern the status of the report
               if (isset($keyChanges[ChangeService::REPORT_STATUS])) {
                  $status = $keyChanges[ChangeService::REPORT_STATUS]->getNewValue();
                  $args['%notation%'] = $this->om
                          ->getRepository('ProgracqteurWikipedaleBundle:Management\Notation')
                          ->find($status->getType());

                  $t .= $prefix;

                  switch ($status->getValue())
                  {
                      case -1 : 
                          $t .=  $this->t->trans('mail.report.status.rejected', 
                                  $args, self::DOMAIN);
                          break;
                      case 0 :
                          $t .=  $this->t->trans('mail.report.status.notReviewed', 
                                  $args, self::DOMAIN);
                          break;
                      case 1 :
                          $t .=  $this->t->trans('mail.report.status.takenIntoAccount', 
                                  $args, self::DOMAIN);
                          break;
                      case 2 :
                          $t .=  $this->t->trans('mail.report.status.inChange', 
                                  $args, self::DOMAIN);
                          break;
                      case 3 :
                          $t .=  $this->t->trans('mail.report.status.success', 
                                  $args, self::DOMAIN);
                          break;
                  }

                  $t .= "\n";
               }

               //if the author added a private comment
               if (isset($keyChanges[ChangeService::REPORT_COMMENT_MODERATOR_MANAGER_ADD])) {
                  $t .= $prefix . $this->t->trans('mail.report.comment.private_add',
                          $args, self::DOMAIN);
                  $t .= "\n";

                  //retrieve the comment
                  $comment = $this->om
                          ->getRepository('ProgracqteurWikipedaleBundle:Model\Comment')
                          ->find($keyChanges[ChangeService::REPORT_COMMENT_MODERATOR_MANAGER_ADD]
                                  ->getNewValue());

                  if ($comment !== null) {
                      $t .= ">".$comment->getContent();
                      $t .= "\n";
                  }
               }


               //if a manager was assigned
               if (isset($keyChanges[ChangeService::REPORT_MANAGER_ADD])
                      OR isset($keyChanges[ChangeService::REPORT_MANAGER_ALTER])) {
                  if (isset($keyChanges[ChangeService::REPORT_MANAGER_ADD])) {
                     $temp_ch = $keyChanges[ChangeService::REPORT_MANAGER_ADD];
                  }
                  elseif (isset($keyChanges[ChangeService::REPORT_MANAGER_ALTER])) {
                     $temp_ch = $keyChanges[ChangeService::REPORT_MANAGER_ALTER];
                  }

                  $manager = $this->om
                     ->getRepository('ProgracqteurWikipedaleBundle:Management\Group')
                     ->find($temp_ch->getNewValue());

                  //if the manager is the actual owner of the notification
                  $groups = $owner->getGroups();
                  $groupIds = array();

                  foreach ($groups as $group) {
                      $groupIds[] = $group->getId();
                  }

                  if (in_array($manager->getId(), $groupIds)){
                      $t.= $prefix . $this->t->trans('mail.report.manager.you', $args, self::DOMAIN);
                  } else {
                      $args['%manager%'] = $manager->getName();
                      $t.= $prefix . $this->t->trans('mail.report.manager.add', $args, self::DOMAIN);
                  }

                  unset($temp_ch);

                  $t.= "\n";
               }

               if (isset($keyChanges[ChangeService::REPORT_MANAGER_REMOVE])) {
                  $t.= $prefix . $this->t->trans('mail.report.manager.remove', $args, self::DOMAIN);
                  $t.="\n";
               }


               //if the changes are other : 

               //count the changes
               $nb = 0;
               $key_not_to_track = array(ChangeService::REPORT_CREATION, 
                  ChangeService::REPORT_STATUS, ChangeService::REPORT_ADD_PHOTO,
                  ChangeService::REPORT_MANAGER_ADD, ChangeService::REPORT_MANAGER_ALTER,
                  ChangeService::REPORT_MANAGER_REMOVE, 
                  ChangeService::REPORT_COMMENT_MODERATOR_MANAGER_ADD);
               $changes = array();

               foreach($keyChanges as $key => $value) {
                  if (in_array($key, 
                        $this->array[$notification->getSubscription()->getKind()]) &&
                     !in_array($key, $key_not_to_track)) {
                     $nb++;
                     $changes[] = $value;
                  } else {
                     echo "TOTEXTMAIL : change $key not selected or previously processed \n";
                  }
               }

               //if only one : 
               if ($nb == 1) {
                  $args['%change%'] = 
                     $this->getStringFromChangeType($changes[0]->getType());
                  $t .= $prefix . $this->t->trans('mail.report.change.one', $args, self::DOMAIN);
                  $t .= "\n";
               } elseif ($nb == 2) {
                  $args['%change_%'] =  $this->getStringFromChangeType($changes[0]->getType());
                  $args['%change__%'] = $this->getStringFromChangeType($changes[1]->getType());
                  $t .= $prefix . $this->t->trans('mail.report.change.two', $args, self::DOMAIN);
                  $t .= "\n";
               } elseif ($nb > 2) {
                  $args['%change_%'] = 
                       $this->getStringFromChangeType($changes[0]->getType());
                  $args['%change__%'] = 
                       $this->getStringFromChangeType($changes[1]->getType());
                  $more = $nb - 2;
                  $args['%more%'] = $more;
                  $t .=  $prefix . $this->t->transChoice('mail.report.change.more', $more, $args, self::DOMAIN);
                  $t .= "\n";
               }
            } catch (\Exception $e) {
               $exception = new SendPendingNotificationException($notification,
                  'error during transformation to text of the reporttracking', 0, $e);
               $this->exceptions[] = $exception;
            }
         }

         $t .= "\n\n";

         //if moderator or manager, add a direct link for adding a comment
         $currentNotification = current($notifications_);
         $ownerGroups = $owner->getGroups();
         $ownerGroupsId =  $ownerGroups->map(function ($uG) { return $uG->getId(); });
         $report = $currentNotification->getreportTracking()->getReport();
         if ($ownerGroupsId->contains($report->getManager()->getId())) {
            $t .= $this->t->trans(
               'mail.comment.add_with_apikey', 
               array(
                  '%lien%' => $this->router->generate(
                     'wikipedale_comment_add_with_api_key',
                     array('APIKey' => $report->getAddingCommentAPIKey($owner->getId()),
                        'reportId' => $report->getId(), 
                        'userId' => $owner->getId()
                     ),
                     true
                  )
               ),
               self::DOMAIN
            );
         }

         $t .= "\n\n";

         $t .= $this->addReportPresentation($reporttracking->getReport());
         $t .= "\n\n\n\n\n\n\n\n";
      }
     
      $t .= $this->t->trans('mail.footer_text', array(), self::DOMAIN);
     
      return $t;
   }

   private function getStringFromChangeType($type)
   {
     //domain of the translations
     $d = self::DOMAIN;
     
     switch ($type)
     {
         case ChangeService::REPORT_ADDRESS :
             return $this->t->trans('mail.report.change.item.address' , array(), $d);
             break;
         case ChangeService::REPORT_DESCRIPTION:
             return $this->t->trans('mail.report.change.item.description', array(), $d);
             break;
         case ChangeService::REPORT_GEOM:
             return $this->t->trans('mail.report.change.item.geom', array(), $d);
             break;
         case ChangeService::REPORT_CATEGORY:
         case ChangeService::REPORT_ADD_CATEGORY: //depreciated
         case ChangeService::REPORT_REMOVE_CATEGORY: //depreciate
             return $this->t->trans('mail.report.change.item.category', array(), $d);
             break;
         case ChangeService::REPORT_REPORTTYPE_ALTER:
             return $this->t->trans('mail.report.change.item.report_type', array(), $d);
         case ChangeService::REPORT_MODERATOR_COMMENT_ALTER:
             return $this->t->trans('mail.report.change.item.moderator_comment', array(), $d);
         default:
             return $this->t->trans('mail.report.change.item.other', array(), $d);
      }
   }

   public function addReportPresentation(Report $report) {
      $t = '';
     
      $t.= $this->t->trans('mail.report.presentation.actual',
         array(
           '%label%' => $report->getLabel(),
           '%id%' => $report->getId()
         ), self::DOMAIN);
     
      $t.="\n\n";
      $t.= $this->t->trans('mail.report.presentation.description_header', array(), self::DOMAIN);
      $t.="\n";
      $t.= '>'.$report->getDescription();
      $t.="\n";
     
      $t.= $this->t->trans('mail.report.presentation.introduced_by_when', 
         array(
           '%creator%' => $report->getCreator()->getLabel(),
           '%create_date%' => $report->getCreateDate()->format($this->date_format)
         ), self::DOMAIN);
     
      $t.="\n";
     
      if ($report->getModeratorComment() != '') {
         $t.= $this->t->trans('mail.report.presentation.moderator_comment_header',
                 array(), self::DOMAIN);

         $t.= "\n";
         $t.= '>'.$report->getModeratorComment();
         $t.= "\n";
      }
     
      $t.= $this->t->trans('mail.report.presentation.category',
            array('%category%' => $report->getCategory()->getLabel()), self::DOMAIN);
     
      $t.="\n";
     
      if ($report->getManager() === null) {
         $managerLabel = $this->t->trans(
            'mail.report.presentation.no_manager',
            array(), self::DOMAIN);
      } else {
         $managerLabel = $report->getManager()->getName();
      }
     
      $t.= $this->t->trans('mail.report.presentation.manager', array(
         '%manager_label%' => $managerLabel
      ), self::DOMAIN);
             
      $t.="\n";
     
      $t.= $this->t->trans('mail.report.presentation.link', array(
         '%url%' => $this->router->generate('wikipedale_homepage', array(
                 'id' => $report->getId()
             ), true)
      ), self::DOMAIN);
        
      return $t;
   }
}