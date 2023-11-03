=== PropertyHive Printable Brochures ===
Contributors: PropertyHive,BIOSTALL
Tags: propertyhive, property hive, property, real estate, software, estate agents, estate agent, property management, property particular, property brochures, print property
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 5.8.3
Stable tag: trunk
Version: 1.0.14
Homepage: http://wp-property-hive.com/addons/printable-brochures/

This add on for Property Hive adds the ability for your users to get a print-friendly version of the property details.

== Description ==

This add on for Property Hive adds the ability for your users to get a print-friendly version of the property details.

== Installation ==

= Special Requirements =

If wanting to output the details in PDF format you will need the wkhtmltopdf package installed on the server. We recommend you speak to your server providers about getting this setup. Note that on shared servers this might not always be possible.

= Manual installation =

The manual installation method involves downloading the Property Hive Printable Brochures Add-on plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

Once installed and activated, you can access the settings for this add on by navigating to 'PropertyHive > Settings > Printable Brochures' from within Wordpress.

= Updating =

Updating should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.0.14 =
* Added new 'propertyhive_printable_brochures_dompdf_strip_html_whitespace' filter
* Moved position of new debugging to give better representation of generated HTML

= 1.0.13 =
* Allowed 'debug' parameter to be passed in querystring to quickly debug HTML
* Strip whitespace between tags in generated HTML output as it was causing a few issues with domPDF and blank pages
* Declared support for WordPress 5.8.2

= 1.0.12 =
* Added ability to add print button to Elementor Tabbed Details widgets
* Added support for brochures being stored as URLs
* Declared support for WordPress 5.7

= 1.0.11 =
* Reinstated $pdf variable to prevent breaking existing custom templates
* Added new filters 'propertyhive_wkhtmltopdf_options' and 'propertyhive_printable_brochures_wkhtmltopdf_options' to wkhtmltopdf options

= 1.0.10 =
* Added support for dompdf PDF library. In the settings area you can now choose HTML, PDF (wkhtmltopdf) or PDF (dompdf)
* Added ability to remove existing header image if one exists
* Declared support for WordPress 5.5.1

= 1.0.9 =
* Added a new action to print/preview the brochure from the property record in the backend
* Declared support for WordPress 5.2.1

= 1.0.8 =
* Added a whole host of new PDF options including page margins and wkhtmltopdf binary path
* Declared support for WordPress 5.0.2

= 1.0.7 =
* If no full description is present then display full excerpt instead of truncating it
* Appended Google Maps API key to static map URLs
* Declared support for WordPress 4.9.8

= 1.0.6 =
* Added options for setting of orientation and paper size
* Declared support for WordPress 4.7.2

= 1.0.5 =
* Correct full descriptions not showing for commercial properties
* Correct 'Use X Environment?' default checked status not being set

= 1.0.4 =
* Check for valid [license key](https://wp-property-hive.com/product/12-month-license-key/) before performing future updates
* Declared support for WordPress 4.7.1

= 1.0.3 =
* Header in printout uses originally uploaded image, as opposed to large version which had potential to be cropped

= 1.0.2 =
* Add EPC graphs to both layouts if present
* Declare compatibility for WordPress 4.7

= 1.0.1 =
* Include custom print.php differently
* Declare compatibility for WordPress 4.6.1

= 1.0.0 =
* First working release of the add on