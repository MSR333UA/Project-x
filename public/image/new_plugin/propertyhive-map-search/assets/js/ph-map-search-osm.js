var ph_map; // Global declaration of the map
var ph_map_lat_lngs = new Array();
var ph_map_polygon_lat_lngs = new Array();
var ph_map_markers_cluster;
var ph_map_markers = new Array();
var ph_polygon = false;
var ph_doing_fetch = false;
var ph_done_initial_fit = false;

jQuery(document).ready(function()
{
    jQuery('#ph_draw_a_search_clear').click(function(e)
    {
        e.preventDefault();

        ph_map.removeLayer(ph_polygon);
        ph_polygon = false;

        ph_map_lat_lngs = new Array();

        var drawnItems = new L.FeatureGroup();
        ph_map.addLayer(drawnItems);
        var drawControl = new L.Control.Draw({
            edit: {
                featureGroup: drawnItems,
            },
            draw: {
                polygon: {
                    shapeOptions: {
                        fillColor: ajax_object.draw_options.fill_color,
                        fillOpacity: ajax_object.draw_options.fill_opacity,
                        color: ajax_object.draw_options.stroke_color,
                        opacity: ajax_object.draw_options.stroke_opacity,
                        weight: ajax_object.draw_options.stroke_weight
                    },
                }
            }
        });
        
        new L.Draw.Polygon(ph_map, drawControl.options.draw.polygon).enable();

        ph_map.on(L.Draw.Event.CREATED, function (e) 
        {
            polygon_complete(e, drawnItems);
        });
    });
});

function polygon_complete(e, drawnItems)
{
    // when polygon is complete
    ph_polygon = e.layer;

    drawnItems.addLayer(ph_polygon);

    ph_polygon.editing.enable();

    // Set 'View Properties' link
    // Add new 'pgp' parameter
    new_location = jQuery('#ph_draw_a_search_view').attr('href');
    new_location = ph_remove_url_parameter(new_location, 'pgp');
    if ( new_location.indexOf('?') != -1 )
    {
        new_location = new_location + '&';
    }
    else
    {
        new_location = new_location + '?';
    }

    var polygon_lat_lngs = ph_polygon.getLatLngs();
    polygon_lat_lngs = polygon_lat_lngs[0];

    for ( var i in polygon_lat_lngs )
    {
        ph_map_lat_lngs.push(polygon_lat_lngs[i]);
    }

    var polygon = L.polyline(polygon_lat_lngs);

    new_location = new_location + 'pgp=' + encodeURIComponent( polygon.encodePath() );

    jQuery('#ph_draw_a_search_view')
        .attr('disabled', false)
        .attr('href', new_location);

    ph_polygon.on("edit", function(e) 
    {
        ph_map_lat_lngs = new Array();

        new_location = jQuery('#ph_draw_a_search_view').attr('href');
        new_location = ph_remove_url_parameter(new_location, 'pgp');
        if ( new_location.indexOf('?') != -1 )
        {
            new_location = new_location + '&';
        }
        else
        {
            new_location = new_location + '?';
        }
        var polygon_lat_lngs = ph_polygon.getLatLngs();
        polygon_lat_lngs = polygon_lat_lngs[0];

        for ( var i in polygon_lat_lngs )
        {
            ph_map_lat_lngs.push(polygon_lat_lngs[i]);
        }

        var polygon = L.polyline(polygon_lat_lngs);

        new_location = new_location + 'pgp=' + encodeURIComponent( polygon.encodePath() );

        jQuery('#ph_draw_a_search_view')
            .attr('disabled', false)
            .attr('href', new_location);
    });
}

function propertyhive_init_map()
{
    ph_map = L.map("propertyhive_map_canvas", {drawControl: true, scrollWheelZoom: scrollwheel}).setView([map_center_lat, map_center_lng], ( default_zoom_level != false ? default_zoom_level : 13));

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(ph_map);

    if ( !draw_mode )
    {
        if ( pgp != '' )
        {
            var polygon = L.Polygon.fromEncoded(pgp, {
                fillColor: ajax_object.draw_options.fill_color,
                fillOpacity: ajax_object.draw_options.fill_opacity,
                color: ajax_object.draw_options.stroke_color,
                opacity: ajax_object.draw_options.stroke_opacity,
                weight: ajax_object.draw_options.stroke_weight
            }).addTo(ph_map);

            var polygon_lat_lngs = polygon.getLatLngs();

            for ( var i in polygon_lat_lngs )
            {
                ph_map_polygon_lat_lngs.push(polygon_lat_lngs[i]);
            }
        }
        
        ph_get_map_properties();

        if ( refresh_on_bounds_changed )
        {
            ph_map.on('moveend', function(e) {
                if ( !ph_doing_fetch && ph_done_initial_fit )
                {
                    ph_get_map_properties();
                }
            });
        }
    }
    else
    {
        var drawnItems = new L.FeatureGroup();
        ph_map.addLayer(drawnItems);
        var drawControl = new L.Control.Draw({
            edit: {
                featureGroup: drawnItems
            },
            draw: {
                polygon: {
                    shapeOptions: {
                        fillColor: ajax_object.draw_options.fill_color,
                        fillOpacity: ajax_object.draw_options.fill_opacity,
                        color: ajax_object.draw_options.stroke_color,
                        opacity: ajax_object.draw_options.stroke_opacity,
                        weight: ajax_object.draw_options.stroke_weight
                    }
                }
            }
        });

        if ( pgp != '' )
        {
            ph_polygon = L.Polygon.fromEncoded(pgp, {
                fillColor: ajax_object.draw_options.fill_color,
                fillOpacity: ajax_object.draw_options.fill_opacity,
                color: ajax_object.draw_options.stroke_color,
                opacity: ajax_object.draw_options.stroke_opacity,
                weight: ajax_object.draw_options.stroke_weight
            }).addTo(ph_map);

            var polygon_lat_lngs = ph_polygon.getLatLngs();

            for ( var i in polygon_lat_lngs )
            {
                ph_map_polygon_lat_lngs.push(polygon_lat_lngs[i]);
            }

            ph_polygon.editing.enable();

            // Set 'View Properties' link
            // Add new 'pgp' parameter
            new_location = jQuery('#ph_draw_a_search_view').attr('href');
            new_location = ph_remove_url_parameter(new_location, 'pgp');
            if ( new_location.indexOf('?') != -1 )
            {
                new_location = new_location + '&';
            }
            else
            {
                new_location = new_location + '?';
            }

            var polygon_lat_lngs = ph_polygon.getLatLngs();
            polygon_lat_lngs = polygon_lat_lngs[0];

            for ( var i in polygon_lat_lngs )
            {
                ph_map_polygon_lat_lngs.push(polygon_lat_lngs[i]);
            }

            var polygon = L.polyline(polygon_lat_lngs);

            new_location = new_location + 'pgp=' + encodeURIComponent( polygon.encodePath() );

            jQuery('#ph_draw_a_search_view')
                .attr('disabled', false)
                .attr('href', new_location);

            ph_polygon.on("edit", function(e) 
            {
                ph_map_polygon_lat_lngs = new Array();

                new_location = jQuery('#ph_draw_a_search_view').attr('href');
                new_location = ph_remove_url_parameter(new_location, 'pgp');
                if ( new_location.indexOf('?') != -1 )
                {
                    new_location = new_location + '&';
                }
                else
                {
                    new_location = new_location + '?';
                }
                var polygon_lat_lngs = ph_polygon.getLatLngs();
                polygon_lat_lngs = polygon_lat_lngs[0];

                for ( var i in polygon_lat_lngs )
                {
                    ph_map_polygon_lat_lngs.push(polygon_lat_lngs[i]);
                }

                var polygon = L.polyline(polygon_lat_lngs);

                new_location = new_location + 'pgp=' + encodeURIComponent( polygon.encodePath() );

                jQuery('#ph_draw_a_search_view')
                    .attr('disabled', false)
                    .attr('href', new_location);
            });
        }
        else
        {
            new L.Draw.Polygon(ph_map, drawControl.options.draw.polygon).enable();

            ph_map.on(L.Draw.Event.CREATED, function (e) 
            {
                polygon_complete(e, drawnItems);
            });
        }

    }
}

// http://stackoverflow.com/questions/1634748/how-can-i-delete-a-query-string-parameter-in-javascript
function ph_remove_url_parameter(url, parameter) 
{
    //prefer to use l.search if you have a location/link object
    var urlparts = url.split('?');   
    if (urlparts.length>=2) {

        var prefix = encodeURIComponent(parameter)+'=';
        var pars = urlparts[1].split(/[&;]/g);

        //reverse iteration as may be destructive
        for (var i = pars.length; i-- > 0;) {    
            //idiom for string.startsWith
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {  
                pars.splice(i, 1);
            }
        }

        url = urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
        return url;
    } else {
        return url;
    }
}

function ph_get_map_properties()
{
    ph_doing_fetch = true;

    jQuery('.propertyhive-map-canvas-wrapper .map-loading').fadeIn('fast');

    // clear any exising markers
    for ( var i in ph_map_markers )
    {
        ph_map.removeLayer(ph_map_markers[i]);
    }
    ph_map_lat_lngs = new Array();
    ph_map_markers = new Array();
    ph_map_popups = new Array();

    // We have a query. Pass it to AJAX
    var data = {
        'action': 'propertyhive_load_map_properties',
        'atts': ajax_object.atts
    };

    default_zoom_level = default_zoom_level || false;
    override_center = override_center || false;

    if ( refresh_on_bounds_changed && (ph_done_initial_fit || ( !ph_done_initial_fit && ( default_zoom_level != false || override_center != false ) ) ) )
    {
        var bounds =  ph_map.getBounds();
        if ( typeof bounds != 'undefined' )
        {
            var ne = bounds.getNorthEast();
            var sw = bounds.getSouthWest();

            data.ne_lat = ne.lat;
            data.ne_lng = ne.lng;
            data.sw_lat = sw.lat;
            data.sw_lng = sw.lng;
        }
    }

    if ( typeof map_property_query != 'undefined' && map_property_query != '' )
    {
        data.map_property_query = map_property_query;
    }

    jQuery.post(ajax_object.ajax_url, data, function( data ) 
    {
        jQuery('.propertyhive-map-canvas-wrapper .map-over-limit').hide();

        if ( marker_clustering_enabled )
        {
            ph_map_markers_cluster = L.markerClusterGroup();
        }

        // Here we'll receive a JSON object containing the properties
        if ( data.properties.length > 0 )
        {
            if ( data.over_limit )
            {
                jQuery('.propertyhive-map-canvas-wrapper .map-over-limit span').html(data.total);
                jQuery('.propertyhive-map-canvas-wrapper .map-over-limit').show();
            }

            var lat_lngs_done = new Array();
            for ( var i in data.properties )
            {
                var property = data.properties[i];

                var done_lat_lng_already = new Array();
                for ( var j in lat_lngs_done )
                {
                    if ( lat_lngs_done[j] == property.latitude + ',' + property.longitude )
                    {
                        done_lat_lng_already.push(j);
                    }
                }

                if ( done_lat_lng_already.length == 0 )
                {
                    ph_add_property_marker(property, false);
                }
                else
                {
                    ph_add_property_marker(property, done_lat_lng_already); // only push infowindow content
                }

                lat_lngs_done[property.id] = property.latitude + ',' + property.longitude;
            }

            if ( marker_clustering_enabled )
            {
                ph_map.addLayer(ph_map_markers_cluster);
            }

            ph_fit_map_to_bounds(default_zoom_level, override_center);
        }
        else if ( pgp != '' )
        {
            // no results but a polygon exists so still want to fit to bounds
            ph_fit_map_to_bounds();
        }
        else
        {
            ph_doing_fetch = false;
            ph_done_initial_fit = true;
        }

        jQuery('.propertyhive-map-canvas-wrapper .map-loading').fadeOut();
    }, 'json');
}

function ph_map_decode_html(html) {
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}

function ph_add_property_marker( property, existing_property_ids_with_this_lat_lng )
{
    /*if ( existing_property_ids_with_this_lat_lng !== false )
    {
        var primary_property_id = false;
        for ( var i in ph_map_popups)
        {
            for ( var j in existing_property_ids_with_this_lat_lng )
            {
                if ( i == existing_property_ids_with_this_lat_lng[j] )
                {
                    primary_property_id = parseInt(existing_property_ids_with_this_lat_lng[j]);
                }
            }
        }

        var summary_html = '';
        if ( property.department == 'residential-sales' || property.department == 'residential-lettings' )
        {
            if ( property.bedrooms != '' && property.bedrooms != '0' )
            {
                summary_html += property.bedrooms + ' bed ';
            }
        }
        summary_html += property.type_name + ' | ' + property.availability;
        var property_list_html = eval("'" + ajax_object.infowindow_html + "'");

        ph_map_popups[primary_property_id].args.html = ph_map_popups[primary_property_id].args.html.replace('<div class="properties">', '<div class="properties">' + property_list_html);
    }
    else
    {*/
        var markerOptions = { title: ph_map_decode_html(property.address) };

        if ( typeof icon_type != 'undefined' && icon_type != '' )
        {
            switch (icon_type)
            {
                case "custom_single":
                {
                    if ( typeof marker_icon != 'undefined' && marker_icon != '' )
                    {
                        var custom_icon = { iconUrl: marker_icon };
                        if ( marker_icon_width !== undefined && marker_icon_height !== undefined )
                        {
                            custom_icon.iconSize = [marker_icon_width, marker_icon_height];
                            if ( marker_icon_anchor == 'center' )
                            {
                                // Relative to image size, where the anchor point is
                                custom_icon.iconAnchor = [Math.floor(marker_icon_width / 2), Math.floor(marker_icon_height / 2)];

                                // Relative to image anchor point, where the popup should show from
                                custom_icon.popupAnchor = [0, -Math.floor(marker_icon_height / 2)];
                            }
                            else
                            {
                                // Relative to image size, where the anchor point is
                                custom_icon.iconAnchor = [Math.floor(marker_icon_width / 2), marker_icon_height];

                                // Relative to image anchor point, where the popup should show from
                                custom_icon.popupAnchor = [0, -marker_icon_height];
                            }
                        }
                        markerOptions.icon = L.icon(custom_icon);
                    }
                    break;
                }
                case "custom_per_department":
                {
                    var custom_icon = {};
                    if ( property.department == 'residential-sales' && typeof marker_icon_residential_sales != 'undefined' )
                    {
                        custom_icon.iconUrl = marker_icon_residential_sales;
                        if ( marker_icon_residential_sales_width !== undefined && marker_icon_residential_sales_height !== undefined )
                        {
                            custom_icon.iconSize = [marker_icon_residential_sales_width, marker_icon_residential_sales_height];
                            if ( marker_icon_anchor == 'center' )
                            {
                                // Relative to image size, where the anchor point is
                                custom_icon.iconAnchor = [Math.floor(marker_icon_residential_sales_width / 2), Math.floor(marker_icon_residential_sales_height / 2)];

                                // Relative to image anchor point, where the popup should show from
                                custom_icon.popupAnchor = [0, -Math.floor(marker_icon_residential_sales_height / 2)];
                            }
                            else
                            {
                                // Relative to image size, where the anchor point is
                                custom_icon.iconAnchor = [Math.floor(marker_icon_residential_sales_width / 2), marker_icon_residential_sales_height];

                                // Relative to image anchor point, where the popup should show from
                                custom_icon.popupAnchor = [0, -marker_icon_residential_sales_height];
                            }
                        }
                        markerOptions.icon = L.icon(custom_icon);
                    }
                    if ( property.department == 'residential-lettings' && typeof marker_icon_residential_lettings != 'undefined' )
                    {
                        custom_icon.iconUrl = marker_icon_residential_lettings;
                        if ( marker_icon_residential_lettings_width !== undefined && marker_icon_residential_lettings_height !== undefined )
                        {
                            custom_icon.iconSize = [marker_icon_residential_lettings_width, marker_icon_residential_lettings_height];
                            if ( marker_icon_anchor == 'center' )
                            {
                                // Relative to image size, where the anchor point is
                                custom_icon.iconAnchor = [Math.floor(marker_icon_residential_lettings_width / 2), Math.floor(marker_icon_residential_lettings_height / 2)];

                                // Relative to image anchor point, where the popup should show from
                                custom_icon.popupAnchor = [0, -Math.floor(marker_icon_residential_lettings_height / 2)];
                            }
                            else
                            {
                                // Relative to image size, where the anchor point is
                                custom_icon.iconAnchor = [Math.floor(marker_icon_residential_lettings_width / 2), marker_icon_residential_lettings_height];

                                // Relative to image anchor point, where the popup should show from
                                custom_icon.popupAnchor = [0, -marker_icon_residential_lettings_height];
                            }
                        }
                        markerOptions.icon = L.icon(custom_icon);
                    }
                    if ( property.department == 'commercial' && typeof marker_icon_commercial != 'undefined' )
                    {
                        custom_icon.iconUrl = marker_icon_commercial;
                        if ( marker_icon_commercial_width !== undefined && marker_icon_commercial_height !== undefined )
                        {
                            custom_icon.iconSize = [marker_icon_commercial_width, marker_icon_commercial_height];
                            if ( marker_icon_anchor == 'center' )
                            {
                                // Relative to image size, where the anchor point is
                                custom_icon.iconAnchor = [Math.floor(marker_icon_commercial_width / 2), Math.floor(marker_icon_commercial_height / 2)];

                                // Relative to image anchor point, where the popup should show from
                                custom_icon.popupAnchor = [0, -Math.floor(marker_icon_commercial_height / 2)];
                            }
                            else
                            {
                                // Relative to image size, where the anchor point is
                                custom_icon.iconAnchor = [Math.floor(marker_icon_commercial_width / 2), marker_icon_commercial_height];

                                // Relative to image anchor point, where the popup should show from
                                custom_icon.popupAnchor = [0, -marker_icon_commercial_height];
                            }
                        }
                        markerOptions.icon = L.icon(custom_icon);
                    }
                    break;
                }
                case "custom_per_type":
                case "custom_per_availability":
                {
                    if (icon_type == 'custom_per_type')
                    {
                        var taxonomy_id = property.type_id;
                    }
                    else
                    {
                        var taxonomy_id = property.availability_id;
                    }

                    if ( typeof marker_icons != 'undefined'  && typeof marker_icons[taxonomy_id] != 'undefined' )
                    {
                        var custom_icon = { iconUrl: marker_icons[taxonomy_id] };
                        if ( marker_icon_width !== undefined && marker_icon_height !== undefined )
                        {
                            custom_icon.iconSize = [marker_icon_width, marker_icon_height];
                            if ( marker_icon_anchor == 'center' )
                            {
                                // Relative to image size, where the anchor point is
                                custom_icon.iconAnchor = [Math.floor(marker_icon_width / 2), Math.floor(marker_icon_height / 2)];

                                // Relative to image anchor point, where the popup should show from
                                custom_icon.popupAnchor = [0, -Math.floor(marker_icon_height / 2)];
                            }
                            else
                            {
                                // Relative to image size, where the anchor point is
                                custom_icon.iconAnchor = [Math.floor(marker_icon_width / 2), marker_icon_height];

                                // Relative to image anchor point, where the popup should show from
                                custom_icon.popupAnchor = [0, -marker_icon_height];
                            }
                        }
                        markerOptions.icon = L.icon(custom_icon);
                    }
                    break;
                }
            }
        }
        var marker = L.marker([parseFloat(property.latitude), parseFloat(property.longitude)], markerOptions);

        var summary_html = '';
        if ( property.department == 'residential-sales' || property.department == 'residential-lettings' )
        {
            if ( property.bedrooms != '' && property.bedrooms != '0' )
            {
                summary_html += property.bedrooms + ' bed ';
            }
        }
        summary_html += property.type_name + ' | ' + property.availability;
        var property_list_html = eval("'" + ajax_object.infowindow_html + "'");

        var properties_html = '<div class="properties-map-popup leaflet">';
        properties_html += '<div class="properties">' + property_list_html + '</div>';
        properties_html += '</div>';

        marker.bindPopup(properties_html)

        if ( marker_clustering_enabled )
        {
            ph_map_markers_cluster.addLayer(marker);
        }
        else
        {
            marker.addTo(ph_map);
        }

        ph_map_markers.push(marker);
        ph_map_lat_lngs.push([parseFloat(property.latitude), parseFloat(property.longitude)]);

        return marker;
    //}
}

function ph_fit_map_to_bounds( default_zoom_level, override_center ) 
{
    if ( ph_done_initial_fit )
    {
        ph_doing_fetch = false;
        return;
    }

    default_zoom_level = default_zoom_level || false;
    override_center = override_center || false;

    if ( default_zoom_level != false && override_center != false )
    {
        /*ph_map.setZoom(default_zoom_level); 

        var myLatlng = new google.maps.LatLng(map_center_lat, map_center_lng);
        ph_map.panTo([map_center_lat, map_center_lng]);*/ 
    }
    else
    {
        if ( ph_map_lat_lngs.length > 0 || ph_map_polygon_lat_lngs.length > 0 ) 
        {
            var fit_bounds = new Array();
            if ( ph_map_lat_lngs.length > 0 )
            {
                fit_bounds = ph_map_lat_lngs;
            }
            if ( ph_map_polygon_lat_lngs.length > 0 )
            {
                fit_bounds = fit_bounds.concat(ph_map_polygon_lat_lngs);
            }
            ph_map.fitBounds(fit_bounds, { padding: L.point(20, 20) });

        }

        if ( default_zoom_level != false )
        {
            ph_map.setZoom(default_zoom_level); 
        }
        if ( override_center != false )
        {
            ph_map.panTo([map_center_lat, map_center_lng]); 
        }
    }

    setTimeout(function() { ph_doing_fetch = false; ph_done_initial_fit = true; }, 1000);
}

if (window.addEventListener) {
    window.addEventListener('load', propertyhive_init_map);
}else{
    window.attachEvent('onload', propertyhive_init_map);
}

function CustomMarker(latlng, map, args) {
    this.latlng = latlng;   
    this.args = args;

    this.setMap(map);   
}