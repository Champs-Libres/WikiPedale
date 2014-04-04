/* jslint vars: true */
/*jslint indent: 3 */
/* global define */
'use strict';

/**
* Some functions to deal with a description and access to its
* properties
*/

define([], function() {
   function get_status(statusType, desc, notFoundValue) {
   /**
    * Access to the status of a description for a given type
    * @param {String} statusType The name of the status that we want to access
    * @param {Description} desc The description
    * @param {AGivenData} notFoundValue The value to return if the type is not founded
   */
      var i, max;

      for(i = 0, max = desc.statuses.length; i < max; i++)
      {
         if(desc.statuses[i].t === statusType) {
            return parseInt(desc.statuses[i].v);
         }
      }
      return notFoundValue;
   }

   return {
      get_status: get_status
   };
});