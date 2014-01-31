require.config({
    paths: {
        'jQuery': 'lib/jQuery/jquery-1.8.2',
        'OpenLayers': 'lib/OpenLayers/OpenLayers',
        'colorbox': 'lib/Colorbox/jquery.colorbox',
    },
    shim: {
        'jQuery': {
            exports: '$'
        },
        'OpenLayers': {
            exports: 'OpenLayers'
        },
        'colorbox':{
            deps: ['jQuery'],
            exports: 'jQuery'
        } 
    }
});

require(['jQuery','user','description_create'],
    function($,user,description_create){

    $.ajaxSetup({ cache: false }); // IE save json data in a cache, this line avoids this behavior

        var data_for_init = $('#data_for_init');
        if (data_for_init.length !== 0)
        {
            var city_name = data_for_init.attr('data-city');
            var city_lon = data_for_init.attr('data-lon');
            var city_lat = data_for_init.attr('data-lat');
            var map_mode = true;
            var marker;

            var map = L.map('map').setView([city_lat, city_lon], 13);
            L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
                maxZoom: 18
                }).addTo(map);

            function onMapClick(e) {
                $("input[name=lon]").val(e.latlng.lat);
                $("input[name=lat]").val(e.latlng.lng);

                if(!marker) {
                    marker = L.marker(e.latlng).addTo(map);
                }
                marker.setLatLng(e.latlng).update();
                $('#show_new_description_form_button').removeAttr('disabled');
            }

            map.on('click', onMapClick);

            if(user.isRegistered()) {
                $("#div_new_place_form_user_mail").hide();
            } else {
                $("#div_new_place_form_user_mail").show();
            }

            $("#put_marker_on_the_map_fieldset").hide();
            $('#show_new_description_form_button').click(function() { 
                $("#add_new_description_div").show();
                $("#show_new_description_form_button").hide();
                $("#map").hide();
                $("#map_instruction").hide();
                $("#show_map_button").show();
            });

            $('#show_map_button').click(function() { 
                $("#add_new_description_div").hide();
                $("#show_new_description_form_button").show();
                $("#map").show();
                $("#map_instruction").show();
                $("#show_map_button").hide();
            });
            
            // Add New Description
            $("#new_place_form_submit_button").click(function() { description_create.catch_creating_form($("#add_new_description_formf")); });
        }
    });