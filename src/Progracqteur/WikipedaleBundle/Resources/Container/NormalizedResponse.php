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

namespace Progracqteur\WikipedaleBundle\Resources\Container;

use Progracqteur\WikipedaleBundle\Entity\Management\User;

/**
 * Description of NormalizedResponse
 *
 * @author julien
 */
class NormalizedResponse {
    
    private $results;
    private $total;
    private $count = 0;
    private $start = 0;
    private $limit;
    private $user = null;
    
    
    public function __construct($results = null)
    {
        if ($results !== null)
            $this->setResults($results);
    }
    
    public function setResults($results)
    {
        $this->results = $results;
        if (is_array($results)) 
        {
            foreach ($results as $object)
            {
                $this->count++;
            }
        } else {
            $this->count = 1;
        }
    }
    
    public function setUser(User $user)
    {
        $this->user = $user;
    }
    
    public function hasUser()
    {
        if ($this->user === null) {
            return false;
        } else 
        {
            return true;
        }
    }
    
    public function getUser()
    {
        return $this->user;
    }
    
    public function setTotal($total)
    {
        $this->total = $total;
    }
    
    
    public function setStart($start)
    {
        $this->start = $start;
    }
    
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }
    
    public function getLimit()
    {
        return $this->limit;
    }
    
    public function getStart()
    {
        return $this->start;
    }
    
    public function getTotal()
    {
        return $this->total;
    }
    
    public function getCount()
    {
        return $this->count;
    }
    
    public function getResults()
    {
        return $this->results;
    }
}

