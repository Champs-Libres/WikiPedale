/* jslint vars: true */
/*jslint indent: 3 */
/* global define, Routing */
'use strict';

/**
* This module is the glue between all the other modules. This leads to fundamental function
* for the application.
*/

define(['jQuery','report_map','report','description_text_display','user','informer','json_string','markers_filtering','ol'],
      function($,report_map,report,description_text_display,user,informer,json_string,markers_filtering,ol) {
   var townId = null;
   var last_description_selected = null;
   var add_new_place_mode = false; // true when the user is in a mode for adding new place

   function init_app(townId_param, town_lon, town_lat, marker_id_to_display) {
      /**
      * Init the application and the map.
      * @param {int} townId  The id of the town
      * @param {float} town_lon The longitude of the town
      * @param {float} town_lat The latitude of the town
      * @param {int} marker_id_to_display The id of the marker to display (direct access). It is optional
      * (none if no marker to display)
      */
      townId = townId_param;

      markers_filtering.initFormFor('manager');

      report_map.init(4.648801835937508, 50.20168148245898, 8, function() {
         markers_filtering.updateFormFor('manager');
         markers_filtering.displayMarkersRegardingToFiltering();
         if(add_new_place_mode) {
            report_map.unactivateMarkers();
         }
      });

      report_map.addClickReportEvent(focus_on_place_of);

      report_map.addZoomChangeEvent(function(evt) {
         var zoom = evt.map.getView().getZoom();

         if(zoom >= 13) {
            report_map.displayLayer('markers');
            report_map.hideLayer('cluster');
         } else {
            report_map.displayLayer('cluster');
            report_map.hideLayer('markers');
         }
      });
   }

   function update_data_and_map(){
      /**
      * Update the data of the app contained in report.js and re-draw the map
      * (regarding to the updated informations)
      */
      if (townId !== null) {
         report.eraseAll();

         var jsonUrlData  =  Routing.generate('wikipedale_report_list_by_city', {_format: 'json', city: townId});
         $.ajax({
            dataType: 'json',
            url: jsonUrlData,
            success: function(data) {
               report.updateAll(data.results,null);
            },
            complete: function() {
               var signalement_id = $('#input_report_description_id').val();
               if (typeof signalement_id !== 'undefined' && signalement_id !== '') {
                  // be sure that a place is selected
                  description_text_display.display_regarding_to_user_role();
               }
            }
         });
      }
   }

   function add_marker_and_description(report, anEventFunction) {
      /**
      * Add on the map a new description and store this description in the local saved data.
      * @param {object} aDescription The data describing the new description to add on the map.
      * @param {function} anEventFunction The function to execute when the user click on the marker
      */
      report_map.addReport(report);
   }

   function last_description_selected_reset() {
      /**
      * Resetting the private last_description_selected variable.
      */
      last_description_selected = null;
   }
               

   function last_description_selected_delete() {
      /**
      * Delete a description. The description deleted is the description
      * having its id in the private variable last_description_selected.
      * It is the last displayed description.
      */
      var json_request = json_string.delete_place(last_description_selected);
      var url_edit = Routing.generate('wikipedale_report_change', {_format: 'json'});
      $.ajax({
         type: 'POST',
         data: {entity: json_request},
         url: url_edit,
         cache: false,
         success: function(output_json) {
            if(! output_json.query.error) {
               report_map.deleteReport(last_description_selected);
               $('#div_report_description_display').hide();
               last_description_selected = null;
            } else {
               $('#span_report_description_delete_error').show();
            }
         },
         error: function() {
            $('#span_report_description_delete_error').show();
         }
      });
   }

   function mode_change() {
      /**
      * Changin the mode between 'add_new_place' and 'edit_place' / 'show_place'.
      */
      if(!add_new_place_mode) {
         $('#div_add_new_description_button').hide();
         $('#div_add_new_description_cancel_button').show();
         report_map.unactivateMarkers();
         report_map.rmClickReportEvent();

         report_map.addClickMapEvent(function(evt) {
            informer.map_ok(); //le croix rouge dans le formulaire nouveau point devient verte

            var position = ol.proj.transform(evt.coordinate,'EPSG:3857', 'EPSG:4326');

            $('input[name=lon]').val(position[0]);
            $('input[name=lat]').val(position[1]);

            report_map.moveMarker('new_report', position);
         });

         report_map.displayMarker('new_report');

         if(user.isRegistered()) {
            $('#div_new_report_form_user_mail').hide();
         } else {
            $('#div_new_report_form_user_mail').show();
         }
         $('#form__add_new_description').show();
         $('#div_report_description_display').hide();
      }
      else {
         $('#div_add_new_description_button').show();
         $('#div_add_new_description_cancel_button').hide();

         report_map.hideMarker('new_report');

         report_map.reactivateMarkers();
         report_map.addClickReportEvent(focus_on_place_of);
         report_map.rmClickMapEvent();

         $('#form__add_new_description').hide();

         if(last_description_selected !== null ) {
            $('#div_report_description_display').show();
            report_map.selectMarker(last_description_selected);
         }
      }
      add_new_place_mode = ! add_new_place_mode;
   }

   function focus_on_place_of(id_sig) {
      /**
      * Function which display some data of the description on the webpage
      * and draw the marker relative to this description as selected.
      * To be executed when the user click on a marker on the global map.
      * @param {int} id_sig The id of the description to display.
      */
      if (last_description_selected) {
         report_map.unselectMarker(last_description_selected);
      }

      report_map.selectMarker(id_sig);
      last_description_selected = id_sig;
      description_text_display.display_description_of((id_sig));
   }

   return {
      init_app: init_app,
      update_data_and_map: update_data_and_map,
      add_marker_and_description: add_marker_and_description,
      last_description_selected_reset: last_description_selected_reset,
      last_description_selected_delete: last_description_selected_delete,
      mode_change: mode_change,
      focus_on_place_of: focus_on_place_of,
   };
});
