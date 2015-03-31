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

    /**
     * Get all the zones and send it to a callback function
     * @param{function} A call back to throw when the zones are loaded
     */
   function getAll(callback) {
      if(!zones) {
         $.get(Routing.generate('wikipedale_all_moderated_zones', {_format: 'json'}), function( data ) {
            if(! data.query.error) {
               callback(data.results);
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