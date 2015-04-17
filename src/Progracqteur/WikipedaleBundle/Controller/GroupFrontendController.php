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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Progracqteur\WikipedaleBundle\Resources\Container\NormalizedResponse;
use Progracqteur\WikipedaleBundle\Entity\Management\Group;

/**
 * Controller for the groups (set of user that can manage / moderate a zone
 */
class GroupFrontendController extends Controller
{
    /**
     * Return, in JSON, the groups for a given zone and for a given type
     * of group (moderator, manager)
     * 
     * @param type $slugZone The slug of the zone
     * @param type $type The type of the group
     * @param type $_format The format (only 'json')
     * @param Request $request
     * @return Response A JSON object conainy the groups of a given type) for a 
     * given zone
     * @throws Exception if the slug of the zone is not registered in db.
     */
    public function getGroupCoveringZoneAction($slugZone, $type, $_format, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $zone = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Zone')
                ->findOneBy(array('slug' => $slugZone));
        
        if ($zone === null) {
            throw $this->createNotFoundException(
                "The zone $slugZone does not match any zone");
        }

        $groups = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Group')
            ->findBy(array('type' => $type, 'zone' => $zone));
        
        switch ($_format) {
            case 'json' : 
                $serializer = $this->get('progracqteurWikipedaleSerializer');
        
                $nr = new NormalizedResponse();
                $nr->setResults($groups);
                
                return new Response($serializer->serialize($nr, 'json'));
            default:
                $a =  new Response('format invalid');
                $a->setStatusCode(400);
                return $a;
        }
    }
    
    /**
     * Return, in JSON, the groups of a given type (moderator, manager)
     * 
     * @param type $type The given type (moderator, manager)
     * @param type $_format The format (only 'json')
     * @return Response A JSON object containing the groups of the given type
     * of a given type)
     * @throws Exception if the type is not (moderator or manager)
     */
    public function listByTypeAction($type, $_format = 'json')
    {
        if ($_format !== 'json') {
            $response = new Response("The format $_format is not allowed");
            $response->setStatusCode(406);
            return $response;
        }
        
        if (! in_array(mb_strtoupper($type), array(Group::TYPE_MODERATOR, Group::TYPE_MANAGER, Group::TYPE_NOTATION))) {
            throw $this->createNotFoundException("'$type' not found");
        }
    
        $em = $this->getDoctrine()->getManager();
        $moderators = $em->getRepository('ProgracqteurWikipedaleBundle:Management\Group')
            ->findBy(array('type' => mb_strtoupper($type)));
    
        $normalizer = $this->get('progracqteurWikipedaleSerializer');
        $response = new NormalizedResponse($moderators);
    
        return new Response($normalizer->serialize($response, 'json'));
    }
}


