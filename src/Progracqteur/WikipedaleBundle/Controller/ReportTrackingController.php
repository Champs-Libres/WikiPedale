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
use Progracqteur\WikipedaleBundle\Resources\Container\NormalizedResponse;
use Progracqteur\WikipedaleBundle\Entity\Management\User;

/**
 * 
 * @author Julien Fastré <julien arobase fastre point info>
 * @author Champs-Libres Coop
 */
class ReportTrackingController extends Controller {
    
    public function byCityAction($citySlugP, $_format, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $citySlug = $this->get('progracqteur.wikipedale.slug')->slug($citySlugP);
        if (! $citySlug) {
            throw new \Exception('Renseigner une ville dans une variable \'city\' ');
        }
        
        $city = $em->getRepository('ProgracqteurWikipedaleBundle:Management\\Zone')
            ->findOneBy(array('slug' => $citySlug));
        
        if (! $city) {
            throw $this->createNotFoundException("Aucune ville correspondant à $citySlug n'a pu être trouvée");
        }
        
        $max = $request->get('max', 20);
        if ($max > 100) {
            $response = new Response('limite du nombre de réponse dépassée. Maximum = 100');
            $response->setStatusCode(413);
            return $response;
        }
        
        $first = $request->get('first', 0);
        if ($first < 0) {
            $response = new Response('le paramètre first ne peut aps être négatif');
            $response->setStatusCode(400);
            return $response;
        }
        
        //check if the user may seen comments
        $private = false;
        if ($this->get('security.context')->isGranted(User::ROLE_COMMENT_MODERATOR_MANAGER)) {
            $private = true;
        } 
        
        $tracks = $em->getRepository('ProgracqteurWikipedaleBundle:Model\Report\ReportTracking')
            ->getLastEvents($first, $max, $city, $private);
        
        switch ($_format) {
            case 'json' :
                $r = new NormalizedResponse($tracks);
                $r->setLimit($max);
                $r->setStart($first);
                $normalizer = $this->get('progracqteurWikipedaleSerializer');
                return new Response($normalizer->serialize($r, $_format));
            case 'atom' :
                $r = $this->render('ProgracqteurWikipedaleBundle:History:reports.atom.twig', array(
                   'title' => $city->getName(),
                   'subtitle' => "Dernières mises à jour de la ville de ".$city->getName(),
                   'tracks' => $tracks,
                   'citySlug' => $city->getSlug(),
                   'toTextService' => $this->get('progracqteur.wikipedale.report.tracking.toText'),
                   'urlFeed' => $this->generateUrl('wikipedale_history_report_by_city', 
                           array('_format' => 'atom',
                               'citySlug' => $citySlug), true)
                ));
                return $r;
        }
    }
}

