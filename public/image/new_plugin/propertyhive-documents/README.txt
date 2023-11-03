=== PropertyHive Documents ===
Contributors: PropertyHive,BIOSTALL
Tags: documents, propertyhive, property hive
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 5.7
Stable tag: trunk
Version: 1.0.8
Homepage: http://wp-property-hive.com/addons/documents/

This add on for Property Hive generates .docx documents with merged tags.

== Description ==

This add on for Property Hive generates .docx documents with merged tags.

== Installation ==

= Special Requirements =

This add on uses the PHPWord library (https://github.com/PHPOffice/PHPWord). Please check the list of requirements for this library to ensure you meet them if encountering issues.

= Manual installation =

The manual installation method involves downloading the Property Hive Documents Add-on plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

Once installed and activated, you can access the settings this add on by navigating to 'Property Hive > Settings > Documents' from within Wordpress.

= Updating =

Updating should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.0.8 =
* Added support for new 'Dear' field on contact record
* Added ability to create tenancy documents
* Corrected issue with ampersands in names corrupting documents
* Corrected undefined PHP error when copying first template to library
* Declared support for WordPress 5.7

= 1.0.7 =
* Added support for appraisal tags so valuation letters can be generated
* Added event date/time based tags to viewing (e.g. viewing_start_date)

= 1.0.6 =
* Added new company_name tag for use in document templates
* Correct issue with ampersands in contact addresses corrupting documents
* Declared support for WordPress 5.5.1

= 1.0.5 =
* Added timestamp to generated filenames to avoid issues with caching
* Declared support for WordPress 5.5

= 1.0.4 =
* Corrected issue with £ symbol in property formatted price causing corrupt document
* Added new filter for third party plugin to add tags
* Declared support for WordPress 5.4.2

= 1.0.3 =
* Put created/uploaded documents into separate uploads/ph_documents folder
* Added .htaccess rule to prevent public accessing documents, unless 'public' tickbox is ticked
* Physically delete attachment media file when document deleted
* Open documents in new window/tab when clicked

= 1.0.2 =
* Corrected issue with owner tags not pulling through
* Corrected issue with default post types on blank owner letter template being wrong

= 1.0.1 =
* Removed importing default templates when installing add on for first time. Instead offer sample templates that user can add to their template library
* Added ability to select which post type(s) templates are relevant too. Then only show the applicable templates depending on post type of the record you're in
* Allowed batch delete of templates
* Added template tag dictionary to add/edit template screen as probably more useful there

= 1.0.0 =
* First working release of the add on