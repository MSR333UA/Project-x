var ph_map; // Global declaration of the map
var ph_map_lat_lngs = new Array();
var ph_map_polygon_lat_lngs = new Array();
var ph_map_markers = new Array();
var ph_map_popups = new Array();
var ph_drawing_manager;
var ph_polygon = false;
var ph_doing_fetch = false;
var ph_done_initial_fit = false;

jQuery(document).ready(function()
{
    jQuery('#ph_draw_a_search_clear').click(function(e)
    {
        e.preventDefault();

        ph_polygon_clear();
    });

    if ( ajax_object.format == 'split' )
    {
        jQuery('ul.properties > li.property').hover(
            function(e)
            {
                var post_id = false;
                var classes = jQuery(this).attr('class').split(' ');
                for ( var i in classes)
                {
                    if ( classes[i].slice(0, 5) == 'post-' )
                    {
                        post_id = classes[i].replace("post-", "");
                    }
                }
                
                if ( post_id != false && ph_map_markers[parseInt(post_id)] )
                {
                    ph_map_markers[parseInt(post_id)].setAnimation(google.maps.Animation.BOUNCE);
                }
            },
            function(e)
            {
                for ( var i in ph_map_markers )
                {
                    ph_map_markers[i].setAnimation(null);
                }
            }
        );
    };
});

function propertyhive_init_map()
{
    var myLatlng = new google.maps.LatLng(map_center_lat, map_center_lng);

    var myOptions = {
        zoom: ( default_zoom_level != false ? default_zoom_level : 13),
        center: myLatlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        scrollwheel: scrollwheel
    }
    if ( typeof map_style_js != 'undefined' && map_style_js.length > 0 )
    {
        myOptions.styles = map_style_js;
    }
    ph_map = new google.maps.Map(document.getElementById("propertyhive_map_canvas"), myOptions);

    if ( show_transit_layer )
    {
        const transitLayer = new google.maps.TransitLayer();
        transitLayer.setMap(ph_map);
    }

    if ( !draw_mode && refresh_on_bounds_changed )
    {
        google.maps.event.addListener(ph_map, 'dragend', function() {
            if ( !ph_doing_fetch && ph_done_initial_fit )
            {
                ph_get_map_properties();
            }
        });
        google.maps.event.addListener(ph_map, 'zoom_changed', function() {
            if ( !ph_doing_fetch && ph_done_initial_fit )
            {
                ph_get_map_properties();
            }
        });
    }

    if ( !draw_mode )
    {
        if ( pgp != '' )
        {
            ph_polygon = new google.maps.Polygon({
                paths: google.maps.geometry.encoding.decodePath(pgp),
                fillColor: ajax_object.draw_options.fill_color,
                fillOpacity: ajax_object.draw_options.fill_opacity,
                strokeColor: ajax_object.draw_options.stroke_color,
                strokeOpacity: ajax_object.draw_options.stroke_opacity,
                strokeWeight: ajax_object.draw_options.stroke_weight,
            });
            ph_polygon.setMap(ph_map);

            var path = ph_polygon.getPath();
            for ( var i = 0; i < path.length; i++ )
            {
                var xy = path.getAt(i);
                ph_map_polygon_lat_lngs.push(new google.maps.LatLng(xy.lat(), xy.lng()));
            }
        }

        google.maps.event.addListenerOnce(ph_map, 'idle', function()
        {
            ph_get_map_properties();
        });
    }
    else
    {
        ph_drawing_manager = new google.maps.drawing.DrawingManager({
            drawingMode: google.maps.drawing.OverlayType.POLYGON,
            drawingControl: false,
            polygonOptions: {
                fillColor: ajax_object.draw_options.fill_color,
                fillOpacity: ajax_object.draw_options.fill_opacity,
                strokeColor: ajax_object.draw_options.stroke_color,
                strokeOpacity: ajax_object.draw_options.stroke_opacity,
                strokeWeight: ajax_object.draw_options.stroke_weight,
                suppressUndo: true
            }
        });
        ph_drawing_manager.setMap(ph_map);

        if ( pgp != '' )
        {
            ph_polygon = new google.maps.Polygon({
                paths: google.maps.geometry.encoding.decodePath(pgp),
                fillColor: ajax_object.draw_options.fill_color,
                fillOpacity: ajax_object.draw_options.fill_opacity,
                strokeColor: ajax_object.draw_options.stroke_color,
                strokeOpacity: ajax_object.draw_options.stroke_opacity,
                strokeWeight: ajax_object.draw_options.stroke_weight,
                editable: true,
                suppressUndo: true
            });
            ph_polygon.setMap(ph_map);

            // Disable drawing so a second polygon can't be drawn
            ph_drawing_manager.setDrawingMode(null);

            ph_polygon_updated();

            google.maps.event.addListener(ph_polygon.getPath(), 'set_at', function() {
                ph_polygon_updated();
            });

            google.maps.event.addListener(ph_polygon.getPath(), 'insert_at', function() {
                ph_polygon_updated();
            });

            var path = ph_polygon.getPath();
            for ( var i = 0; i < path.length; i++ )
            {
                var xy = path.getAt(i);
                ph_map_polygon_lat_lngs.push(new google.maps.LatLng(xy.lat(), xy.lng()));
            }
            ph_fit_map_to_bounds();
        }

        google.maps.event.addListener(ph_drawing_manager, 'polygoncomplete', function(polygon) 
        {
            // Disable drawing so a second polygon can't be drawn
            ph_drawing_manager.setDrawingMode(null);

            polygon.setEditable(true);

            ph_polygon = polygon;
            ph_polygon_updated();

            google.maps.event.addListener(ph_polygon.getPath(), 'set_at', function() {
                ph_polygon_updated();
            });

            google.maps.event.addListener(ph_polygon.getPath(), 'insert_at', function() {
                ph_polygon_updated();
            });
        });
    }
}

function ph_polygon_updated()
{
    if ( draw_mode )
    {
        if ( ph_polygon )
        {
            // Set 'View Properties' link
            // Add new 'pgp' parameter
            new_location = jQuery('#ph_draw_a_search_view').attr('href');
            if ( new_location.indexOf('?') != -1 )
            {
                new_location = new_location + '&';
            }
            else
            {
                new_location = new_location + '?';
            }
            new_location = new_location + 'pgp=' + encodeURIComponent( google.maps.geometry.encoding.encodePath(ph_polygon.getPath()) );

            jQuery('#ph_draw_a_search_view')
                .attr('disabled', false)
                .attr('href', new_location);

        }
        else
        {
            // Disable 'View Properties' link
            jQuery('#ph_draw_a_search_view').attr('disabled', 'disabled');
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

function ph_polygon_clear()
{
    if ( ph_polygon ) { 
        ph_polygon.setEditable(false); 
        ph_polygon.setMap(null);
        ph_polygon = false;
    }
    
    ph_drawing_manager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);

    ph_polygon_updated();
}

function ph_get_map_properties()
{
    ph_doing_fetch = true;

    jQuery('.propertyhive-map-canvas-wrapper .map-loading').fadeIn('fast');

    // clear any exising markers
    for ( var i in ph_map_markers )
    {
        ph_map_markers[i].setMap(null);
    }
    for ( var i in ph_map_popups )
    {
        ph_map_popups[i].remove();
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

            data.ne_lat = ne.lat();
            data.ne_lng = ne.lng();
            data.sw_lat = sw.lat();
            data.sw_lng = sw.lng();
        }
    }

    if ( typeof map_property_query != 'undefined' && map_property_query != '' )
    {
        data.map_property_query = map_property_query;
    }

    jQuery.post(ajax_object.ajax_url, data, function( data ) 
    {
        jQuery('.propertyhive-map-canvas-wrapper .map-over-limit').hide();

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
                var markerCluster = new MarkerClusterer(ph_map, ph_map_markers, { imagePath: marker_clustering_assets_path });
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
    if ( existing_property_ids_with_this_lat_lng !== false )
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
    {
        var myLatlng = new google.maps.LatLng( parseFloat(property.latitude), parseFloat(property.longitude) );

        var markerOptions = {
            map: ph_map,
            position: myLatlng,
            title: ph_map_decode_html(property.address)
        };

        if ( typeof icon_type != 'undefined' && icon_type != '' )
        {
            switch (icon_type)
            {
                case "custom_single":
                {
                    if ( typeof marker_icon != 'undefined' && marker_icon != '' )
                    {
                        var ph_map_icon = {
                            url: marker_icon
                        }
                        if ( marker_icon_anchor == 'center' && typeof marker_icon_anchor_left !== 'undefined' && typeof marker_icon_anchor_top !== 'undefined' )
                        {
                            ph_map_icon.anchor = new google.maps.Point(marker_icon_anchor_left, marker_icon_anchor_top);
                        }
                        markerOptions.icon = ph_map_icon;
                    }
                    break;
                }
                case "custom_per_department":
                {
                    if ( property.department == 'residential-sales' && typeof marker_icon_residential_sales != 'undefined' )
                    {
                        var ph_map_icon = {
                            url: marker_icon_residential_sales
                        }
                        if ( marker_icon_anchor == 'center' && typeof marker_icon_anchor_left !== 'undefined' && typeof marker_icon_anchor_top !== 'undefined' )
                        {
                            ph_map_icon.anchor = new google.maps.Point(marker_icon_anchor_left, marker_icon_anchor_top);
                        }
                        markerOptions.icon = ph_map_icon;
                    }
                    if ( property.department == 'residential-lettings' && typeof marker_icon_residential_lettings != 'undefined' )
                    {
                        var ph_map_icon = {
                            url: marker_icon_residential_lettings
                        }
                        if ( marker_icon_anchor == 'center' && typeof marker_icon_anchor_left !== 'undefined' && typeof marker_icon_anchor_top !== 'undefined' )
                        {
                            ph_map_icon.anchor = new google.maps.Point(marker_icon_anchor_left, marker_icon_anchor_top);
                        }
                        markerOptions.icon = ph_map_icon;
                    }
                    if ( property.department == 'commercial' && typeof marker_icon_commercial != 'undefined' )
                    {
                        var ph_map_icon = {
                            url: marker_icon_commercial
                        }
                        if ( marker_icon_anchor == 'center' && typeof marker_icon_anchor_left !== 'undefined' && typeof marker_icon_anchor_top !== 'undefined' )
                        {
                            ph_map_icon.anchor = new google.maps.Point(marker_icon_anchor_left, marker_icon_anchor_top);
                        }
                        markerOptions.icon = ph_map_icon;
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
                        var ph_map_icon = {
                            url: marker_icons[taxonomy_id]
                        }
                        if ( marker_icon_anchor == 'center' && typeof marker_icon_anchor_left !== 'undefined' && typeof marker_icon_anchor_top !== 'undefined' )
                        {
                            ph_map_icon.anchor = new google.maps.Point(marker_icon_anchor_left, marker_icon_anchor_top);
                        }
                        markerOptions.icon = ph_map_icon;
                    }
                    break;
                }
            }
        }

        var marker = new google.maps.Marker(markerOptions);

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

        var properties_html = '';
        properties_html += '<div class="title-close"><div class="close"><a href="" onclick="closePropertyInfoWindows(); return false;">X</a></div><div style="clear:both"></div></div>';
        properties_html += '<div class="properties">' + property_list_html + '</div>';

        var properties_info_window = new CustomMarker(myLatlng, ph_map, { html: properties_html });
        ph_map_popups[property.id] = properties_info_window;

        google.maps.event.addListener(marker, "click", function(event) {
            properties_info_window.show();
            ph_map.panTo(marker.getPosition());
            ph_map.set('scrollwheel', false);
        });

        ph_map_markers[property.id] = marker;
        ph_map_lat_lngs.push(marker.getPosition());

        return marker;
    }
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
        ph_map.setCenter(myLatlng); */
    }
    else
    {
        var bounds = new google.maps.LatLngBounds();
        if ( ph_map_lat_lngs.length > 0 || ph_map_polygon_lat_lngs.length > 0 ) 
        {
            if ( ph_map_lat_lngs.length > 0 )
            {
                for ( var i = 0; i < ph_map_lat_lngs.length; i++ ) 
                {
                    bounds.extend(ph_map_lat_lngs[i]);
                }
            }

            if ( ph_map_polygon_lat_lngs.length > 0 )
            {
                for ( var i = 0; i < ph_map_polygon_lat_lngs.length; i++ ) 
                {
                    bounds.extend(ph_map_polygon_lat_lngs[i]);
                }
            }

            ph_map.fitBounds(bounds);
        }

        if ( default_zoom_level != false )
        {
            google.maps.event.addListenerOnce(ph_map, "idle", function() { 
                ph_map.setZoom(default_zoom_level); 
            });
        }
        if ( override_center != false )
        {
            google.maps.event.addListenerOnce(ph_map, "idle", function() { 
                var myLatlng = new google.maps.LatLng(map_center_lat, map_center_lng);
                ph_map.setCenter(myLatlng); 
            });
        }
    }

    setTimeout(function() { ph_doing_fetch = false; ph_done_initial_fit = true; }, 1000);
}

google.maps.event.addDomListener(window, "load", propertyhive_init_map);

function closePropertyInfoWindows()
{
    for (var i in ph_map_popups)
    {
        ph_map_popups[i].hide();
    }
    ph_map.set('scrollwheel', scrollwheel);
}

function CustomMarker(latlng, map, args) {
    this.latlng = latlng;   
    this.args = args;

    this.setMap(map);   
}

CustomMarker.prototype = new google.maps.OverlayView();

CustomMarker.prototype.draw = function() {
    
    var self = this;
    
    var div = this.div;
    
    if (!div) {
    
        div = this.div = document.createElement('div');
        
        div.className = 'properties-map-popup';
        
        div.style.position = 'absolute';
        div.style.display = 'none';
        
        /*if (typeof(self.args.marker_id) !== 'undefined') {
            div.dataset.marker_id = self.args.marker_id;
        }*/

        if (typeof(self.args.html) !== 'undefined') {
            div.innerHTML = self.args.html;
        }
        
        google.maps.event.addDomListener(div, "click", function(event) {            
            google.maps.event.trigger(self, "click");
        });
        
        var panes = this.getPanes();
        panes.overlayImage.appendChild(div);
    }
    
    var point = this.getProjection().fromLatLngToDivPixel(this.latlng);
    
    if (point) {
        div.style.left = (point.x + 15) + 'px';
        div.style.top = (point.y - 35) + 'px';
    }
};

CustomMarker.prototype.show = function() {
    if (this.div) {

        closePropertyInfoWindows();

        this.div.style.display = 'block';
    }   
};

CustomMarker.prototype.hide = function() {
    if (this.div) {
        this.div.style.display = 'none';
    }   
};

CustomMarker.prototype.remove = function() {
    if (this.div) {
        this.div.parentNode.removeChild(this.div);
        this.div = null;
    }   
};

CustomMarker.prototype.getPosition = function() {
    return this.latlng; 
};