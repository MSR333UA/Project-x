=== PropertyHive Infinite Scroll ===
Contributors: PropertyHive,BIOSTALL
Tags: propertyhive, property hive, property, real estate, estate agents, estate agent, infinite scroll
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 5.9.3
Stable tag: trunk
Version: 1.0.10
Homepage: http://wp-property-hive.com/addons/infinite-scroll/

This add on for Property Hive adds infinite scroll functionality to the search page.

== Description ==

This add on for Property Hive adds infinite scroll functionality to the search page.

== Installation ==

= Manual installation =

The manual installation method involves downloading the Property Hive Infinite Scroll Add-on plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

Once installed and activated, you can access the settings for this add on by navigating to 'PropertyHive > Settings > Infinite Scroll' from within Wordpress.

= Updating =

Updating should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.0.10 =
* Set $_GET parameters as well as $_REQUEST when loading properties
* Added JS event 'ph:infinite_scroll_loading_properties'
* Added JS event 'ph:infinite_scroll_loaded_properties'
* Declared support for WordPress 5.9.3

= 1.0.9 =
* Ensure only published properties are returned when additional properties loaded
* Declared support for WordPress 5.7.2

= 1.0.8 =
* Support for jQuery changes in WordPress 5.6
* Declared support for WordPress 5.5.3

= 1.0.7 =
* Make compatible with radial search add on by ensuring $_GET params are maintained when more properties are loaded
* Declared support for WordPress 5.5.1

= 1.0.6 =
* Correct password protected properties from appearing in loaded results
* Ensure infinite scroll still works when SEO friendly search URL's being used
* Trigger a resize and scroll when properties are loaded
* Added settings link to plugins page
* Declared support for WordPress 5.5

= 1.0.5 =
* Corrected issue when using custom search URLs (i.e. /sales/ and /lettings/) and taxonomies being duplicated when searching on property type, for example.

= 1.0.4 =
* Updated result count as more properties are loaded in
* Declared support for WordPress 4.9.8

= 1.0.3 =
* Added fix for when both Radial Search and Infinite Scroll add ons are used at same time

= 1.0.2 =
* Don't perform infinite scroll actions on map view
* Declared support for WordPress 4.9.7

= 1.0.1 =
* Corrected issue with infinite scroll not working when previous AJAX requests had been made, such as by map add on
* Declared support for WordPress 4.8.2

= 1.0.0 =
* First working release of the add on