=== Property Hive Data Import ===
Contributors: PropertyHive,BIOSTALL
Tags: property hive, propertyhive
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 5.9.2
Stable tag: trunk
Version: 1.0.13
Homepage: http://wp-property-hive.com/addons/data-import/

This add on for Property Hive allows you to import contacts, viewings and more

== Description ==

This add on for Property Hive allows you to import contacts, viewings and more

== Installation ==

= Manual installation =

The manual installation method involves downloading the Property Hive Data Import Add-on plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

Once installed and activated, you can access the settings for this add on by navigating to 'Property Hive > Import Data' from within WordPress.

= Updating =

Updating should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.0.13 =
* Allowed property types and locations of same name but different parent to be imported
* Declared compatibility with WordPress 5.9.2

= 1.0.12 =
* Added support for new viewing status 'No Show'
* Declared compatibility with WordPress 5.8.2

= 1.0.11 =
* Corrected typo in new applicant match price fields causing them to not import
* Declared compatibility with WordPress 5.5.3

= 1.0.10 =
* Added ability to set relationship name on applicant import if filter 'propertyhive_always_show_applicant_relationship_name' set to true

= 1.0.9 =
* Added support for min and max match price range in applicant import

= 1.0.8 =
* Added ability to import applicant match additional fields set using the Template Assistant add on
* Corrected issue with contact related additional fields set using the Template Assistant add on from not being saved
* Declared compatibility with WordPress 5.5.1

= 1.0.7 =
* Added ability to import locations and properties with a parent
* Declared compatibility with WordPress 5.4.2

= 1.0.6 =
* Added ability to import custom field taxonomies/terms
* Declared compatibility with WordPress 5.4.1

= 1.0.5 =
* Added ability to import owners/landlords
* Added ability to import viewings

= 1.0.4 =
* Added ability to import third party contacts

= 1.0.3 =
* Corrected issue with multiple blank applicant profiles being created if running an import where some contacts already exist

= 1.0.2 =
* Try to create ph_data_import uploads folder and show warning if can't

= 1.0.1 =
* Ouput errors nicer rather than just dumping them on the screen
* Cater for when no department set on applicants. Default it to the primary department
* Cater for nested locations
* Declared compatibility with WordPress 5.2.2

= 1.0.0 =
* First working release of the add on