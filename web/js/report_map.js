/* jslint vars: true */
/*jslint indent: 3 */
/* global define, Routing */
'use strict';

/**
* This is for all the action for the display of the map
*/

define(['jQuery','basic_data_and_functions','report','ol','params', 'user'],
      function($,basic_data_and_functions, detailed_report,ol,params, user) {
   var old_center; // To re-center the map after displaying the tiny map
   var map; // Variable to acces to the map
   var marker_source; // source for the layer displaying reports
   //var zoom_map = 13; // zoom level of the map
   var zoom_map = 17; // zoom level of the map
   var marker_img_url = basic_data_and_functions.web_dir + 'img/OpenLayers/';
   
   var markers = [];

   var cluster_source;
   var style_cache = [];

   var layers = {};

   var click_report_event_fct;
   var click_map_event_fct;
   var zoom_change_event_fct;

   // marker with color
   var color_trad = [];
   color_trad['0'] = 'w';
   color_trad['-1'] = 'd';
   color_trad['1'] = 'r';
   color_trad['2'] = 'o';
   color_trad['3'] = 'g';

   function setTarget(target) {
      /**
       * Sets the target element to render this map into.
       * @param {string} target The id of the element that the map is rendered in.
       * @return nothing
       */
      map.setTarget(target);
   }

   function centerMapOnMarker(marker_id) {
      /**
       * Sets the center of the map on a marker (a report is a marker).
       * @param {int} marker_id The id of the marker where to center the map (can be a report).
       * @return nothing
       */
      old_center = map.getView().getCenter();
      map.getView().setCenter(markers[marker_id].feature.getGeometry().getCoordinates());
   }

   function undoCenterMapOnMarker() {
      /**
       * Resets the center of the map where it was before the call of the centerMapOnMarker function
       * @return nothing
       */
      map.getView().setCenter(old_center);
   }
   
   function init(town_lon,town_lat, evt_new_reports){
      /**
       * Initializes the map on the good town
       * @param {float} town_lon Longitude of the town
       * @param {float} town_lat Latitude of the town
       * @param {function} evt_new_reports The event to trigger when new reports are displayed on the map
       * @return nothing
       */
      var voies_lentes_layer, voies_lentes_visible;
      var lightReportGeoJSONUrl =  Routing.generate('wikipedale_light_list', {_format: 'json'});

      voies_lentes_layer = new ol.layer.Tile({
         source: new ol.source.TileWMS(/** @type {olx.source.TileWMSOptions} */ ({
            url: 'http://geoservices.wallonie.be/arcgis/services/MOBILITE/VOIES_LENTES/MapServer/WMSServer?',
            params: {'LAYERS': '0', 'TILED': true},
            projection: 'EPSG:3857'
         }))
      });

      marker_source = new ol.source.Vector({
         features: []
      });

      layers.markers = new ol.layer.Vector({
         source: marker_source
      });

      layers.markers.uello_displayed = true;

      map = new ol.Map({
         target: 'map',
         layers: [
            new ol.layer.Tile({
               source: new ol.source.OSM({})
            }),
            voies_lentes_layer,
            layers.markers
         ],
         view: new ol.View({
            center: ol.proj.transform([parseFloat(town_lon), parseFloat(town_lat)], 'EPSG:4326', 'EPSG:3857'),
            zoom: zoom_map
         })
      });

      // Adding layer for 'voie lentes'
      voies_lentes_visible = new ol.dom.Input(document.getElementById('voies_lentes_visible'));
      voies_lentes_visible.bindTo('checked', voies_lentes_layer, 'visible');

      // Adding layer for 'report cluster'
      cluster_source = new ol.source.Cluster({
         distance: 40,
         source: new ol.source.GeoJSON({
            url: lightReportGeoJSONUrl,
            projection: 'EPSG:3857'
         })
      });

      layers.cluster = new ol.layer.Vector({
         source: cluster_source,
         style: function(feature) {
            var size = feature.get('features').length;
            var style = style_cache[size];
            if (!style) {
               style = [new ol.style.Style({
                  image: new ol.style.Circle({
                     radius: 10,
                     stroke: new ol.style.Stroke({
                        color: '#fff'
                     }),
                     fill: new ol.style.Fill({
                        color: '#3399CC'
                     })
                  }),
                  text: new ol.style.Text({
                     text: size.toString(),
                     fill: new ol.style.Fill({
                        color: '#fff'
                     })
                  })
               })];
               style_cache[size] = style;
            }
            return style;
         }
      });

      map.addLayer(layers.cluster);
      layers.cluster.uello_displayed = true;

      map.on('moveend', function() {
         var extent = map.getView().calculateExtent(map.getSize());
         var bottom_left_4326 = ol.proj.transform(
            ol.extent.getBottomLeft(extent),'EPSG:3857', 'EPSG:4326');

         var top_right_4326 = ol.proj.transform(
            ol.extent.getTopRight(extent), 'EPSG:3857', 'EPSG:4326');

         var bbox = decodeURIComponent(
            top_right_4326[1].toString() + ',' +
            top_right_4326[0].toString() + ',' +
            bottom_left_4326[1].toString() + ',' +
            bottom_left_4326[0].toString()
            );

         var jsonUrlData  =  Routing.generate('wikipedale_report_list_by_bbox', {_format: 'json', bbox: bbox, addUserInfo: true});

         $.when(
            $.getJSON(jsonUrlData, function(data) {
               user.update(data.user);
               $.each(data.results, function(index, report) {
                  addReport(report);
               });
            })

         ).done(function() {
            evt_new_reports();
         });
      });
   }

   function addClickMapEvent(event_function) {
      /**
       * Registers an function to execute when the user click on the map 
       * (if such a function already exists, this function is removed)
       * @param {function} event_function The function to trigger when the user click on the map
       * @return nothing
       */
      if(click_map_event_fct) {
         map.un('click', click_map_event_fct);
      }

      click_map_event_fct = function(evt) {
         event_function(evt);
      };

      map.on('click', click_map_event_fct);
   }

   function rmClickMapEvent() {
      /**
       * Removes the function associated to the action "click on the map"
       * (the function is registered with the addClickMapEvent function)
       * @return nothing
       */
      map.un('click', click_map_event_fct);
   }

   function addClickReportEvent(event_function) {
      /**
       * Registers an function to execute when the user click on a report 
       * (if such a function already exists, this function is removed)
       * @param {function} event_function The function to trigger when the user click on a report
       * @return nothing
       */
      if(click_report_event_fct) {
         map.un('click', click_report_event_fct);
      }

      click_report_event_fct = function(evt) {
         var triggered = false;
         map.forEachFeatureAtPixel(evt.pixel,
            function(feature, layer) {
               if((!triggered) && (layer === layers.markers)) {
                  event_function(feature.report_id);
                  triggered = true;
               }
            }
         );
      };

      map.on('click', click_report_event_fct);
   }

   function addLastestClickReportEvent() {
      /**
       * Reactivates the previously declared function to execute when the user click on a report 
       * (if such a function already exists, this function is removed)
       * @return nothing
       */
      if(click_report_event_fct) {
         map.on('click', click_report_event_fct);
      }
   }

   function rmClickReportEvent() {
      /**
       * Removes the function associated to the action "click on a report"
       * (the function is registered with the addClickReportEvent function)
       * @return nothing
       */
      map.un('click', click_report_event_fct);
   }

   function getIconStyle(report, icon_suffix) {
      /**
       * Creates the open-layers icon style
       * @param {Report} report The report (can be null)
       * @param {string} icon_suffix The suffix for the icon :
       * - null for the normal icon
       * - 'selected' for the pink icon
       * - 'no_active' for the gray icon
       * @return nothing
       */
      var statuses = [];
      var term = null;

      if(typeof report !== 'undefined' && report !== null) {
         statuses = report.statuses;
         term = report.category.term;
      }

      return new ol.style.Style({
         image: new ol.style.Icon(({
            src: iconName(statuses,term,icon_suffix),
            scale: 0.55,
            anchor: [0.5,1]
         }))
      });
   }

   function updateIconStyle(marker_id, icon_suffix) {
      /**
       * Updates the icon for a marker
       * @param {int} marker_id The id of the marker
       * @param {string} icon_suffix The suffix for the icon :
       * - null for the normal icon
       * - 'selected' for the pink icon
       * - 'no_active' for the gray icon
       * @return nothing
       */
      var iconStyle;

      if(markers[marker_id].type === 'report') {
         iconStyle = getIconStyle(detailed_report.get(marker_id),icon_suffix);
      } else {
         iconStyle = getIconStyle(null,icon_suffix);
      }

      markers[marker_id].feature.setStyle(iconStyle);
   }

   function createFeatureForCoord(coordinates) {
      /**
       * Creates an OL Feature for coordinates
       * @params {coordinates} coordinates The coordinates.
       * @return {ol.Feature}
       */
      return  new ol.Feature(
         new ol.geom.Point(
            ol.proj.transform(coordinates, 'EPSG:4326', 'EPSG:3857')
            )
         );
   }

   function addMarker(marker_id, coordinates, report) {
      /**
       * Adds a marker on the map
       * @param {string} marker_id A string to identify the marker
       (used for interact with the marker)
       * @param {coordinates} coordinates The coordinates of the marker
       * @param {Report} report if exists the report associated to the marker (to generate the icon) 
       * otherwise nothing
       * @return nothing
       */
      var feature = createFeatureForCoord(coordinates);
      feature.setStyle(getIconStyle(report, ''));
      marker_source.addFeature(feature);
      markers[marker_id] = {feature: feature, displayed: true};
   }

   function addReport(report) {
      /**
       * Adds a report on the map
       * This create a marker on the map (all function for markers can be used with report.id as id)
       * and register the report in report.js
       * @param {report} report The report
       * @return nothing
       */

      if (typeof(markers[report.id]) === 'undefined') { // only add if the report is not already registered
         detailed_report.update(report);
         addMarker(report.id, report.geom.coordinates, report);
         markers[report.id].type = 'report';
         markers[report.id].feature.report_id = report.id;
      }
   }

   function deleteMarker(marker_id) {
      /**
       * Deletes a marker from the map
       * @param {string} marker_id The string that identify the marker to delete
       * @return nothing
       */
      hideMarker(marker_id);
      delete markers[marker_id];
   }

   function deleteReport(report_id) {
      /**
       * Deletes a marker associated to a report from the map (also in report.js)
       * @param {int} report_id The id of the report to delete
       * @return nothing
       */
      detailed_report.erase(report_id);
      deleteMarker(report_id);
   }

   function moveMarker(marker_id, new_position) {
      /**
       * Moves a marker from the map
       * @param {string} marker_id The string that identify the marker to move
       * @param {coordinates} new_position The new position
       * @return nothing
       */
      if (typeof markers[marker_id] === 'undefined') {
         addMarker(marker_id, new_position);
      } else {
         var feature = markers[marker_id].feature;
         feature.setGeometry(
            new ol.geom.Point(
               ol.proj.transform(new_position, 'EPSG:4326', 'EPSG:3857')
            )
         );
      }
   }

   function displayMarker(marker_id){
      /**
       * Displays on the map a marker (a marker can be hidden).
       * @param {string|int} marker_id The id of the marker
       * @return nothing
       */
      if (markers[marker_id] && ! markers[marker_id].displayed) {
         marker_source.addFeature(markers[marker_id].feature);
         markers[marker_id].displayed = true;
      }
   }

   function displayAllReports() {
      /**
       * Displays on the map all the markers associate to a report
       * @return nothing
       */
      $.each(markers, function(marker_id, marker) {
         if(marker.type === 'report') {
            displayMarker(marker_id);
         }
      });
   }

   function hideMarker(report_id){
      /**
      * Hides a marker.
      * @param {string|int} marker_id The id of the marker
      * @return nothing
      */
      if (markers[report_id] && markers[report_id].displayed) {
         marker_source.removeFeature(markers[report_id].feature);
         markers[report_id].displayed = false;
      }
   }

   function hideAllReports() {
      /**
       * Hides all the markers associate to a report
       * @return nothing
       */
      $.each(markers, function(marker_id, marker) {
         if(marker.type === 'report') {
            hideMarker(marker_id);
         }
      });
   }

   function selectMarker(marker_id){
      /**
       * Sets the icon of a marker as 'selected' (in pink)
       * @param {int|string} marker_id The id of the marker (int for report, string for marker)
       * @return nothing
       */
      updateIconStyle(marker_id, 'selected');
   }

   function unselectMarker(marker_id){
      /**
       * Sets the icon of a marker as 'unselected' (in black)
       * @param {int|string} marker_id The id of the marker (int for report, string for marker)
       * @return nothing
       */
      updateIconStyle(marker_id, '');
   }

   function iconName(statuses,term,icon_suffix){
      /** 
       * Generate the icon name
       * @param {array} statuses  (array of the status (color) - only cem is considered)
       * @param {array} term (long medium, short or null)
       * @param {string} icon_suffix The suffix for the icon :
       * - null for the normal icon
       * - 'selected' for the pink icon
       * - 'no_active' for the gray icon
       * @return nothing
       */
      var manager_c = 'w';

      if(typeof statuses !== 'undefined') {
         for (var i = 0; i < (statuses.length); i++) {
            if (statuses[i].t == params.manager_color) {
               manager_c = color_trad[statuses[i].v];
            }
         }
      }

      if (typeof term === 'undefined' || term === null) {
         term = '';
      }
      else {
         term = term + '_';
      }

      if (typeof icon_suffix === 'undefined' || icon_suffix === null) {
         icon_suffix = '';
      }
      else if (icon_suffix !== '') {
         icon_suffix = '_' + icon_suffix;
      }

      return marker_img_url + 'm_' + term + manager_c + icon_suffix +  '.png';
   }

   function unactivateMarkers(){
      /**
       * Display all the markers associated to a report as unactivate (gray)
       * @return nothing
       */
      $.each(markers, function(report_id, marker) {
         if (typeof marker !== 'undefined') {
            updateIconStyle(report_id,'no_active');
         }
      });
   }

   function reactivateMarkers(){
      /**
       * Display all the markers associated to a report as activate (black)
       * @return nothing
       */
      $.each(markers, function(report_id, marker) {
         if (typeof marker !== 'undefined') {
            updateIconStyle(report_id,'');
         }
      });
   }

   function addZoomChangeEvent(event_fct) {
      /**
       * Register a function to trigger when the zoom change
       * @param {function} event_fct The function to trigger
       * @return nothing
       */
      if(zoom_change_event_fct) {
         map.un('moveend', zoom_change_event_fct);
      }

      zoom_change_event_fct = function(evt) {
         var new_zoom_map = map.getView().getZoom();
         if(new_zoom_map != zoom_map) {
            event_fct(evt);
            zoom_map = new_zoom_map;
         }
      };

      map.on('moveend', zoom_change_event_fct);
   }

   function displayLayer(layer_name) {
      /** 
       * Display a layer on the map
       * @param {string} layer_name The name of the layer ('markers' or 'cluster')
       * @return nothing
       */
      if(layer_name in layers && ! layers[layer_name].uello_displayed) {
         map.addLayer(layers[layer_name]);
         layers[layer_name].uello_displayed = true;
      }


   }

   function hideLayer(layer_name) {
      /** 
       * Hide a layer on the map
       * @param {string} layer_name The name of the layer  ('markers' or 'cluster')
       * @return nothing
       */
      if(layer_name in layers && layers[layer_name].uello_displayed) {
         map.removeLayer(layers[layer_name]);
         layers[layer_name].uello_displayed = false;
      }
   }

   function getMapCenter() {
      /** 
       * Gets the center of the map
       * @return the coordinates of the center of the map
       */
      return  map.getView().getCenter();
   }

   return {
      setTarget: setTarget,
      addClickMapEvent: addClickMapEvent,
      rmClickMapEvent: rmClickMapEvent,
      addClickReportEvent: addClickReportEvent,
      rmClickReportEvent: rmClickReportEvent,
      addLastestClickReportEvent: addLastestClickReportEvent,
      centerMapOnMarker: centerMapOnMarker,
      undoCenterMapOnMarker: undoCenterMapOnMarker,
      init: init,
      deleteMarker: deleteMarker,
      deleteReport: deleteReport,
      addReport: addReport,
      unactivateMarkers: unactivateMarkers,
      reactivateMarkers: reactivateMarkers,
      selectMarker: selectMarker,
      unselectMarker: unselectMarker,
      displayMarker: displayMarker,
      displayAllReports: displayAllReports,
      hideMarker: hideMarker,
      hideAllReports: hideAllReports,
      moveMarker: moveMarker,
      addZoomChangeEvent: addZoomChangeEvent,
      displayLayer: displayLayer,
      hideLayer: hideLayer,
      getMapCenter: getMapCenter
   };
});