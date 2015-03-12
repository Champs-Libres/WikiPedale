<?php

namespace Progracqteur\WikipedaleBundle\Resources\Doctrine\Types;

use Progracqteur\WikipedaleBundle\Resources\Geo\Polygon;
use Doctrine\DBAL\Types\Type; 
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * A Type for Doctrine to implement the Geography Polygon type
 * implemented by Postgis on postgis+postgresql databases
 *
 * @author Champs-Libres COOP
 */
class PolygonType extends Type
{
    const NAME = 'polygon';
    
    /**
     * Return the sql declaration
     * 
     * @param array $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return type 
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'geography(POLYGON,'.Polygon::$SRID.')';
    }
    
    /**
     * Return convert the element into a Php object
     * 
     * @param type $value
     * @param AbstractPlatform $platform
     * @return Polygon 
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value; //Polygon::fromGeoJson($value);
    }
    
    public function getName()
    {
        return self::NAME;
    }
    
    public function convertToDatabaseValue($polygon, AbstractPlatform $platform)
    {        
        return $polygon->toWKT();
    }
    
    public function canRequireSQLConversion()
    {
        return false;
    }    
}

