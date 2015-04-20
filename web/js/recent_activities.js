/* jslint vars: true */
/*jslint indent: 3 */
/* global define, Routing */
'use strict';

/**
* Displaying for each zone in the extent the recents activites
* - when the zones in the extent map are updated
*/
define(['jQuery', 'zone'], function($, zone) {
   var max_nbr = 5;

   /**
    * Initialization of the module
    */
   function init(max_nbr_p) {
      if(max_nbr_p) {
         max_nbr = max_nbr_p;
      }
      zone.addUpdateZonesInExtentCallback(displayRecentActivitesCB);
   }

   /**
    * Callback to call when the zones in the map extent have been changed.
    *
    * @param{array of zones} Array of the known zones. A zone in the extent
    * must have its element 'in_extent' at true.
    */
   function displayRecentActivitesCB(zones) {
      $.each(zones, function(slug,z) {
         if(!('ra_div' in z)) {
            createRecentActivitesFor(z, function() {
               if(z.in_extent) {
                  z.ra_div.show();
               } else {
                  z.ra_div.hide();
               }
            });
         } else {
            if(z.in_extent) {
               z.ra_div.show();
            } else {
               z.ra_div.hide();
            }
         }
      });
   }

   function createRecentActivitesFor(zone, callback) {
      var jsonUrlData = Routing.generate('wikipedale_history_report_by_zone', {_format: 'json', citySlugP: zone.slug, max:max_nbr});
      $.ajax({
         dataType: 'json',
         url: jsonUrlData,
         success: function(data) {
            var div_id = 'div__recent_activities_' + zone.slug;
            var div_selector = '#' + div_id;
            $('#div__recent_activities_container').append('<div id="' + div_id + '"></div>');
            zone.ra_div = $(div_selector);
            zone.ra_div.html($('#div__recent_activities_template').html());
            $(div_selector + ' a.zone-title').prepend(zone.name);
            $(div_selector + ' a.zone-title').attr(
               'href', Routing.generate('wikipedale_history_report_by_zone', {_format:'atom', citySlugP: zone.slug}));
            if(data.results.length > 0) {
               $.each(data.results, function(index, activity) {
                  zone.ra_div.append(activity.text);
                  var see_more_link = $(document.createElement('a'))
                     .text('(voir)')
                     .attr('href', '?id=' + activity.reportId);
                  zone.ra_div.append(see_more_link);
                  zone.ra_div.append('<br>');
               });
            } else {
               zone.ra_div.append('Pas de modifications.');
            }
            if(callback) {
               callback();
            }
         }
      });
   }

   return {
      init: init
   };
});