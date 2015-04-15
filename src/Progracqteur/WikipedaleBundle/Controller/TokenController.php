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

/**
 * Description of TokenController
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class TokenController extends Controller{
    
    
    public function insertTokensAction($number = 15)
    {
        $provider = $this->get('progracqteur.wikipedale.token_provider');
        
        $tokens = $provider->getTokens('ajax', $number);
        $string = json_encode($tokens);
        
        return $this->render('ProgracqteurWikipedaleBundle:Token:insertTokens.html.twig', 
                array('string' => $string));
    }
    
    
    public function getNewTokensAction($_format, Request $request)
    {
        /**
         * @var Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface
         */
        $provider = $this->get('progracqteur.wikipedale.token_provider');
        
        $token = $request->request->get('token', null);
        
        if ($token === null)
        {
            $r = new Response('missing parameter token');
            $r->setStatusCode(400);
            return $r;
        }
        
        if ($provider->isCsrfTokenValid('ajax', $token))
        {
            $number = $request->query->get('number', 15);
            $tokens = $provider->getTokens('ajax', $number);
            return new Response(json_encode($tokens));
        }
        else 
        {
            $r = new Response('token provided is not valid');
            $r->setStatusCode(400);
            return $r;
        }
        
    }
    
    
}

