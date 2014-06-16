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
   var r = {}; //all the reports // object pour ne pas remplir le tableau de cellules vides
   var min_timestamp = new Date().getTime(); //the minimal timestamp over all the reports
   var max_timestamp = 0; //the maximal timestamp over all the reports
   var managers_list = {}; //list of the mangers //all the reports // object pour ne pas remplir le tableau de cellules vides


   function setManagers(m) {
      /**
       * Set the manager m in the managers_list
       * @param {manager (group)} m The manager to add
       */
       var id_manager;

       if(m.manager) {
         var id_manager = parseInt(m.manager.id);
         if(!(id_manager in managers_list)) {
            managers_list[id_manager] = m;
         }
      }
   }

   function getAllManagers() {
      /**
       * Returns the list of the managers.
       * @return {Array of group} The list of the managers.
       */
       return managers_list;
   }

   function updateAll(new_reports_in_json, action_after_update) {
      /**
      * Updates the local data. r[[]]
      * @param {array of reports} new_reports_in_json It is a 
      * @param {function} action_after_update a function to be executed after updating the reports. can be null if not execute
      json object containing all the report
      */
      r = {};
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
      setManagers(a_report);

      if (min_timestamp > a_report.createDate.u) {
         min_timestamp = a_report.createDate.u;
      }

      if (max_timestamp < a_report.createDate.u) {
         max_timestamp = a_report.createDate.u;
      }
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

   function getMinTimestamp() {
      /**
      * Gets the minimal timestamp over all the reports
      */
      return min_timestamp;
   }

   function getMaxTimestamp() {
      /**
      * Gets the maximal timestamp over all the reports
      */
      return max_timestamp;
   }

   function eraseAll(){
      /**
      * Remove all the reports
      */
      r = {};
      min_timestamp = new Date().getTime();
      max_timestamp = 0;
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
       * @return {\{-1,0,1,2,3\}} The status : -1 (gray) = rejected, ... ,3 (green) resolved
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
      getMinTimestamp: getMinTimestamp,
      getMaxTimestamp: getMaxTimestamp,
      getAll: getAll,
      eraseAll: eraseAll,
      erase: erase,
      getStatus: getStatus,
      getAllManagers: getAllManagers
   };
});
