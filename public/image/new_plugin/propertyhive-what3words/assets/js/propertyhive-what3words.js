jQuery(window).on('load', function()
{
    // Update 3 word location field when lat long is changed, or location is changed on the map
    jQuery('#_latitude, #_longitude').change(function()
    {
        get_three_word_location();
    });

    setTimeout(function() { ph_w3w_set_marker_event_listeners() }, 250);

    var what3wordsGeoJson;
    var rectangles = [];

    jQuery('.what3words_checkbox').change(function() {

        var map_provider = jQuery('#_what3words_maps_provider_option').val();

        // If any coloured squares are visible on the map, hide them
        if ( rectangles.length > 0 )
        {
            if ( map_provider == 'osm' )
            {
                rectangles.forEach(rectangle => map.removeLayer(rectangle));
            }
            else
            {
                rectangles.forEach(rectangle => rectangle.setMap(null));
            }
        }

        if(this.checked)
        {
            // If there are any other boxes ticked, untick them and remove any coloured borders
            jQuery('.what3words_checkbox').not(this).removeAttr('checked');
            jQuery('.what3words_custom_location').css('border', '');

            var field_id = jQuery(this).attr('id').split('_').pop();
            var fieldColour = jQuery('#_what3words_custom_location_colour_' + field_id).val();
            jQuery('#_what3words_custom_location_' + field_id).css('border', '1px solid ' + fieldColour);

            // If a location is already selected for this location,display it on the map
            var existing_square = jQuery('#_what3words_custom_location_coords_' + field_id).val();
            if ( existing_square != '' )
            {
                existing_square = existing_square.split('|');
                rectangles = add_rectangle_to_map(rectangles, fieldColour, existing_square, map_provider);
                if ( map_provider == 'osm' )
                {
                    map.panTo(new L.LatLng(existing_square[0], existing_square[2]));
                }
                else
                {
                    map.setCenter(new google.maps.LatLng(existing_square[0], existing_square[2]));
                }
            }

            // Zoom to the default as the grid section API has a maximum grid size it can return
            if ( map.getZoom() < 19 )
            {
                map.setZoom(16);
            }
            // Change the map to satellite view to make selecting a square easier (only possible with Google)
            if ( map_provider == 'google' )
            {
                map.setMapTypeId('satellite');
            }

            // If there isn't already a squared grid on the map, get it from the what3words API and overlay it on the map
            if ( what3wordsGeoJson == undefined )
            {
                if ( map_provider == 'osm' )
                {
                    var bounds = map.getBounds();
                    var boundingBoxElements = [bounds._northEast.lat, bounds._northEast.lng, bounds._southWest.lat, bounds._southWest.lng];
                    var boundingBox = boundingBoxElements.join(',');
                }
                else
                {
                    var boundingBox = map.getBounds().toString().replace(/[()]/g, '');
                }

                jQuery.ajax({
                    type: 'POST',
                    url: ph_what3words_ajax_object.ajax_url,
                    data: {
                        action: 'propertyhive_get_grid_section',
                        boundingBox: boundingBox,
                    },
                    success: function(response)
                    {
                        if (response.data.response.code == 200)
                        {
                            var geojson = JSON.parse(response.data.body);

                            if ( map_provider == 'osm' )
                            {
                                what3wordsGeoJson = L.geoJSON(geojson, {
                                    style: function () {
                                        return {weight: 1, color: '#000000', opacity: 0.2};
                                    }
                                }).addTo(map);
                                var maximumZoom = map.getMaxZoom();
                            }
                            else
                            {
                                what3wordsGeoJson = map.data.addGeoJson(geojson);
                                map.data.setStyle({
                                    strokeWeight: 1,
                                });
                                var maximumZoom = 20;
                            }

                            // Zoom the map further after applying the grid so we have a large enough grid, but the zoom level is more useful
                            if ( map.getZoom() < 19 )
                            {
                                map.setZoom(maximumZoom);
                            }
                        }
                    }
                });
            }

            // Hide the existing map marker, unset the existing map click event add new map onclick for what3words
            if ( map_provider == 'osm' )
            {
                marker.remove();
                map.off( 'click' );

                map.on('click', function(event){
                    rectangles = set_custom_three_word_location(field_id, fieldColour, event.latlng.lat, event.latlng.lng, rectangles, map_provider);
                });
            }
            else
            {
                marker.setMap(null);
                google.maps.event.clearListeners(map, 'click');

                google.maps.event.addListener(map, 'click', function(event)
                {
                    rectangles = set_custom_three_word_location(field_id, fieldColour, event.latLng.lat(), event.latLng.lng(), rectangles, map_provider);
                });
            }
        }
        else
        {
            // Remove any checked boxes and the border colour
            jQuery('.what3words_checkbox').not(this).removeAttr('checked');
            jQuery('.what3words_custom_location').css('border', '');

            // If there is a grid on the map, remove it and unset the variable
            if (what3wordsGeoJson != undefined)
            {
                if ( map_provider == 'osm' )
                {
                    what3wordsGeoJson.remove();
                }
                else
                {
                    for (var i = 0; i < what3wordsGeoJson.length; i++)
                    {
                        map.data.remove(what3wordsGeoJson[i]);
                    }
                }
            }
            what3wordsGeoJson = undefined;

            map.setZoom(16);
            if ( map_provider == 'osm' )
            {
                // Recreate a map marker for the main property location
                marker = L.marker([jQuery('#_latitude').val(), jQuery('#_longitude').val()], { draggable:true }).addTo(map).on('moveend', marker_move_end);

                // Remove the what3words map click action and replace it with the regular map actions
                map.off( 'click' );
                map.on('click', function(e){
                    if ( marker != null )
                    {
                        marker.remove();
                    }
                    marker = L.marker(e.latlng, { draggable:true }).addTo(map).on('moveend', marker_move_end);
                    jQuery('#_latitude').val(e.latlng.lat);
                    jQuery('#_longitude').val(e.latlng.lng);

                    jQuery('#help-marker-not-set').fadeOut('fast', function()
                    {
                        jQuery('#help-marker-set').fadeIn();
                    });

                    get_three_word_location();
                });
            }
            else
            {
                // Reset map back to default roadmap view
                map.setMapTypeId('roadmap');

                // Re-show the default map marker
                marker.setMap(map);

                // Remove the what3words map click action and replace it with the regular map actions
                google.maps.event.clearListeners(map, 'click');
                google.maps.event.addListener(map, 'click', function(event)
                {
                    marker = ph_create_marker(event.latLng.lat(), event.latLng.lng());
                    jQuery('#_latitude').val(event.latLng.lat());
                    jQuery('#_longitude').val(event.latLng.lng());

                    get_three_word_location();
                });
            }
        }
    });

    function set_custom_three_word_location(field_id, fieldColour, latitude, longitude, rectangles, map_provider)
    {
        // Use the API to get the 3 word location for the lat long that was clicked
        jQuery.ajax({
            type: 'POST',
            url: ph_what3words_ajax_object.ajax_url,
            data: {
                action: 'propertyhive_get_three_word_location',
                latitude: latitude,
                longitude: longitude,
            },
            success: function(response)
            {
                // Set the text input and hidden field to the location and the square coordinates
                jQuery('#_what3words_custom_location_' + field_id).val(response.location);

                var concat_coords = [response.square.northeast.lat, response.square.southwest.lat, response.square.northeast.lng, response.square.southwest.lng];
                jQuery('#_what3words_custom_location_coords_' + field_id).val(concat_coords.join('|'));

                // Remove any existing selected squares, then add the new one
                if ( rectangles.length > 0 )
                {
                    if ( map_provider == 'osm' )
                    {
                        rectangles.forEach(rectangle => map.removeLayer(rectangle));
                    }
                    else
                    {
                        rectangles.forEach(rectangle => rectangle.setMap(null));
                    }
                }

                var coordinatesArray = [response.square.northeast.lat, response.square.southwest.lat, response.square.northeast.lng, response.square.southwest.lng];
                rectangles = add_rectangle_to_map(rectangles, fieldColour, coordinatesArray, map_provider);
            }
        });
        return rectangles;
    }

    function add_rectangle_to_map(rectangles, fieldColour, coordinates, map_provider)
    {
        if ( map_provider == 'osm' )
        {
            var bounds = [[Number(coordinates[0]), Number(coordinates[2])], [Number(coordinates[1]), Number(coordinates[3])]];

            var options = {
                color: '#000000',
                opacity: 0.8,
                weight: 1,
                fillColor: fieldColour,
                fillOpacity: 0.35,
            };
            var rectangle = L.rectangle(bounds, options).addTo(map);
        }
        else
        {
            var rectangle = new google.maps.Rectangle({
                strokeColor: '#000000',
                strokeOpacity: 0.8,
                strokeWeight: 1,
                fillColor: fieldColour,
                fillOpacity: 0.35,
                map,
                bounds: {
                    north: Number(coordinates[0]),
                    south: Number(coordinates[1]),
                    east: Number(coordinates[2]),
                    west: Number(coordinates[3]),
                },
            });
        }
        rectangles.push(rectangle);
        return rectangles;
    }
});

function ph_w3w_set_marker_event_listeners()
{
    if ( jQuery('#_what3words_maps_provider_option').val() == 'osm' )
    {
        marker.on('dragend', function(event){
            get_three_word_location();
        });

        map.on('click', function(event){
            get_three_word_location();
        });
    }
    else
    {
        if ( markerSet )
        {
            google.maps.event.addListener(marker, 'dragend', function(event)
            {
                get_three_word_location();
            });

            google.maps.event.addListener(map, 'click', function(event)
            {
                get_three_word_location();
            });
        }
        
    }
}

// Based on the latitude and longitude fields, use the what3words API to get the 3 word location
function get_three_word_location()
{
    var latitude = jQuery('#_latitude').val();
    var longitude = jQuery('#_longitude').val();

    if ( latitude != '' && longitude != '' && latitude != '0' && longitude != '0' )
    {
        jQuery.ajax({
            type: 'POST',
            url: ph_what3words_ajax_object.ajax_url,
            data: {
                action: 'propertyhive_get_three_word_location',
                latitude: latitude,
                longitude: longitude,
            },
            success: function(response)
            {
                jQuery('#_what3words_location').val(response.location);
            }
        });
    }
}