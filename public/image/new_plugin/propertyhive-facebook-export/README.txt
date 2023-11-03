=== PropertyHive Facebook Marketplace Property Export ===
Contributors: PropertyHive,BIOSTALL
Tags: blm, propertyhive, property hive, property portal, facebook, property feed, property, real estate, software, estate agents, estate agent
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 5.7.1
Stable tag: trunk
Version: 1.0.7
Homepage: http://wp-property-hive.com/addons/facebook-marketplace-property-export/

This add on for Property Hive automatically sends feeds to Facebook Marketplace.

== Description ==

This add on for Property Hive automatically sends feeds to Facebook Marketplace.

== Installation ==

= Manual installation =

The manual installation method involves downloading the Property Hive Facebook Marketplace Export Add-on plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

Once installed and activated, you can access the settings this add on by navigating to 'Property Hive > Settings > Facebook Export' from within Wordpress.

= Updating =

Updating should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.0.7 =
* Corrected parking mapping changing off-street to off_street
* Declare support for WordPress 5.7.1

= 1.0.6 =
* Added filter 'propertyhive_facebook_export_property_values' so XML produced can be modified
* Declare support for WordPress 5.6

= 1.0.5 =
* Exclude password protected properties from generated XML
* Add 'propertyhive_facebook_export_query_args' filter so query arguments can be customised
* Declare support for WordPress 5.5.3

= 1.0.4 =
* Ensure currency sent is correct

= 1.0.3 =
* Use correct country if property not in UK
* Declare support for WordPress 5.5.1

= 1.0.2 =
* Added ability to filter generated XML by availability(ies)

= 1.0.1 =
* Added fallback for city and region nodes for when certain address elements don't exist

= 1.0.0 =
* First working release of the add on