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
use Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizerSerializerService;
use Progracqteur\WikipedaleBundle\Entity\Management\UnregisteredUser;
use Progracqteur\WikipedaleBundle\Resources\Container\NormalizedResponse;

/**
 * Controller that manages the user session. This session contains information
 * about the selected zone (or not) and the current logged user (or not)
 *
 * @author Champs-Libres COOP
 */
class ManagerController extends Controller
{    
    /**
     * Returns a json data containing the current user. If the user is not logged, 
     * an empty UnregisteredUser is returned.
     *
     * @param string $_format The format. For the moment only JSON is supported.
     * @return Response The json data containing the actual user.
     * @throws \Exception If the format is not JSON
     */
    public function httpBasicAuthenticateAction($_format) {
        if ($_format != NormalizerSerializerService::JSON_FORMAT) {
            throw new \Exception("Le format demandé n'est pas disponible");
        }
        
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = new UnregisteredUser();
        } else {
            $user = $this->get('security.context')->getToken()->getUser();
        }
        
        $normalizedResponse = new NormalizedResponse();
        $normalizedResponse->setResults(array($user));
        
        $serializer = $this->get('progracqteurWikipedaleSerializer');
         
        $serializer->getUserNormalizer()->addGroupsToNormalization(true);
        
        return new Response($serializer->serialize($normalizedResponse, NormalizerSerializerService::JSON_FORMAT)); 
    }
}