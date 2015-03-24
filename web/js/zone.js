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
     * Load the categories
     * @param{function} A call back to throw when the zones are loaded
     */
   function init(callback) {
      $.get(Routing.generate('wikipedale_all_moderated_zones', {_format: 'json'}), function( data ) {
         if(! data.query.error) {
            zones = data.results;

            for (var i = 0; i < zones.length; i ++) {
               zones[i].fill_color = fill_colors[Math.floor(Math.random()*fill_colors.length)];
            }

            if(callback) {
               callback(zones);
            }
         }
      });
   }


   return {
      init: init,
   };
});