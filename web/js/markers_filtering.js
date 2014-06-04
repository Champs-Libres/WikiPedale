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
   var filtering_form_activated = false,  // true iff  displaying the div "div_options_affichage" and
   // the choice of the user (done in the filtering form) has to be considered.
      mode_activated = {}; // to remember each option of the filtering form has been
   // choosed by the user

   mode_activated.FilterCategories = false; // true iff  filtering categories
   mode_activated.AddLongTermCategories = false; // true iff  adding (with filtering) signalement with PN Categories (ie Categories with long term)
   mode_activated.FilterStatusCeM = false; // true iff filtering CeM Status
   mode_activated.AddStatusCeMRejete = false; // true iff  adding signalements with CeM Status Rejected
   mode_activated.timestamp = false; // true iff filtering on timestamp (creation date)

   var timestamp_min, timestamp_max;

   function change_export_link_regarding_to_filtering(statusCeM_to_display, id_cat_to_display) {
      /**
      * Change the csv export link regarding to the filtering options checked
      * @param {integer Array} statusCeM_to_display An array of the selected statusCeM  (select the signalements with its CeM notation in this array)
      * @param {integer Array} id_cat_to_display An array of the selected categories (select the signalements with its category in this array)
      */
      var csv_basic_export_link = $('#csv_basic_export_link').attr('href'),
         csv_export_link;
      csv_export_link = csv_basic_export_link + '&categories=' + id_cat_to_display.join(',');
      csv_export_link = csv_export_link + '&notations=' + statusCeM_to_display.join(',');

      if(mode_activated.timestamp) {
         csv_export_link = csv_export_link + '&timestamp_begin=' + timestamp_min;
         csv_export_link = csv_export_link + '&timestamp_end=' + timestamp_max;
      }

      $('#csv_export_link').attr('href', csv_export_link);
   }

   function display_markers_regarding_to_filtering() {
      /**
      * Display on the map the markers regarding to the selection made by the user
      * via the filtering form (or not if not activated).
      */
      var id_cat_to_display = [], //the id of the categories that will be displayed on the map
         statusCeM_to_display = []; //the statusCeM that will be displayed on the map

      // Short term and medium categories
      if (mode_activated.FilterCategories && filtering_form_activated) {
         $.each($('#optionsAffichageFilterCategoriesChildren').select2('val'), function (index, id_cat) {
            id_cat_to_display.push(parseInt(id_cat));
         });
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
      if (mode_activated.AddLongTermCategories) {
         $.each($('#optionsAffichageAddLongTermCategoriesChildren').select2('val'), function (index, id_cat) {
            id_cat_to_display.push(parseInt(id_cat));
         });
      }

      // White, Red, Yellow, Green statuses
      if (mode_activated.FilterStatusCeM && filtering_form_activated) {
         $.each($('#optionsAffichageFilterStatusCeM').select2('val'), function (index, id_type) {
            statusCeM_to_display.push(parseInt(id_type));
         });
      } else {
         $('#optionsAffichageFilterStatusCeM option').each(function (i, v) {
            statusCeM_to_display.push(parseInt(v.value));
         });
      }

      // Gray (rejected) status
      if (mode_activated.AddStatusCeMRejete) {
         statusCeM_to_display.push(-1);
      }

      //timestamp filtering
      if (mode_activated.timestamp) {
         timestamp_min = 0;
         timestamp_max = ((new Date()).getTime() / 1000);

         timestamp_min = basic_data_and_functions.stringDate2UnixTimestamp($('#optionsAffichageFilterTimestampFrom').val(),timestamp_min);
         timestamp_max = basic_data_and_functions.stringDate2UnixTimestamp($('#optionsAffichageFilterTimestampTo').val(),timestamp_max,true);
      }

      $.each(report.getAll(), function (desc_id, desc_data) {
         if (typeof desc_data !== undefined) {
            // desc_data does not have a status of type cem it has to be considered as 0 (not considered)
            if ($.inArray(parseInt(report.getStatus('cem', desc_data, 0)),statusCeM_to_display) !== -1 &&
               $.inArray(parseInt(desc_data.category.id),id_cat_to_display) !== -1 &&
               (!mode_activated.timestamp || (desc_data.createDate.u > timestamp_min && desc_data.createDate.u < timestamp_max))
               ) {
               map_display.display_marker(desc_id);
            } else {
               map_display.undisplay_marker(desc_id);
            }
         }
      });

      change_export_link_regarding_to_filtering(statusCeM_to_display, id_cat_to_display);
   }

   function activate_unactivate_filtering_form() {
      /**
      * Function used to signal that the user wants to activate/unactivate the filtering mode.
      * This function will display/undisplay the filtering form.
      */
      if (filtering_form_activated) {
         $('#buttonOptionsAffichage_cancel').hide();
         $('#buttonOptionsAffichage').show();
         $('#div_options_affichage').hide();
      } else {
         $('#buttonOptionsAffichage_cancel').show();
         $('#buttonOptionsAffichage').hide();
         $('#div_options_affichage').show();
      }
      filtering_form_activated = !filtering_form_activated;
      display_markers_regarding_to_filtering();
   }


   function change_mode_for(filtering_option) {
      /**
      * To be used when the user activate/unactivate a filtering option in the filtering mode.
      * @param {string } typesOrCategories either 'Placetypes' either 'Categories'
      */
      if (mode_activated[filtering_option]) {
         $('#' + filtering_option + ' select.filter').each(function() { $(this).select2('disable'); });
         $('#' + filtering_option + ' input.filter').each(function() { $(this).attr('disabled', 'disabled'); });
         $('#' + filtering_option + 'Filter select.filter').each(function() { $(this).select2('disable'); });
         $('#' + filtering_option + 'Filter input.filter').each(function() { $(this).attr('disabled', 'disabled'); });
      } else {
         $('#' + filtering_option + ' select.filter').each(function() { $(this).select2('enable'); });
         $('#' + filtering_option + ' input.filter').each(function() { $(this).removeAttr('disabled'); });
         $('#' + filtering_option + 'Filter select.filter').each(function() { $(this).select2('enable'); });
         $('#' + filtering_option + 'Filter input.filter').each(function() { $(this).removeAttr('disabled'); });
      }
      mode_activated[filtering_option] = !mode_activated[filtering_option];
      display_markers_regarding_to_filtering();
   }

   return {
      activate_unactivate_filtering_form: activate_unactivate_filtering_form,
      display_markers_regarding_to_filtering: display_markers_regarding_to_filtering,
      change_mode_for: change_mode_for
   };
});