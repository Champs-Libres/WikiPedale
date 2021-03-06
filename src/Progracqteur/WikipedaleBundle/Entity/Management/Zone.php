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

namespace Progracqteur\WikipedaleBundle\Entity\Management;

/**
 * Progracqteur\WikipedaleBundle\Entity\Management\Zone
 */
class Zone {

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $slug
     */
    private $slug;

    /**
     * @var string $codeProvince
     * TODO : remove this field: unused !
     */
    private $codeProvince;

    /**
     * @var polygon $polygon
     */
    private $polygon;

    /**
     *
     * @var point $center;
     */
    private $center;

    /**
     *
     * @var string
     */
    private $type;

    /**
     *
     * @var string
     */
    private $url = '';

    /**
     *
     * @var string
     */
    private $description = '';

    /** @var string The fill color for displaying the zone on the map */
    private $fillColor;

    /**
     * Type of zone "city"
     * 
     * @var string
     */
    const TYPE_CITY = 'city';
    const TYPE_SPW = "spw";

    /**
     * Set id
     *
     * @param integer $id
     * @return Zone
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Zone
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Zone
     */
    public function setSlug($slug) {
        $this->slug = $slug;
        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug() {
        return $this->slug;
    }

    /**
     * Set codeProvince
     *
     * @param string $codeProvince
     * @return Zone
     */
    public function setCodeProvince($codeProvince) {
        $this->codeProvince = $codeProvince;
        return $this;
    }

    /**
     * Get codeProvince
     *
     * @return string 
     */
    public function getCodeProvince() {
        return $this->codeProvince;
    }

    /**
     * Set polygon
     *
     * @param polygon $polygon
     * @return Zone
     */
    public function setPolygon($polygon)
    {
        $this->polygon = $polygon;
        return $this;
    }

    /**
     * Get polygon
     *
     * @return polygon 
     */
    public function getPolygon()
    {
        return $this->polygon;
    }
    
    /**
     * Set the center of the zone
     * 
     * @param point $center
     * @return Zone The updated zone
     */
    public function setCenter($center)
    {
        $this->center = $center;
        return $this;
    }

    /**
     *
     * @return point
     */
    public function getCenter()
    {
        return $this->center;
    }

    public function __toString() {
        if ($this->getType() === self::TYPE_CITY) {
            return "Commune de " . $this->getName();
        } else {
            return "District SPW de " . $this->getName();
        }
    }

    /**
     * Type may be : 
     * 
     * - city
     * 
     * @return type The type of the zone
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Set the type of the Zone.
     * 
     * @param type $type
     * @return \Progracqteur\WikipedaleBundle\Entity\Management\Zone
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * Return the URL of the website of the zone
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Return a description of the zone. This description should be shown
     * to the public.
     * 
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set the description of the Zone. The description is shown to the public.
     * 
     * @param string $description
     * @return \Progracqteur\WikipedaleBundle\Entity\Management\Zone
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }
    
    /**
     * Get the fill color used for displaying the zone on the map
     *
     * @return string The fill color
     */
    public function getFillColor() {
        return $this->fillColor;
    }

    /**
     * Set the fill color used for displaying the zone on the map
     *
     * @param string $fillColor The fill color
     * @return \Progracqteur\WikipedaleBundle\Entity\Management\Zone
     */
    public function setFillColor($fillColor) {
        $this->fillColor = $fillColor;
        return $this;
    }
    
}
