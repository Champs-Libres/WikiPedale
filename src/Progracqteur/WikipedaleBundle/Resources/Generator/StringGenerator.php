<?php

namespace Progracqteur\WikipedaleBundle\Resources\Generator;

/**
 * Functions for generating random string
 *
 * @author user
 */
class StringGenerator {
   public static $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

   /**
    * Generate a random String
    * @param $length the length of the returned string
    * @return A random string of lenght $length
    */
   public static function randomGenerate($length = 10) {
      $randomString = '';
         for ($i = 0; $i < $length; $i++) {
            $randomString .= self::$characters[rand(0, strlen(self::$characters) - 1)];
         }
      return $randomString;
   }
}