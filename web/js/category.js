/* jslint vars: true */
/*jslint indent: 3 */
/* global define */
'use strict';

/**
* Dealing with the categories :
* - storage in JS
*/

define(['jQuery'], function($) {
   var buffered_caterories; // the known categories


   function getAll(callback) {
      /**
      * Gets all the categories
      */

      if (buffered_caterories) {
         callback(buffered_caterories);
      } else {
         $.get(Routing.generate('public_category_list_all_parent_children', {_format: 'json'}), function( data ) {
            console.log(data);
            console.log(data.results);
            if(! data.query.error) {
               buffered_caterories = data.results;
            } else {
               console.log(data.query);
               buffered_caterories = null;
            }

         console.log("--");
         console.log(data.results);
         console.log(buffered_caterories);
         callback(buffered_caterories);
         });
      }
   }

   return {
      getAll: getAll
   };
});