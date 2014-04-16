/* jslint vars: true */
/*jslint indent: 3 */
/* global define */
'use strict';

/**
* Dealing with the categories :
* - storage in JS
*/

define(['jQuery'], function($) {
   var buffer; // the known categories

   function updateAll(new_categories) {
      /**
      * Update the categories buffer
      * @param {array of categories parent/children} new_categories The new categories
      */

      buffer = new_categories;
   }

   function getAll() {
      /**
      * Gets all the categories
      */
      return buffer;
   }

   return {
      updateAll: updateAll,
      getAll: getAll
   };
});