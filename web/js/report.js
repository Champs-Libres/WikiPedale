/* jslint vars: true */
/*jslint indent: 3 */
/* global define */
'use strict';

/**
* Dealing with the reports :
* - storage in JS
* - function to access easily to information
*/

// mettre des parseInt
define(['jQuery','params'], function($,params) {
   var r = {}; //all the reports

   function updateAll(new_reports_in_json, action_after_update) {
      /**
      * Updates the local data. r[[]]
      * @param {array of reports} new_reports_in_json It is a 
      * @param {function} action_after_update a function to be executed after updating the reports. can be null if not execute
      json object containing all the report
      */
      $.when(
         $.each(new_reports_in_json,
            function(index, a_report) {
               update(a_report);
            }
         )
      ).done( function() {
         if (action_after_update) {
            action_after_update();
         }
      });
   }

   function update(a_report){
      /**
      * Update a report
      * @param {a json reports} a_report The report to be updated. it is a
      json object containing all the information about the report
      */
      r[parseInt(a_report.id)] = a_report;
   }

   function get(an_id) {
      /**
      * Gets the report of id 'an_id'
      @param {int} an_id The relative id.
      */
      return r[parseInt(an_id)];
   }

   function getAll() {
      /**
      * Gets all the reports
      */
      return r;
   }

   function eraseAll(){
      /**
      * Remove all the reports
      */
      init();
   }

   function erase(report_id) {
      /**
      * Remove the report with id report_id
      * @param {int} dest_id The id of the report
      */
      delete r[parseInt(report_id)];
   }

   function getStatus(statusType, report, notFoundValue) {
      /**
       * Access to the status of a report for a given type
       * @param {String} statusType The name of the status that we want to access
       * @param {Report} desc The report
       * @param {AGivenData} notFoundValue The value to return if the type is not founded
      */
      var i, max;

      for(i = 0, max = report.statuses.length; i < max; i++) {
         if(report.statuses[i].t === statusType) {
            return parseInt(report.statuses[i].v);
         }
      }
      return notFoundValue;
   }

   return {
      updateAll: updateAll,
      update: update,
      get: get,
      getAll: getAll,
      eraseAll: eraseAll,
      erase: erase,
      getStatus: getStatus
   };
});
