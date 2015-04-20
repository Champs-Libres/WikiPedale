/* jslint vars: true */
/*jslint indent: 3 */
/* global define */
'use strict';

/**
* Dealing with the zone presentation divs :
* - when the zones in the extent map are updated, this module change
* its presentation divs.
*/

define(['jQuery', 'zone'], function($, zone) {
   var zone_presentation_container_selector = '#div__zone_presentation_container';

   /**
    * Initialization of the module
    */
   function init() {
      zone.addUpdateZonesInExtentCallback(displayPresentationCB);
   }

   /**
    * Callback to call when the zones in the map extent have been changed.
    *
    * @param{array of zones} Array of the known zones. A zone in the extent
    * must have its property 'in_extent' at true.
    */
   function displayPresentationCB(zones) {
      $.each(zones, function(slug,z) {
         if(!('zp_div' in z)) {
            createPresentation(z);
         }

         if(z.in_extent) {
            z.zp_div.show();
         } else {
            z.zp_div.hide();
         }
      });
   }

   /**
    * Callback to call when the zones in the map extent have been changed.
    *
    * @param{array of zones} Array of the known zones. A zone in the extent
    * must have its element 'in_extent' at true.
    */
   function createPresentation(zone) {
      var div_id = divPresentationId(zone);
      var div_selector = divPresentationSelector(zone);
      $(zone_presentation_container_selector).append('<div id="' + div_id + '"></div>');
      zone.zp_selector = div_selector;
      zone.zp_div = $(div_selector);
      zone.zp_div.addClass('zone_presentation');
      $(zone.zp_selector).html($('#div__zone_presentation').html());
      $(zone.zp_selector  + ' .title').text(zone.name.toUpperCase());
      $(zone.zp_selector  + ' .content').html(zone.description);
   }

   /**
    * Returns the id of the zone presentation div.
    * 
    * @param{zone} The given zone
    * @return{string} The id of zone presentation div.
    */
   function divPresentationId(zone) {
      return 'div__zone_presentation_' + zone.slug;
   }

   /**
    * Returns the selector (for jquery) of the zone presentation div.
    * 
    * @param{zone} The given zone
    * @return{string} The selector of zone presentation div.
    */
   function divPresentationSelector(zone) {
      return '#' + divPresentationId(zone);
   }

   return {
      init: init
   };
});