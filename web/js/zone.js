/* jslint vars: true */
/*jslint indent: 3 */
/* global define, Routing */
'use strict';

/**
* Dealing with the zones :
* - storage in JS
*/

define(['jQuery'], function($) {
   var new_report_zones = [];
   var selected_zone;

    /**
     * Initialize the zone module. If a zone is selected it only load this
     * zones, otherwise it loads all the zones.
     * @param {zone object | null} selected_zone The selected zone (an object with id, slug, type attribyte) or null
     * @param{function} A callback to throw when the zones are loaded. The zones are
     * an array of zones (with only one element when a zone is selected)
     */
   function init(selected_zone_p, callback) {
      var url;
      selected_zone = selected_zone_p;
      if(isSelectedMinisite()) {
         url = Routing.generate('wikipedale_get_zone',
            {_format: 'json', zoneSlug: selected_zone.slug});
      } else {
         url = Routing.generate('wikipedale_all_moderated_zones',
            {_format: 'json'});
      }
      $.get(url, function(data) {
         if(! data.query.error) {
            callback(data.results);
         }
      });
   }

   /**
    * Returns the selected zone, or null if no zone is selected.
    *
    * The selected zone is an object with the attributes : id, slug, type.
    * @return The selected zone or null
   */
   function getSelected() {
      return selected_zone;
   }
   
   /**
    * Returns True if the selected zone is a minisite.
    *
    * When the selected zone is a ministe the all the action (creating report,
    * displaying reports, editing report) are only available for this zone
    */
   function isSelectedMinisite() {
      return (selected_zone && selected_zone.type === 'minisite');
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

      highlightZonesFromNewReportZonesArray();
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
    * Highlight the zones, from the extent zones list, that contains the marker
    * @param {event} The click event
    */
   function highlightSelectedZone(evt) {
      new_report_zones = [];
      evt.map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
         if(layer.hasOwnProperty('uello_layer_id') && layer.uello_layer_id == 'zones') {
            new_report_zones.push(feature.zone);
            
         }
      });

      highlightZonesFromNewReportZonesArray();
   }

   /**
    * Hightligth the zones, from the extent zones list, that are in the new_report_zones array
    */
   function highlightZonesFromNewReportZonesArray() {
      $.each(new_report_zones, function(i, zone){
         $('#' + addNewDescriptionZoneId(zone)).addClass('bold');
      });
   }

   return {
      init: init,
      updateModeratedZonesListForExtent: updateModeratedZonesListForExtent,
      highlightSelectedZone: highlightSelectedZone,
      getSelected: getSelected,
      isSelectedMinisite: isSelectedMinisite
   };
});