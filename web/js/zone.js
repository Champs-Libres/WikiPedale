/* jslint vars: true */
/*jslint indent: 3 */
/* global define, Routing */
'use strict';

/**
* Dealing with the zones :
* - storage in JS
*/

define(['jQuery'], function($) {
   var zones; // the known zones
   var fill_colors = ['green', 'red', 'yellow', 'blue', 'orange', 'white'];
   // fill color to display the polygon zones on the map

    /**
     * Get all the zones and send it to a callback function
     * @param{function} A call back to throw when the zones are loaded
     */
   function getAll(callback) {
      if(!zones) {
         $.get(Routing.generate('wikipedale_all_moderated_zones', {_format: 'json'}), function( data ) {
            if(! data.query.error) {
               zones = data.results;

               for (var i = 0; i < zones.length; i ++) {
                  zones[i].fill_color = fill_colors[Math.floor(Math.random()*fill_colors.length)];
               }
               
               callback(zones);
            }
         });
      } else {
         callback(zones);
      }
   }
   
   function updateModeratedZonesListForExtent(zones) {
      var div_zones_list_id = '#div_add_new_description__moderated_zones_list';

      $(div_zones_list_id).html('');
      $.each(zones, function(index, zone) {
         $(div_zones_list_id).append((
            '<div class="zone_icon">' +
               '<div style="background-color: ' + zone.fill_color + ';"></div>' +
            '</div>' +
            '<div id="#div_add_new_description__moderated_zones_list_' + zone.name + '">' + zone.name + '</div>'));
      });
   }
   return {
      getAll: getAll,
      updateModeratedZonesListForExtent: updateModeratedZonesListForExtent,
   };
});