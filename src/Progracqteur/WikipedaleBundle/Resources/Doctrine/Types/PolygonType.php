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
        return Polygon::fromGeoJson($value);
    }
    
    /**
     * Gets the name of this type.
     * @see http://www.doctrine-project.org/api/dbal/2.2/class-Doctrine.DBAL.Types.Type.html
     */
    public function getName()
    {
        return self::NAME;
    }
    
    /**
     * Converts a value from its PHP representation to its database representation of this type.
     * @see http://www.doctrine-project.org/api/dbal/2.2/class-Doctrine.DBAL.Types.Type.html
     */
    public function convertToDatabaseValue($polygon, AbstractPlatform $platform)
    {        
        return $polygon->toWKT();
    }
    
    /**
     * Does working with this column require SQL conversion functions?
     * @see http://www.doctrine-project.org/api/dbal/2.2/class-Doctrine.DBAL.Types.Type.html
     */
    public function canRequireSQLConversion()
    {
        return true;
    } 
    
    /**
     * Modifies the SQL expression (identifier, parameter) to convert to a database value.
     * @see http://www.doctrine-project.org/api/dbal/2.2/class-Doctrine.DBAL.Types.Type.html
     */
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        return $sqlExpr;
    }
    
    /**
     * Modifies the SQL expression (identifier, parameter) to convert to a PHP value.
     * @see http://www.doctrine-project.org/api/dbal/2.2/class-Doctrine.DBAL.Types.Type.html
     */
    public function convertToPHPValueSQL($sqlExpr, $platform)
    {
        return 'ST_AsGeoJSON('.$sqlExpr.') ';
    }
}

