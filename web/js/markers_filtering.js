/* jslint vars: true */
/*jslint indent: 3 */
/* global define */
'use strict';

/*jslint browser: true */

/**
* To filter the markers displayed in function of places caterogries, places CeM status or place type.
* This module is used to display the markers regarding to the filtering options.
*/

define(['jQuery', 'report_map', 'report', 'category', 'basic_data_and_functions'], function ($, report_map, report, category, basic_data_and_functions) {
   var filter = { //to remember for each filtering element if it has been activated, and the data asked
      // filter.element.enable -> true if the filtering on this element is enable
      // filted.element.XXX -> data used by the filtering
      enable : false,  // true iff  displaying the div "filter_and_export_menu". This implies that
      // the choice of the user (done in the filtering form) has to be considered.
      manager: {enable: false, to_display: []},
      timestamp: {enable: false},
      category: {enable: false, to_display: []},
      long_term_category: {enable: false},
      moderator_rejected_status: {enable: false},
      moderator_status: {enable: false, to_display: []}
   };

   function changeExportLinkRegardingToFiltering() {
      /**
      * Change the csv export link regarding to the filtering options checked
      */
      // no need to check if filter.enable -> otherwise not displayed
      var csv_export_link = $('#csv_basic_export_link').attr('href') + '&categories=' + filter.category.to_display.join(',') +
         '&moderator_status=' + filter.moderator_status.to_display.join(',');

      if(filter.manager.enable) {
         csv_export_link = csv_export_link + '&managers=' + filter.manager.to_display.join(',');
      }

      if(filter.timestamp.enable) {
         csv_export_link = csv_export_link + '&timestamp_begin=' +
            filter.timestamp.begin + '&timestamp_end=' + filter.timestamp.end;
      }

      $('#csv_export_link').attr('href', csv_export_link);
   }

   function displayMarkersRegardingToFiltering() {
      /**
      * Display on the map the markers regarding to the selection made by the user
      * via the filtering form (or not if not activated).
      */
      
      // Short term and medium categories
      if (filter.category.enable && filter.enable) {
         filter.category.to_display = $('#filtering__category__value_children').select2('val').map(function(s) {return parseInt(s);});
      } else {
         filter.category.to_display = [];
         category.getAll(function(categories) {
            $.each(categories, function(i,cat) {
               if(cat.children.length > 0) {
                  $.each(cat.children, function(i, child) {
                     if(child.term !== 'long') {
                        filter.category.to_display.push(child.id);
                     }
                  });
               }
               if(cat.children.length === 0 && cat.term !== 'long') {
                  filter.category.to_display.push(cat.id);
               }
            });
         });
      }

      // Long term categories
      if (filter.long_term_category.enable && filter.enable) {
         $.each($('#filtering__long_term_category__value_children').select2('val'), function (index, id_cat) {
            filter.category.to_display.push(parseInt(id_cat));
         });
      }

      filter.moderator_status.to_display = [];
      // Moderator status (white, red, yellow or green)
      if (filter.moderator_status.enable && filter.enable) {
         filter.moderator_status.to_display = $('#filtering__moderator_status__value').select2('val').map(function(s) {return parseInt(s);});
      } else {
         $('#filtering__moderator_status__value option').each(function (i, v) {
            filter.moderator_status.to_display.push(parseInt(v.value));
         });
      }

      // Gray (rejected) status
      if (filter.moderator_rejected_status.enable && filter.enable) {
         filter.moderator_status.to_display.push(-1);
      }

      if(filter.enable) {
         // Manager
         if (filter.manager.enable) {
            filter.manager.to_display = $('#filtering__manager__value').select2('val').map(function(s) {return parseInt(s);});
         }

         //timestamp filtering
         if (filter.timestamp.enable) {
            filter.timestamp.begin = basic_data_and_functions.stringDate2UnixTimestamp($('#filtering__timestamp__value_begin').val(),0); //0 is set if no value
            filter.timestamp.end = basic_data_and_functions.stringDate2UnixTimestamp($('#filtering__timestamp__value_end').val(), (((new Date()).getTime() / 1000)) ,true); //((new Date()).getTime() / 1000) is set if no value
         }

      }

      $.each(report.getAll(), function (desc_id, desc_data) {
         if (typeof desc_data !== undefined) {
            // desc_data does not have a status of type cem it has to be considered as 0 (not considered)
            if ($.inArray(parseInt(report.getStatus('cem', desc_data, 0)),filter.moderator_status.to_display) !== -1 &&
                  $.inArray(parseInt(desc_data.category.id),filter.category.to_display) !== -1 &&
                  (
                     !filter.enable || (
                        (!filter.timestamp.enable || (desc_data.createDate.u > filter.timestamp.begin && desc_data.createDate.u < filter.timestamp.end)) &&
                        (!filter.manager.enable || $.inArray(report.getManagerId(desc_data,-1),filter.manager.to_display ) !== -1)
                     )
                  )
               ) {
               report_map.displayMarker(desc_id);
            } else {
               report_map.hideMarker(desc_id);
            }
         }
      });

      changeExportLinkRegardingToFiltering();
   }

   function activateUnactivateFilteringForm() {
      /**
      * Function used to signal that the user wants to activate/unactivate the filtering.
      * This function will display/undisplay the filtering form.
      */
      if (filter.enable) {
         $('#stop_filter_and_export_button').hide();
         $('#filter_and_export_button').show();
         $('#filter_and_export_menu').hide();
      } else {
         $('#stop_filter_and_export_button').show();
         $('#filter_and_export_button').hide();
         $('#filter_and_export_menu').show();
      }
      filter.enable = !filter.enable;
      displayMarkersRegardingToFiltering();
   }

   function initFor(element) {
      /**
      * init the filtering for the element {element}.
      * @param{string} element The element to filter (or not) (see var filter)
      */
      if(element === 'manager') {
         $.each(report.getAllManagers(), function(i, e) {
            $('#filtering__' + element + '__value').append(
               '<option value="' +  e.manager.id +   '">' +  e.manager.label +   '</option>');
         });
      } else if(element === 'category') {
         category.insertParentCategoryToSelectField('#filtering__category__value_parent', ['short','medium']);
         $('#filtering__category__value_parent').on('select2-selecting', function(e) {
            category.setChildrenToSelect2Filed('#filtering__category__value_children',e.val, ['short','medium']);
            $('#filtering__category__value_children').on('select2-selecting', displayMarkersRegardingToFiltering);
            displayMarkersRegardingToFiltering();
         });
      } else if(element === 'long_term_category') {
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

      $('#filtering__' + element + '__form_div select').select2();
      $('#filtering__' + element + '__checkbox').on('change', function() { changeModeFor(element); });


      $('#filtering__' + element + '__form_div .filter').on('change', function() { displayMarkersRegardingToFiltering(); });
   }

   function changeModeFor(element) {
      /**
      * Enable / Disable the filtering for the element {element}.
      * @param{string} element The element to filter (or not) (see var filter)
      */
      if (filter[element].enable) {
         $('#filtering__' + element + '__form_div').hide();
      }
      else {
         $('#filtering__' + element + '__form_div').show();
      }
      filter[element].enable = !filter[element].enable;
      displayMarkersRegardingToFiltering();
   }


   return {
      displayMarkersRegardingToFiltering: displayMarkersRegardingToFiltering,
      activateUnactivateFilteringForm: activateUnactivateFilteringForm,
      initFor: initFor
   };
});