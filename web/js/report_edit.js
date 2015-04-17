/* jslint vars: true */
/*jslint indent: 3 */
/* global define, Routing */
'use strict';

/**
* This module is used when the user wants to edit a description
*/
define(['jQuery','report_map','report','basic_data_and_functions','json_string','markers_filtering','params', 'ol'],
      function($,report_map,report,basic_data_and_functions,json_string,markers_filtering,params,ol) {
   var mode_edit = {},
      new_lat = null,
      new_lon = null,
      new_position = null,
      url_edit = Routing.generate('wikipedale_report_change', {_format: 'json'});

   function stop_position_edition() {
      /**
      * Stops the edition of the position in the map
      */
      new_lat = null; //reinit these variables
      new_lon = null;
      new_position = null;
      $('#span_edit_lon_lat_delete_error').hide();
      $('#button_save_lon_lat').hide();
      $('#button_edit_lon_lat').show();
      $('#edit_report__draw_button').show();
      mode_edit.lon_lat = false;

      report_map.deleteMarker('move_report');
      report_map.displayAllReports();
      report_map.rmClickMapEvent();

      report_map.addLastestClickReportEvent();

      markers_filtering.displayMarkersRegardingToFiltering();
   }

   function stop_edition(){
      /**
      * Hides all the forms that were opened (and display the data in text) and
      * stops the postion description edition.
      */
      $('#div__report_description_display div').each(function(i,e) {
         var id_e = $(e).attr('id');
         if(id_e !== undefined && id_e.indexOf('_edit') !== -1 &&  id_e.indexOf('div_') !== -1) {
            $(e).hide();
         }
      });

      $('.ButtonEdit img').each(function(i,e) {
         $(e).attr('src', basic_data_and_functions.web_dir + 'img/edit.png')
         .attr('title', 'Editer');
      });

      $('#div__report_description_display span').each(function(i,e) {
         // show span element except error 
         var id_e = $(e).attr('id');
         if(id_e !== undefined && id_e.indexOf('_error') === -1) {
            $(e).show();
         }
      });

      if (mode_edit.lon_lat) {
         stop_position_edition();
      }

      mode_edit = {};
   }

   function position_edit_or_save() {
      /**
       * When this function is tiggered,
       * either the edition mode for position of the selected marker  (map) is displayed
       * either the new position of the selected marker is saved
       * The choice between is done alternatively
       */
      var report_id =  $('#input_report_description_id').val();
      if (!( 'lon_lat' in  mode_edit && mode_edit.lon_lat)) {
         $('#button_save_lon_lat').show();
         $('#button_edit_lon_lat').hide();
         $('#edit_report__draw_button').hide();
         report_map.hideAllReports();
         report_map.displayMarker(report_id);
         report_map.rmClickReportEvent();
         report_map.addClickMapEvent(
            function(evt) {
               new_position = ol.proj.transform(evt.coordinate,'EPSG:3857', 'EPSG:4326');

               new_lon = new_position[0];
               new_lat = new_position[1];

               report_map.hideMarker(report_id);
               report_map.moveMarker('move_report', new_position);
               report_map.selectMarker('move_report');
            });
         mode_edit.lon_lat = true;
      } else {
         if (new_lat !== null) {
            var signalement_id = parseInt($('#input_report_description_id').val()),
               json_request = json_string.editReportPosition(signalement_id,new_lon,new_lat);
            $.ajax({
               type: 'POST',
               data: {entity: json_request},
               url: url_edit,
               cache: false,
               success: function(output_json) {
                  if(! output_json.query.error) {
                     var new_description = output_json.results[0];
                     report.update(new_description);
                     report_map.moveMarker(new_description.id, new_position);
                     stop_position_edition();
                  } else {
                     $('#span_edit_lon_lat_delete_error').show();
                  }
               },
               error: function() {
                  $('#span_edit_lon_lat_delete_error').show();
               }
            });
         }
         else { // no change
            stop_position_edition();
         }
      }
   }

   function edit_or_save(element_type){
      /**
      * When this function is tiggered,
      either the edition form is displayed relative to 'element_type'
      either the data given by the edition form relative 'element_type' is saved
      The choice between the two comportements is in function of the variable 'mode_edit'
      */
      var element_id = '#span_report_description_' + element_type,
         signalement_id = parseInt($('#input_report_description_id').val()),
         signalement = report.get(signalement_id),
         json_request;

      if (! (element_type in mode_edit && mode_edit[element_type])) {
         // SHOW THE EDIT FORM
         if (element_type === 'cat'){
            $(element_id + '_edit').select2('val', signalement.category.id);
         } else if (element_type === 'status') {
            var color_selected = 0;
            $.each(signalement.statuses, function(i,s) { if(s.t === params.manager_color) color_selected = s.v; });
            $(element_id + '_edit').select2('val', color_selected);
         } else {
            $(element_id + '_edit').val($(element_id).text());
         }

         $(element_id).hide();
         $('#div_report_description_' + element_type + '_edit').show();
         $(element_id + '_button').html(
            $(document.createElement('img'))
               .attr('src', basic_data_and_functions.web_dir + 'img/sauver.png')
               .attr('title', 'Sauver'));
         mode_edit[element_type] = true;
      } else {
         // SAVE THE FORM
         if(element_type === 'commentaireCeM') {
            json_request = json_string.editModeratorComment(signalement_id,$(element_id + '_edit').val());
         } else if(element_type === 'desc') {
            json_request = json_string.editDescription(signalement_id,$(element_id + '_edit').val());
         } else if (element_type === 'loc') {
            json_request = json_string.editLocation(signalement_id,$(element_id + '_edit').val());
         } else if (element_type === 'cat') {
            json_request = json_string.editCategory(signalement_id,$(element_id + '_edit').select2('val'));
         } else if (element_type === 'status') {
            json_request = json_string.editStatus(signalement_id,params.manager_color,$(element_id + '_edit').select2('val'));
         } else if (element_type === 'gestionnaire') {
            json_request = json_string.editManager(signalement_id,$(element_id + '_edit').select2('val'));
         } else if (element_type === 'moderator') {
            json_request = json_string.editModerator(signalement_id,$(element_id + '_edit').select2('val'));
         } else if (element_type === 'type'){
            json_request = json_string.editReportType(signalement_id,$(element_id + '_edit').select2('val'));
         }
      
         $.ajax({
            type: 'POST',
            data: {entity: json_request},
            url: url_edit,
            cache: false,
            success: function(output_json) {
               if(! output_json.query.error) {
                  var new_description = output_json.results[0];
                  report.update(new_description);
                  if(element_type === 'cat'){
                     $(element_id).text(new_description.category.label);
                  } else if (element_type === 'status'){
                     report_map.selectMarker(signalement_id);
                  } else if (element_type === 'gestionnaire' || element_type === 'type' || element_type === 'moderator' ) {
                     $(element_id).text($(element_id + '_edit').select2('data').text);
                  } else {
                     $(element_id).text($(element_id + '_edit').val());
                  }
                  markers_filtering.displayMarkersRegardingToFiltering();
                  $(element_id +  '_error').hide();
                  $('#div_report_description_' + element_type + '_edit').hide();
                  $(element_id).show();
                  $(element_id + '_button').html(
                     $(document.createElement('img'))
                        .attr('src',  basic_data_and_functions.web_dir + 'img/edit.png')
                        .attr('title', 'Editer'));
                  mode_edit[element_type] = false;
               } else {
                  $(element_id +  '_error').show();
               }
            },
            error: function() {
               $(element_id +  '_error').show();
            }
         });
      }
      return false;
   }

   function saveDrawings() {
      /**
       * Save the drawings done by the user on the map.
       * @return nothing
       */
      var signalement_id = parseInt($('#input_report_description_id').val()),
      json_request = json_string.editReportDrawings(
         signalement_id,report_map.getDrawnDetails('edit_report'));
      $.ajax({
         type: 'POST',
         data: {entity: json_request},
         url: url_edit,
         cache: false,
         success: function(output_json) {
            if(! output_json.query.error) {
               var new_description = output_json.results[0];
               report.update(new_description);
               $('#div_edit_report__draw_error').hide();
            } else {
               $('#div_edit_report__draw_error').show();
            }
         },
         error: function() {
            $('#div_edit_report__draw_error').show();
         }
      });
   }

   return {
      stop_edition: stop_edition,
      edit_or_save: edit_or_save,
      position_edit_or_save:position_edit_or_save,
      saveDrawings: saveDrawings,
   };
});
