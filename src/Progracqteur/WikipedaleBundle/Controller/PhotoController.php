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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Progracqteur\WikipedaleBundle\Resources\Container\NormalizedResponse;
use Progracqteur\WikipedaleBundle\Entity\Management\User;

/**
 * Description of PhotoController
 *
 * @author julien [arobase] fastre POINT info
 */
class PhotoController extends Controller 
{
    public function getPhotoByReportAction($_format, $reportId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $report = $em->getRepository("ProgracqteurWikipedaleBundle:Model\\Report")->find($reportId);
        
        if ($report === null) {
            throw $this->createNotFoundException("Le signalement $reportId n'a pas été trouvée");
        }
        
        $q = $em->createQuery("SELECT ph from ProgracqteurWikipedaleBundle:Model\\Photo ph 
            where ph.report = :report and ph.published = true")
                ->setParameter('report', $report);
        
        $photos = $q->getResult();
        
        switch($_format) {
            case 'json':
                $response = new NormalizedResponse();
                $response->setResults($photos);
                
                $serializer = $this->get('progracqteurWikipedaleSerializer');
                $string = $serializer->serialize($response, $_format);
                
                return new Response($string);
                break;
            default:
                throw new \Exception("le format $_format est inconnu");
        }
    }
    
    public function newAction($reportId, $_format, Request $request)
    {
        if (!$this->get('security.context')->getToken()->getUser() instanceof User) {
            throw new AccessDeniedException('Vous devez être un administrateur pour modifier une image');
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $report = $em->getRepository("ProgracqteurWikipedaleBundle:Model\\Report")->find($reportId);
        
        if ($report === null) {
            throw $this->createNotFoundException("Le signalement $reportId n'a pas été trouvée");
        }
        
        $photo = $this->get('progracqteurWikipedalePhotoService')->createPhoto();
        $photo->setReport($report);
        
        $security = $this->get('security.context');
        
        if ($security->isGranted('IS_AUTHENTICATED_FULLY')) {
            $photo->setCreator($security->getToken()->getUser());
        }
        
        $formPhotoType = $this->get('progracqteurWikipedale.form.type.photo');
        $formPhotoType->isForANewPhoto(true);
        
        $form = $this->createForm($formPhotoType, $photo);
        
        
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            
            $uploadedFile = $form['file']->getData();
            
            if (!$uploadedFile->isValid()) {
                $r = new Response("Erreur d'envoi de fichier. Vérifiez la taille du fichier. Erreur du fichier :"
                        .$uploadedFile->getError()." ".
                        $this->file_upload_error_message($uploadedFile->getError()));
                $r->setStatusCode(413);
                return $r;
            }
            
            if ($form->isValid()) { //TODO créer une formulaire de validation
                //enregistre l'utilisateur courant dans le tracking policy du signalement
                $photo->getReport()->getChangeset()->setAuthor(
                    $this->get('security.context')->getToken()->getUser()
                );
                
                $em->persist($photo);
                $em->flush();
                
                $this->get('session')->getFlashBag()->add('notice', "Votre photo a été correctement enregistrée.");
                
                if ($this->get('security.context')->isGranted('ROLE_NOTATION')) {
                    $path = 'wikipedale_photo_update';
                } else {
                    $path = 'wikipedale_photo_view';
                }
                
                return $this->redirect($this->generateUrl($path, array(
                    'fileNameP' => $photo->getFileName(),
                    'photoType' => $photo->getPhotoType(),
                    '_format' => $_format
                ))); //TODO renvoyer vers le chemin adéquat
            }
        }
        
        return $this->render('ProgracqteurWikipedaleBundle:Photo:form.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    public function viewAction($fileNameP, $photoType, $_format, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $photo = $em->getRepository('ProgracqteurWikipedaleBundle:Model\Photo')
                ->findOneBy(array('file' => $fileNameP.'.'.$photoType));
        
        if ($photo === null) {
            throw $this->createNotFoundException("La photo demandée n'a pas été trouvée");
        }
        
        return $this->render('ProgracqteurWikipedaleBundle:Photo:view.html.twig', array(
            'photo' => $photo,
        ));
    }
    
    public function updateAction($fileNameP, $photoType, $_format, Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_NOTATION')) {
            $this->get('session')->getFlashBag()->add('notice', "Vous devez être un administrateur pour modifier une image");
            throw new AccessDeniedException('Vous devez être authentifié pour modifier une image');
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $photo = $em->getRepository('ProgracqteurWikipedaleBundle:Model\Photo')
                ->findOneBy(array('file' => $fileNameP.'.'.$photoType));
        
        if ($photo === null) {
            throw $this->createNotFoundException("La photo demandée n'a pas été trouvée");
        }
        
        $form = $this->createForm(
                    $this->get('progracqteurWikipedale.form.type.photo'), $photo
                );
        
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            $photo->setPhotoService($this->get('progracqteurWikipedalePhotoService'));
            
            $uploadedFile = $form['file']->getData();
            if ($uploadedFile != null && !$uploadedFile->isValid()) {
                $error = $uploadedFile->getError();
                throw new \Exception("Le fichier envoyé n'est pas valide. Erreur : $error");
            }
            
            if ($form->isValid()) {
                $em->flush();
                
                $this->get('session')->getFlashBag()->add('notice', "La photo a été mise à jour");
                
                return $this->redirect($this->generateUrl('wikipedale_photo_update', array(
                    'fileNameP' => $photo->getFileName(),
                    'photoType' => $photo->getPhotoType(),
                    '_format' => $_format
                    
                    )));
            }
        }
        
        return $this->render('ProgracqteurWikipedaleBundle:Photo:form.html.twig', array(
            'form' => $form->createView(),
            'photo' => $photo
        ));
    }
    
    private function file_upload_error_message($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    } 
}

