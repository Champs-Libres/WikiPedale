/* jslint vars: true */
/*jslint indent: 3 */
/* global define, Routing */
'use strict';

/**
* This module is the glue between all the other modules. This leads to fundamental function
* for the application.
*/

define(['jQuery','map_display','report','description_text_display','user','informer','json_string','markers_filtering'],
      function($,map_display,report,description_text_display,user,informer,json_string,markers_filtering) {
   var townId = null;
   var last_description_selected = null;
   var add_new_place_mode = false; // true when the user is in a mode for adding new place

   function init_app(townId_param, townLon, townLat, marker_id_to_display) {
      /**
      * Init the application and the map.
      * @param {int} townId  The id of the town
      * @param {float} townLon The longitude of the town
      * @param {float} townLat The latitude of the town
      * @param {int} marker_id_to_display The id of the marker to display (direct access). It is optional
      * (none if no marker to display)
      */
      townId = townId_param;
      var jsonUrlData  =  Routing.generate('wikipedale_report_list_by_city', {_format: 'json', city: townId_param, addUserInfo: true});

      map_display.init(townLon,townLat);

      $.when(
         $.getJSON(jsonUrlData, function(data) {
            user.update(data.user);
            report.updateAll(data.results, function () {
               $.each(data.results, function(index, a_report) {
                  map_display.add_marker(a_report.id, focus_on_place_of); //focus_on_place_of is the event fct
               });
            });
         })
      ).done(function() {
         if(marker_id_to_display) {
            focus_on_place_of(marker_id_to_display);
         }
         markers_filtering.display_markers_regarding_to_filtering();
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

   function add_marker_and_description(aDescription, anEventFunction) {
      /**
      * Add on the map a new description and store this description in the local saved data.
      * @param {object} aDescription The data describing the new description to add on the map.
      * @param {function} anEventFunction The function to execute when the user click on the marker
      */
      report.singleUpdate(aDescription);
      map_display.add_marker(aDescription.id, anEventFunction);
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
               map_display.get_marker_for(last_description_selected).erase();
               report.erase(last_description_selected);
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
         map_display.unactivate_markers();

         map_display.display_marker('new_description');

         map_display.get_map().events.register('click', map_display.get_map(), function(e) {
            informer.map_ok(); //le croix rouge dans le formulaire nouveau point devient verte
            var position = map_display.get_map().getLonLatFromPixel(e.xy);
            $('input[name=lon]').val(position.lon);
            $('input[name=lat]').val(position.lat);

            map_display.marker_change_position('new_description', position);
            map_display.display_marker('new_description');

         });

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

         map_display.undisplay_marker('new_description');
         map_display.undisplay_marker('new_description');
         map_display.get_map().events.remove('click');
         map_display.reactivate_description_markers(focus_on_place_of);
         // ne plus utiliser makers_and_assoc_data

         $('#form__add_new_description').hide();

         /*
         if(last_description_selected !== null ) {
            $("#div_report_description_display").show();
            map_display.select_marker(last_description_selected);
         }
         */
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
         map_display.unselect_marker(last_description_selected);
      }
      map_display.select_marker(id_sig);
      last_description_selected = id_sig;
      description_text_display.display_description_of(id_sig);
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
