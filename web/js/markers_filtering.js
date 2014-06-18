/* jslint vars: true */
/*jslint indent: 3 */
/* global define */
'use strict';

/*jslint browser: true */

/**
* To filter the markers displayed in function of places caterogries, places CeM status or place type.
* This module is used to display the markers regarding to the filtering options.
*/

define(['jQuery', 'map_display', 'report', 'category', 'basic_data_and_functions'], function ($, map_display, report, category, basic_data_and_functions) {
   var filtering_form_activated = false,  // true iff  displaying the div "filter_and_export_menu" and
   // the choice of the user (done in the filtering form) has to be considered.
      mode_activated = {}; // to remember each option of the filtering form has been
   // choosed by the user

   var timestamp_begin, timestamp_end;

   function changeExportLinkRegardingToFiltering(statusCeM_to_display, id_cat_to_display) {
      /**
      * Change the csv export link regarding to the filtering options checked
      * @param {integer Array} statusCeM_to_display An array of the 2ted statusCeM  (select the signalements with its CeM notation in this array)
      * @param {integer Array} id_cat_to_display An array of the selected categories (select the signalements with its category in this array)
      */
      var csv_basic_export_link = $('#csv_basic_export_link').attr('href'),
         csv_export_link;
      csv_export_link = csv_basic_export_link + '&categories=' + id_cat_to_display.join(',');
      csv_export_link = csv_export_link + '&notations=' + statusCeM_to_display.join(',');

      if(mode_activated.timestamp) {
         csv_export_link = csv_export_link + '&timestamp_begin=' + timestamp_begin;
         csv_export_link = csv_export_link + '&timestamp_end=' + timestamp_end;
      }

      $('#csv_export_link').attr('href', csv_export_link);
   }

   function displayMarkersRegardingToFiltering() {
      /**
      * Display on the map the markers regarding to the selection made by the user
      * via the filtering form (or not if not activated).
      */
      var id_cat_to_display = [], //the id of the categories that will be displayed on the map
         moderator_status_to_display = [], //the moderator status that will be displayed on the map
         id_manager_to_display = [];

      // Short term and medium categories
      if (mode_activated.category && filtering_form_activated) {
         id_cat_to_display = $('#filtering__category__value_children').select2('val').map(function(s) {return parseInt(s);});
      } else {
         category.getAll(function(categories) {
            $.each(categories, function(i,cat) {
               if(cat.children.length > 0) {
                  $.each(cat.children, function(i, child) {
                     if(child.term !== 'long') {
                        id_cat_to_display.push(child.id);
                     }
                  });
               }
               if(cat.children.length === 0 && cat.term !== 'long') {
                  id_cat_to_display.push(cat.id);
               }
            });
         });
      }

      // Long term categories
      if (mode_activated.long_term_category && filtering_form_activated) {
         $.each($('#filtering__long_term_category__value_children').select2('val'), function (index, id_cat) {
            id_cat_to_display.push(parseInt(id_cat));
         });
      }

      // White, Red, Yellow, Green statuses (Moderator status)
      if (mode_activated.moderator_status && filtering_form_activated) {
         moderator_status_to_display = $('#filtering__moderator_status__value').select2('val').map(function(s) {return parseInt(s);});
      } else {
         $('#filtering__moderator_status__value option').each(function (i, v) {
            moderator_status_to_display.push(parseInt(v.value));
         });
      }

      // Gray (rejected) status
      if (mode_activated.moderator_rejected_status && filtering_form_activated) {
         moderator_status_to_display.push(-1);
      }

      // Manager
      if (mode_activated.manager && filtering_form_activated) {
         id_manager_to_display = $('#filtering__manager__value').select2('val').map(function(s) {return parseInt(s);});
      }

      //timestamp filtering
      if (mode_activated.timestamp && filtering_form_activated) {
         timestamp_begin = basic_data_and_functions.stringDate2UnixTimestamp($('#filtering__timestamp__value_begin').val(),0); //0 is set if no value
         timestamp_end = basic_data_and_functions.stringDate2UnixTimestamp($('#filtering__timestamp__value_end').val(), (((new Date()).getTime() / 1000)) ,true); //((new Date()).getTime() / 1000) is set if no value
      }

      $.each(report.getAll(), function (desc_id, desc_data) {
         if (typeof desc_data !== undefined) {

            // desc_data does not have a status of type cem it has to be considered as 0 (not considered)
            if ($.inArray(parseInt(report.getStatus('cem', desc_data, 0)),moderator_status_to_display) !== -1 &&
               $.inArray(parseInt(desc_data.category.id),id_cat_to_display) !== -1 &&
               (!mode_activated.timestamp || !filtering_form_activated || (desc_data.createDate.u > timestamp_begin && desc_data.createDate.u < timestamp_end)) &&
               (!mode_activated.manager || !filtering_form_activated || $.inArray(report.getManagerId(desc_data,-1),id_manager_to_display) !== -1)
               ) {
               map_display.display_marker(desc_id);
            } else {
               map_display.undisplay_marker(desc_id);
            }
         }
      });

      changeExportLinkRegardingToFiltering(moderator_status_to_display, id_cat_to_display);
   }

   function activateUnactivateFilteringForm() {
      /**
      * Function used to signal that the user wants to activate/unactivate the filtering mode.
      * This function will display/undisplay the filtering form.
      */
      if (filtering_form_activated) {
         $('#stop_filter_and_export_button').hide();
         $('#filter_and_export_button').show();
         $('#filter_and_export_menu').hide();
      } else {
         $('#stop_filter_and_export_button').show();
         $('#filter_and_export_button').hide();
         $('#filter_and_export_menu').show();
      }
      filtering_form_activated = !filtering_form_activated;
      displayMarkersRegardingToFiltering();
   }

   function initFor(element) {
      if(element === 'manager') {
         $.each(report.getAllManagers(), function(i, e) {
            $('#filtering__' + element + '__value').append(
               '<option value="' +  e.manager.id +   '">' +  e.manager.label +   '</option>');
         });
      }

      if(element === 'category') {
         category.insertParentCategoryToSelectField('#filtering__category__value_parent', ['short','medium']);
         $('#filtering__category__value_parent').on('select2-selecting', function(e) {
            category.setChildrenToSelect2Filed('#filtering__category__value_children',e.val, ['short','medium']);
            $('#filtering__category__value_children').on('select2-selecting', displayMarkersRegardingToFiltering);
            displayMarkersRegardingToFiltering();
         });
      }

      if(element === 'long_term_category') {
         category.insertParentCategoryToSelectField('#filtering__long_term_category__value_parent', ['long']);
         $('#filtering__long_term_category__value_parent').on('select2-selecting', function(e) {
            category.setChildrenToSelect2Filed('#filtering__long_term_category__value_children',e.val, ['long']);
            $('#filtering__long_term_category__value_children').on('select2-selecting', displayMarkersRegardingToFiltering);
            displayMarkersRegardingToFiltering();
         });
      }

      if($('#filtering__' + element + '__checkbox').attr('checked') === 'checked') {
         $('#filtering__' + element + '__checkbox').removeAttr('checked');
      }

      mode_activated[element] = false;
      $('#filtering__' + element + '__form_div select').select2();
      $('#filtering__' + element + '__checkbox').on('change', function() { changeModeFor(element); });


      $('#filtering__' + element + '__form_div .filter').on('change', function() { displayMarkersRegardingToFiltering(); });
   }

   function changeModeFor(element) {
      if (mode_activated[element]) {
         $('#filtering__' + element + '__form_div').hide();
      }
      else {
         $('#filtering__' + element + '__form_div').show();
      }
      mode_activated[element] = !mode_activated[element];
      displayMarkersRegardingToFiltering();
   }


   return {
      displayMarkersRegardingToFiltering: displayMarkersRegardingToFiltering,
      activateUnactivateFilteringForm: activateUnactivateFilteringForm,
      initFor: initFor
   };
});