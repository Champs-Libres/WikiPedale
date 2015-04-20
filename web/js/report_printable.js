/* jslint vars: true */
/*jslint indent: 4 */ // passer à 3?
/* global ol, web_dir */
'use strict';

var reportPrintable = {};

reportPrintable.getColor = function(statusInt) {
    switch (statusInt) {
        case 0:
            return 'w';
        case -1:
            return 'd';
        case 1:
            return 'r';
        case 2:
            return 'o';
        case 3:
            return 'g';
    }
};

reportPrintable.launchMap = function(div, lon, lat, status) {

    var iconFeature = new ol.Feature({
        geometry: new ol.geom.Point(ol.proj.transform([lon, lat], 'EPSG:4326', 'EPSG:3857'))
    });

    var iconStyle = new ol.style.Style({
        image: new ol.style.Icon(({
            src:web_dir + 'img/OpenLayers/m_' + this.getColor(status) + '.png',
            scale: 0.75,
            anchor: [0.5,1]
        }))
    });

    iconFeature.setStyle(iconStyle);

    var vectorSource = new ol.source.Vector({
        features: [iconFeature]
    });

    var vectorLayer = new ol.layer.Vector({
        source: vectorSource
    });

    new ol.Map({
        target: div,
        layers: [
            new ol.layer.Tile({
                source: new ol.source.OSM()
            }),
            vectorLayer
        ],
        view: new ol.View({
            center: ol.proj.transform([lon, lat], 'EPSG:4326', 'EPSG:3857'),
            zoom: 18
        })
    });
};