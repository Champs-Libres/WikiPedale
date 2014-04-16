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
   var r, id_for; //see init for information

   function init() {
      /**
      * Initialize the variables used for the storage :
      * - r the reports
      * - id_for['Categories'][c_id] The report ids having c_id as category id
      * - id_for['PlaceTypes'][p_id] The report ids having p_id as placetype id
      * - id_for['StatusCeM'][s] The report ids having s as cem_status
      */
      r = {};
      id_for = {};
      id_for['Categories'] = {};
      id_for['PlaceTypes'] = {};
      id_for['StatusCeM'] = {};
      id_for['StatusCeM']['0'] = [];
   }


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
      var report_id = parseInt(a_report.id);

      if (r[report_id]) { // removing all the information about this report in id_for
         erase_id_for_data_relative_to(r[report_id]);
      }

      // Then adding the id in id_for regarding to the new_report
      if (typeof id_for['Categories'][parseInt(a_report.category.id)] === 'undefined') {
         id_for['Categories'][parseInt(a_report.category.id)] = [];
      }
      id_for['Categories'][parseInt(a_report.category.id)].push(report_id);

      if (a_report.placetype != null) {
         if (typeof id_for['PlaceTypes'][parseInt(a_report.placetype.id)] === 'undefined') {
            id_for['PlaceTypes'][parseInt(a_report.placetype.id)] = [];
         }
         id_for['PlaceTypes'][parseInt(a_report.placetype.id)].push(report_id);
      }

      var a_report_id_added_for_cem = false;
      $.each(a_report.statuses, function(index, type_value) {
         if (type_value.t == params.manager_color) {
            if (typeof id_for['StatusCeM'][type_value.v.toString()] === 'undefined') {
               id_for['StatusCeM'][type_value.v.toString()] = [];
            }
            id_for['StatusCeM'][type_value.v.toString()].push(report_id);
            a_report_id_added_for_cem = true;
         }
      });
      if (! a_report_id_added_for_cem) {
         id_for['StatusCeM']['0'].push(report_id);
      }

      r[report_id] = a_report;
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

   function erase_id_for_data_relative_to(a_report) {
      /**
      * Erases the data in the id_for variable relative to the report a_report
      * @param {a json report} a_report The report for which erasing the data.
      */
      var report_id = parseInt(a_report.id);
      var index_sig;

      index_sig = id_for['Categories'][parseInt(a_report.category.id)].indexOf(report_id);
      id_for['Categories'][parseInt(a_report.category.id)].splice(index_sig,1);

      $.each(a_report.statuses, function(i, stat) {
         if (stat.t == params.manager_color) {
            index_sig = id_for['StatusCeM'][stat.v].indexOf(report_id);
            id_for['StatusCeM'][stat.v].splice(index_sig,1);
         }
      });
      
      if (a_report.placetype != null) {
         index_sig = id_for['PlaceTypes'][parseInt(a_report.placetype.id)].indexOf(report_id);
         id_for['PlaceTypes'][parseInt(a_report.placetype.id)].splice(index_sig,1);
      }
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
      report_id = parseInt(report_id);
      erase_id_for_data_relative_to(r[report_id]);
      delete r[report_id];
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
   
   init();

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
