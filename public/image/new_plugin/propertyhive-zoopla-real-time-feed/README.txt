=== PropertyHive Zoopla Real-Time Feed ===
Contributors: PropertyHive,BIOSTALL
Tags: real time, realtime, property hive, propertyhive, property portal, zoopla
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 5.8.2
Stable tag: trunk
Version: 1.0.22
Homepage: http://wp-property-hive.com/addons/zoopla-real-time-feed/

This add on for Property Hive automatically sends feeds to Zoopla in real-time.

== Description ==

This add on for Property Hive automatically sends feeds to Zoopla in real-time.

== Installation ==

= Special Requirements =

This plugin requires cURL. You'll also be required to create a private key and CSR on your server so please ensure you have access to do this. If you don't have the ability to do this please get in touch with info@wp-property-hive.com and we can do this on your behalf.

= Manual installation =

The manual installation method involves downloading the Property Hive Zoopla Real-Time Add-on plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

Once installed and activated, you can access the settings for this add on by navigating to 'Property Hive > Settings > Zoopla RTDF' from within WordPress.

= Updating =

Updating should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.0.22 =
* Added support for custom departments
* Added 'Logs' link next to portal checkbox on property record allowing quick access to all logs for a particular property to assist with debugging
* Added new setting to only send requests if property data has changed, and save sha1 hash of request data
* Declared support for WordPress 5.8.2

= 1.0.21 =
* Include PDF floorplans
* Declared support for WordPress 5.7.2

= 1.0.20 =
* Added filter 'ph_zoopla_rtdf_perform_request' to disable requests. Useful when add on installed on staging site
* Declared support for WordPress 5.7.1

= 1.0.19 =
* When a commercial property has a from and to price, ensure the lower is sent with a price qualifier of 'from'
* Corrected Marketing Status on property list not filtering accordingly

= 1.0.18 =
* Only send media captions when present when media stored as URLs. Sending blank captions caused validation issues
* Added new 'propertyhive_zoopla_realtime_feed_enquiry_imported' action
* Run request data through function to replace dodgy apostrophes and dashes that might cause requests to fail or formatting issues
* Declare support for WordPress 5.6.1

= 1.0.17 =
* Correct issue with availabilities not being added to overseas feeds
* Added warning if feed in test mode alerting the user to the fact no properties will actually be sent
* Added documentation link to plugins page
* Declare support for WordPress 5.5.3

= 1.0.16 =
* Added ability to import leads from Zoopla (requires the SSH2 PHP library to be installed)
* Declare support for WordPress 5.4.2

= 1.0.15 =
* Trim empty line breaks from end of descriptions to prevent properties being rejected by Zoopla because of this
* Declare support for WordPress 5.4.1

= 1.0.14 =
* Further tweak to recent commercial area change
* Corrected undefined variable error

= 1.0.13 =
* Send area for commercial properties when necessary
* Limit logs to 250 entries to prevent memory limits being hit on settings page

= 1.0.12 =
* Corrected issue with commercial sales properties being sent with a rent frequency and therefore being rejected
* Added support for images and other media being stored as URLs
* Declare support for WordPress 5.4

= 1.0.11 =
* Corrected issue with properties not getting sent as PPPW
* Declare support for WordPress 5.3.2

= 1.0.10 =
* Send floor area for commercial properties
* Corrected issue with wrong frequency being sent for commercial properties
* Send price_per_unit_area when applicable for commercial properties
* Declare support for WordPress 5.2.3

= 1.0.9 =
* Corrected issue with furnished not being sent for lettings properties

= 1.0.8 =
* Take into account exclusivity on another portal when deciding to send
* Changed cron to run twicedaily instead of daily
* Declare support for WordPress 5.2.2

= 1.0.7 =
* Don't include coordinates node if no lat/lng present to prevent it getting sent blank and causing an error

= 1.0.6 =
* Send different fees from settings area based on department
* Added a couple of filters around querying properties: ph_zoopla_rtdf_push_all_query_args and ph_zoopla_rtdf_send_query_args
* Declare support for WordPress 5.2.1

= 1.0.5 =
* Added 'Push All Properties' button
* Corrected issue with CRT getting wrong extension

= 1.0.4 =
* Ensure 'furnished_state' isn't sent for commercial properties

= 1.0.3 =
* Only include 'furnished_state' for lettings properties
* Declare support for WordPress 5.2

= 1.0.2 =
* Further improved support for commercial properties

= 1.0.1 =
* Added improved support for commercial properties

= 1.0.0 =
* First working release of the add on