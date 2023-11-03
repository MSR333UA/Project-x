=== PropertyHive Map Search ===
Contributors: PropertyHive,BIOSTALL
Tags: propertyhive, property hive, property, real estate, software, estate agents, estate agent, property management
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 5.9.3
Stable tag: trunk
Version: 1.1.34
Homepage: http://wp-property-hive.com/addons/map-search/

This add on for Property Hive adds map view to your websites property search results.

== Description ==

This add on for Property Hive adds map view to your websites property search results.

== Installation ==

= Special Requirements =

Please ensure you are using version 1.0.24 of Property Hive or later for full compatibility

= Manual installation =

The manual installation method involves downloading the Property Hive Map Search Add-on plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

Once installed and activated, you can access the settings for this add on by navigating to 'PropertyHive > Settings > Map Search' from within Wordpress.

= Updating =

Updating should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.1.34 =
* Swap out deprecated GEOMFROMTEXT function for ST_GEOMFROMTEXT in draw-a-search functionality
* Declared support for WordPress 5.9.3

= 1.1.33 =
* Center map on the marker clicked to ensure infowindow isn't off screen
* If a location has been entered whilst starting to draw then center on that location
* Declared support for WordPress 5.9.2

= 1.1.32 =
* Added filter 'propertyhive_map_search_draw_options' to change polygon styling in draw-a-search feature
* Declared support for WordPress 5.8.2

= 1.1.31 =
* Allowed for different icons to be shown based on a property's availability
* Querystring used to change views changed to use ? or & accordingly based on whether the URL already has a ? in it
* Declared support for WordPress 5.8.1

= 1.1.30 =
* Correct issue with marker clustering not working following recent half and half map change
* Declared support for WordPress 5.7.1

= 1.1.29 =
* Added new option to show results and map half and half on same page

= 1.1.28 =
* Added new option to show Transit Layer on Google map showing underground lines etc
* Moved draw-a-search to main settings area to be more prominent

= 1.1.27 =
* Make the views toggle a template (map-search-results-views.php) so it can be copied into the theme and customised
* Declared support for WordPress 5.7

= 1.1.26 =
* Added fix for when marker anchor position set, but top and left undefined, maybe in the case where a non-image marker icon was uploaded, or the size couldn't be obtained
* Declared support for WordPress 5.6

= 1.1.25 =
* Added new option to limit number of markers shown on the map. Useful for sites with thousands of properties where loading so many markers onto a map would be slow and unusable. If the limit is reached a message will be shown informing the user
* Added new option to only load markers within the bounds of the map viewing viewed. Again, primarily aimed at larger sites with thousands of properties
* Organise settings area into sections
* Declared support for WordPress 5.5.3

= 1.1.24 =
* Support for custom map markers on map search when OpenStreetMap is the map provider

= 1.1.23 =
* Added support for OpenStreetMap setting coming soon to core Property Hive plugin
* Added new setting allowing you choose the anchor position of markers when using custom icon. Useful when using a circle marker icon where the center of the icon should be the position on the map
* Declared support for WordPress 5.5.1

= 1.1.22 =
* Added settings link to plugins page
* Draw-A-Search links generated to handle multiselect filters where arrays are passed around in querystrings
* Turn Google Maps params into filterable array so third parties can hook in
* Declared support for WordPress 5.5

= 1.1.21 =
* Removed any unwanted additional ampersands and question marks from URLs when changing views
* Moved posts_where filter later in execution when polygon has been drawn to support SEO friendly URLs
* Declared support for WordPress 5.4.1

= 1.1.20 =
* Added ability to pass a 'href' through to views when using the 'propertyhive_results_views' filter to override default functionality of just appending view
* Corrected issue with characters being encoded in marker title
* Declared support for WordPress 5.3.2

= 1.1.19 =
* Added support for a few attributes to be passed through if loading the map via the shortcode. This includes department, office_id, negotiator_id, availability_id and property_type_id
* Declared support for WordPress 5.3

= 1.1.18 =
* Corrected issue with default zoom and center when polygon drawn
* Declared support for WordPress 5.2.2

= 1.1.17 =
* Added new option of having different marker icons per department

= 1.1.16 =
* Added price qualifier to infowindow popup
* Refined draw-a-search controls shown including new 'Exit' button
* Added filter 'propertyhive_map_property_json' to modify returned JSON
* Added filters 'propertyhive_map_search_ajax_query_args' and 'propertyhive_map_search_shortcode_atts' to give more control over custom attributes and query modification
* Declared support for WordPress 5.2

= 1.1.15 =
* Added ability for infowindow HTML to be overwritten through use of a new filter 'propertyhive_map_infowindow_html'
* Added clear:both to map div to prevent common issue with it overlapping/not showing
* Disabled scrollwheel zoom on map when an infowindow is open. If an infowindow was scrollable it would conflict
* Removed adding of hidden fields (view and pgp) as this is now handled by main plugin
* Declared support for WordPress 4.9.8

= 1.1.14 =
* Added ability to pass in center_lat and center_lng attributes into shortcode. Useful for when displaying map on area guides, for example
* Declared support for WordPress 4.9.6

= 1.1.13 =
* Added two new settings to override the default center and zoom level. By default it will still automatically center and zoom to accomodate all of the markers
* Fixed issue when editing a drawn shape would break. Caused by no URL encoding when encoded polygon contained certain characters
* Declared support for WordPress 4.9.2

= 1.1.12 =
* Added support for individual property type marker icons in commercial department

= 1.1.11 =
* Cater for multiple properties with same lat/lng. Will now show them in the same infowindow
* Fix availability not showing in infowindow following optimisations in previous release
* Declared support for WordPress 4.8.2

= 1.1.10 =
* Added new 'Loading' overlay shown whilst AJAX request is being made to obtain properties
* Added new 'Disable Scrollwheel Zooming' options to settings
* Optimisation to property query. Approximate 20-30% quicker. Especially noticeable when displaying lots of properties
* Declared support for WordPress 4.8

= 1.1.9 =
* Added new 'Enable Marker Clustering' option to automatically group properties in close proximity
* Added the property availability to the map infowindow shown when you click on a marker

= 1.1.8 =
* Ensure the add on works when SEO-friendly search results URL's are enabled

= 1.1.7 =
* Move posts_where filter so list view gets effected too if there is a polygon set
* Declared support for WordPress 4.7.5

= 1.1.6 =
* Fix 'View Properties' URL catering for when no existing query string
* Declared support for WordPress 4.7.4

= 1.1.5 =
* Added attribute to shortcode for scrollwheel
* Declared support for WordPress 4.7.2

= 1.1.4 =
* Check for valid [license key](https://wp-property-hive.com/product/12-month-license-key/) before performing future updates
* Declared support for WordPress 4.7.1

= 1.1.3 =
* Don't hardcode URL on view switcher links in the event a site is using bespoke URLs (i.e /sales/ and /lettings/)
* Correction to SQL query generated by draw-a-search to not throw error in the event a property doesn't have a lat/lng set
* Enqueue map CSS and JS inside output buffering to prevent it coming out in unexpected placed (i.e. in sitemaps)

= 1.1.2 =
* Corrected PHP undefined indexes errors on activation
* Declared support for WordPress 4.7

= 1.1.1 =
* Set datatype parameter on AJAX request to assist servers that can't work out the datatype for itself for whatever reason

= 1.1.0 =
* Added Draw-A-Search functionality (requires MySQL version 5.6.1 or newer)

= 1.0.5 =
* Make instance of map search add on accessible to themes so actions can be overwritten

= 1.0.4 =
* Don't require query be passed to shortcode. If no query passed, just default to showing all on market properties

= 1.0.3 =
* Default map center to primary office lat/lng instead of Palo Alto
* Declared support for WordPress 4.6.1

= 1.0.2 =
* Remove API key field as it's now part of the core plugin

= 1.0.1 =
* Move map functionality to an 'init' action meaning it can be overwritten in the theme

= 1.0.0 =
* First working release of the add on