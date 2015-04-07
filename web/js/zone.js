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
   
   /**
    * Update the moderated zones list that are in the actual extent.
    * @param{array(zones)} The list of zones that are in the extent
    */
   function updateModeratedZonesListForExtent(zones) {
      var div_zones_list_id = '#div_add_new_description__moderated_zones_list';

      $(div_zones_list_id).html('');
      $.each(zones, function(index, zone) {
         $(div_zones_list_id).append((
            '<div class="zone_icon">' +
               '<div style="background-color: ' + zone.fill_color + ';"></div>' +
            '</div>' +
            '<div id="' + addNewDescriptionZoneId(zone) + '">' + zone.name + '</div>'));
      });
   }

   /**
    * Id of a zone in the the item of the extent zone list.
    * @param{zone} The zone
    * @return The id of the zone.
    */
   function addNewDescriptionZoneId(zone) {
      return 'div_add_new_description__moderated_zones_list_' + zone.slug;
   }

   /**
    * Highlight the zone, form the extent zones list, that contains the marker
    * @param {event} The click event
    */
   function highlightSelectedZone(evt) {
      evt.map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
         if(layer.hasOwnProperty('uello_layer_id') && layer.uello_layer_id == 'zones') {
            $('#' + addNewDescriptionZoneId(feature.zone)).addClass('bold');
         }
      });
   }

   return {
      getAll: getAll,
      updateModeratedZonesListForExtent: updateModeratedZonesListForExtent,
      highlightSelectedZone: highlightSelectedZone,
   };
});