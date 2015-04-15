<?php

/*
 *  Uello is a reporting tool. This file is part of Uello.
 * 
 *  Copyright (C) 2015, Champs-Libres Cooperative SCRLFS,
 *  <http://www.champs-libres.coop>, <info@champs-libres.coop>
 * 
 *  Uello is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Uello is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Uello.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Progracqteur\WikipedaleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Progracqteur\WikipedaleBundle\Entity\Management\User;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * Description of NotificationController
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class NotificationsController extends Controller {
    
    public function indexAction() {
        $user = $this->get('security.context')->getToken()->getUser();
        
        //if user is not connected
        if (!($user instanceof User)) {
            throw new AccessDeniedException('you must be registered for accessing this page');
        }
        
        $notifications = $user->getNotificationSubscriptions();
        
        return $this->render('ProgracqteurWikipedaleBundle:NotificationSubscriptions:list.html.twig', 
                array(
                    'notifications' => $notifications,
                    'user' => $user)
                );
    }
    
    public function showAction($id) {
        $user = $this->get('security.context')->getToken()->getUser();
        
        //if user is not connected
        if (!($user instanceof User)) {
            throw new AccessDeniedException('you must be registered for accessing this page');
        }
        
        
        $notification = $this->getDoctrine()->getManager()
                ->getRepository('ProgracqteurWikipedaleBundle:Management\NotificationSubscription')
                ->find($id);
        
        if ($notification === null ) {
            throw $this->createNotFoundException('notification not found');
        }
        
        if ($user->getId() !== $notification->getOwner()->getId()) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('you cannot modify notification belonging to other users');
        }
        
        $form = $this->createForm(
                $this->get('progracqteur.wikipedale.notification.corner')
                   ->getProcessor($notification->getKind())->getForm( 
                           $user,
                           $notification)
                , $notification)
              ;
        
        return $this->render('ProgracqteurWikipedaleBundle:NotificationSubscriptions:form.html.twig',
                array(
                    'form' => $form->createView(), 
                    'action' => 'update', 
                    'notification' => $notification,
                    'form_template' => $this->get('progracqteur.wikipedale.notification.corner')
                        ->getProcessor($notification->getKind())
                        ->getFormTemplate()
                ))
                
                ;
    }
    
    public function updateAction($id, \Symfony\Component\HttpFoundation\Request $request) {
        $user = $this->get('security.context')->getToken()->getUser();
        
        //if user is not connected
        if (!($user instanceof User)) {
            throw new AccessDeniedException('you must be registered for accessing this page');
        }
        
        
        $notification = $this->getDoctrine()->getManager()
                ->getRepository('ProgracqteurWikipedaleBundle:Management\NotificationSubscription')
                ->find($id);
        
        if ($notification === null ) {
            throw $this->createNotFoundException('notification not found');
        }
        
        if ($user->getId() !== $notification->getOwner()->getId()) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('you cannot modify notification belonging to other users');
        }
        
        $form = $this->createForm(
                $this->get('progracqteur.wikipedale.notification.corner')
                   ->getProcessor($notification->getKind())->getForm( 
                           $user,
                           $notification)
                , $notification)
              ;
        
        $form->bind($request);
        
        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            //TODO : add a message
            return $this->redirect(
                    $this->generateUrl('wikipedale_notification_subscriptions_list')
                    );
        } else {
            $this->forward('ProgracqteurWikipedaleBundle:Notifications:show', 
                    array('id' => $id)
                    );
        }
    }
    
    
}

