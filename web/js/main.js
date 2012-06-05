/**
 * PARAMETERS
 */
var map; // will contain the openlayers map
var osm; // will contain the OpenStreetMap layer

var zoom_map = 13; // zoom level of the map
var img_url =  '../OpenLayers/img/'  // where is the dir containing the OpenLayers images

$.ajaxSetup({ cache: false }); // IE save json data in a cache, this line avoids this behavior


/**
 * FUNCTION
 */

function mapWithClickActionMarkers(townId, townLon, townLat, clickAction) {
    /**
     * Create a map with markers. When the user click on a marker,
     an action is executed.
     * @param {string} townId Identifier of the town
     * @param {number} townLon Longitude of the town
     * @param {number} townLat Latitude of the town
     * @param {function} clickAction Action executed when the user click on the
     marker
     */
    jsonUrlData  = '../app_dev.php/place/list/bycity.json?city=' + townId;
    map = new OpenLayers.Map('map');
    osm = new OpenLayers.Layer.OSM("OSM MAP");
    map.addLayer(osm);
    map.setCenter(
        new OpenLayers.LonLat(townLon, townLat).transform(
            new OpenLayers.Projection("EPSG:4326"),
            map.getProjectionObject()
        ), zoom_map );
      
    var markersLayer = new OpenLayers.Layer.Markers( "Markers" );
    map.addLayer(markersLayer);

    $.getJSON(jsonUrlData, function(data) {
	$.each(data.results, function(index, aPlaceData) {
	    addMarkerWithClickAction(markersLayer,
				     aPlaceData.geom.coordinates[0],
				     aPlaceData.geom.coordinates[1],
				     clickAction,
				     aPlaceData); } ) }
	     ); }

function addMarkerWithClickAction(aLayer, aLon, aLat, anEventFunction, someData) {
    /**
     * Add a marker on a layer such that when the user click on it, an 
     action is executed.
     * @param {OpenLayers.Layer} aLayer The layer where the marker is added
     * @param {number} aLon The longitude where to add the marker
     * @param {number} aLat The latitude where to add the marker
     * @param {function} anEventFunction A function to execute when the user click on the marker
     * @param {object} someData Some dota passed to the function anEvent
     */
    var feature = new OpenLayers.Feature(osm, new OpenLayers.LonLat(aLon, aLat).transform(
	new OpenLayers.Projection("EPSG:4326"),
	map.getProjectionObject()
    ));
    var size = new OpenLayers.Size(21,25);
    var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
    var icon = new OpenLayers.Icon(img_url + 'marker.png', size, offset);  
    feature.data.icon = icon;
    
    var marker = feature.createMarker();

    var markerMouseDownFunction = function(evt) {
	anEventFunction(marker,someData); 
        OpenLayers.Event.stop(evt);
    };
    marker.events.register("mousedown", feature, markerMouseDownFunction);
    aLayer.addMarker(marker);
}

function displayPlaceDataFunction(placeMarker, placeData) {
    /**
     * Function which display some data of the place on the webpage.
     executed when the user click on a marker on the index page.
     For this page, a marker represents a place
     * @param {OpenLayers.Marker} placeMarker The marker clicked
     * @param {object} placeData The know data given for the place and receivd from 
     web/app_dev.php/place/list/bycity.json?city=mons
     */
    
    document.getElementById("span_id").innerHTML = placeData.id;
    document.getElementById("span_description").innerHTML = placeData.description;
    document.getElementById("span_nbComm").innerHTML = placeData.nbComm;
    document.getElementById("span_nbVote").innerHTML = placeData.nbVote;
    document.getElementById("span_creator").innerHTML = placeData.creator.label;
    
    document.getElementById("div_placeDetails").style.display = "block";
}



// to be continue
function view_place(place_lon, place_lat) {
    /**
     * Doc TODO
     */
    map = new OpenLayers.Map('map');
    osm = new OpenLayers.Layer.OSM("OSM Map");
    map.addLayer(osm);

    map.setCenter(
        new OpenLayers.LonLat(place_lon, place_lat).transform(
            new OpenLayers.Projection("EPSG:4326"),
            map.getProjectionObject()
        ), zoom_map );

    var markers = new OpenLayers.Layer.Markers("Markers");
    map.addLayer(markers);

    var feature = new OpenLayers.Feature(osm, 
         new OpenLayers.LonLat(place_lon, place_lat).transform(
             new OpenLayers.Projection("EPSG:4326"),
             map.getProjectionObject()
        ));
    var size = new OpenLayers.Size(21,25);
    var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
    var icon = new OpenLayers.Icon(img_url + 'marker.png', size, offset);  
    feature.data.icon = icon;
    
    var marker = feature.createMarker();
    markers.addMarker(marker);
}

