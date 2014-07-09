<?php

namespace Progracqteur\WikipedaleBundle\Resources\Services;

/**
 * transform an string to a slug.
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class SlugService {    
   /**
    * 
    * @param string $string
    * @return string slug form of the string
    */
   public function slug($string)
   {
      $string = trim($string);
      $string = mb_strtolower($string,'utf8');

      $a = array('à','á','â','ã','ä','å','æ', 'ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','ÿ','Œ', 'œ', 'Š','š','Ÿ','Ž','ž','ƒ', ' ');
      $b = array('a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y','OE','oe','S','s','Y','z','Z','f', '-');
      $string = str_replace($a, $b, $string);

      // replace non letter or digits by -
      $string = preg_replace('/\'/', '', $string);

      return $string;
   }
}