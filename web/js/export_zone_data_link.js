/* jslint vars: true */
/*jslint indent: 3 */
/* global define, Routing */
'use strict';

/**
* Display the link for exporting the data (csv / html - print)
*/
define(['jQuery', 'zone'], function($, zone) {
   var links_span_selector = '#export_zone_data_link_span';

   /**
    * Initialization of the module
    */
   function init() {
      zone.addUpdateZonesInExtentCallback(updateExportZoneDataLinkCB);
   }
   
   function updateExportZoneDataLinkCB(zones) {
      $.each(zones, function(slug,z) {
         if(!('ezdl_link_csv' in z)) {
            createLinks(z);
         }
      });

      $(links_span_selector).html('');

      $.each(zones, function(slug,z) {
         if(z.in_extent) {
            displayLinks(z);
         }
      });
   }

   function createLinks(zone) {
      zone.ezdl_link_constraint_csv = {zone_slug: zone.slug, _format: 'csv'};
      zone.ezdl_link_constraint_impress = {zone_slug: zone.slug, _format: 'html'};
   }

   function addConstraint() {
      //TODO
   }

   function rmConstraint() {
      //TODO
   }

   function url(constraint) {
      return Routing.generate('wikipedale_report_list_by_zone', constraint);
   }

   function displayLinks(zone) {
      $('#export_zone_data_link_span').append(
         '<a href="'+url(zone.ezdl_link_constraint_csv)+'" target="_blank">' + zone.name + ' CSV</a> ');
      $('#export_zone_data_link_span').append(
         '<a href="'+url(zone.ezdl_link_constraint_csv)+'" target="_blank">Impression de ' + zone.name + '</a> ');
   }

   return {
      init: init,
      addConstraint: addConstraint,
      rmConstraint: rmConstraint
   };
});